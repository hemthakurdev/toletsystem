<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Tenant;
use App\Models\Property;

class TenantsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $organization = $user->organization;

        // Get tenants for the organization
        $tenants = $organization->tenants()
            ->with(['property'])
            ->latest()
            ->get()
            ->map(function ($tenant) {
                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'email' => $tenant->email,
                    'phone' => $tenant->phone,
                    'occupation' => $tenant->occupation,
                    'company' => $tenant->company,
                    'property_id' => $tenant->property_id,
                    'property_title' => $tenant->property ? $tenant->property->title : null,
                    'monthly_rent' => $tenant->monthly_rent,
                    'security_deposit' => $tenant->security_deposit,
                    'lease_start_date' => $tenant->lease_start,
                    'lease_end_date' => $tenant->lease_end,
                    'status' => $tenant->status,
                    'emergency_contact_name' => $tenant->emergency_contact_name,
                    'emergency_contact_phone' => $tenant->emergency_contact_phone,
                    'notes' => $tenant->notes,
                    'created_at' => $tenant->created_at,
                    'updated_at' => $tenant->updated_at,
                ];
            });

        // Get properties for the organization (for the dropdown)
        $properties = $organization->properties()
            ->select('id', 'title', 'city', 'locality')
            ->get();

        // Calculate stats
        $stats = [
            'total' => $tenants->count(),
            'active' => $tenants->where('status', 'active')->count(),
            'expiring' => $tenants->filter(function ($tenant) {
                if (!$tenant['lease_end_date']) return false;
                $daysUntilExpiry = (new \DateTime($tenant['lease_end_date']))->diff(new \DateTime())->days;
                return $daysUntilExpiry <= 30 && $daysUntilExpiry > 0;
            })->count(),
            'overdue' => 0 // This would be calculated from payment data
        ];

        return Inertia::render('Tenants/Index', [
            'tenants' => $tenants,
            'properties' => $properties,
            'stats' => $stats,
        ]);
    }

    public function create(): Response
    {
        $user = Auth::user();
        $organization = $user->organization;

        // Get properties for the dropdown
        $properties = $organization->properties()
            ->select('id', 'title', 'city', 'locality')
            ->get();

        return Inertia::render('Tenants/Create', [
            'properties' => $properties,
        ]);
    }

    public function show($id): Response
    {
        $user = Auth::user();
        $organization = $user->organization;

        $tenant = $organization->tenants()
            ->with(['property'])
            ->findOrFail($id);

        return Inertia::render('Tenants/Show', [
            'tenant' => $tenant,
        ]);
    }

    public function edit($id): Response
    {
        $user = Auth::user();
        $organization = $user->organization;

        $tenant = $organization->tenants()->findOrFail($id);

        // Get properties for the dropdown
        $properties = $organization->properties()
            ->select('id', 'title', 'city', 'locality')
            ->get();

        return Inertia::render('Tenants/Edit', [
            'tenant' => $tenant,
            'properties' => $properties,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $organization = $user->organization;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'occupation' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'property_id' => 'required|exists:properties,id',
            'monthly_rent' => 'required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'lease_start_date' => 'required|date',
            'lease_end_date' => 'required|date|after:lease_start_date',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $validated['org_id'] = $organization->id;
        $validated['status'] = 'active';
        
        // Map form field names to database field names
        $validated['lease_start'] = $validated['lease_start_date'];
        $validated['lease_end'] = $validated['lease_end_date'];
        $validated['rent_amount'] = $validated['monthly_rent'];
        
        // Remove the form field names
        unset($validated['lease_start_date'], $validated['lease_end_date'], $validated['monthly_rent']);

        $tenant = Tenant::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tenant created successfully',
            'data' => $tenant
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $organization = $user->organization;

        $tenant = $organization->tenants()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'occupation' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'property_id' => 'required|exists:properties,id',
            'monthly_rent' => 'required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'lease_start_date' => 'required|date',
            'lease_end_date' => 'required|date|after:lease_start_date',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,terminated',
        ]);

        // Map form field names to database field names
        $validated['lease_start'] = $validated['lease_start_date'];
        $validated['lease_end'] = $validated['lease_end_date'];
        $validated['rent_amount'] = $validated['monthly_rent'];
        
        // Remove the form field names
        unset($validated['lease_start_date'], $validated['lease_end_date'], $validated['monthly_rent']);

        $tenant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tenant updated successfully',
            'data' => $tenant
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $organization = $user->organization;

        $tenant = $organization->tenants()->findOrFail($id);
        $tenant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully'
        ]);
    }
}
