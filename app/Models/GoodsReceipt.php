<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceipt extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $fillable = [
        'grn_number',
        'supplier_id',
        'purchase_order_id',
        'reference',
        'notes',
        'received_by',
        'received_at',
        'status_id',
        'confirmed_by',
        'confirmed_at',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Setting::class, 'status_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function isDraft(): bool
    {
        return $this->status?->key === 'draft';
    }

    public function isConfirmed(): bool
    {
        return $this->status?->key === 'confirmed';
    }

    public function isDeleted(): bool
    {
        return $this->status?->key === 'deleted';
    }
}
