<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\CashMovementService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChequePaymentController extends Controller
{
    public function __construct(
        private CashMovementService $cashMovements,
        private AuditService $audit
    ) {}

    /**
     * Display a listing of pending cheques
     */
    public function index(Request $request)
    {
        $pendingCheques = Payment::with(['invoice.job.customer', 'invoice.job.vehicle', 'receivedBy'])
            ->where('method', 'cheque')
            ->where('payment_received', false)
            ->where('is_bounced', false)
            ->orderBy('cheque_due_date', 'asc')
            ->get();

        $clearedCheques = Payment::with(['invoice.job.customer', 'invoice.job.vehicle', 'receivedBy'])
            ->where('method', 'cheque')
            ->where('payment_received', true)
            ->where('is_bounced', false)
            ->orderBy('payment_received_at', 'desc')
            ->get();

        $bouncedCheques = Payment::with(['invoice.job.customer', 'invoice.job.vehicle', 'receivedBy'])
            ->where('method', 'cheque')
            ->where('is_bounced', true)
            ->orderBy('bounced_at', 'desc')
            ->get();

        return view('cheque-payments.index', compact(
            'pendingCheques',
            'clearedCheques',
            'bouncedCheques'
        ));
    }

    /**
     * Show the form for confirming a cheque payment
     */
    public function confirm(Payment $payment)
    {
        if ($payment->method !== 'cheque') {
            return back()->with('error', 'This is not a cheque payment.');
        }

        if ($payment->payment_received || $payment->is_bounced) {
            return back()->with('error', 'This cheque has already been processed.');
        }

        $payment->load(['invoice.job.customer', 'invoice.job.vehicle']);

        return view('cheque-payments.confirm', compact('payment'));
    }

    /**
     * Process the cheque payment confirmation
     */
    public function processConfirmation(Request $request, Payment $payment)
    {
        $request->validate([
            'action' => 'required|in:confirm,bounce',
            'bounce_reason' => 'nullable|string|required_if:action,bounce',
        ]);

        if ($payment->method !== 'cheque') {
            return back()->with('error', 'This is not a cheque payment.');
        }

        if ($payment->payment_received || $payment->is_bounced) {
            return back()->with('error', 'This cheque has already been processed.');
        }

        return DB::transaction(function () use ($request, $payment) {
            if ($request->action === 'confirm') {
                // Mark cheque as received/cleared
                $payment->markAsReceived();

                // Log cheque confirmation in audit logs
                $this->audit->log('cheque_confirmed', 'Payment', $payment->id, null, [
                    'payment_id' => $payment->id,
                    'invoice_id' => $payment->invoice_id,
                    'amount' => $payment->amount,
                    'cheque_number' => $payment->cheque_number,
                    'bank_name' => $payment->bank_name,
                    'cheque_due_date' => $payment->cheque_due_date,
                ], 'Cheque payment confirmed and cleared');

                // Record the cash movement in till since payment is now received
                $invoice = $payment->invoice;
                $netAmount = $payment->amount;

                $this->cashMovements->recordSale(
                    amount: $netAmount,
                    reference: $payment,
                    userId: auth()->id(),
                );

                return redirect()
                    ->route('cheque-payments.index')
                    ->with('success', 'Cheque payment confirmed and recorded in till.');

            } elseif ($request->action === 'bounce') {
                // Mark cheque as bounced
                $payment->markAsBounced($request->bounce_reason);

                // Log cheque bounce in audit logs
                $this->audit->log('cheque_bounced', 'Payment', $payment->id, null, [
                    'payment_id' => $payment->id,
                    'invoice_id' => $payment->invoice_id,
                    'amount' => $payment->amount,
                    'cheque_number' => $payment->cheque_number,
                    'bank_name' => $payment->bank_name,
                    'bounce_reason' => $request->bounce_reason,
                ], 'Cheque bounced: ' . $request->bounce_reason);

                // Reverse the invoice payment status
                $invoice = $payment->invoice;
                $invoice->update([
                    'paid' => max(0, $invoice->paid - $payment->amount),
                    'balance' => $invoice->total - max(0, $invoice->paid - $payment->amount),
                    'status' => $invoice->balance > 0 ? 'partially_paid' : 'issued',
                ]);

                // If job was marked as paid, revert it back
                $job = $invoice->job;
                if ($job->status === 'paid') {
                    $job->update(['status' => 'ready_for_payment']);
                }

                return redirect()
                    ->route('cheque-payments.index')
                    ->with('success', 'Cheque marked as bounced. Invoice status has been updated.');
            }
        });
    }

    /**
     * Show details of a specific cheque payment
     */
    public function show(Payment $payment)
    {
        if ($payment->method !== 'cheque') {
            return back()->with('error', 'This is not a cheque payment.');
        }

        $payment->load(['invoice.job.customer', 'invoice.job.vehicle', 'receivedBy']);

        return view('cheque-payments.show', compact('payment'));
    }

    /**
     * Show the form for editing a bounced cheque (to correct if needed)
     */
    public function editBounce(Payment $payment)
    {
        if ($payment->method !== 'cheque' || !$payment->is_bounced) {
            return back()->with('error', 'This is not a bounced cheque payment.');
        }

        $payment->load(['invoice.job.customer', 'invoice.job.vehicle']);

        return view('cheque-payments.edit-bounce', compact('payment'));
    }

    /**
     * Update a bounced cheque (e.g., if it was incorrectly marked as bounced)
     */
    public function updateBounce(Request $request, Payment $payment)
    {
        $request->validate([
            'action' => 'required|in:reverse,update_reason,mark_followup_complete',
            'bounce_reason' => 'nullable|string',
            'follow_up_notes' => 'nullable|string',
        ]);

        if ($payment->method !== 'cheque' || !$payment->is_bounced) {
            return back()->with('error', 'This is not a bounced cheque payment.');
        }

        return DB::transaction(function () use ($request, $payment) {
            if ($request->action === 'reverse') {
                // Reverse the bounce - mark as pending again
                $payment->update([
                    'is_bounced' => false,
                    'bounced_at' => null,
                    'bounce_reason' => null,
                    'payment_received' => false,
                    'payment_received_at' => null,
                    'follow_up_required' => false,
                    'follow_up_date' => null,
                ]);

                // Restore the invoice payment status
                $invoice = $payment->invoice;
                $invoice->update([
                    'paid' => $invoice->paid + $payment->amount,
                    'balance' => max(0, $invoice->total - ($invoice->paid + $payment->amount)),
                    'status' => $invoice->balance <= 0 ? 'paid' : 'partially_paid',
                ]);

                return redirect()
                    ->route('cheque-payments.index')
                    ->with('success', 'Bounce status reversed. Cheque is now pending again.');

            } elseif ($request->action === 'update_reason') {
                // Just update the bounce reason
                $payment->update([
                    'bounce_reason' => $request->bounce_reason,
                ]);

                return redirect()
                    ->route('cheque-payments.show', $payment)
                    ->with('success', 'Bounce reason updated successfully.');

            } elseif ($request->action === 'mark_followup_complete') {
                // Mark follow-up as complete (customer has been contacted)
                $payment->update([
                    'follow_up_required' => false,
                    'follow_up_date' => null,
                    'follow_up_notes' => $request->follow_up_notes,
                ]);

                return redirect()
                    ->route('cheque-payments.show', $payment)
                    ->with('success', 'Follow-up marked as complete. Customer has been contacted.');
            }
        });
    }

    /**
     * Show form to record replacement payment for bounced cheque
     */
    public function showReplacementForm(Payment $payment)
    {
        if ($payment->method !== 'cheque' || !$payment->is_bounced) {
            return back()->with('error', 'This is not a bounced cheque payment.');
        }

        if ($payment->replacement_payment_received) {
            return back()->with('error', 'A replacement payment has already been recorded for this cheque.');
        }

        $payment->load(['invoice.job.customer', 'invoice.job.vehicle']);

        return view('cheque-payments.replacement', compact('payment'));
    }

    /**
     * Process replacement payment for bounced cheque
     */
    public function processReplacement(Request $request, Payment $payment)
    {
        $request->validate([
            'replacement_payment_method' => 'required|string|in:cash,card,upi,bank_transfer,cheque',
            'replacement_amount' => 'required|numeric|min:0',
            'replacement_reference' => 'nullable|string',
            'notes' => 'nullable|string',
            'cheque_number' => 'required_if:replacement_payment_method,cheque|nullable|string',
            'bank_name' => 'required_if:replacement_payment_method,cheque|nullable|string',
            'cheque_due_date' => 'required_if:replacement_payment_method,cheque|nullable|date',
        ]);

        if ($payment->method !== 'cheque' || !$payment->is_bounced) {
            return back()->with('error', 'This is not a bounced cheque payment.');
        }

        if ($payment->replacement_payment_received) {
            return back()->with('error', 'A replacement payment has already been recorded for this cheque.');
        }

        return DB::transaction(function () use ($request, $payment) {
            // Determine if replacement should be marked as received immediately
            // Only non-cheque payments are immediately received
            $isImmediatePayment = $request->replacement_payment_method !== 'cheque';
            
            // Prepare replacement payment data
            $replacementData = [
                'invoice_id' => $payment->invoice_id,
                'method' => $request->replacement_payment_method,
                'amount' => $request->replacement_amount,
                'reference' => $request->replacement_reference,
                'received_by' => auth()->id(),
                'payment_received' => $isImmediatePayment,
                'payment_received_at' => $isImmediatePayment ? now() : null,
                'is_bounced' => false,
            ];

            // Add cheque-specific fields if payment method is cheque
            if ($request->replacement_payment_method === 'cheque') {
                $replacementData['cheque_number'] = $request->cheque_number;
                $replacementData['bank_name'] = $request->bank_name;
                $replacementData['cheque_due_date'] = $request->cheque_due_date;
            }

            // Create replacement payment
            $replacementPayment = Payment::create($replacementData);

            // Log replacement payment in audit logs
            $this->audit->logPayment($replacementPayment->id, [
                'invoice_id' => $invoice->id,
                'original_payment_id' => $payment->id,
                'payment_method' => $request->replacement_payment_method,
                'amount' => $replacementData['amount'],
                'reason' => 'Replacement for bounced cheque',
                'cheque_number' => $request->cheque_number,
                'bank_name' => $request->bank_name,
            ]);

            // Link original bounced cheque to replacement payment
            $payment->update([
                'replacement_payment_received' => true,
                'replacement_payment_id' => $replacementPayment->id,
                'follow_up_required' => false,
            ]);

            // Update invoice with replacement payment
            $invoice = $payment->invoice;
            $invoice->update([
                'paid' => $invoice->paid + $replacementPayment->amount,
                'balance' => max(0, $invoice->total - ($invoice->paid + $replacementPayment->amount)),
                'status' => $invoice->balance <= 0 ? 'paid' : 'partially_paid',
            ]);

            // Record in till if it's a cash replacement or immediately received payment
            if ($isImmediatePayment) {
                $this->cashMovements->recordSale(
                    amount: $replacementPayment->amount,
                    reference: $replacementPayment,
                    userId: auth()->id(),
                );
            }

            $message = 'Replacement payment of Rs. ' . number_format($replacementPayment->amount, 2) . ' recorded successfully via ' . ucfirst($request->replacement_payment_method);
            
            if ($request->replacement_payment_method === 'cheque') {
                $message .= '. The new cheque has been added to pending cheques and will need to be confirmed when cleared.';
            }

            return redirect()
                ->route('cheque-payments.show', $payment)
                ->with('success', $message);
        });
    }
}
