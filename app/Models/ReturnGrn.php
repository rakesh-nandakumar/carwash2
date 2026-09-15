<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnGrn extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $fillable = [
        'return_grn_number',
        'supplier_id',
        'reference',
        'address',
        'reason',
        'notes',
        'total_cost',
        'status_id',
        'returned_by',
        'returned_at',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
        'deleted_at' => 'datetime',
        'total_cost' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
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
        return $this->hasMany(ReturnGrnItem::class);
    }

    public function isDraft(): bool
    {
        return $this->status_id == 56;
    }

    public function isConfirmed(): bool
    {
        return $this->status_id == 57;
    }

    public function isDeleted(): bool
    {
        return $this->status_id == 58;
    }
}
