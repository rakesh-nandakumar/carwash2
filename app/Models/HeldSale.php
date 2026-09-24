<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class HeldSale extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'customer_id',
        'items',
        'discount_type',
        'discount_value',
        'discount_apply_to',
        'individual_discounts',
    ];

    protected $casts = [
        'items' => 'array',
        'individual_discounts' => 'array',
        'discount_value' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
