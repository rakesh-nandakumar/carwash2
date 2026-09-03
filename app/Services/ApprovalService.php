<?php

namespace App\Services;

use App\Models\JobService;
use App\Models\ServiceApproval;
use App\Models\AdditionalWorkRequest;
use App\Models\Job;
use App\Enums\ApprovalStatus;
use App\Enums\JobStatus;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public function requestServiceApproval(int $jobServiceId, float $estimatedCost, string $reason): ServiceApproval
    {
        return DB::transaction(function () use ($jobServiceId, $estimatedCost, $reason) {
            $jobService = JobService::with('job')->findOrFail($jobServiceId);
            
            $approval = ServiceApproval::create([
                'job_service_id' => $jobServiceId,
                'status' => ApprovalStatus::PENDING,
                'approved_amount' => $estimatedCost,
            ]);

            // Update job status to await customer approval
            $job = $jobService->job;
            if ($job->status === JobStatus::INSPECTION_COMPLETED || $job->status === JobStatus::APPROVED) {
                $job->transitionTo(JobStatus::CUSTOMER_APPROVAL_PENDING, auth()->user());
            }

            // Notify customer
            app(CommunicationService::class)->notifyAdditionalWorkRequired(
                $job,
                $jobService->name_snapshot,
                $estimatedCost
            );

            return $approval;
        });
    }

    public function approveService(int $approvalId, ?float $approvedAmount = null): ServiceApproval
    {
        return DB::transaction(function () use ($approvalId, $approvedAmount) {
            $approval = ServiceApproval::with('jobService.job')->findOrFail($approvalId);
            
            $approval->update([
                'status' => ApprovalStatus::APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approved_amount' => $approvedAmount ?? $approval->approved_amount,
            ]);

            // Update job service
            $approval->jobService->update([
                'approval_status' => 'approved',
                'unit_price' => $approval->approved_amount,
            ]);

            // Check if all pending approvals are done
            $this->checkJobApprovals($approval->jobService->job_id);

            return $approval;
        });
    }

    public function rejectService(int $approvalId, string $reason): ServiceApproval
    {
        return DB::transaction(function () use ($approvalId, $reason) {
            $approval = ServiceApproval::with('jobService.job')->findOrFail($approvalId);
            
            $approval->update([
                'status' => ApprovalStatus::REJECTED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            // Update job service
            $approval->jobService->update([
                'approval_status' => 'rejected',
                'removed' => true,
                'removal_reason' => $reason,
            ]);

            // Check if all pending approvals are done
            $this->checkJobApprovals($approval->jobService->job_id);

            return $approval;
        });
    }

    public function partiallyApproveService(int $approvalId, float $approvedAmount, string $reason): ServiceApproval
    {
        return DB::transaction(function () use ($approvalId, $approvedAmount, $reason) {
            $approval = ServiceApproval::with('jobService.job')->findOrFail($approvalId);
            
            $approval->update([
                'status' => ApprovalStatus::PARTIALLY_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approved_amount' => $approvedAmount,
            ]);

            // Update job service
            $approval->jobService->update([
                'approval_status' => 'approved',
                'unit_price' => $approvedAmount,
            ]);

            $this->checkJobApprovals($approval->jobService->job_id);

            return $approval;
        });
    }

    public function requestAdditionalWork(int $jobId, array $workData): AdditionalWorkRequest
    {
        return DB::transaction(function () use ($jobId, $workData) {
            $job = Job::findOrFail($jobId);
            
            $request = AdditionalWorkRequest::create([
                'job_id' => $jobId,
                'requested_by' => auth()->id(),
                'title' => $workData['title'],
                'description' => $workData['description'],
                'estimated_cost' => $workData['estimated_cost'],
                'status' => 'pending',
            ]);

            // Notify customer
            app(CommunicationService::class)->notifyAdditionalWorkRequired(
                $job,
                $workData['title'],
                $workData['estimated_cost']
            );

            return $request;
        });
    }

    public function approveAdditionalWork(int $requestId): AdditionalWorkRequest
    {
        return DB::transaction(function () use ($requestId) {
            $request = AdditionalWorkRequest::with('job')->findOrFail($requestId);
            
            $request->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Add the approved work to the job
            // This would typically create a new JobService entry
            // Implementation depends on specific business logic

            return $request;
        });
    }

    public function rejectAdditionalWork(int $requestId, string $reason): AdditionalWorkRequest
    {
        return DB::transaction(function () use ($requestId, $reason) {
            $request = AdditionalWorkRequest::findOrFail($requestId);
            
            $request->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            return $request;
        });
    }

    private function checkJobApprovals(int $jobId): void
    {
        $job = Job::with(['services' => function ($query) {
            $query->where('approval_status', 'pending');
        }])->findOrFail($jobId);

        // If no pending approvals, move job to approved status
        if ($job->services->where('approval_status', 'pending')->isEmpty()) {
            if ($job->status === JobStatus::CUSTOMER_APPROVAL_PENDING) {
                $job->transitionTo(JobStatus::APPROVED, auth()->user());
            }
        }
    }

    public function getPendingApprovals(int $jobId): array
    {
        $job = Job::with(['services.approval', 'additionalWorkRequests'])->findOrFail($jobId);
        
        return [
            'pending_services' => $job->services->filter(function ($service) {
                return $service->approval_status === 'pending';
            })->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name_snapshot,
                    'estimated_cost' => $service->approval->approved_amount ?? 0,
                    'approval_id' => $service->approval->id ?? null,
                ];
            })->values(),
            'pending_additional_work' => $job->additionalWorkRequests->where('status', 'pending')->map(function ($request) {
                return [
                    'id' => $request->id,
                    'title' => $request->title,
                    'description' => $request->description,
                    'estimated_cost' => $request->estimated_cost,
                ];
            })->values(),
        ];
    }

    public function getApprovalHistory(int $jobId): array
    {
        $job = Job::with(['services.approval', 'additionalWorkRequests'])->findOrFail($jobId);
        
        $history = [];
        
        foreach ($job->services as $service) {
            if ($service->approval) {
                $history[] = [
                    'type' => 'service',
                    'name' => $service->name_snapshot,
                    'status' => $service->approval->status,
                    'approved_by' => $service->approval->approvedBy->name ?? null,
                    'approved_at' => $service->approval->approved_at?->format('Y-m-d H:i:s'),
                    'approved_amount' => $service->approval->approved_amount,
                    'rejection_reason' => $service->approval->rejection_reason,
                ];
            }
        }
        
        foreach ($job->additionalWorkRequests as $request) {
            $history[] = [
                'type' => 'additional_work',
                'title' => $request->title,
                'status' => $request->status,
                'approved_by' => $request->approvedBy->name ?? null,
                'approved_at' => $request->approved_at?->format('Y-m-d H:i:s'),
                'estimated_cost' => $request->estimated_cost,
                'rejection_reason' => $request->rejection_reason,
            ];
        }
        
        return $history;
    }
}
