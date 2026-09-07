<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Till extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    public function getExpectedBalanceAttribute(): float
    {
        return (float) $this->opening_balance
            + (float) $this->cashMovements()
                ->where('type', 'in')
                ->sum('amount')
            - (float) $this->cashMovements()
                ->where('type', 'out')
                ->sum('amount');
    }
}