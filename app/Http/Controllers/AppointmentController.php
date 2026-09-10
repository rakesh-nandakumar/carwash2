<?php

namespace App\Http\Controllers;

use App\Models\{Appointment, Customer, Vehicle, Branch};
use App\Services\AuditService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(private readonly AuditService $auditService)
    {
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->get('search');

        $query = Appointment::with(['customer', 'vehicle'])
            ->where('tenant_id', $user->tenant_id)
            ->latest('scheduled_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })
                ->orWhereHas('vehicle', function ($q) use ($search) {
                    $q->where('registration_number', 'like', "%{$search}%");
                });
            });
        }

        $appointments = $query->paginate(20);

        return view('appointments.index', compact('appointments', 'search'));
    }

    public function create()
    {
        return view('appointments.create', [
            'customers' => Customer::orderBy('full_name')->get(),
            'vehicles' => Vehicle::select('id', 'registration_number', 'make', 'model', 'customer_id')
                ->orderBy('registration_number')
                ->get(),
        ]);
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'customer_id' => 'required',
            'vehicle_id' => 'required',
            'scheduled_at' => 'required|date',
            'notes' => 'nullable',
        ]);

        $d += [
            'tenant_id' => auth()->user()->tenant_id,
            'business_id' => auth()->user()->business_id,
            'branch_id' => auth()->user()->branch_id ?? null,
            'status' => 'confirmed',
        ];

        $appointment = Appointment::create($d);

        $this->auditService->log('appointment_created', 'Appointment', $appointment->id, null, $d);

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Appointment created.');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['customer', 'vehicle']);
        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $customers = Customer::orderBy('full_name')->get();
        $vehicles = Vehicle::select('id', 'registration_number', 'make', 'model', 'customer_id')
            ->orderBy('registration_number')
            ->get();
        return view('appointments.edit', compact('appointment', 'customers', 'vehicles'));
    }

    public function update(Request $r, Appointment $appointment)
    {
        $data = $r->validate([
            'customer_id' => 'required',
            'vehicle_id' => 'required',
            'scheduled_at' => 'required|date',
            'status' => 'required',
            'notes' => 'nullable',
        ]);

        $oldValues = $appointment->toArray();
        $appointment->update($data);

        $this->auditService->log('appointment_updated', 'Appointment', $appointment->id, $oldValues, $data);

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Appointment updated successfully.');
    }

    public function destroy(Appointment $appointment)
    {
        $oldStatus = $appointment->status;
        $appointment->update(['status' => 'cancelled']);

        $this->auditService->log('appointment_cancelled', 'Appointment', $appointment->id, ['status' => $oldStatus], ['status' => 'cancelled']);

        return back()->with('success', 'Appointment cancelled.');
    }
}