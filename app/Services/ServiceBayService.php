<?php

namespace App\Services;

use App\Models\ServiceBay;
use App\Models\ServiceBayAssignment;
use App\Models\Job;
use Illuminate\Support\Facades\DB;

class ServiceBayService
{
    public function createServiceBay(array $data): ServiceBay
    {
        return ServiceBay::create([
            'branch_id' => $data['branch_id'],
            'name' => $data['name'],
            'status' => 'available',
        ]);
    }

    public function assignJobToBay(int $jobId, int $serviceBayId, ?int $assignedBy = null): ServiceBayAssignment
    {
        return DB::transaction(function () use ($jobId, $serviceBayId, $assignedBy) {
            $job = Job::findOrFail($jobId);
            $serviceBay = ServiceBay::findOrFail($serviceBayId);

            if ($serviceBay->status !== 'available') {
                abort(422, 'Service bay is not available');
            }

            // Release any existing bay assignment
            $existingAssignment = ServiceBayAssignment::where('job_id', $jobId)
                ->whereNull('released_at')
                ->first();
            
            if ($existingAssignment) {
                $existingAssignment->update([
                    'released_at' => now(),
                ]);
                // Mark previous bay as available
                $existingAssignment->serviceBay->update(['status' => 'available']);
            }

            // Create new assignment
            $assignment = ServiceBayAssignment::create([
                'job_id' => $jobId,
                'service_bay_id' => $serviceBayId,
                'assigned_at' => now(),
                'assigned_by' => $assignedBy ?? auth()->id(),
            ]);

            // Mark bay as occupied
            $serviceBay->update(['status' => 'occupied']);

            return $assignment;
        });
    }

    public function releaseBay(int $jobId, ?int $releasedBy = null): void
    {
        DB::transaction(function () use ($jobId, $releasedBy) {
            $assignment = ServiceBayAssignment::where('job_id', $jobId)
                ->whereNull('released_at')
                ->firstOrFail();

            $assignment->update([
                'released_at' => now(),
            ]);

            // Mark bay as available
            $assignment->serviceBay->update(['status' => 'available']);
        });
    }

    public function getBayStatus(int $branchId): array
    {
        $serviceBays = ServiceBay::where('branch_id', $branchId)
            ->with(['currentAssignment.job', 'currentAssignment.job.vehicle', 'currentAssignment.job.customer'])
            ->get();

        return $serviceBays->map(function ($bay) {
            return [
                'id' => $bay->id,
                'name' => $bay->name,
                'status' => $bay->status,
                'current_job' => $bay->currentAssignment ? [
                    'job_id' => $bay->currentAssignment->job_id,
                    'job_number' => $bay->currentAssignment->job->job_number,
                    'vehicle' => $bay->currentAssignment->job->vehicle->registration_number,
                    'customer' => $bay->currentAssignment->job->customer->full_name,
                    'assigned_at' => $bay->currentAssignment->assigned_at->format('H:i'),
                ] : null,
            ];
        })->toArray();
    }

    public function getBayUtilization(string $startDate, string $endDate, ?int $branchId = null): array
    {
        $query = ServiceBayAssignment::whereBetween('assigned_at', [$startDate, $endDate]);

        if ($branchId) {
            $query->whereHas('serviceBay', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $assignments = $query->with('serviceBay')->get();

        $totalBays = $branchId 
            ? ServiceBay::where('branch_id', $branchId)->count()
            : ServiceBay::count();

        $utilization = $assignments->groupBy('service_bay_id')->map(function ($bayAssignments) {
            $totalMinutes = $bayAssignments->sum(function ($assignment) {
                $end = $assignment->released_at ?? now();
                return $assignment->assigned_at->diffInMinutes($end);
            });

            return [
                'bay_id' => $bayAssignments->first()->service_bay_id,
                'bay_name' => $bayAssignments->first()->serviceBay->name,
                'total_minutes' => $totalMinutes,
                'total_hours' => round($totalMinutes / 60, 2),
                'assignment_count' => $bayAssignments->count(),
            ];
        });

        return [
            'total_bays' => $totalBays,
            'utilization_by_bay' => $utilization,
            'total_assignments' => $assignments->count(),
        ];
    }

    public function getAvailableBays(int $branchId): array
    {
        return ServiceBay::where('branch_id', $branchId)
            ->where('status', 'available')
            ->get()
            ->map(function ($bay) {
                return [
                    'id' => $bay->id,
                    'name' => $bay->name,
                ];
            })
            ->toArray();
    }
}
