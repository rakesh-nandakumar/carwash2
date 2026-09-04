<?php

namespace App\Services;

use App\Models\QualityCheck;
use App\Models\Job;
use App\Enums\JobStatus;
use Illuminate\Support\Facades\DB;

class QualityControlService
{
    public function createQualityCheck(int $jobId, array $data): QualityCheck
    {
        return DB::transaction(function () use ($jobId, $data) {
            $job = Job::findOrFail($jobId);
            
            $qualityCheck = QualityCheck::create([
                'job_id' => $jobId,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            // Update job status
            if ($job->status === JobStatus::IN_SERVICE) {
                $job->transitionTo(JobStatus::QUALITY_CHECK, auth()->user());
            }

            return $qualityCheck;
        });
    }

    public function approveQualityCheck(int $qualityCheckId, ?string $notes = null): QualityCheck
    {
        return DB::transaction(function () use ($qualityCheckId, $notes) {
            $qualityCheck = QualityCheck::with('job')->findOrFail($qualityCheckId);
            
            $qualityCheck->update([
                'status' => 'passed',
                'checked_by' => auth()->id(),
                'notes' => $notes ?? $qualityCheck->notes,
            ]);

            // Update job status to completed
            $job = $qualityCheck->job;
            if ($job->status === JobStatus::QUALITY_CHECK) {
                $job->transitionTo(JobStatus::COMPLETED, auth()->user());
            }

            return $qualityCheck;
        });
    }

    public function rejectQualityCheck(int $qualityCheckId, string $reason, ?string $notes = null): QualityCheck
    {
        return DB::transaction(function () use ($qualityCheckId, $reason, $notes) {
            $qualityCheck = QualityCheck::with('job')->findOrFail($qualityCheckId);
            
            $qualityCheck->update([
                'status' => 'failed',
                'checked_by' => auth()->id(),
                'notes' => ($notes ?? $qualityCheck->notes) . "\nRejection reason: " . $reason,
            ]);

            // Return job to in_service status for rework
            $job = $qualityCheck->job;
            if ($job->status === JobStatus::QUALITY_CHECK) {
                $job->transitionTo(JobStatus::IN_SERVICE, auth()->user(), 'Quality check failed: ' . $reason);
            }

            return $qualityCheck;
        });
    }

    public function getQualityCheckDetails(int $jobId): array
    {
        $qualityCheck = QualityCheck::with(['job', 'job.vehicle', 'job.customer', 'checkedBy'])
            ->where('job_id', $jobId)
            ->firstOrFail();

        return [
            'quality_check' => $qualityCheck,
            'job' => $qualityCheck->job,
            'vehicle' => $qualityCheck->job->vehicle,
            'customer' => $qualityCheck->job->customer,
            'inspector' => $qualityCheck->checkedBy,
        ];
    }

    public function getPendingQualityChecks(?int $branchId = null): array
    {
        $query = QualityCheck::with(['job.vehicle', 'job.customer', 'job.technician'])
            ->where('status', 'pending');

        if ($branchId) {
            $query->whereHas('job', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        return $query->get()->map(function ($qc) {
            return [
                'id' => $qc->id,
                'job_id' => $qc->job_id,
                'job_number' => $qc->job->job_number,
                'vehicle' => $qc->job->vehicle->registration_number,
                'customer' => $qc->job->customer->full_name,
                'technician' => $qc->job->technician->name ?? 'Unassigned',
                'created_at' => $qc->job->completed_at?->format('Y-m-d H:i:s') ?? $qc->job->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    public function getQualityCheckHistory(int $jobId): array
    {
        return QualityCheck::where('job_id', $jobId)
            ->with('checkedBy')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($qc) {
                return [
                    'status' => $qc->status,
                    'notes' => $qc->notes,
                    'checked_by' => $qc->checkedBy->name ?? null,
                    'checked_at' => $qc->updated_at->format('Y-m-d H:i:s'),
                ];
            })->toArray();
    }
}
