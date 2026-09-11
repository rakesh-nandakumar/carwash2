<?php

namespace App\Http\Controllers;

use App\Models\{Invoice, Payment};
use App\Services\CashMovementService;
use App\Services\AuditService;
use App\Enums\JobStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function __construct(private readonly AuditService $auditService)
    {
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->get('search');

        $query = Invoice::with('customer')
            ->where('tenant_id', $user->tenant_id)
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->paginate(20);

        return view('invoices.index', compact('invoices', 'search'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('customer', 'job.vehicle', 'items', 'payments');

        return view('invoices.show', compact('invoice'));
    }

    public function printInvoice(Invoice $invoice, $format = 'a4')
    {
        // Reload invoice from database to get latest values including discount
        $invoice = Invoice::with('customer', 'job.vehicle', 'items', 'payments')->find($invoice->id);

        // Log for debugging
        \Log::info('Print Invoice', [
            'invoice_id' => $invoice->id,
            'subtotal' => $invoice->subtotal,
            'discount' => $invoice->discount,
            'tax' => $invoice->tax,
            'total' => $invoice->total,
            'paid' => $invoice->paid,
            'balance' => $invoice->balance,
        ]);

        $currentPaymentAmount = (float) $invoice->paid;

        $currentBalance = (float) $invoice->balance;

        $returnAmount = $currentBalance < 0
            ? abs($currentBalance)
            : 0;

        $dueAmount = $currentBalance > 0
            ? $currentBalance
            : 0;

        $view = $format === 'thermal' ? 'invoices.print.thermal' : 'invoices.print.a4';

        return view($view, compact(
            'invoice',
            'currentPaymentAmount',
            'currentBalance',
            'returnAmount',
            'dueAmount'
        ));
    }

    public function pay(Request $r, Invoice $invoice, CashMovementService $cashMovements)
    {
        $d = $r->validate(['amount' => 'required|numeric|min:.01', 'method' => 'required', 'from' => 'nullable|string']);

        DB::transaction(function () use ($invoice, $d, $cashMovements) {
            // Allow overpayments - remove validation that prevented payments exceeding balance

            $oldStatus = $invoice->status;
            $oldPaid = $invoice->paid;
            $oldBalance = $invoice->balance;

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'method' => $d['method'],
                'amount' => $d['amount'],
                'received_by' => auth()->id(),
            ]);

            $invoice->paid += $d['amount'];
            $invoice->balance = $invoice->total - $invoice->paid;
            $invoice->status = $invoice->balance <= 0 ? 'paid' : 'partially_paid';
            $invoice->save();

            // Update job status when invoice is paid in full
            if ($invoice->balance <= 0 && $invoice->job) {
                $job = $invoice->job;
                if ($job->status === JobStatus::READY_FOR_PAYMENT) {
                    $job->transitionTo(JobStatus::PAID, auth()->user());
                }
            }

            $this->auditService->logPayment($payment->id, [
                'invoice_id' => $invoice->id,
                'method' => $d['method'],
                'amount' => $d['amount'],
                'received_by' => auth()->id(),
            ]);

            $this->auditService->logInvoiceModification($invoice->id, [
                'status' => $oldStatus,
                'paid' => $oldPaid,
                'balance' => $oldBalance,
            ], [
                'status' => $invoice->status,
                'paid' => $invoice->paid,
                'balance' => $invoice->balance,
            ], 'Payment recorded');

            if ($d['method'] === 'cash') {
                // Record only the net cash amount that stays in the till
                // If customer overpaid (balance < 0), record the invoice total since change is given back
                // Otherwise record the full payment amount
                $netCashAmount = $invoice->balance < 0 ? $invoice->total : (float) $d['amount'];

                $cashMovements->recordSale(
                    amount: $netCashAmount,
                    reference: $payment,
                    userId: auth()->id(),
                );
            }
        });

        // Preserve the 'from' parameter for proper back button behavior
        $redirectBack = back()->with('success', 'Payment recorded.')->with('payment_completed', true);

        // If came from notifications, ensure the session knows for proper navigation
        if ($r->input('from') === 'notifications') {
            session(['payment_from_notifications' => true]);
        }

        return $redirectBack;
    }
}