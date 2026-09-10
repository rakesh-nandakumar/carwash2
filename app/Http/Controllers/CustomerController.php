<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Services\AuditService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(private readonly AuditService $auditService)
    {
    }

    public function index(Request $r)
    {
        $customers = Customer::with('vehicles')
            ->withCount('jobs')
            ->withSum([
                'invoices' => function ($query) {
                    $query->where('status', '!=', 'cancelled');
                }
            ], 'total')
            ->when($r->q, fn ($q, $v) =>
                $q->where(function ($query) use ($v) {
                    $query->where('full_name', 'like', '%' . $v . '%')
                          ->orWhere('phone', 'like', '%' . $v . '%')
                          ->orWhere('phone', 'like', '%' . $this->normalizePhoneNumber($v) . '%')
                          ->orWhere('whatsapp_number', 'like', '%' . $v . '%')
                          ->orWhere('whatsapp_number', 'like', '%' . $this->normalizePhoneNumber($v) . '%');
                })
            )
            ->when($r->customer_name, fn ($q, $v) =>
                $q->where('full_name', 'like', '%' . $v . '%')
            )
            ->when($r->customer_phone, fn ($q, $v) =>
                $q->where(function ($query) use ($v) {
                    $query->where('phone', 'like', '%' . $v . '%')
                          ->orWhere('phone', 'like', '%' . $this->normalizePhoneNumber($v) . '%');
                })
            )
            ->when($r->customer_vehicle, fn ($q, $v) =>
                $q->whereHas('vehicles', function ($query) use ($v) {
                    $query->where('registration_number', 'like', '%' . $v . '%')
                          ->orWhere('make', 'like', '%' . $v . '%')
                          ->orWhere('model', 'like', '%' . $v . '%');
                })
            )
            ->latest()
            ->paginate(20);

        return view('customers.index', compact('customers'));
    }

    /**
     * Normalize phone number to +94 format
     * Converts: 07520727722 -> +94752072772
     *           94752072772 -> +94752072772
     *           +94752072772 -> +94752072772
     */
    private function normalizePhoneNumber($phone)
    {
        $phone = trim($phone);
        
        // If already in +94 format, return as is
        if (str_starts_with($phone, '+94')) {
            return $phone;
        }
        
        // If starts with 94, add + prefix
        if (str_starts_with($phone, '94')) {
            return '+' . $phone;
        }
        
        // If starts with 0 and has 10 digits, convert to +94 format
        if (str_starts_with($phone, '0') && strlen($phone) === 10) {
            return '+94' . substr($phone, 1);
        }
        
        return $phone;
    }

    public function list(Request $request)
    {
        $query = Customer::select('id', 'full_name', 'phone', 'whatsapp_number')
            ->where('business_id', auth()->user()->business_id)
            ->orderBy('full_name');

        // Add search functionality for phone number normalization
        if ($request->has('search')) {
            $search = $request->search;
            $normalizedPhone = $this->normalizePhoneNumber($search);
            
            $query->where(function ($q) use ($search, $normalizedPhone) {
                $q->where('full_name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $normalizedPhone . '%')
                  ->orWhere('whatsapp_number', 'like', '%' . $search . '%')
                  ->orWhere('whatsapp_number', 'like', '%' . $normalizedPhone . '%');
            });
        }

        return response()->json($query->get());
    }

    public function phoneNumbers(Request $request)
    {
        $phoneNumbers = Customer::where('business_id', auth()->user()->business_id)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->distinct()
            ->pluck('phone')
            ->sort()
            ->values();

        return response()->json($phoneNumbers);
    }

    public function vehicles(Request $request)
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

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $r)
    {
        try {
            $validated = $r->validate([
                'full_name' => 'required',
                'phone' => 'required|unique:customers,phone',
                'whatsapp_number' => 'nullable',
                'email' => 'nullable|email',
                'address' => 'nullable',
            ]);

            $existingCustomer = Customer::where('full_name', $r->full_name)
                ->where('phone', $r->phone)
                ->first();

            if ($existingCustomer) {
                if ($r->wantsJson()) {
                    return response()->json([
                        'error' => 'A customer with this name and phone number already exists in the system.'
                    ], 422);
                }

                return back()
                    ->with('error', 'A customer with this name and phone number already exists in the system.')
                    ->withInput();
            }

            $validated['business_id'] = auth()->user()->business_id;
            $validated['customer_code'] = 'CUS-' . now()->format('Y') . '-' . str_pad(
                (string) (Customer::max('id') + 1),
                6,
                '0',
                STR_PAD_LEFT
            );

            $customer = Customer::create($validated);

            $this->auditService->log('customer_created', 'Customer', $customer->id, null, $validated);

            if ($r->transfer_vehicle_id) {
                $vehicle = \App\Models\Vehicle::find($r->transfer_vehicle_id);
                if ($vehicle) {
                    $vehicle->update(['customer_id' => $customer->id]);
                }
            }

            if ($r->wantsJson()) {
                return response()->json($customer);
            }

            $redirectUrl = $r->input('redirect_to') ?: route('customers.show', $customer);

            return redirect($redirectUrl)->with('success', 'Customer created.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($r->wantsJson()) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'errors' => $e->errors()
                ], 422);
            }

            throw $e;
        }
    }

    public function show(Customer $customer)
    {
        $customer->load('vehicles', 'jobs', 'invoices');

        $customer->loadCount('jobs');

        $customer->loadSum([
            'invoices' => function ($query) {
                $query->where('status', '!=', 'cancelled');
            }
        ], 'total');

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $r, Customer $customer)
    {
        $validated = $r->validate([
            'full_name' => 'required',
            'phone' => 'required',
            'whatsapp_number' => 'nullable',
            'email' => 'nullable|email',
            'address' => 'nullable',
        ]);

        $oldValues = $customer->toArray();
        $customer->update($validated);

        $this->auditService->log('customer_updated', 'Customer', $customer->id, $oldValues, $validated);

        return back()->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        abort(405);
    }
}