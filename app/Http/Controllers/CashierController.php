<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Payment;
use App\Models\Invoice;
use App\Enums\JobStatus;
use App\Services\PricingService;
use App\Services\CashMovementService;
use App\Services\CommunicationService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function __construct(
        private PricingService $pricing,
        private CashMovementService $cashMovements,
        private CommunicationService $communication,
        private AuditService $audit
    ) {}

    public function checkTillStatus()
    {
        $till = $this->cashMovements->getSelectedTill();
        $tillOpen = false;

        if ($till) {
            $lastClosure = $this->cashMovements->lastClosure($till);
            $tillOpen = $lastClosure && !$lastClosure->closed_at;
        }

        return response()->json([
            'till_open' => $tillOpen
        ]);
    }

    public function jobsCount()
    {
        $jobCount = Job::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', JobStatus::READY_FOR_PAYMENT->value)
            ->whereDoesntHave('invoice.payments') // Exclude jobs that have any payment records
            ->where(function ($query) {
                $query->whereDoesntHave('invoice')
                      ->orWhereHas('invoice', function ($q) {
                          $q->where('status', '!=', 'cancelled');
                      });
            })
            ->where('status', '!=', JobStatus::DELIVERED->value)
            ->count();

        $posCount = Invoice::where('tenant_id', auth()->user()->tenant_id)
            ->whereNull('job_id')
            ->where('status', '!=', 'cancelled')
            ->where('balance', '>', 0)
            ->whereDoesntHave('payments') // Exclude POS invoices that have any payment records
            ->count();

        return response()->json([
            'job_count' => $jobCount,
            'pos_count' => $posCount
        ]);
    }

    public function index()
    {
        $readyForPayment = Job::with([
            'customer',
            'vehicle',
            'invoice'
        ])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where(
                'status',
                JobStatus::READY_FOR_PAYMENT->value
            )
            ->where('status', '!=', JobStatus::CANCELLED->value)
            ->whereDoesntHave('invoice.payments') // Exclude jobs that have any payment records
            ->where(function ($query) {
                $query->whereDoesntHave('invoice')
                      ->orWhereHas('invoice', function ($q) {
                          $q->where('status', '!=', 'cancelled');
                      });
            })
            ->where('status', '!=', JobStatus::DELIVERED->value)
            ->orderBy('updated_at', 'desc')
            ->get();

        // Also load POS invoices (invoices with no job)
        // Only show POS invoices that haven't received any payment yet and are not cancelled
        $posInvoices = Invoice::with(['customer', 'items'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->whereNull('job_id')
            ->where('status', '!=', 'cancelled')
            ->where('balance', '>', 0)
            ->whereDoesntHave('payments') // Only show if no payment records exist
            ->orderBy('created_at', 'desc')
            ->get();

        $till = $this->cashMovements->getSelectedTill();

        // Update last activity timestamp for the selected till
        if ($till && $till->current_user_id == auth()->id()) {
            $till->update(['last_activity_at' => now()]);
        }

        $currentClosure = $this->cashMovements->lastClosure($till);
        $isShiftOpen = $currentClosure && !$currentClosure->closed_at;

        // If shift is closed, get the most recent closed closure to show its data
        if (!$isShiftOpen) {
            $currentClosure = \App\Models\TillClosure::where('till_id', $till->id)
                ->where('user_id', auth()->id())
                ->whereNotNull('closed_at')
                ->latest('closed_at')
                ->first();
        }

        $movements = $till->cashMovements();

        // Filter movements to only include current shift if shift is open
        if ($isShiftOpen && $currentClosure) {
            $movements = $movements->where('till_closure_id', $currentClosure->id);
        }

        // If shift is closed, show zero for all current shift values
        if (!$isShiftOpen) {
            $cashSales = 0;
            $cashIn = 0;
            $cashOut = 0;
        } else {
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
        }

        $expectedBalance = $isShiftOpen
            ? ((float) $currentClosure->opening_balance + $cashSales + $cashIn - $cashOut)
            : ($currentClosure ? $currentClosure->counted_balance : 0);

        return view('cashier.index', compact(
            'readyForPayment',
            'posInvoices',
            'till',
            'currentClosure',
            'isShiftOpen',
            'cashSales',
            'cashIn',
            'cashOut',
            'expectedBalance',
        ));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');

        $jobs = Job::with(['customer', 'vehicle', 'invoice'])
            ->where('status', JobStatus::READY_FOR_PAYMENT->value)
            ->whereDoesntHave('invoice', function ($query) {
                // Exclude jobs with partial payments (have some payments but still have balance)
                $query->where('paid', '>', 0)
                      ->where('balance', '>', 0.01);
            })
            ->where(function ($query) {
                $query->whereDoesntHave('invoice')
                      ->orWhereHas('invoice', function ($q) {
                          $q->where('status', '!=', 'cancelled');
                      });
            })
            ->where(function ($q) use ($query) {
                $q->whereHas('vehicle', function ($q) use ($query) {
                    $q->where('registration_number', 'like', '%' . $query . '%');
                })
                ->orWhereHas('customer', function ($q) use ($query) {
                    $q->where('full_name', 'like', '%' . $query . '%');
                })
                ->orWhere('job_number', 'like', '%' . $query . '%');
            })
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

        // Check if job is already delivered (completed payment process)
        if ($job->status->value === \App\Enums\JobStatus::DELIVERED->value) {
            return response()->view('cashier.payment-completed', compact('job'))
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }

        // Check if invoice exists and is already fully paid
        if ($job->invoice && $job->invoice->balance <= 0) {
            return response()->view('cashier.payment-completed', compact('job'))
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }

        $calculation = $this->pricing->calculateFinalInvoice($job->id);

        // Prevent browser caching of payment page
        return response()->view('cashier.payment', compact('job', 'calculation'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * Payment page for POS-created invoices (no job)
     */
    public function paymentForInvoice(Invoice $invoice)
    {
        // Tenant check
        if ($invoice->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'You can only view invoices from your tenant.');
        }

        $invoice->load(['customer', 'items']);

        // Check if invoice is cancelled
        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'This invoice has been cancelled and cannot be paid.');
        }

        // Check if invoice is already fully paid
        if ($invoice->balance <= 0) {
            return response()->view('cashier.payment-completed-pos', compact('invoice'))
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }

        // Prevent browser caching of payment page
        return response()->view('cashier.payment-pos', compact('invoice'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function processPayment(Request $request, Job $job)
    {
        // Check if adding to a previous closure (Full Admin only)
        $selectedClosureId = $request->input('closure_id');
        $selectedClosure = null;
        if ($selectedClosureId) {
            if (!auth()->user()->isFullAdmin()) {
                abort(403, 'Only Full Administrator can add payments to previous closures.');
            }

            $selectedClosure = \App\Models\TillClosure::find($selectedClosureId);
            if (!$selectedClosure || $selectedClosure->tenant_id !== auth()->user()->tenant_id) {
                return back()->with('error', 'Invalid closure selected.');
            }
        }

        // Check if till is closed (no open shift) - only if not adding to previous closure
        $till = $this->cashMovements->getSelectedTill();
        if ($till && !$selectedClosureId) {
            $lastClosure = $this->cashMovements->lastClosure($till);
            if (!$lastClosure || $lastClosure->closed_at) {
                return back()->with('error', 'Cannot process payment. Till is closed. Please open a new shift first.');
            }
        }

        $request->validate([
            'payment_method' => 'required|string|in:cash,card,upi,bank_transfer,cheque',
            'amount_received' => 'nullable|numeric|min:0',

            'discount_type' => 'nullable|in:none,amount,percentage',
        ]);

        // Get current invoice balance before validation
        $job->load('invoice');
        if (!$job->invoice) {
            return back()->with('error', 'No invoice found for this job.');
        }

        $request->validate([
            'discount_value' => 'nullable|numeric|min:0',
            'discount_apply_to' => 'nullable|in:total,services,parts,individual_services,individual_parts',
            'individual_service_discounts' => 'nullable|array',
            'individual_service_discounts.*' => 'nullable|numeric|min:0',
            'individual_part_discounts' => 'nullable|array',
            'individual_part_discounts.*' => 'nullable|numeric|min:0',
            'coupon_code' => 'nullable|string',
            'cheque_number' => 'nullable|string|required_if:payment_method,cheque',
            'bank_name' => 'nullable|string|required_if:payment_method,cheque',
            'cheque_due_date' => 'nullable|date|required_if:payment_method,cheque',
            'payment_received' => 'nullable|in:yes,no',
        ]);

        return DB::transaction(function () use ($request, $job, $selectedClosureId, $selectedClosure) {

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

            $paymentData = [
                'invoice_id' => $invoice->id,
                'method' => $request->payment_method,
                'amount' => $amountReceived,
                'reference' => $request->input('reference_number'),
                'received_by' => auth()->id(),
            ];

            // Add cheque-specific fields if payment method is cheque
            if ($request->payment_method === 'cheque') {
                $paymentData['cheque_number'] = $request->cheque_number;
                $paymentData['bank_name'] = $request->bank_name;
                $paymentData['cheque_due_date'] = $request->cheque_due_date;
                $paymentData['payment_received'] = $request->payment_received === 'yes';
                if ($request->payment_received === 'yes') {
                    $paymentData['payment_received_at'] = now();
                }
            }

            $payment = Payment::create($paymentData);

            // Log payment completion in audit logs
            $this->audit->logPayment($payment->id, [
                'invoice_id' => $invoice->id,
                'job_id' => $job->id,
                'job_number' => $job->job_number,
                'customer_name' => $job->customer->full_name,
                'vehicle_registration' => $job->vehicle->registration_number,
                'payment_method' => $request->payment_method,
                'amount' => $amountReceived,
                'discount_amount' => $discountAmount,
                'discount_type' => $discountType,
                'final_total' => $finalTotal,
                'balance_due' => $invoiceBalance,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create CashMovement only for cash payments and received cheques
            |--------------------------------------------------------------------------
            */

            if ($request->payment_method === 'cash' && $amountReceived > 0) {
                // Record only the net cash amount that stays in the till
                // If customer overpaid (invoiceBalance < 0), record the invoice total since change is given back
                // If final total is 0 (prepayment/deposit), record the actual amount received
                // Otherwise record the full payment amount
                if ($finalTotal <= 0) {
                    $netCashAmount = $amountReceived;
                } else {
                    $netCashAmount = $invoiceBalance < 0 ? $finalTotal : $amountReceived;
                }

                $this->cashMovements->recordSale(
                    amount: $netCashAmount,
                    reference: $payment,
                    userId: auth()->id(),
                    closureId: $selectedClosureId ?? null,
                );

                // If adding to a previous closure, update its totals
                if ($selectedClosureId) {
                    $selectedClosure->increment('cash_sales', $netCashAmount);
                    $selectedClosure->increment('total_sales', $netCashAmount);
                    $selectedClosure->increment('expected_balance', $netCashAmount);
                }
            } elseif ($request->payment_method === 'cheque' && $request->payment_received === 'yes' && $amountReceived > 0) {
                // Only record cheque payment in till when payment is actually received/cleared
                if ($finalTotal <= 0) {
                    $netChequeAmount = $amountReceived;
                } else {
                    $netChequeAmount = $invoiceBalance < 0 ? $finalTotal : $amountReceived;
                }

                $this->cashMovements->recordSale(
                    amount: $netChequeAmount,
                    reference: $payment,
                    userId: auth()->id(),
                    closureId: $selectedClosureId ?? null,
                );

                // If adding to a previous closure, update its totals
                if ($selectedClosureId) {
                    $selectedClosure->increment('cheque_sales', $netChequeAmount);
                    $selectedClosure->increment('total_sales', $netChequeAmount);
                }
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

    /**
     * Process payment for POS-created invoices (no job)
     */
    public function processPaymentForInvoice(Request $request, Invoice $invoice)
    {
        // Check if adding to a previous closure (Full Admin only)
        $selectedClosureId = $request->input('closure_id');
        $selectedClosure = null;
        if ($selectedClosureId) {
            if (!auth()->user()->isFullAdmin()) {
                abort(403, 'Only Full Administrator can add payments to previous closures.');
            }

            $selectedClosure = \App\Models\TillClosure::find($selectedClosureId);
            if (!$selectedClosure || $selectedClosure->tenant_id !== auth()->user()->tenant_id) {
                return back()->with('error', 'Invalid closure selected.');
            }
        }

        // Check if till is closed (no open shift) - only if not adding to previous closure
        $till = $this->cashMovements->getSelectedTill();
        if ($till && !$selectedClosureId) {
            $lastClosure = $this->cashMovements->lastClosure($till);
            if (!$lastClosure || $lastClosure->closed_at) {
                return back()->with('error', 'Cannot process payment. Till is closed. Please open a new shift first.');
            }
        }

        $request->validate([
            'payment_method' => 'required|string|in:cash,card,upi,bank_transfer,cheque',
            'amount_received' => 'nullable|numeric|min:0',

            'discount_type' => 'nullable|in:none,amount,percentage',
        ]);

        $request->validate([
            'discount_value' => 'nullable|numeric|min:0',
            'discount_apply_to' => 'nullable|in:total,individual_items',
            'individual_item_discounts' => 'nullable|array',
            'individual_item_discounts.*' => 'nullable|numeric|min:0',
            'coupon_code' => 'nullable|string',
            'cheque_number' => 'nullable|string|required_if:payment_method,cheque',
            'bank_name' => 'nullable|string|required_if:payment_method,cheque',
            'cheque_due_date' => 'nullable|date|required_if:payment_method,cheque',
            'payment_received' => 'nullable|in:yes,no',
        ]);

        return DB::transaction(function () use ($request, $invoice, $selectedClosureId, $selectedClosure) {
            $invoice->load(['items', 'customer']);

            $amountReceived = (float) $request->amount_received;
            $paymentMethod = $request->payment_method;

            $subtotal = (float) $invoice->subtotal;
            $tax = (float) $invoice->tax;

            $discountType = $request->input('discount_type', 'none');
            $discountApplyTo = $request->input('discount_apply_to', 'total');
            $discountValue = (float) $request->input('discount_value', 0);

            $discountAmount = 0;

            /*
            |--------------------------------------------------------------------------
            | Invoice items
            |--------------------------------------------------------------------------
            */

            $invoiceItems = $invoice->items;

            /*
            |--------------------------------------------------------------------------
            | Restore the original invoice-item discounts first
            |--------------------------------------------------------------------------
            */

            foreach ($invoiceItems as $item) {
                $itemBase = (float) $item->unit_price * (float) $item->quantity;
                $item->update([
                    'discount' => 0,
                    'line_total' => $itemBase + (float) $item->tax,
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
                $eligibleBase = $eligibleItems->sum(function ($item) {
                    return (float) $item->unit_price * (float) $item->quantity;
                });

                if ($discountType === 'amount') {
                    $discountAmount = min($discountValue, $eligibleBase);
                } elseif ($discountType === 'percentage') {
                    $percentage = min($discountValue, 100);
                    $discountAmount = ($eligibleBase * $percentage) / 100;
                }

                /*
                |--------------------------------------------------------------------------
                | Distribute discount across every invoice item
                |--------------------------------------------------------------------------
                */

                if ($eligibleBase > 0 && $discountAmount > 0) {
                    foreach ($eligibleItems as $item) {
                        $itemBase = (float) $item->unit_price * (float) $item->quantity;

                        if ($discountType === 'percentage') {
                            $itemDiscount = ($itemBase * min($discountValue, 100)) / 100;
                        } else {
                            $itemDiscount = $discountAmount * ($itemBase / $eligibleBase);
                        }

                        $itemDiscount = min($itemDiscount, $itemBase);

                        $item->update([
                            'discount' => $itemDiscount,
                            'line_total' => $itemBase - $itemDiscount + (float) $item->tax,
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | INDIVIDUAL ITEMS
            |--------------------------------------------------------------------------
            */

            elseif ($discountApplyTo === 'individual_items') {
                $individualDiscounts = $request->input('individual_item_discounts', []);

                foreach ($invoiceItems as $item) {
                    $itemBase = (float) $item->unit_price * (float) $item->quantity;
                    $value = (float) ($individualDiscounts[$item->id] ?? 0);

                    if ($discountType === 'percentage') {
                        $cashierDiscount = ($itemBase * min($value, 100)) / 100;
                    } else {
                        $cashierDiscount = min($value, $itemBase);
                    }

                    $discountAmount += $cashierDiscount;

                    $item->update([
                        'discount' => $cashierDiscount,
                        'line_total' => $itemBase - $cashierDiscount + (float) $item->tax,
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Final safety limit
            |--------------------------------------------------------------------------
            */

            $discountAmount = min(max(0, $discountAmount), $subtotal);

            /*
            |--------------------------------------------------------------------------
            | Final invoice total
            |--------------------------------------------------------------------------
            */

            $finalTotal = max(0, ($subtotal - $discountAmount) + $tax);

            /*
            |--------------------------------------------------------------------------
            | Payment / balance
            |--------------------------------------------------------------------------
            */

            $previousPaid = (float) $invoice->getOriginal('paid');
            $totalPaid = (float) $invoice->paid + $amountReceived;
            $invoiceBalance = $finalTotal - $totalPaid;

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
                'status' => $invoiceBalance <= 0 ? 'paid' : ($totalPaid > 0 ? 'partially_paid' : 'issued'),
            ]);

            $invoice->refresh();

            /*
            |--------------------------------------------------------------------------
            | Create Payment record
            |--------------------------------------------------------------------------
            */

            $paymentData = [
                'tenant_id' => auth()->user()->tenant_id,
                'invoice_id' => $invoice->id,
                'job_id' => null,
                'method' => $paymentMethod,
                'amount' => $amountReceived,
                'reference' => $request->input('reference_number'),
                'received_by' => auth()->id(),
            ];

            // Add cheque-specific fields if payment method is cheque
            if ($paymentMethod === 'cheque') {
                $paymentData['cheque_number'] = $request->cheque_number;
                $paymentData['bank_name'] = $request->bank_name;
                $paymentData['cheque_due_date'] = $request->cheque_due_date;
                $paymentData['payment_received'] = $request->payment_received === 'yes';
                if ($request->payment_received === 'yes') {
                    $paymentData['payment_received_at'] = now();
                }
            }

            $payment = Payment::create($paymentData);

            // Log payment completion in audit logs
            $this->audit->logPayment($payment->id, [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'job_id' => null,
                'job_number' => null,
                'customer_name' => $invoice->customer?->full_name ?? 'Walk-in',
                'vehicle_registration' => null,
                'payment_method' => $paymentMethod,
                'amount' => $amountReceived,
                'discount_amount' => $discountAmount,
                'discount_type' => $discountType,
                'final_total' => $finalTotal,
                'balance_due' => $invoiceBalance,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create CashMovement only for cash payments and received cheques
            |--------------------------------------------------------------------------
            */

            if ($paymentMethod === 'cash' && $amountReceived > 0) {
                if ($finalTotal <= 0) {
                    $netCashAmount = $amountReceived;
                } else {
                    $netCashAmount = $invoiceBalance < 0 ? $finalTotal : $amountReceived;
                }

                $this->cashMovements->recordSale(
                    amount: $netCashAmount,
                    reference: $payment,
                    userId: auth()->id(),
                    closureId: $selectedClosureId ?? null,
                );

                // If adding to a previous closure, update its totals
                if ($selectedClosureId) {
                    $selectedClosure->increment('cash_sales', $netCashAmount);
                    $selectedClosure->increment('total_sales', $netCashAmount);
                    $selectedClosure->increment('expected_balance', $netCashAmount);
                }
            } elseif ($paymentMethod === 'cheque' && $request->payment_received === 'yes' && $amountReceived > 0) {
                if ($finalTotal <= 0) {
                    $netChequeAmount = $amountReceived;
                } else {
                    $netChequeAmount = $invoiceBalance < 0 ? $finalTotal : $amountReceived;
                }

                $this->cashMovements->recordSale(
                    amount: $netChequeAmount,
                    reference: $payment,
                    userId: auth()->id(),
                    closureId: $selectedClosureId ?? null,
                );

                // If adding to a previous closure, update its totals
                if ($selectedClosureId) {
                    $selectedClosure->increment('cheque_sales', $netChequeAmount);
                    $selectedClosure->increment('total_sales', $netChequeAmount);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Balance message
            |--------------------------------------------------------------------------
            */

            if ($invoiceBalance > 0) {
                $balanceMessage = 'Balance due: Rs. ' . number_format($invoiceBalance, 2);
            } elseif ($invoiceBalance < 0) {
                $balanceMessage = 'Change to return: Rs. ' . number_format(abs($invoiceBalance), 2);
            } else {
                $balanceMessage = 'Fully paid';
            }

            /*
            |--------------------------------------------------------------------------
            | Generate WhatsApp Web URL for invoice notification
            |--------------------------------------------------------------------------
            */

            $whatsappUrl = null;
            try {
                $bossNumber = '94753643227';
                $whatsappUrl = $this->communication->generateWhatsAppWebUrlForPos(
                    phoneNumber: $bossNumber,
                    invoice: $invoice,
                    amount: $amountReceived,
                    paymentMethod: $paymentMethod
                );
            } catch (\Throwable $e) {
                \Log::error('Failed to generate WhatsApp URL for POS', [
                    'error' => $e->getMessage(),
                    'invoice_id' => $invoice->id,
                    'amount' => $amountReceived
                ]);
            }

            return redirect()
                ->route('cashier.print-pos-invoice', $invoice)
                ->with('success', 'Payment processed successfully. ' . $balanceMessage)
                ->with('whatsapp_url', $whatsappUrl);
        });
    }

    /**
     * Print options for POS invoice
     */
    public function printPosInvoice(Invoice $invoice)
    {
        $invoice->load(['customer', 'items']);

        return view('cashier.print-pos-options', compact('invoice'));
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

    public function reverseInvoice(Request $request, Invoice $invoice)
    {
        // Only Full Administrator can reverse invoices
        if (!auth()->user()->isFullAdmin()) {
            abort(403, 'Only Full Administrator can reverse invoices.');
        }

        // Tenant check
        if ($invoice->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'You can only reverse invoices from your tenant.');
        }

        // Cannot reverse already cancelled invoices
        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'This invoice is already cancelled.');
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'in:duplicate_invoice,incorrect_customer,wrong_products,pricing_error,accidental_creation,customer_request,system_error,payment_issue'],
        ]);

        // Map reason codes to readable descriptions
        $reasonDescriptions = [
            'duplicate_invoice' => 'Duplicate invoice created',
            'incorrect_customer' => 'Incorrect customer selected',
            'wrong_products' => 'Wrong products added to invoice',
            'pricing_error' => 'Pricing error on invoice',
            'accidental_creation' => 'Invoice created accidentally',
            'customer_request' => 'Customer requested cancellation',
            'system_error' => 'System error during creation',
            'payment_issue' => 'Payment processing issue',
        ];

        $readableReason = $reasonDescriptions[$data['reason']] ?? $data['reason'];

        DB::transaction(function () use ($invoice, $data, $readableReason) {
            // Reverse stock movements for invoice items
            foreach ($invoice->items as $item) {
                $productId = null;

                // Handle different item types
                if ($item->item_type === 'product') {
                    // POS sales: item_id is the product_id
                    $productId = $item->item_id;
                } elseif ($item->item_type === 'part') {
                    // Job parts: item_id is the job_part id, need to get product_id from it
                    $jobPart = \App\Models\JobPart::find($item->item_id);
                    if ($jobPart) {
                        $productId = $jobPart->product_id;
                    }
                }

                if ($productId) {
                    $inventory = \App\Models\Inventory::where('tenant_id', $invoice->tenant_id)
                        ->where('product_id', $productId)
                        ->where('branch_id', $invoice->branch_id)
                        ->first();

                    if ($inventory) {
                        // Increment stock back
                        $inventory->increment('quantity', $item->quantity);

                        // Create inventory movement record for reversal
                        \App\Models\InventoryMovement::create([
                            'product_id' => $productId,
                            'tenant_id' => $invoice->tenant_id,
                            'business_id' => $invoice->business_id,
                            'branch_id' => $invoice->branch_id,
                            'type' => \App\Enums\InventoryMovementType::INVOICE_REVERSAL->value,
                            'quantity' => $item->quantity,
                            'unit_cost' => $item->unit_cost ?? 0,
                            'reference_type' => 'invoice_reversal',
                            'reference_id' => $invoice->id,
                            'user_id' => auth()->id(),
                            'reason' => 'Invoice reversal: ' . $readableReason,
                        ]);
                    }
                }
            }

            // Reverse payments if they exist
            foreach ($invoice->payments as $payment) {
                // If payment was cash, reverse cash movement
                if ($payment->method === 'cash') {
                    $till = $this->cashMovements->getSelectedTill();
                    if ($till) {
                        // Record cash out to reverse the cash in
                        $this->cashMovements->recordCashOut(
                            amount: (float) $payment->amount,
                            reason: 'Invoice reversal - payment refund',
                            description: 'Reversing payment for invoice ' . $invoice->invoice_number,
                            userId: auth()->id(),
                        );
                    }
                }

                // Mark payment as reversed
                $payment->update([
                    'status' => 'reversed',
                    'notes' => ($payment->notes ?? '') . ' | Reversed due to invoice cancellation: ' . $readableReason,
                ]);
            }

            // Update invoice status to cancelled
            $invoice->update([
                'status' => 'cancelled',
                'cancellation_reason' => $readableReason,
            ]);

            // If invoice was for a job, mark it as cancelled instead of deleting (to avoid foreign key issues)
            if ($invoice->job) {
                try {
                    $invoice->job->update([
                        'status' => \App\Enums\JobStatus::CANCELLED->value,
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail the reversal
                    \Log::error('Failed to cancel job during invoice reversal: ' . $e->getMessage());
                }
            }

            // Log the reversal
            $this->audit->log(
                'invoice_reversed',
                "Invoice #{$invoice->invoice_number} reversed",
                'warning',
                'tenant_user',
                auth()->user()->email,
                [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'old_status' => $invoice->status,
                    'new_status' => 'cancelled',
                    'cancellation_reason' => $readableReason,
                ]
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Invoice reversed successfully. Stock and payments have been restored.'
        ]);
    }
}