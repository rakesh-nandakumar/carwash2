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
        'cheque_sales' => 'decimal:2',
        'other_payment_sales' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'cash_in' => 'decimal:2',
        'cash_out' => 'decimal:2',
        'denomination_breakdown' => 'array',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_variance' => 'decimal:2',
    ];

    protected $fillable = [
        'till_id',
        'user_id',
        'tenant_id',
        'opening_balance',
        'expected_balance',
        'counted_balance',
        'discrepancy',
        'cash_sales',
        'card_sales',
        'mobile_money_sales',
        'bank_transfer_sales',
        'cheque_sales',
        'other_payment_sales',
        'total_sales',
        'cash_in',
        'cash_out',
        'denomination_breakdown',
        'notes',
        'variance_reason',
        'opening_variance_reason',
        'opening_variance',
        'opened_at',
        'closed_at',
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
