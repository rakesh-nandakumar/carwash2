<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Till extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'last_activity_at' => 'datetime',
    ];

    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    public function closures(): HasMany
    {
        return $this->hasMany(TillClosure::class);
    }

    public function lastClosure(): HasMany
    {
        return $this->hasMany(TillClosure::class)->latest();
    }

    public function currentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isOpen(): bool
    {
        $lastClosure = $this->lastClosure()->first();
        return $lastClosure && !$lastClosure->closed_at;
    }

    public function isInUse(): bool
    {
        return $this->current_user_id !== null;
    }

    public function markAsInUse(int $userId): void
    {
        $this->update([
            'current_user_id' => $userId,
            'last_activity_at' => now(),
        ]);
    }

    public function isStale(): bool
    {
        if (!$this->last_activity_at) {
            return true; // If no activity timestamp, consider it stale
        }
        
        try {
            // Consider a till stale if no activity for 30 minutes
            return $this->last_activity_at->lt(now()->subMinutes(30));
        } catch (\Exception $e) {
            // If there's any error with date comparison, consider it stale
            return true;
        }
    }

    public function scopeActiveAndAvailable($query, ?int $userId = null)
    {
        $userId = $userId ?? auth()->id();
        $staleThreshold = now()->subMinutes(30);
        
        return $query->where('is_active', true)
            ->where(function ($q) use ($userId, $staleThreshold) {
                $q->whereNull('current_user_id')
                  ->orWhere('current_user_id', $userId)
                  ->orWhere(function ($subQuery) use ($staleThreshold) {
                      $subQuery->whereNotNull('last_activity_at')
                               ->where('last_activity_at', '<', $staleThreshold);
                  });
            });
    }

    public static function autoReleaseStaleTills(): void
    {
        // Release tills that have been inactive for 30+ minutes
        $staleThreshold = now()->subMinutes(30);
        
        self::where('is_active', true)
            ->whereNotNull('current_user_id')
            ->whereNotNull('last_activity_at')
            ->where('last_activity_at', '<', $staleThreshold)
            ->update([
                'current_user_id' => null,
                'last_activity_at' => null,
            ]);
    }

    public function release(): void
    {
        $this->update([
            'current_user_id' => null,
            'last_activity_at' => null,
        ]);
    }

    public function scopeAvailable($query, ?int $userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('current_user_id')
              ->orWhere('current_user_id', $userId);
        });
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