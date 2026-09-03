<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\DiscountType;

class Discount extends Model
{
    protected $guarded = [];

    protected $casts = [
        'type' => DiscountType::class,
        'value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'applicable_ids' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'active' => 'boolean',
        'requires_approval' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
