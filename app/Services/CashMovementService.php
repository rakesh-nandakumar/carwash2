<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\Till;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CashMovementService
{
    public function mainTill(): Till
    {
        return Till::query()
            ->where('code', 'MAIN')
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function recordSale(
        float $amount,
        ?Model $reference = null,
        ?int $userId = null
    ): CashMovement {
        return $this->record(
            type: 'in',
            source: 'sale',
            amount: $amount,
            reason: 'Cash Sale',
            reference: $reference,
            userId: $userId,
        );
    }

    public function recordRefund(
        float $amount,
        ?Model $reference = null,
        ?int $userId = null
    ): CashMovement {
        return $this->record(
            type: 'out',
            source: 'refund',
            amount: $amount,
            reason: 'Cash Refund',
            reference: $reference,
            userId: $userId,
        );
    }

    public function recordCashIn(
        float $amount,
        string $reason,
        ?string $description = null,
        ?int $userId = null
    ): CashMovement {
        return $this->record(
            type: 'in',
            source: 'manual',
            amount: $amount,
            reason: $reason,
            description: $description,
            userId: $userId,
        );
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
            $till = $this->mainTill();

            $expected = $this->expectedBalance($till);

            if ($amount > $expected) {
                throw new RuntimeException(
                    'Cash out amount exceeds the current Till balance.'
                );
            }

            return $this->record(
                type: 'out',
                source: 'manual',
                amount: $amount,
                reason: $reason,
                description: $description,
                userId: $userId,
                till: $till,
            );
        });
    }

    public function recordCashDrop(
        float $amount,
        string $reason = 'Cash Drop',
        ?string $description = null,
        ?int $userId = null
    ): CashMovement {
        return $this->record(
            type: 'out',
            source: 'drop',
            amount: $amount,
            reason: $reason,
            description: $description,
            userId: $userId,
        );
    }

    public function expectedBalance(?Till $till = null): float
    {
        $till ??= $this->mainTill();

        $in = (float) $till->cashMovements()
            ->where('type', 'in')
            ->sum('amount');

        $out = (float) $till->cashMovements()
            ->where('type', 'out')
            ->sum('amount');

        return round(
            (float) $till->opening_balance + $in - $out,
            2
        );
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

        $till ??= $this->mainTill();

        $movement = new CashMovement([
            'tenant_id' => $till->tenant_id,
            'till_id' => $till->id,
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