<?php

namespace App\Services;

use App\Models\User;
use App\Models\TechnicianSkill;
use App\Models\WorkTimeLog;
use App\Models\Job;
use Illuminate\Support\Facades\DB;

class TechnicianService
{
    public function addSkill(int $technicianId, array $skillData): TechnicianSkill
    {
        return TechnicianSkill::create([
            'technician_id' => $technicianId,
            'service_category_id' => $skillData['service_category_id'] ?? null,
            'skill_name' => $skillData['skill_name'],
            'proficiency' => $skillData['proficiency'] ?? 'intermediate',
        ]);
    }

    public function removeSkill(int $technicianId, string $skillName): void
    {
        TechnicianSkill::where('technician_id', $technicianId)
            ->where('skill_name', $skillName)
            ->delete();
    }

    public function getTechnicianSkills(int $technicianId): array
    {
        return TechnicianSkill::where('technician_id', $technicianId)
            ->with('serviceCategory')
            ->get()
            ->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'skill_name' => $skill->skill_name,
                    'proficiency' => $skill->proficiency,
                    'category' => $skill->serviceCategory?->name,
                ];
            })
            ->toArray();
    }

    public function startWorkTime(int $jobId, int $technicianId): WorkTimeLog
    {
        return DB::transaction(function () use ($jobId, $technicianId) {
            // Check if there's an active work log for this job and technician
            $activeLog = WorkTimeLog::where('job_id', $jobId)
                ->where('technician_id', $technicianId)
                ->whereNull('completed_at')
                ->first();

            if ($activeLog) {
                abort(422, 'Work time already started for this job');
            }

            return WorkTimeLog::create([
                'job_id' => $jobId,
                'technician_id' => $technicianId,
                'started_at' => now(),
            ]);
        });
    }

    public function pauseWorkTime(int $workTimeLogId): WorkTimeLog
    {
        $log = WorkTimeLog::findOrFail($workTimeLogId);
        
        if ($log->paused_at) {
            abort(422, 'Work time already paused');
        }

        $log->update(['paused_at' => now()]);
        
        return $log;
    }

    public function resumeWorkTime(int $workTimeLogId): WorkTimeLog
    {
        $log = WorkTimeLog::findOrFail($workTimeLogId);
        
        if (!$log->paused_at) {
            abort(422, 'Work time is not paused');
        }

        $log->update(['resumed_at' => now()]);
        
        return $log;
    }

    public function completeWorkTime(int $workTimeLogId, ?string $notes = null): WorkTimeLog
    {
        return DB::transaction(function () use ($workTimeLogId, $notes) {
            $log = WorkTimeLog::findOrFail($workTimeLogId);
            
            if ($log->completed_at) {
                abort(422, 'Work time already completed');
            }

            $startedAt = $log->started_at;
            $endedAt = now();
            
            // Calculate duration considering pauses
            $duration = $startedAt->diffInMinutes($endedAt);
            
            if ($log->paused_at && $log->resumed_at) {
                $pauseDuration = $log->paused_at->diffInMinutes($log->resumed_at);
                $duration -= $pauseDuration;
            }

            $log->update([
                'completed_at' => $endedAt,
                'duration_minutes' => $duration,
                'notes' => $notes,
            ]);

            return $log;
        });
    }

    public function getTechnicianPerformance(int $technicianId, string $startDate, string $endDate): array
    {
        $workLogs = WorkTimeLog::where('technician_id', $technicianId)
            ->whereBetween('started_at', [$startDate, $endDate])
            ->whereNotNull('completed_at')
            ->with('job')
            ->get();

        $jobs = Job::where('technician_id', $technicianId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'delivered')
            ->get();

        $totalWorkMinutes = $workLogs->sum('duration_minutes');
        $totalWorkHours = $totalWorkMinutes / 60;

        return [
            'technician_id' => $technicianId,
            'total_work_hours' => round($totalWorkHours, 2),
            'total_jobs_completed' => $jobs->count(),
            'average_time_per_job' => $jobs->count() > 0 ? round($totalWorkHours / $jobs->count(), 2) : 0,
            'total_revenue' => $jobs->sum(function ($job) {
                return $job->invoice?->total ?? 0;
            }),
            'revenue_per_hour' => $totalWorkHours > 0 ? round($jobs->sum(function ($job) {
                return $job->invoice?->total ?? 0;
            }) / $totalWorkHours, 2) : 0,
            'work_logs' => $workLogs->map(function ($log) {
                return [
                    'job_id' => $log->job_id,
                    'job_number' => $log->job->job_number,
                    'started_at' => $log->started_at->format('Y-m-d H:i'),
                    'completed_at' => $log->completed_at?->format('Y-m-d H:i'),
                    'duration_minutes' => $log->duration_minutes,
                ];
            }),
        ];
    }

    public function getAllTechniciansPerformance(string $startDate, string $endDate, ?int $branchId = null): array
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('slug', 'technician');
        });

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $technicians = $query->get();

        return $technicians->map(function ($technician) use ($startDate, $endDate) {
            $performance = $this->getTechnicianPerformance($technician->id, $startDate, $endDate);
            
            return [
                'id' => $technician->id,
                'name' => $technician->name,
                'performance' => $performance,
            ];
        })->sortByDesc('performance.total_jobs_completed')->values()->toArray();
    }

    public function getTechnicianAvailability(int $branchId): array
    {
        $technicians = User::where('branch_id', $branchId)
            ->whereHas('roles', function ($q) {
                $q->where('slug', 'technician');
            })
            ->where('active', true)
            ->get();

        return $technicians->map(function ($technician) {
            $activeWorkLog = WorkTimeLog::where('technician_id', $technician->id)
                ->whereNull('completed_at')
                ->first();

            return [
                'id' => $technician->id,
                'name' => $technician->name,
                'status' => $activeWorkLog ? 'busy' : 'available',
                'current_job' => $activeWorkLog ? [
                    'job_id' => $activeWorkLog->job_id,
                    'job_number' => $activeWorkLog->job->job_number,
                    'started_at' => $activeWorkLog->started_at->format('H:i'),
                ] : null,
            ];
        })->toArray();
    }

    public function assignTechnicianToJob(int $jobId, int $technicianId): Job
    {
        $job = Job::findOrFail($jobId);
        
        $job->update(['technician_id' => $technicianId]);
        
        return $job;
    }
}
