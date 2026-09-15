<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnGrnItem extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $fillable = [
        'return_grn_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'notes',
        'tenant_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function returnGrn(): BelongsTo
    {
        return $this->belongsTo(ReturnGrn::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
