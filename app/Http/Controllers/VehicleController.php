<?php

namespace App\Http\Controllers;

use App\Models\{Vehicle, Customer};
use App\Services\AuditService;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct(private readonly AuditService $auditService)
    {
    }

    public function index(Request $r)
    {
        $vehicles = Vehicle::with('customer')
            ->when($r->q, fn($q, $v) => 
                $q->where(function ($query) use ($v) {
                    $query->where('registration_number', 'like', '%' . $v . '%')
                          ->orWhere('make', 'like', '%' . $v . '%')
                          ->orWhere('model', 'like', '%' . $v . '%')
                          ->orWhere('category', 'like', '%' . $v . '%')
                          ->orWhereHas('customer', function ($customerQuery) use ($v) {
                              $customerQuery->where('full_name', 'like', '%' . $v . '%');
                          });
                })
            )
            ->latest()
            ->paginate(20);

        return view('vehicles.index', compact('vehicles'));
    }

    public function create(Request $r)
    {
        return view('vehicles.create');
    }

    public function store(Request $r)
    {
        try {
            $validated = $r->validate([
                'customer_id' => 'required|integer|exists:customers,id',
                'registration_number' => 'required',
                'make' => 'nullable',
                'model' => 'nullable',
                'category' => 'required',
                'mileage' => 'nullable|integer',
                'image' => 'nullable|image|max:5120', // max 5MB
            ]);

            $existingVehicle = Vehicle::where('registration_number', $r->registration_number)
                ->where('customer_id', $r->customer_id)
                ->first();

            if ($existingVehicle) {
                if ($r->wantsJson()) {
                    return response()->json([
                        'error' => 'This customer already has a vehicle with this registration number.',
                    ], 422);
                }
                return back()
                    ->with('error', 'This customer already has a vehicle with this registration number.')
                    ->withInput();
            }

            // Save uploaded image if present
            if ($r->hasFile('image')) {
                $validated['image'] = $r->file('image')->store('vehicles', 'public');
            }

            $vehicle = Vehicle::create($validated);

            $this->auditService->log('vehicle_created', 'Vehicle', $vehicle->id, null, $validated);

            if ($r->wantsJson()) {
                return response()->json([
                    'id' => $vehicle->id,
                    'registration_number' => $vehicle->registration_number,
                    'make' => $vehicle->make,
                    'model' => $vehicle->model,
                    'category' => $vehicle->category,
                    'customer_id' => $vehicle->customer_id,
                    'image' => $vehicle->image,
                    'image_url' => $vehicle->image
                        ? asset('storage/' . $vehicle->image)
                        : null,
                ]);
            }

            return redirect()
                ->route('vehicles.show', $vehicle)
                ->with('success', 'Vehicle created.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($r->wantsJson()) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load('customer', 'jobs');
        $customers = Customer::orderBy('full_name')->get();
        return view('vehicles.show', compact('vehicle', 'customers'));
    }

    public function transferOwnership(Request $r, Vehicle $vehicle)
    {
        $validated = $r->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        $oldCustomer = $vehicle->customer_id;
        $vehicle->update(['customer_id' => $validated['customer_id']]);

        $this->auditService->log('vehicle_ownership_transferred', 'Vehicle', $vehicle->id, ['customer_id' => $oldCustomer], ['customer_id' => $validated['customer_id']]);

        return back()->with('success', 'Vehicle ownership transferred successfully.');
    }

    public function edit(Vehicle $vehicle)
    {
        $customers = Customer::all();
        return view('vehicles.edit', compact('vehicle', 'customers'));
    }

    public function update(Request $r, Vehicle $vehicle)
    {
        $validated = $r->validate([
            'customer_id' => 'required',
            'registration_number' => 'required',
            'make' => 'nullable',
            'model' => 'nullable',
            'category' => 'required',
            'mileage' => 'nullable|integer',
            'image' => 'nullable|image|max:5120',
        ]);

        $existingVehicle = Vehicle::where('registration_number', $r->registration_number)
            ->where('customer_id', $r->customer_id)
            ->where('id', '<>', $vehicle->id)
            ->first();

        if ($existingVehicle) {
            return back()
                ->with('error', 'This customer already has a vehicle with this registration number.')
                ->withInput();
        }

        $oldValues = $vehicle->toArray();

        // Replace image if a new one is uploaded
        if ($r->hasFile('image')) {
            if ($vehicle->image) {
                \Storage::disk('public')->delete($vehicle->image);
            }
            $validated['image'] = $r->file('image')->store('vehicles', 'public');
        }

        $vehicle->update($validated);

        $this->auditService->log('vehicle_updated', 'Vehicle', $vehicle->id, $oldValues, $validated);

        return back()->with('success', 'Vehicle updated.');
    }

    public function categories(Request $request)
    {
        $categories = [
            'Small Car',
            'Sedan', 
            'SUV',
            'Luxury',
            'Van',
            'Pickup',
            'Jeep',
            'Three-wheeler',
            'Motorcycle',
            'Commercial',
            'Bus',
            'JCB Truck'
        ];

        return response()->json($categories);
    }

    public function vehicleList(Request $request)
    {
        $vehicles = Vehicle::with('customer')
            ->select('id', 'registration_number', 'make', 'model', 'customer_id')
            ->orderBy('make')
            ->orderBy('model')
            ->get()
            ->map(function ($vehicle) {
                $makeModel = $vehicle->make && $vehicle->model 
                    ? trim($vehicle->make . ' ' . $vehicle->model) 
                    : $vehicle->registration_number;
                    
                return [
                    'id' => $vehicle->id,
                    'label' => $makeModel,
                    'registration_number' => $vehicle->registration_number,
                    'make' => $vehicle->make,
                    'model' => $vehicle->model,
                    'customer_id' => $vehicle->customer_id,
                    'customer_name' => $vehicle->customer->full_name ?? 'Unknown'
                ];
            });

        return response()->json($vehicles);
    }

    public function destroy()
    {
        abort(405);
    }
}