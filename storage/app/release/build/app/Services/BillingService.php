<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceVersion;
use App\Models\Payment;
use App\Models\Job;
use App\Models\Refund;
use App\Models\CreditNote;
use App\Services\PricingService;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function __construct(
        private PricingService $pricingService
    ) {}

    public function createInvoice(int $jobId): Invoice
    {
        return DB::transaction(function () use ($jobId) {
            $job = Job::with(['customer', 'branch', 'business'])->findOrFail($jobId);
            
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(
                Invoice::whereYear('created_at', date('Y'))->count() + 1,
                6,
                '0',
                STR_PAD_LEFT
            );

            $calculation = $this->pricingService->calculateFinalInvoice($jobId);

            $invoice = Invoice::create([
                'business_id' => $job->business_id,
                'branch_id' => $job->branch_id,
                'customer_id' => $job->customer_id,
                'job_id' => $job->id,
                'invoice_number' => $invoiceNumber,
                'status' => 'issued',
                'subtotal' => $calculation['subtotal'],
                'discount' => $calculation['discount'] + $calculation['membership_discount'],
                'tax' => $calculation['tax'],
                'total' => $calculation['total'],
                'paid' => 0,
                'balance' => $calculation['total'],
            ]);

            // Create invoice items for services
            foreach ($calculation['breakdown']['services'] as $service) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'service',
                    'item_id' => $service['id'],
                    'description' => $service['name'],
                    'quantity' => $service['quantity'],
                    'unit_price' => $service['unit_price'],
                    'discount' => 0,
                    'tax' => $service['tax'],
                    'line_total' => $service['line_total'],
                ]);
            }

            // Create invoice items for parts
            foreach ($calculation['breakdown']['parts'] as $part) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'part',
                    'item_id' => $part['id'],
                    'description' => $part['product_name'],
                    'quantity' => $part['quantity'],
                    'unit_price' => $part['unit_price'],
                    'discount' => 0,
                    'tax' => 0,
                    'line_total' => $part['line_total'],
                ]);
            }

            return $invoice;
        });
    }

    public function updateInvoice(int $invoiceId, array $changes, string $reason): Invoice
    {
        return DB::transaction(function () use ($invoiceId, $changes, $reason) {
            $invoice = Invoice::with('items')->findOrFail($invoiceId);

            // Store old data for versioning
            $oldData = [
                'subtotal' => $invoice->subtotal,
                'discount' => $invoice->discount,
                'tax' => $invoice->tax,
                'total' => $invoice->total,
                'items' => $invoice->items->toArray(),
            ];

            // Apply changes
            if (isset($changes['subtotal'])) {
                $invoice->subtotal = $changes['subtotal'];
            }
            if (isset($changes['discount'])) {
                $invoice->discount = $changes['discount'];
            }
            if (isset($changes['tax'])) {
                $invoice->tax = $changes['tax'];
            }
            if (isset($changes['total'])) {
                $invoice->total = $changes['total'];
            }

            $invoice->balance = $invoice->total - $invoice->paid;
            $invoice->save();

            // Create version record
            $version = $invoice->versions()->count() + 1;
            InvoiceVersion::create([
                'invoice_id' => $invoice->id,
                'version' => $version,
                'old_data' => $oldData,
                'new_data' => $changes,
                'old_total' => $oldData['total'],
                'new_total' => $invoice->total,
                'changed_by' => auth()->id(),
                'reason' => $reason,
            ]);

            return $invoice;
        });
    }

    public function processPayment(int $invoiceId, float $amount, string $method, ?string $reference = null): Payment
    {
        return DB::transaction(function () use ($invoiceId, $amount, $method, $reference) {
            $invoice = Invoice::findOrFail($invoiceId);

            if ($invoice->balance < $amount) {
                abort(422, 'Payment amount exceeds balance');
            }

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'method' => $method,
                'amount' => $amount,
                'reference' => $reference,
                'received_by' => auth()->id(),
            ]);

            $invoice->paid += $amount;
            $invoice->balance -= $amount;

            if ($invoice->balance <= 0) {
                $invoice->status = 'paid';
            }

            $invoice->save();

            // Update job status if fully paid
            if ($invoice->balance <= 0) {
                $job = $invoice->job;
                if ($job && $job->status === \App\Enums\JobStatus::READY_FOR_PAYMENT) {
                    $job->transitionTo(\App\Enums\JobStatus::PAID, auth()->user());
                }
            }

            return $payment;
        });
    }

    public function createRefund(int $invoiceId, float $amount, string $method, string $reason): Refund
    {
        return DB::transaction(function () use ($invoiceId, $amount, $method, $reason) {
            $invoice = Invoice::findOrFail($invoiceId);

            if ($invoice->paid < $amount) {
                abort(422, 'Refund amount exceeds paid amount');
            }

            $refundNumber = 'REF-' . date('Y') . '-' . str_pad(
                Refund::whereYear('created_at', date('Y'))->count() + 1,
                6,
                '0',
                STR_PAD_LEFT
            );

            $refund = Refund::create([
                'refund_number' => $refundNumber,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'method' => $method,
                'reason' => $reason,
                'status' => 'pending',
                'requested_by' => auth()->id(),
            ]);

            return $refund;
        });
    }

    public function approveRefund(int $refundId, int $approvedBy): Refund
    {
        return DB::transaction(function () use ($refundId, $approvedBy) {
            $refund = Refund::with('invoice')->findOrFail($refundId);

            $refund->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            // Update invoice
            $invoice = $refund->invoice;
            $invoice->paid -= $refund->amount;
            $invoice->balance += $refund->amount;
            $invoice->save();

            return $refund;
        });
    }

    public function createCreditNote(int $invoiceId, float $amount, string $reason): CreditNote
    {
        return DB::transaction(function () use ($invoiceId, $amount, $reason) {
            $invoice = Invoice::findOrFail($invoiceId);

            $creditNoteNumber = 'CN-' . date('Y') . '-' . str_pad(
                CreditNote::whereYear('created_at', date('Y'))->count() + 1,
                6,
                '0',
                STR_PAD_LEFT
            );

            $validUntil = now()->addMonths(6);

            return CreditNote::create([
                'credit_note_number' => $creditNoteNumber,
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'amount' => $amount,
                'reason' => $reason,
                'status' => 'issued',
                'valid_until' => $validUntil,
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function applyCreditNote(int $creditNoteId, int $invoiceId): void
    {
        DB::transaction(function () use ($creditNoteId, $invoiceId) {
            $creditNote = CreditNote::findOrFail($creditNoteId);
            $invoice = Invoice::findOrFail($invoiceId);

            if ($creditNote->status !== 'issued') {
                abort(422, 'Credit note is not available');
            }

            if ($creditNote->valid_until < now()) {
                abort(422, 'Credit note has expired');
            }

            if ($creditNote->amount > $invoice->balance) {
                abort(422, 'Credit note amount exceeds invoice balance');
            }

            $invoice->balance -= $creditNote->amount;
            $invoice->paid += $creditNote->amount;

            if ($invoice->balance <= 0) {
                $invoice->status = 'paid';
            }

            $invoice->save();

            $creditNote->status = 'applied';
            $creditNote->save();
        });
    }

    public function getInvoiceHistory(int $invoiceId): array
    {
        $invoice = Invoice::with(['versions', 'payments', 'refunds', 'creditNotes'])
            ->findOrFail($invoiceId);

        return [
            'invoice' => $invoice,
            'versions' => $invoice->versions->sortByDesc('version'),
            'payments' => $invoice->payments,
            'refunds' => $invoice->refunds,
            'credit_notes' => $invoice->creditNotes,
        ];
    }
}
