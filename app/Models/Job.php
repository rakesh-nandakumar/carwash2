<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\JobStatus;
use Illuminate\Support\Facades\DB;

class Job extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'completed_at' => 'datetime',
        'status' => JobStatus::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($job) {
            if (empty($job->job_number)) {
                // Use database lock to prevent race conditions
                $job->job_number = DB::transaction(function () use ($job) {
                    $tenantId = $job->tenant_id ?? auth()->user()->tenant_id ?? null;

                    // Count jobs for this tenant in the current year
                    $count = static::where('tenant_id', $tenantId)
                        ->whereYear('created_at', date('Y'))
                        ->lockForUpdate()
                        ->count();

                    // Generate job number with tenant prefix
                    return $tenantId . '-JOB-' . date('Y') . '-' . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
                });
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class)->withDefault();
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(JobService::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(JobPart::class);
    }

    public function inspection(): HasOne
    {
        return $this->hasOne(Inspection::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function qualityCheck(): HasOne
    {
        return $this->hasOne(QualityCheck::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(JobStatusHistory::class);
    }

    public function additionalWorkRequests(): HasMany
    {
        return $this->hasMany(AdditionalWorkRequest::class);
    }

    public function serviceBayAssignments(): HasMany
    {
        return $this->hasMany(ServiceBayAssignment::class);
    }

    public function workTimeLogs(): HasMany
    {
        return $this->hasMany(WorkTimeLog::class);
    }

    public function customerSuppliedParts(): HasMany
    {
        return $this->hasMany(CustomerSuppliedPart::class);
    }

    public function emergencyPurchases(): HasMany
    {
        return $this->hasMany(EmergencyPurchase::class);
    }

    public function communications(): HasMany
    {
        return $this->hasMany(Communication::class);
    }

    /**
     * Transition job to a new status with validation and history tracking
     */
    public function transitionTo(JobStatus $newStatus, $user, ?string $reason = null): void
    {
        $oldStatus = $this->status;

        // Validate transition is allowed
        if (!$oldStatus->canTransitionTo($newStatus)) {
            abort(422, "Cannot transition from {$oldStatus->value} to {$newStatus->value}");
        }

        // Update status
        $this->status = $newStatus;

        // Set timestamps based on status
        if ($newStatus === JobStatus::CHECKED_IN && !$this->checked_in_at) {
            $this->checked_in_at = now();
        }

        if ($newStatus === JobStatus::DELIVERED && !$this->completed_at) {
            $this->completed_at = now();
        }

        $this->save();

        // Create status history record
        JobStatusHistory::create([
            'job_id' => $this->id,
            'from_status' => $oldStatus->value,
            'to_status' => $newStatus->value,
            'changed_by' => $user->id ?? auth()->id(),
            'reason' => $reason,
        ]);
    }
}
