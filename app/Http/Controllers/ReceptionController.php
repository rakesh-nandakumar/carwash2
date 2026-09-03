<?php

namespace App\Http\Controllers;

use App\Models\{Customer, Vehicle, Job, Service, Product, Inventory, JobPart};
use App\Enums\JobStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReceptionController extends Controller
{
    public function index()
    {
        return view('reception.index');
    }

    public function search(Request $request)
    {
        $query = trim($request->input('q', ''));

        if (strlen($query) < 2) {
            return response()->json([
                'found'    => false,
                'vehicles' => [],
            ]);
        }

        $vehicles = Vehicle::where('registration_number', 'like', "%{$query}%")
            ->with(['customer:id,full_name'])
            ->limit(10)
            ->get()
            ->map(function ($vehicle) {

                return [
                    'vehicle_id'          => $vehicle->id,
                    'registration_number' => $vehicle->registration_number,
                    'make'                => $vehicle->make,
                    'model'               => $vehicle->model,
                    'category'            => $vehicle->category,
                    'customer_id'         => $vehicle->customer_id,
                    'customer_name'       => $vehicle->customer->full_name ?? 'Unknown',

                    'image' => $vehicle->image,

                    'image_url' => $vehicle->image
                        ? route('reception.vehicle-image', $vehicle)
                        : null,
                ];
            });

        return response()->json([
            'found'    => $vehicles->isNotEmpty(),
            'vehicles' => $vehicles,
        ]);
    }

    public function vehicleImage(Vehicle $vehicle)
    {
        $businessId = auth()->user()->business_id;

        // Vehicle does not have business_id.
        // Verify ownership through the vehicle's customer.
        $vehicle = Vehicle::whereKey($vehicle->id)
            ->whereHas('customer', function ($query) use ($businessId) {
                $query->where('business_id', $businessId);
            })
            ->firstOrFail();

        if (!$vehicle->image) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($vehicle->image)) {
            abort(404);
        }

        return $disk->response(
            $vehicle->image,
            null,
            [
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    }

    public function createJob(Request $request)
    {
        try {
            $businessId = auth()->user()->business_id;
            $branchId   = auth()->user()->branch_id;

            $validated = $request->validate([
                'customer_id' => [
                    'required',
                    'integer',
                    Rule::exists('customers', 'id'),
                ],
                'vehicle_id' => [
                    'required',
                    'integer',
                    Rule::exists('vehicles', 'id'),
                ],
                'service_ids' => [
                    'nullable',
                    'array',
                ],
                'service_ids.*' => [
                    'required',
                    'integer',
                    Rule::exists('services', 'id'),
                ],
                'product_items' => [
                    'nullable',
                    'array',
                ],
                'product_items.*.product_id' => [
                    'required',
                    'integer',
                    Rule::exists('products', 'id'),
                ],
                'product_items.*.quantity' => [
                    'required',
                    'numeric',
                    'min:0.001',
                ],
                'notes' => 'nullable|string',
                'vehicle_image' => 'nullable|image|max:5120',
            ]);

            // Must have at least one service OR one product
            if (empty($validated['service_ids']) && empty($validated['product_items'])) {
                throw ValidationException::withMessages([
                    'job' => 'Please select at least one service or product.',
                ]);
            }

            // ---------- Safe image handling ----------
            $oldImagePath = null;
            $newImagePath = null;

            if ($request->hasFile('vehicle_image')) {
                $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
                $oldImagePath = $vehicle->image;
                $newImagePath = $request->file('vehicle_image')->store('vehicles', 'public');
            }

            $job = DB::transaction(function () use (
                $validated,
                $businessId,
                $branchId,
                $newImagePath
            ) {
                if ($newImagePath) {
                    Vehicle::where('id', $validated['vehicle_id'])
                        ->update(['image' => $newImagePath]);
                }

                $job = Job::create([
                    'business_id'   => $businessId,
                    'branch_id'     => $branchId,
                    'customer_id'   => $validated['customer_id'],
                    'vehicle_id'    => $validated['vehicle_id'],
                    'status'        => JobStatus::CHECKED_IN,
                    'checked_in_at' => now(),
                    'notes'         => $validated['notes'] ?? null,
                ]);

                // Attach services (if any)
                foreach ($validated['service_ids'] ?? [] as $serviceId) {
                    $service = Service::find($serviceId);
                    $job->services()->create([
                        'service_id'    => $serviceId,
                        'name_snapshot' => $service->name,
                        'unit_price'    => $service->base_price,
                        'quantity'      => 1,
                    ]);
                }

                // Aggregate quantities to prevent duplicate product_id bypass
                $productQuantities = collect($validated['product_items'] ?? [])
                    ->groupBy('product_id')
                    ->map(fn ($items) => $items->sum(fn ($item) => (float) $item['quantity']));

                foreach ($productQuantities as $productId => $quantity) {
                    $product = Product::where('business_id', $businessId)
                        ->where('active', true)
                        ->findOrFail($productId);

                    $inventory = Inventory::where('product_id', $product->id)
                        ->where('branch_id', $branchId)
                        ->first();

                    $available = $inventory
                        ? (float) $inventory->quantity - (float) $inventory->reserved_quantity
                        : 0;

                    if ($available < $quantity) {
                        throw new \RuntimeException(
                            "Insufficient stock for {$product->name}. " .
                            "Available: {$available}, Required: {$quantity}"
                        );
                    }

                    JobPart::create([
                        'job_id'     => $job->id,
                        'product_id' => $product->id,
                        'quantity'   => $quantity,
                        'unit_price' => $product->selling_price,
                        'cost_price' => $product->cost_price,
                        'source'     => 'inventory',
                        'approved'   => false,
                    ]);
                }

                return $job;
            });

            // Delete old image only after successful transaction
            if ($oldImagePath && $newImagePath) {
                Storage::disk('public')->delete($oldImagePath);
            }

            return response()->json([
                'success'    => true,
                'job_id'     => $job->id,
                'job_number' => $job->job_number,
                'redirect'   => route('jobs.show', $job),
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            if (isset($newImagePath) && $newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getServices()
    {
        $services = Service::where('business_id', auth()->user()->business_id)
            ->with('category')
            ->orderBy('name')
            ->get();

        return response()->json($services);
    }

    public function getProducts()
    {
        $branchId   = auth()->user()->branch_id;
        $businessId = auth()->user()->business_id;

        $products = Product::query()
            ->where('business_id', $businessId)
            ->where('active', true)
            ->with([
                'inventory' => function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                }
            ])
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $inventory = $product->inventory->first();

                $quantity  = $inventory?->quantity ?? 0;
                $reserved  = $inventory?->reserved_quantity ?? 0;
                $available = max(0, $quantity - $reserved);

                return [
                    'id'                 => $product->id,
                    'name'               => $product->name,
                    'sku'                => $product->sku,
                    'barcode'            => $product->barcode,
                    'brand'              => $product->brand,
                    'part_number'        => $product->part_number,
                    'unit'               => $product->unit,
                    'selling_price'      => (float) $product->selling_price,
                    'available_quantity' => (float) $available,
                ];
            });

        return response()->json($products);
    }
}