<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TillClosure extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'expected_balance' => 'decimal:2',
        'counted_balance' => 'decimal:2',
        'discrepancy' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'card_sales' => 'decimal:2',
        'mobile_money_sales' => 'decimal:2',
        'bank_transfer_sales' => 'decimal:2',
        'other_payment_sales' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'cash_in' => 'decimal:2',
        'cash_out' => 'decimal:2',
        'cash_refunds' => 'decimal:2',
        'cash_drops' => 'decimal:2',
        'denomination_breakdown' => 'array',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function till(): BelongsTo
    {
        return $this->belongsTo(Till::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
