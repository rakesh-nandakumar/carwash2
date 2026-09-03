<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceVehiclePricing;
use App\Models\Vehicle;
use App\Models\Discount;
use App\Models\Membership;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class PricingService
{
    public function getServicePrice(Service $service, Vehicle $vehicle, ?int $branchId = null): float
    {
        // Priority: 1. Vehicle Category Pricing, 2. Branch Pricing, 3. Base Price
        
        $vehicleCategory = $vehicle->category ?? 'Small Car';
        
        // Try to find vehicle category specific pricing
        $pricing = ServiceVehiclePricing::where('service_id', $service->id)
            ->where('vehicle_category_id', $vehicleCategory)
            ->where('active', true)
            ->where(function ($query) {
                $query->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now());
            })
            ->when($branchId, function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->orderByDesc('branch_id') // Prefer branch-specific pricing
            ->first();

        return $pricing ? $pricing->price : $service->base_price;
    }

    public function calculateJobServicesTotal(int $jobId): array
    {
        $job = \App\Models\Job::with(['services', 'vehicle', 'branch'])->findOrFail($jobId);
        
        $total = 0;
        $services = [];
        
        foreach ($job->services as $jobService) {
            if ($jobService->approval_status === 'rejected' || $jobService->removed) {
                continue;
            }
            
            $price = $this->getServicePrice(
                $jobService->service,
                $job->vehicle,
                $job->branch_id
            );
            
            $lineTotal = $price * $jobService->quantity - $jobService->discount + $jobService->tax;
            $total += $lineTotal;
            
            $services[] = [
                'id' => $jobService->id,
                'name' => $jobService->name_snapshot,
                'quantity' => $jobService->quantity,
                'unit_price' => $price,
                'discount' => $jobService->discount,
                'tax' => $jobService->tax,
                'line_total' => $lineTotal,
            ];
        }
        
        return [
            'subtotal' => $total,
            'services' => $services,
        ];
    }

    public function calculateJobPartsTotal(int $jobId): array
    {
        $job = \App\Models\Job::with(['parts.product'])->findOrFail($jobId);
        
        $total = 0;
        $parts = [];
        
        foreach ($job->parts as $jobPart) {
            $lineTotal = $jobPart->quantity * $jobPart->unit_price;
            $total += $lineTotal;
            
            $parts[] = [
                'id' => $jobPart->id,
                'product_name' => $jobPart->product->name,
                'quantity' => $jobPart->quantity,
                'unit_price' => $jobPart->unit_price,
                'cost_price' => $jobPart->cost_price,
                'line_total' => $lineTotal,
                'source' => $jobPart->source,
            ];
        }
        
        return [
            'subtotal' => $total,
            'parts' => $parts,
        ];
    }

    public function calculateJobTotal(int $jobId): array
    {
        $services = $this->calculateJobServicesTotal($jobId);
        $parts = $this->calculateJobPartsTotal($jobId);
        
        $subtotal = $services['subtotal'] + $parts['subtotal'];
        
        return [
            'services_total' => $services['subtotal'],
            'parts_total' => $parts['subtotal'],
            'subtotal' => $subtotal,
            'services' => $services['services'],
            'parts' => $parts['parts'],
        ];
    }

    public function applyDiscount(float $amount, Discount $discount): float
    {
        if ($discount->type === 'percentage') {
            return $amount * ($discount->value / 100);
        }
        
        return min($discount->value, $amount);
    }

    public function getBestDiscount(float $amount, ?int $customerId = null): ?Discount
    {
        $query = Discount::where('active', true)
            ->where('minimum_amount', '<=', $amount)
            ->where(function ($query) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            });

        // Check usage limit
        $query->whereColumn('used_count', '<', 'usage_limit');
        
        $discounts = $query->get();
        
        $bestDiscount = null;
        $maxSavings = 0;
        
        foreach ($discounts as $discount) {
            $savings = $this->applyDiscount($amount, $discount);
            
            if ($discount->maximum_discount && $savings > $discount->maximum_discount) {
                $savings = $discount->maximum_discount;
            }
            
            if ($savings > $maxSavings) {
                $maxSavings = $savings;
                $bestDiscount = $discount;
            }
        }
        
        return $bestDiscount;
    }

    public function calculateMembershipDiscount(float $amount, ?int $customerId = null): float
    {
        if (!$customerId) {
            return 0;
        }
        
        $membership = Membership::where('customer_id', $customerId)
            ->where('expires_at', '>', now())
            ->first();
        
        if (!$membership) {
            return 0;
        }
        
        // Get membership discount from settings
        $discountPercent = (float) Setting::get('loyalty', 'membership_discount_percent', 10);
        
        return $amount * ($discountPercent / 100);
    }

    public function calculateFinalInvoice(int $jobId, ?int $discountId = null): array
    {
        $job = \App\Models\Job::with(['customer', 'vehicle', 'branch'])->findOrFail($jobId);
        $totals = $this->calculateJobTotal($jobId);
        
        $subtotal = $totals['subtotal'];
        $discount = 0;
        $discountDetails = null;
        
        // Apply discount if provided
        if ($discountId) {
            $discountModel = Discount::find($discountId);
            if ($discountModel) {
                $discount = $this->applyDiscount($subtotal, $discountModel);
                $discountDetails = [
                    'id' => $discountModel->id,
                    'name' => $discountModel->name,
                    'type' => $discountModel->type,
                    'value' => $discountModel->value,
                    'amount' => $discount,
                ];
            }
        }
        
        // Check for membership discount
        $membershipDiscount = $this->calculateMembershipDiscount($subtotal - $discount, $job->customer_id);
        
        $totalAfterDiscount = $subtotal - $discount - $membershipDiscount;
        
        // Calculate tax (if applicable)
        $taxRate = (float) Setting::get('general', 'tax_rate', 0);
        $tax = $totalAfterDiscount * ($taxRate / 100);
        
        $total = $totalAfterDiscount + $tax;
        
        return [
            'services_total' => $totals['services_total'],
            'parts_total' => $totals['parts_total'],
            'subtotal' => $subtotal,
            'discount' => $discount,
            'discount_details' => $discountDetails,
            'membership_discount' => $membershipDiscount,
            'tax_rate' => $taxRate,
            'tax' => $tax,
            'total' => $total,
            'breakdown' => $totals,
        ];
    }
}