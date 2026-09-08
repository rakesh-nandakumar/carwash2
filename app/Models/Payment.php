<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $casts = [
        'payment_received' => 'boolean',
        'is_bounced' => 'boolean',
        'payment_received_at' => 'datetime',
        'bounced_at' => 'datetime',
        'cheque_due_date' => 'date',
        'follow_up_required' => 'boolean',
        'follow_up_date' => 'datetime',
        'replacement_payment_received' => 'boolean',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'received_by');
    }

    public function replacementPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'replacement_payment_id');
    }

    public function originalPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'replacement_payment_id');
    }

    public function isCheque(): bool
    {
        return $this->method === 'cheque';
    }

    public function isPending(): bool
    {
        return $this->isCheque() && !$this->payment_received && !$this->is_bounced;
    }

    public function isCleared(): bool
    {
        return $this->isCheque() && $this->payment_received && !$this->is_bounced;
    }

    public function isBounced(): bool
    {
        return $this->isCheque() && $this->is_bounced;
    }

    public function markAsReceived(): void
    {
        $this->update([
            'payment_received' => true,
            'payment_received_at' => now(),
        ]);
    }

    public function markAsBounced(string $reason = null): void
    {
        $this->update([
            'is_bounced' => true,
            'bounced_at' => now(),
            'bounce_reason' => $reason,
            'payment_received' => false,
            'follow_up_required' => true,
            'follow_up_date' => now()->addDays(3), // Default follow-up in 3 days
        ]);
    }

    public function markFollowUpComplete(): void
    {
        $this->update([
            'follow_up_required' => false,
            'follow_up_date' => null,
        ]);
    }

    public function recordReplacementPayment(Payment $replacementPayment): void
    {
        $this->update([
            'replacement_payment_received' => true,
            'replacement_payment_id' => $replacementPayment->id,
            'follow_up_required' => false,
        ]);
    }

    public function needsFollowUp(): bool
    {
        return $this->is_bounced && $this->follow_up_required && !$this->replacement_payment_received;
    }

    public function isOverdueForFollowUp(): bool
    {
        return $this->needsFollowUp() && $this->follow_up_date && $this->follow_up_date->isPast();
    }
}
