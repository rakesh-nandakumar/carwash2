<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\ApprovalStatus;

class ServiceApproval extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status' => ApprovalStatus::class,
        'approved_at' => 'datetime',
    ];

    public function jobService(): BelongsTo
    {
        return $this->belongsTo(JobService::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
