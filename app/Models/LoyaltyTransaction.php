<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\LoyaltyTransactionType;

class LoyaltyTransaction extends Model
{ use BelongsToTenant;
    protected $guarded = [];

    protected $casts = [
        'type' => LoyaltyTransactionType::class,
        'points' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function loyaltyAccount(): BelongsTo
    {
        return $this->belongsTo(LoyaltyAccount::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
