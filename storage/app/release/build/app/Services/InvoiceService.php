<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Job;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(private PricingService $pricing)
    {
    }

    public function generate(int $jobId, ?int $discountId = null): Invoice
    {
        return DB::transaction(function () use ($jobId, $discountId) {
            $job = Job::with(['customer', 'vehicle', 'branch'])->findOrFail($jobId);

            if ($job->invoice) {
                return $job->invoice;
            }

            $calc = $this->pricing->calculateFinalInvoice($jobId, $discountId);

            $invoice = Invoice::create([
                'business_id' => $job->business_id,
                'branch_id' => $job->branch_id,
                'customer_id' => $job->customer_id,
                'job_id' => $job->id,
                'invoice_number' => 'INV-' . date('Y') . '-' . str_pad(
                    (string) (Invoice::whereYear('created_at', date('Y'))->count() + 1),
                    6,
                    '0',
                    STR_PAD_LEFT
                ),
                'status' => 'issued',
                'subtotal' => $calc['subtotal'],
                'discount' => $calc['discount'] + $calc['membership_discount'],
                'tax' => $calc['tax'],
                'total' => $calc['total'],
                'paid' => 0,
                'balance' => $calc['total'],
            ]);

            foreach ($calc['breakdown']['services'] as $service) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'service',
                    'item_id' => $service['id'],
                    'description' => $service['name'],
                    'quantity' => $service['quantity'],
                    'unit_price' => $service['unit_price'],
                    'discount' => $service['discount'],
                    'tax' => $service['tax'],
                    'line_total' => $service['line_total'],
                ]);
            }

            foreach ($calc['breakdown']['parts'] as $part) {
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
}
