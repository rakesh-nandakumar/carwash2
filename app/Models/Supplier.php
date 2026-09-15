<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'is_blacklisted' => 'boolean',
        'blacklisted_at' => 'datetime',
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $supplier): void {
            if (! $supplier->uuid) {
                $supplier->uuid = (string) Str::ulid();
            }
        });
    }

    public function paymentTerms(): BelongsTo
    {
        return $this->belongsTo(Setting::class, 'payment_terms_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Setting::class, 'status_id');
    }

    public function blacklistedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blacklisted_by');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(SupplierAddress::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(SupplierBankAccount::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(SupplierLedger::class)->latest('created_at');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function isActive(): bool
    {
        // For backward compatibility, check the old active field if it exists
        if (array_key_exists('active', $this->attributes)) {
            return (bool) $this->attributes['active'];
        }
        
        // Otherwise use the new status relationship
        return $this->status?->key === 'active' ?? false;
    }

    public function isBlacklisted(): bool
    {
        return $this->is_blacklisted;
    }
}
