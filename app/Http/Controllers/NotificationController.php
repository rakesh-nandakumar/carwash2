<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Job;
use App\Enums\JobStatus;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        // Get partial payments (invoices with balance > 0 AND paid > 0)
        // This includes jobs that are ready for payment but have partial payments
        $partialPayments = Invoice::with(['job.customer', 'job.vehicle'])
            ->where('balance', '>', 0)
            ->where('total', '>', 0)
            ->where('paid', '>', 0) // Must have some payments already
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'type' => 'partial_payment',
                    'invoice_number' => $invoice->id,
                    'customer_name' => $invoice->job->customer->full_name,
                    'vehicle_registration' => $invoice->job->vehicle->registration_number,
                    'total_amount' => $invoice->total,
                    'paid_amount' => $invoice->paid,
                    'balance' => $invoice->balance,
                    'job_id' => $invoice->job->id,
                    'created_at' => $invoice->created_at,
                ];
            });

        // Get job IDs that have partial payments (to exclude from ready for payment)
        $partialPaymentJobIds = $partialPayments->pluck('job_id')->toArray();

        // Get pending cheques (cheques not yet received)
        $pendingCheques = Payment::with(['invoice.job.customer', 'invoice.job.vehicle'])
            ->where('method', 'cheque')
            ->where('payment_received', false)
            ->where('is_bounced', false)
            ->orderBy('cheque_due_date', 'asc')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'type' => 'pending_cheque',
                    'cheque_number' => $payment->cheque_number,
                    'bank_name' => $payment->bank_name,
                    'amount' => $payment->amount,
                    'cheque_due_date' => $payment->cheque_due_date,
                    'customer_name' => $payment->invoice->job->customer->full_name,
                    'vehicle_registration' => $payment->invoice->job->vehicle->registration_number,
                    'payment_id' => $payment->id,
                    'is_overdue' => $payment->cheque_due_date && $payment->cheque_due_date->isPast(),
                    'created_at' => $payment->created_at,
                ];
            });

        // Get bounced cheques needing follow-up
        $bouncedCheques = Payment::with(['invoice.job.customer', 'invoice.job.vehicle'])
            ->where('method', 'cheque')
            ->where('is_bounced', true)
            ->where('follow_up_required', true)
            ->where('replacement_payment_received', false)
            ->orderBy('follow_up_date', 'asc')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'type' => 'bounced_cheque',
                    'cheque_number' => $payment->cheque_number,
                    'bank_name' => $payment->bank_name,
                    'amount' => $payment->amount,
                    'bounce_reason' => $payment->bounce_reason,
                    'follow_up_date' => $payment->follow_up_date,
                    'customer_name' => $payment->invoice->job->customer->full_name,
                    'vehicle_registration' => $payment->invoice->job->vehicle->registration_number,
                    'payment_id' => $payment->id,
                    'is_overdue' => $payment->follow_up_date && $payment->follow_up_date->isPast(),
                    'created_at' => $payment->created_at,
                ];
            });

        // Get jobs ready for payment (excluding jobs that have partial payments)
        $readyForPayment = Job::with(['customer', 'vehicle', 'invoice'])
            ->where('status', JobStatus::READY_FOR_PAYMENT->value)
            ->whereNotIn('id', $partialPaymentJobIds) // Exclude jobs with partial payments
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'type' => 'ready_for_payment',
                    'job_number' => $job->job_number,
                    'customer_name' => $job->customer->full_name,
                    'vehicle_registration' => $job->vehicle->registration_number,
                    'job_id' => $job->id,
                    'created_at' => $job->updated_at,
                ];
            });

        // Combine all notifications
        $allNotifications = collect()
            ->concat($partialPayments)
            ->concat($pendingCheques)
            ->concat($bouncedCheques)
            ->concat($readyForPayment)
            ->sortByDesc('created_at')
            ->values();

        return view('notifications.index', compact(
            'partialPayments',
            'pendingCheques',
            'bouncedCheques',
            'readyForPayment',
            'allNotifications'
        ));
    }
}