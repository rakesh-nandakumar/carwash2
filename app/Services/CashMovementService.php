<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\Till;
use App\Models\TillClosure;
use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CashMovementService
{
    public function __construct(private readonly AuditService $auditService)
    {
    }
    public function mainTill(): Till
    {
        $user = auth()->user();
        
        return Till::query()
            ->where('code', 'MAIN')
            ->where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function getSelectedTill(): Till
    {
        $user = auth()->user();

        // For users with settings.access, skip permanent till entirely
        // They can switch tills freely and only use session till
        if ($user && $user->hasPermissionTo('settings.access')) {
            if ($user->session_till_id) {
                $till = Till::find($user->session_till_id);
                if ($till && $till->tenant_id === $user->tenant_id && $till->is_active) {
                    return $till;
                }
            }

            // For admins with no session till, find an available till
            $availableTill = Till::where('tenant_id', $user->tenant_id)
                ->where('is_active', true)
                ->where(function ($query) use ($user) {
                    $query->whereNull('current_user_id')
                          ->orWhere('current_user_id', $user->id);
                })
                ->first();

            if ($availableTill) {
                return $availableTill;
            }

            // Last resort: return main till even if in use
            return $this->mainTill();
        }

        // For regular users, check permanent till assignment first
        if ($user && $user->permanent_till_id) {
            $till = Till::find($user->permanent_till_id);
            if ($till && $till->tenant_id === $user->tenant_id && $till->is_active) {
                return $till;
            }
        }

        // Fall back to session till for backward compatibility
        if ($user && $user->session_till_id) {
            $till = Till::find($user->session_till_id);
            if ($till && $till->tenant_id === $user->tenant_id && $till->is_active) {
                return $till;
            }
        }

        // Find an available till if no permanent/session till, no user, or till is invalid
        // Prioritize the main till if it's available, otherwise find any available till
        $mainTill = Till::where('code', 'MAIN')
            ->where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->first();

        if ($mainTill && (!$mainTill->isInUse() || $mainTill->current_user_id == $user->id)) {
            return $mainTill;
        }

        // Find any available till
        $availableTill = Till::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->whereNull('current_user_id')
                      ->orWhere('current_user_id', $user->id);
            })
            ->first();

        if ($availableTill) {
            return $availableTill;
        }

        // Last resort: return main till even if in use (for display purposes)
        return $this->mainTill();
    }

    public function recordSale(
        float $amount,
        ?Model $reference = null,
        ?int $userId = null
    ): CashMovement {
        $movement = $this->record(
            type: 'in',
            source: 'sale',
            amount: $amount,
            reason: 'Cash Sale',
            reference: $reference,
            userId: $userId,
        );

        $this->auditService->log('cash.sale', "Cash sale recorded: Rs. {$amount}", 'info', 'tenant_user', auth()->user()->email, [
            'movement_id' => $movement->id,
            'amount' => $amount,
            'reference_type' => $reference ? $reference->getMorphClass() : null,
            'reference_id' => $reference ? $reference->id : null,
        ]);

        return $movement;
    }

    public function recordRefund(
        float $amount,
        ?Model $reference = null,
        ?int $userId = null
    ): CashMovement {
        $movement = $this->record(
            type: 'out',
            source: 'refund',
            amount: $amount,
            reason: 'Cash Refund',
            reference: $reference,
            userId: $userId,
        );

        $this->auditService->log('cash.refund', "Cash refund recorded: Rs. {$amount}", 'warning', 'tenant_user', auth()->user()->email, [
            'movement_id' => $movement->id,
            'amount' => $amount,
            'reference_type' => $reference ? $reference->getMorphClass() : null,
            'reference_id' => $reference ? $reference->id : null,
        ]);

        return $movement;
    }

    public function recordCashIn(
        float $amount,
        string $reason,
        ?string $description = null,
        ?int $userId = null
    ): CashMovement {
        $movement = $this->record(
            type: 'in',
            source: 'manual',
            amount: $amount,
            reason: $reason,
            description: $description,
            userId: $userId,
        );

        $this->auditService->log('cash.in', "Cash in recorded: Rs. {$amount} - {$reason}", 'info', 'tenant_user', auth()->user()->email, [
            'movement_id' => $movement->id,
            'amount' => $amount,
            'reason' => $reason,
            'description' => $description,
        ]);

        return $movement;
    }

    public function recordCashOut(
        float $amount,
        string $reason,
        ?string $description = null,
        ?int $userId = null
    ): CashMovement {
        return DB::transaction(function () use (
            $amount,
            $reason,
            $description,
            $userId
        ) {
            $till = $this->getSelectedTill();
            $currentClosure = $this->lastClosure($till);

            $expected = $this->expectedBalance($till, $currentClosure && !$currentClosure->closed_at ? $currentClosure : null);

            if ($amount > $expected) {
                throw new RuntimeException(
                    'Cash out amount exceeds the current Till balance.'
                );
            }

            $movement = $this->record(
                type: 'out',
                source: 'manual',
                amount: $amount,
                reason: $reason,
                description: $description,
                userId: $userId,
                till: $till,
            );

            $this->auditService->log('cash.out', "Cash out recorded: Rs. {$amount} - {$reason}", 'warning', 'tenant_user', auth()->user()->email, [
                'movement_id' => $movement->id,
                'amount' => $amount,
                'reason' => $reason,
                'description' => $description,
            ]);

            return $movement;
        });
    }

    public function expectedBalance(?Till $till = null, ?TillClosure $closure = null): float
    {
        $till ??= $this->getSelectedTill();

        $movements = $till->cashMovements();

        // Filter by closure if provided
        if ($closure) {
            $movements = $movements->where('till_closure_id', $closure->id);
        }

        $in = (float) $movements
            ->where('type', 'in')
            ->sum('amount');

        $out = (float) $movements
            ->where('type', 'out')
            ->sum('amount');

        $openingBalance = $closure ? $closure->opening_balance : $till->opening_balance;

        // If there are no movements for this closure yet, return just the opening balance
        if ($in === 0.0 && $out === 0.0 && $closure) {
            return round((float) $openingBalance, 2);
        }

        return round(
            (float) $openingBalance + $in - $out,
            2
        );
    }

    public function lastClosure(?Till $till = null): ?TillClosure
    {
        $till ??= $this->getSelectedTill();

        return $till->closures()->latest()->first();
    }

    public function openShift(
        float $openingBalance,
        ?string $notes = null,
        ?string $varianceReason = null,
        ?int $userId = null,
        ?Till $till = null
    ): TillClosure {
        $till = $till ?? $this->getSelectedTill();

        return DB::transaction(function () use ($till, $openingBalance, $notes, $varianceReason, $userId) {
            $lastClosure = $this->lastClosure($till);

            if ($lastClosure && !$lastClosure->closed_at) {
                throw new RuntimeException(
                    'Cannot open new shift. Previous shift is still open.'
                );
            }

            // Calculate opening variance from previous closing balance
            $previousClosingBalance = $lastClosure ? $lastClosure->counted_balance : 0;
            $openingVariance = $openingBalance - $previousClosingBalance;

            $closure = TillClosure::create([
                'tenant_id' => $till->tenant_id,
                'till_id' => $till->id,
                'user_id' => $userId ?? auth()->id(),
                'opening_balance' => $openingBalance,
                'expected_balance' => $openingBalance,
                'counted_balance' => $openingBalance,
                'discrepancy' => 0,
                'cash_sales' => 0,
                'card_sales' => 0,
                'mobile_money_sales' => 0,
                'bank_transfer_sales' => 0,
                'cheque_sales' => 0,
                'other_payment_sales' => 0,
                'total_sales' => 0,
                'cash_in' => 0,
                'cash_out' => 0,
                'notes' => $notes,
                'variance_reason' => null, // Closing variance (null on open)
                'opening_variance_reason' => $varianceReason,
                'opening_variance' => $openingVariance,
                'opened_at' => now(),
                'closed_at' => null,
            ]);

            \Log::info('Closure created with variance', [
                'closure_id' => $closure->id,
                'opening_variance' => $openingVariance,
                'opening_variance_reason' => $varianceReason,
                'saved_opening_variance' => $closure->opening_variance,
                'saved_opening_variance_reason' => $closure->opening_variance_reason,
            ]);

            return $closure;
        });
    }

    public function closeShift(
        float $countedBalance,
        ?array $denominationBreakdown = null,
        ?string $notes = null,
        ?string $varianceReason = null,
        ?int $userId = null
    ): TillClosure {
        $till = $this->getSelectedTill();

        return DB::transaction(function () use ($till, $countedBalance, $denominationBreakdown, $notes, $varianceReason, $userId) {
            $closure = $this->lastClosure($till);

            if (!$closure) {
                throw new RuntimeException(
                    'No open shift found. Please open a shift first.'
                );
            }

            if ($closure->closed_at) {
                throw new RuntimeException(
                    'Shift is already closed.'
                );
            }

            $sinceOpening = $till->cashMovements()
                ->where('till_closure_id', $closure->id);

            $cashSales = (float) (clone $sinceOpening)
                ->where('type', 'in')
                ->where('source', 'sale')
                ->sum('amount');

            $cashIn = (float) (clone $sinceOpening)
                ->where('type', 'in')
                ->where('source', 'manual')
                ->sum('amount');

            $cashOut = (float) (clone $sinceOpening)
                ->where('type', 'out')
                ->where('source', 'manual')
                ->sum('amount');

            // Get payment method sales from payments (since closure opened)
            if ($closure->opened_at) {
                $payments = \App\Models\Payment::where('created_at', '>=', $closure->opened_at);

                \Log::info('Payments during closure', [
                    'closure_id' => $closure->id,
                    'opened_at' => $closure->opened_at,
                    'payment_count' => $payments->count(),
                ]);
            } else {
                $payments = \App\Models\Payment::where('id', '<', 0); // Return empty query
            }

            $cardSales = (float) (clone $payments)
                ->where('method', 'card')
                ->sum('amount');

            $mobileMoneySales = (float) (clone $payments)
                ->where('method', 'upi')
                ->sum('amount');

            $bankTransferSales = (float) (clone $payments)
                ->where('method', 'bank_transfer')
                ->sum('amount');

            // Only count cheques that have been received/cleared
            $chequeSales = (float) (clone $payments)
                ->where('method', 'cheque')
                ->where('payment_received', true)
                ->where('is_bounced', false)
                ->sum('amount');

            $otherPaymentSales = (float) (clone $payments)
                ->whereNotIn('method', ['cash', 'card', 'upi', 'bank_transfer', 'cheque'])
                ->sum('amount');

            $totalSales = $cashSales + $cardSales + $mobileMoneySales + $bankTransferSales + $chequeSales + $otherPaymentSales;

            $expectedBalance = round(
                $closure->opening_balance + $cashSales + $cashIn - $cashOut,
                2
            );

            $discrepancy = round($countedBalance - $expectedBalance, 2);

            $closure->update([
                'expected_balance' => $expectedBalance,
                'counted_balance' => $countedBalance,
                'discrepancy' => $discrepancy,
                'cash_sales' => $cashSales,
                'card_sales' => $cardSales,
                'mobile_money_sales' => $mobileMoneySales,
                'bank_transfer_sales' => $bankTransferSales,
                'cheque_sales' => $chequeSales,
                'other_payment_sales' => $otherPaymentSales,
                'total_sales' => $totalSales,
                'cash_in' => $cashIn,
                'cash_out' => $cashOut,
                'denomination_breakdown' => $denominationBreakdown,
                'notes' => $notes,
                'variance_reason' => $varianceReason, // Closing variance reason
                'closed_at' => now(),
            ]);

            \Log::info('Closure updated with closed_at', ['closure_id' => $closure->id, 'closed_at' => $closure->closed_at]);

            \Log::info('Till closed', [
                'closure_id' => $closure->id,
                'opening_balance' => $closure->opening_balance,
                'counted_balance' => $closure->counted_balance,
                'expected_balance' => $closure->expected_balance,
                'closed_at' => $closure->closed_at,
            ]);

            // Release the till when shift is closed so it doesn't show as "In Use"
            $till->release();

            $freshClosure = $closure->fresh();
            \Log::info('Fresh closure after close', ['closed_at' => $freshClosure->closed_at]);

            return $freshClosure;
        });
    }

    public function getCashMovementSummary(?Till $till = null, ?\DateTime $since = null): array
    {
        $till ??= $this->getSelectedTill();
        $currentClosure = $this->lastClosure($till);
        $since ??= $currentClosure?->opened_at ?? $till->created_at;

        $movements = $till->cashMovements()
            ->where('created_at', '>=', $since);

        // Filter by current closure if it's open
        if ($currentClosure && !$currentClosure->closed_at) {
            $movements = $movements->where('till_closure_id', $currentClosure->id);
        } else {
            // If till is closed, return empty summary
            return [
                'cash_sales' => 0,
                'card_sales' => 0,
                'mobile_money_sales' => 0,
                'bank_transfer_sales' => 0,
                'cheque_sales' => 0,
                'other_payment_sales' => 0,
                'total_sales' => 0,
                'cash_in' => 0,
                'cash_out' => 0,
                'net_change' => 0,
            ];
        }

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

        // Get payment method sales from payments
        // Filter by current closure if it's open
        if ($currentClosure && !$currentClosure->closed_at && $currentClosure->opened_at) {
            $payments = \App\Models\Payment::where('created_at', '>=', $currentClosure->opened_at);
        } else {
            $payments = \App\Models\Payment::where('created_at', '>=', $since);
        }

        $cardSales = (float) (clone $payments)
            ->where('method', 'card')
            ->sum('amount');

        $mobileMoneySales = (float) (clone $payments)
            ->where('method', 'upi')
            ->sum('amount');

        $bankTransferSales = (float) (clone $payments)
            ->where('method', 'bank_transfer')
            ->sum('amount');

        // Only count cheques that have been received/cleared
        $chequeSales = (float) (clone $payments)
            ->where('method', 'cheque')
            ->where('payment_received', true)
            ->where('is_bounced', false)
            ->sum('amount');

        $otherPaymentSales = (float) (clone $payments)
            ->whereNotIn('method', ['cash', 'card', 'upi', 'bank_transfer', 'cheque'])
            ->sum('amount');

        $totalSales = $cashSales + $cardSales + $mobileMoneySales + $bankTransferSales + $chequeSales + $otherPaymentSales;

        return [
            'cash_sales' => $cashSales,
            'card_sales' => $cardSales,
            'mobile_money_sales' => $mobileMoneySales,
            'bank_transfer_sales' => $bankTransferSales,
            'cheque_sales' => $chequeSales,
            'other_payment_sales' => $otherPaymentSales,
            'total_sales' => $totalSales,
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'net_change' => round($cashSales + $cashIn - $cashOut, 2),
        ];
    }

    private function record(
        string $type,
        string $source,
        float $amount,
        string $reason,
        ?string $description = null,
        ?Model $reference = null,
        ?int $userId = null,
        ?Till $till = null,
    ): CashMovement {
        if ($amount <= 0) {
            throw new RuntimeException(
                'Cash movement amount must be greater than zero.'
            );
        }

        $till ??= $this->getSelectedTill();
        $currentClosure = $this->lastClosure($till);

        $closureId = null;
        if ($currentClosure && !$currentClosure->closed_at) {
            $closureId = $currentClosure->id;
        }

        $movement = new CashMovement([
            'tenant_id' => $till->tenant_id,
            'till_id' => $till->id,
            'till_closure_id' => $closureId,
            'user_id' => $userId ?? auth()->id(),
            'type' => $type,
            'source' => $source,
            'amount' => round($amount, 2),
            'reason' => $reason,
            'description' => $description,
        ]);

        if ($reference) {
            $movement->reference()->associate($reference);
        }

        $movement->save();

        return $movement;
    }
}