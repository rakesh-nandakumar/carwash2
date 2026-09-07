<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Payment;
use App\Enums\JobStatus;
use App\Services\PricingService;
use App\Services\CashMovementService;
use App\Services\CommunicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function __construct(
        private PricingService $pricing,
        private CashMovementService $cashMovements,
        private CommunicationService $communication
    ) {}

    public function index()
    {
        $readyForPayment = Job::with([
            'customer',
            'vehicle',
            'invoice'
        ])
            ->where(
                'status',
                JobStatus::READY_FOR_PAYMENT->value
            )
            ->orderBy('updated_at', 'desc')
            ->get();

        $till = $this->cashMovements->getSelectedTill();
        
        // Update last activity timestamp for the selected till
        if ($till && $till->current_user_id == auth()->id()) {
            $till->update(['last_activity_at' => now()]);
        }
        
        $currentClosure = $this->cashMovements->lastClosure($till);
        $isShiftOpen = $currentClosure && !$currentClosure->closed_at;

        $movements = $till->cashMovements();

        $cashSales = (float) (clone $movements)
            ->where('type', 'in')
            ->where('source', 'sale')
            ->sum('amount');

        $cashIn = (float) (clone $movements)
            ->where('type', 'in')
            ->where('source', 'manual')
            ->sum('amount');

        $cashOut = (float) (clone $movements)
            ->where('type', 'out')
            ->where('source', 'manual')
            ->sum('amount');

        $cashRefunds = (float) (clone $movements)
            ->where('type', 'out')
            ->where('source', 'refund')
            ->sum('amount');

        $expectedBalance =
            $this->cashMovements->expectedBalance($till);

        return view('cashier.index', compact(
            'readyForPayment',
            'till',
            'currentClosure',
            'isShiftOpen',
            'cashSales',
            'cashIn',
            'cashOut',
            'cashRefunds',
            'expectedBalance',
        ));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $jobs = Job::with(['customer', 'vehicle', 'invoice'])
            ->where('status', JobStatus::READY_FOR_PAYMENT->value)
            ->whereHas('vehicle', function ($q) use ($query) {
                $q->where('registration_number', 'like', '%' . $query . '%');
            })
            ->orWhereHas('customer', function ($q) use ($query) {
                $q->where('full_name', 'like', '%' . $query . '%');
            })
            ->orWhere('job_number', 'like', '%' . $query . '%')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('cashier.search', compact('jobs', 'query'));
    }

    public function payment(Job $job)
    {
        $job->load([
            'customer',
            'vehicle',
            'services.service',
            'parts.product',
            'invoice'
        ]);

        $calculation = $this->pricing->calculateFinalInvoice($job->id);

        return view('cashier.payment', compact('job', 'calculation'));
    }

    public function processPayment(Request $request, Job $job)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'amount_received' => 'required|numeric|min:0',

            'discount_type' => 'nullable|in:none,amount,percentage',

            'discount_value' => 'nullable|numeric|min:0',

            'discount_apply_to' => 'nullable|in:total,services,parts,individual_services,individual_parts',

            'individual_service_discounts' => 'nullable|array',
            'individual_service_discounts.*' => 'nullable|numeric|min:0',

            'individual_part_discounts' => 'nullable|array',
            'individual_part_discounts.*' => 'nullable|numeric|min:0',

            'coupon_code' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $job) {

            $job->load([
                'services',
                'parts',
                'invoice.items',
            ]);

            if (!$job->invoice) {
                return back()->with('error', 'No invoice found for this job.');
            }

            $invoice = $job->invoice;

            $amountReceived = (float) $request->amount_received;

            $calculation = $this->pricing->calculateFinalInvoice($job->id);

            $subtotal = (float) $calculation['subtotal'];
            $tax = (float) $calculation['tax'];

            $discountType =
                $request->input('discount_type', 'none');

            $discountApplyTo =
                $request->input('discount_apply_to', 'total');

            $discountValue =
                (float) $request->input('discount_value', 0);

            $discountAmount = 0;

            /*
            |--------------------------------------------------------------------------
            | Invoice items
            |--------------------------------------------------------------------------
            */

            $invoiceItems = $invoice->items;

            /*
            |--------------------------------------------------------------------------
            | Restore the original invoice-item discounts first.
            |
            | This prevents a second payment attempt from applying the
            | previous cashier discount again.
            |--------------------------------------------------------------------------
            */

            $originalServiceDiscounts =
                $job->services->keyBy('id');

            foreach ($invoiceItems as $item) {

                $originalDiscount = 0;

                if ($item->item_type === 'service') {

                    $jobService =
                        $originalServiceDiscounts->get($item->item_id);

                    if ($jobService) {
                        $originalDiscount =
                            (float) $jobService->discount;
                    }
                }

                $itemBase =
                    max(
                        0,
                        ((float) $item->unit_price * (float) $item->quantity)
                        - $originalDiscount
                    );

                $item->update([
                    'discount' => $originalDiscount,
                    'line_total' =>
                        $itemBase + (float) $item->tax,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | NO DISCOUNT
            |--------------------------------------------------------------------------
            */

            if ($discountType === 'none') {

                $discountAmount = 0;
            }

            /*
            |--------------------------------------------------------------------------
            | TOTAL AMOUNT
            |--------------------------------------------------------------------------
            */

            elseif ($discountApplyTo === 'total') {

                $eligibleItems = $invoiceItems;

                $eligibleBase = $eligibleItems->sum(function ($item) use ($originalServiceDiscounts) {

                    $originalDiscount = 0;

                    if ($item->item_type === 'service') {

                        $jobService =
                            $originalServiceDiscounts->get($item->item_id);

                        if ($jobService) {
                            $originalDiscount =
                                (float) $jobService->discount;
                        }
                    }

                    return max(
                        0,
                        ((float) $item->unit_price * (float) $item->quantity)
                        - $originalDiscount
                    );
                });

                if ($discountType === 'amount') {

                    $discountAmount =
                        min($discountValue, $eligibleBase);

                } elseif ($discountType === 'percentage') {

                    $percentage =
                        min($discountValue, 100);

                    $discountAmount =
                        ($eligibleBase * $percentage) / 100;
                }

                /*
                |--------------------------------------------------------------------------
                | Distribute discount across every invoice item
                |--------------------------------------------------------------------------
                */

                if ($eligibleBase > 0 && $discountAmount > 0) {

                    foreach ($eligibleItems as $item) {

                        $originalDiscount = 0;

                        if ($item->item_type === 'service') {

                            $jobService =
                                $originalServiceDiscounts->get($item->item_id);

                            if ($jobService) {
                                $originalDiscount =
                                    (float) $jobService->discount;
                            }
                        }

                        $itemBase =
                            max(
                                0,
                                ((float) $item->unit_price * (float) $item->quantity)
                                - $originalDiscount
                            );

                        if ($discountType === 'percentage') {

                            $itemDiscount =
                                ($itemBase * min($discountValue, 100)) / 100;

                        } else {

                            $itemDiscount =
                                $discountAmount *
                                ($itemBase / $eligibleBase);
                        }

                        $itemDiscount =
                            min($itemDiscount, $itemBase);

                        $item->update([
                            'discount' =>
                                $originalDiscount + $itemDiscount,

                            'line_total' =>
                                $itemBase
                                - $itemDiscount
                                + (float) $item->tax,
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | SERVICES ONLY
            |--------------------------------------------------------------------------
            */

            elseif ($discountApplyTo === 'services') {

                $serviceItems =
                    $invoiceItems
                        ->where('item_type', 'service');

                $serviceBase =
                    $serviceItems->sum(function ($item) use ($originalServiceDiscounts) {

                        $jobService =
                            $originalServiceDiscounts->get($item->item_id);

                        $originalDiscount =
                            $jobService
                                ? (float) $jobService->discount
                                : 0;

                        return max(
                            0,
                            ((float) $item->unit_price * (float) $item->quantity)
                            - $originalDiscount
                        );
                    });

                if ($discountType === 'amount') {

                    $discountAmount =
                        min($discountValue, $serviceBase);

                } elseif ($discountType === 'percentage') {

                    $discountAmount =
                        ($serviceBase * min($discountValue, 100)) / 100;
                }

                /*
                |--------------------------------------------------------------------------
                | Put the service discount on each service line
                |--------------------------------------------------------------------------
                */

                if ($serviceBase > 0 && $discountAmount > 0) {

                    foreach ($serviceItems as $item) {

                        $jobService =
                            $originalServiceDiscounts->get($item->item_id);

                        $originalDiscount =
                            $jobService
                                ? (float) $jobService->discount
                                : 0;

                        $itemBase =
                            max(
                                0,
                                ((float) $item->unit_price * (float) $item->quantity)
                                - $originalDiscount
                            );

                        if ($discountType === 'percentage') {

                            $cashierDiscount =
                                ($itemBase * min($discountValue, 100)) / 100;

                        } else {

                            $cashierDiscount =
                                $discountAmount *
                                ($itemBase / $serviceBase);
                        }

                        $cashierDiscount =
                            min($cashierDiscount, $itemBase);

                        $item->update([
                            'discount' =>
                                $originalDiscount + $cashierDiscount,

                            'line_total' =>
                                $itemBase
                                - $cashierDiscount
                                + (float) $item->tax,
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | PARTS ONLY
            |--------------------------------------------------------------------------
            */

            elseif ($discountApplyTo === 'parts') {

                $partItems =
                    $invoiceItems
                        ->where('item_type', 'part');

                $partsBase =
                    $partItems->sum(function ($item) {

                        return max(
                            0,
                            (float) $item->unit_price *
                            (float) $item->quantity
                        );
                    });

                if ($discountType === 'amount') {

                    $discountAmount =
                        min($discountValue, $partsBase);

                } elseif ($discountType === 'percentage') {

                    $discountAmount =
                        ($partsBase * min($discountValue, 100)) / 100;
                }

                /*
                |--------------------------------------------------------------------------
                | Put the parts discount on each part line
                |--------------------------------------------------------------------------
                */

                if ($partsBase > 0 && $discountAmount > 0) {

                    foreach ($partItems as $item) {

                        $itemBase =
                            (float) $item->unit_price *
                            (float) $item->quantity;

                        if ($discountType === 'percentage') {

                            $cashierDiscount =
                                ($itemBase * min($discountValue, 100)) / 100;

                        } else {

                            $cashierDiscount =
                                $discountAmount *
                                ($itemBase / $partsBase);
                        }

                        $cashierDiscount =
                            min($cashierDiscount, $itemBase);

                        $item->update([
                            'discount' =>
                                $cashierDiscount,

                            'line_total' =>
                                $itemBase
                                - $cashierDiscount
                                + (float) $item->tax,
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | INDIVIDUAL SERVICES
            |--------------------------------------------------------------------------
            */

            elseif ($discountApplyTo === 'individual_services') {

                $individualDiscounts =
                    $request->input(
                        'individual_service_discounts',
                        []
                    );

                foreach ($invoiceItems->where('item_type', 'service') as $item) {

                    $jobService =
                        $originalServiceDiscounts->get($item->item_id);

                    $originalDiscount =
                        $jobService
                            ? (float) $jobService->discount
                            : 0;

                    $itemBase =
                        max(
                            0,
                            ((float) $item->unit_price * (float) $item->quantity)
                            - $originalDiscount
                        );

                    $value =
                        (float) ($individualDiscounts[$item->item_id] ?? 0);

                    if ($discountType === 'percentage') {

                        $cashierDiscount =
                            ($itemBase * min($value, 100)) / 100;

                    } else {

                        $cashierDiscount =
                            min($value, $itemBase);
                    }

                    $discountAmount += $cashierDiscount;

                    $item->update([
                        'discount' =>
                            $originalDiscount + $cashierDiscount,

                        'line_total' =>
                            $itemBase
                            - $cashierDiscount
                            + (float) $item->tax,
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | INDIVIDUAL PARTS
            |--------------------------------------------------------------------------
            */

            elseif ($discountApplyTo === 'individual_parts') {

                $individualDiscounts =
                    $request->input(
                        'individual_part_discounts',
                        []
                    );

                foreach ($invoiceItems->where('item_type', 'part') as $item) {

                    $itemBase =
                        (float) $item->unit_price *
                        (float) $item->quantity;

                    $value =
                        (float) ($individualDiscounts[$item->item_id] ?? 0);

                    if ($discountType === 'percentage') {

                        $cashierDiscount =
                            ($itemBase * min($value, 100)) / 100;

                    } else {

                        $cashierDiscount =
                            min($value, $itemBase);
                    }

                    $discountAmount += $cashierDiscount;

                    $item->update([
                        'discount' =>
                            $cashierDiscount,

                        'line_total' =>
                            $itemBase
                            - $cashierDiscount
                            + (float) $item->tax,
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Final safety limit
            |--------------------------------------------------------------------------
            */

            $discountAmount =
                min(
                    max(0, $discountAmount),
                    $subtotal
                );

            /*
            |--------------------------------------------------------------------------
            | Final invoice total
            |--------------------------------------------------------------------------
            */

            $finalTotal =
                max(
                    0,
                    ($subtotal - $discountAmount) + $tax
                );

            /*
            |--------------------------------------------------------------------------
            | Payment / balance
            |--------------------------------------------------------------------------
            */

            $previousPaid = (float) $invoice->getOriginal('paid');

            // Apply the full amount received (allowing overpayments)
            $totalPaid =
                (float) $invoice->paid +
                $amountReceived;

            $invoiceBalance =
                $finalTotal -
                $totalPaid;

            /*
            |--------------------------------------------------------------------------
            | Update invoice
            |--------------------------------------------------------------------------
            */

            $invoice->update([
                'discount' => $discountAmount,
                'total' => $finalTotal,
                'paid' => $totalPaid,
                'balance' => $invoiceBalance,
                'status' =>
                    $invoiceBalance <= 0
                        ? 'paid'
                        : ($totalPaid > 0
                            ? 'partially_paid'
                            : 'issued'),
            ]);

            $invoice->refresh();

            /*
            |--------------------------------------------------------------------------
            | Create Payment record
            |--------------------------------------------------------------------------
            */

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'method' => $request->payment_method,
                'amount' => $amountReceived,
                'reference' => $request->input('reference'),
                'received_by' => auth()->id(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create CashMovement only for cash payments
            |--------------------------------------------------------------------------
            */

            if ($request->payment_method === 'cash' && $amountReceived > 0) {
                // Record only the net cash amount that stays in the till
                // If customer overpaid (invoiceBalance < 0), record the invoice total since change is given back
                // Otherwise record the full payment amount
                $netCashAmount = $invoiceBalance < 0 ? $finalTotal : $amountReceived;
                
                $this->cashMovements->recordSale(
                    amount: $netCashAmount,
                    reference: $payment,
                    userId: auth()->id(),
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Note: All payment methods (cash, card, UPI, bank_transfer, etc.)
            | are tracked through the Payment model and will be included in
            | till closure calculations via the CashMovementService
            |--------------------------------------------------------------------------
            */

            /*
            |--------------------------------------------------------------------------
            | Balance message
            |--------------------------------------------------------------------------
            */

            if ($invoiceBalance > 0) {

                $balanceMessage =
                    'Balance due: Rs. ' .
                    number_format($invoiceBalance, 2);

            } elseif ($invoiceBalance < 0) {

                $balanceMessage =
                    'Change to return: Rs. ' .
                    number_format(abs($invoiceBalance), 2);

            } else {

                $balanceMessage =
                    'Fully paid';
            }

            /*
            |--------------------------------------------------------------------------
            | Job status
            |--------------------------------------------------------------------------
            */

            if (
                $job->status !== JobStatus::PAID &&
                $invoiceBalance <= 0
            ) {

                $job->transitionTo(
                    JobStatus::PAID,
                    auth()->user(),
                    "Payment processed via {$request->payment_method}. " .
                    "Amount received: Rs. {$amountReceived}, " .
                    "Discount: Rs. {$discountAmount}, " .
                    "{$balanceMessage}"
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Generate WhatsApp Web URL for invoice notification (no API required)
            |--------------------------------------------------------------------------
            |
            | This uses the same approach as the job feature - creates a WhatsApp Web URL
            | that opens WhatsApp with a pre-filled message. No API configuration needed!
            |
            */

            $whatsappUrl = null;
            try {
                // Generate WhatsApp Web URL for boss notification
                // Use international format for Sri Lanka: +94 followed by number without leading 0
                $bossNumber = '94753643227';
                $whatsappUrl = $this->communication->generateWhatsAppWebUrl(
                    phoneNumber: $bossNumber,
                    job: $job,
                    amount: $amountReceived,
                    paymentMethod: $request->payment_method
                );
            } catch (\Throwable $e) {
                // Log error but don't fail the payment process
                \Log::error('Failed to generate WhatsApp URL', [
                    'error' => $e->getMessage(),
                    'job_id' => $job->id,
                    'amount' => $amountReceived
                ]);
            }

            return redirect()
                ->route('cashier.print-options', $job)
                ->with('success', 'Payment processed successfully. ' . $balanceMessage)
                ->with('whatsapp_url', $whatsappUrl);
        });
    }

    public function printOptions(Job $job)
    {
        // Reload job and invoice from database to get latest values including discount
        $job = Job::with(['customer', 'vehicle', 'services.service', 'parts.product', 'invoice'])->find($job->id);
        
        // Log for debugging
        if ($job->invoice) {
            \Log::info('Print Options', [
                'job_id' => $job->id,
                'invoice_id' => $job->invoice->id,
                'subtotal' => $job->invoice->subtotal,
                'discount' => $job->invoice->discount,
                'tax' => $job->invoice->tax,
                'total' => $job->invoice->total,
                'paid' => $job->invoice->paid,
                'balance' => $job->invoice->balance,
            ]);
        }
        
        if (!$job->invoice) {
            return redirect()->route('cashier.index')->with('error', 'No invoice found for this job.');
        }

        // Calculate balance to return directly from invoice
        $totalPaid = (float) $job->invoice->paid;
        $totalDue = (float) $job->invoice->total;
        $currentBalance = $totalPaid - $totalDue;
        
        // Get the last payment transaction details from status history
        $lastPayment = $job->statusHistory()
            ->where('to_status', \App\Enums\JobStatus::PAID->value)
            ->latest()
            ->first();

        $currentPaymentAmount = 0;

        if ($lastPayment && $lastPayment->reason) {
            // Parse the reason to extract payment details
            if (preg_match('/Amount received: Rs\. ([\d.]+)/', $lastPayment->reason, $matches)) {
                $currentPaymentAmount = floatval($matches[1]);
            }
        }

        return view('cashier.print-options', compact('job', 'currentPaymentAmount', 'currentBalance'));
    }

    public function cashIn(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->cashMovements->recordCashIn(
            amount: (float) $data['amount'],
            reason: $data['reason'],
            description: $data['description'] ?? null,
            userId: auth()->id(),
        );

        return back()->with('success', 'Cash added to Till successfully.');
    }

    public function cashOut(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->cashMovements->recordCashOut(
                amount: (float) $data['amount'],
                reason: $data['reason'],
                description: $data['description'] ?? null,
                userId: auth()->id(),
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Cash removed from Till successfully.');
    }

    public function cashDrop(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->cashMovements->recordCashDrop(
                amount: (float) $data['amount'],
                reason: $data['reason'] ?? 'Cash Drop',
                description: $data['description'] ?? null,
                userId: auth()->id(),
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Cash drop recorded successfully.');
    }
}