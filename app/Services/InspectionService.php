<?php

namespace App\Services;

use App\Models\Inspection;
use App\Models\InspectionPhoto;
use App\Models\DamageRecord;
use App\Models\Job;
use App\Enums\JobStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InspectionService
{
    public function createInspection(int $jobId, array $data): Inspection
    {
        return DB::transaction(function () use ($jobId, $data) {
            $job = Job::findOrFail($jobId);
            
            $inspection = Inspection::create([
                'job_id' => $jobId,
                'mileage' => $data['mileage'] ?? null,
                'fuel_level' => $data['fuel_level'] ?? null,
                'exterior_condition' => $data['exterior_condition'] ?? null,
                'interior_condition' => $data['interior_condition'] ?? null,
                'tyre_condition' => $data['tyre_condition'] ?? null,
                'wheel_condition' => $data['wheel_condition'] ?? null,
                'glass_condition' => $data['glass_condition'] ?? null,
                'lights_condition' => $data['lights_condition'] ?? null,
                'dashboard_warnings' => $data['dashboard_warnings'] ?? null,
                'findings' => $data['findings'] ?? null,
                'recommendations' => $data['recommendations'] ?? null,
            ]);

            // Update job status
            if ($job->status === JobStatus::INSPECTION_PENDING) {
                $job->transitionTo(JobStatus::INSPECTION_COMPLETED, auth()->user());
            }

            return $inspection;
        });
    }

    public function addPhoto(int $inspectionId, string $category, string $path, ?string $caption = null): InspectionPhoto
    {
        return InspectionPhoto::create([
            'job_id' => Inspection::findOrFail($inspectionId)->job_id,
            'category' => $category,
            'path' => $path,
            'caption' => $caption,
            'uploaded_by' => auth()->id(),
        ]);
    }

    public function uploadPhoto(int $inspectionId, $file, string $category, ?string $caption = null): InspectionPhoto
    {
        $path = $file->store('inspections/' . $inspectionId, 'public');
        return $this->addPhoto($inspectionId, $category, $path, $caption);
    }

    public function recordDamage(int $inspectionId, array $damageData): DamageRecord
    {
        return DamageRecord::create([
            'job_id' => Inspection::findOrFail($inspectionId)->job_id,
            'type' => $damageData['type'],
            'location' => $damageData['location'] ?? null,
            'severity' => $damageData['severity'] ?? 'minor',
            'description' => $damageData['description'] ?? null,
            'photo_path' => $damageData['photo_path'] ?? null,
        ]);
    }

    public function getInspectionWithPhotos(int $jobId): array
    {
        $inspection = Inspection::with(['photos', 'damages', 'job.vehicle', 'job.customer'])
            ->where('job_id', $jobId)
            ->firstOrFail();

        return [
            'inspection' => $inspection,
            'photos' => $inspection->photos->groupBy('category'),
            'damages' => $inspection->damages,
            'vehicle' => $inspection->job->vehicle,
            'customer' => $inspection->job->customer,
        ];
    }

    public function getBeforeAfterPhotos(int $jobId): array
    {
        $inspection = Inspection::with(['photos' => function ($query) {
            $query->whereIn('category', ['before', 'after']);
        }])->where('job_id', $jobId)->first();

        return [
            'before' => $inspection->photos->where('category', 'before')->values(),
            'after' => $inspection->photos->where('category', 'after')->values(),
        ];
    }

    public function deletePhoto(int $photoId): void
    {
        $photo = InspectionPhoto::findOrFail($photoId);
        
        // Delete file from storage
        if (Storage::disk('public')->exists($photo->path)) {
            Storage::disk('public')->delete($photo->path);
        }
        
        $photo->delete();
    }

    public function updateInspection(int $inspectionId, array $data): Inspection
    {
        $inspection = Inspection::findOrFail($inspectionId);
        $inspection->update($data);
        return $inspection;
    }

    public function completeInspection(int $jobId): Inspection
    {
        return DB::transaction(function () use ($jobId) {
            $job = Job::findOrFail($jobId);
            
            if (!$job->inspection) {
                abort(422, 'No inspection found for this job');
            }

            // Update job status
            $job->transitionTo(JobStatus::INSPECTION_COMPLETED, auth()->user());

            // Send notification to customer
            app(CommunicationService::class)->notifyInspectionCompleted($job);

            return $job->inspection;
        });
    }

    public function getInspectionSummary(int $jobId): array
    {
        $inspection = Inspection::with(['photos', 'damages'])->where('job_id', $jobId)->firstOrFail();

        return [
            'mileage' => $inspection->mileage,
            'fuel_level' => $inspection->fuel_level,
            'overall_condition' => [
                'exterior' => $inspection->exterior_condition,
                'interior' => $inspection->interior_condition,
                'tyres' => $inspection->tyre_condition,
                'glass' => $inspection->glass_condition,
            ],
            'damage_count' => $inspection->damages->count(),
            'photo_count' => $inspection->photos->count(),
            'findings' => $inspection->findings,
            'recommendations' => $inspection->recommendations,
        ];
    }
}
