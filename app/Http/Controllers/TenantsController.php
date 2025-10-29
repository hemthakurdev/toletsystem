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

        // Base query
        $query = $organization->tenants()->with(['property']);

        // Apply filters from query string
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->integer('property_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('lease_status')) {
            $leaseStatus = $request->string('lease_status');
            // Interpret lease status from dates and status
            if ($leaseStatus === 'active') {
                $query->where('status', 'active')->whereDate('lease_end', '>=', now()->toDateString());
            } elseif ($leaseStatus === 'expired') {
                $query->whereDate('lease_end', '<', now()->toDateString());
            } elseif ($leaseStatus === 'terminated') {
                $query->where('status', 'terminated');
            }
        }

        // Get tenants for the organization
        $tenants = $query->latest()->get()
            ->map(function ($tenant) {
                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'email' => $tenant->email,
                    'phone' => $tenant->phone,
                    'occupation' => $tenant->occupation,
                    'company' => $tenant->company,
                    'property_id' => $tenant->property_id,
                    // Provide nested property object for frontend expectations
                    'property' => $tenant->property ? [
                        'id' => $tenant->property->id,
                        'title' => $tenant->property->title,
                        'locality' => $tenant->property->locality,
                        'city' => $tenant->property->city,
                    ] : null,
                    // Keep property_title for any legacy usage
                    'property_title' => $tenant->property ? $tenant->property->title : null,
                    'monthly_rent' => $tenant->rent_amount,
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

        // Get vacant properties for the dropdown in Add Tenant modal
        $properties = $organization->properties()
            ->where('availability_status', 'vacant')
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
            'appliedFilters' => [
                'search' => (string) $request->query('search', ''),
                'property_id' => $request->query('property_id', ''),
                'status' => (string) $request->query('status', ''),
                'lease_status' => (string) $request->query('lease_status', ''),
            ],
        ]);
    }

    public function create(): Response
    {
        $user = Auth::user();
        $organization = $user->organization;

        // Get properties for the dropdown
        $properties = $organization->properties()
            ->where('availability_status', 'vacant')
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
            ->where(function ($q) use ($tenant) {
                $q->where('availability_status', 'vacant')
                  ->orWhere('id', $tenant->property_id);
            })
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

        // Mark property as occupied
        $property = Property::find($tenant->property_id);
        if ($property) {
            $property->markAsOccupied();
        }

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
        $oldPropertyId = $tenant->property_id;

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

        // If property changed, make old vacant and new occupied
        if (isset($validated['property_id']) && (int)$validated['property_id'] !== (int)$oldPropertyId) {
            if ($oldPropertyId) {
                $old = Property::find($oldPropertyId);
                if ($old) {
                    $old->markAsVacant();
                }
            }
            $new = Property::find($tenant->property_id);
            if ($new) {
                $new->markAsOccupied();
            }
        }

        // If status is terminated, mark property as vacant
        if (($validated['status'] ?? $tenant->status) === 'terminated') {
            $property = Property::find($tenant->property_id);
            if ($property) {
                $property->markAsVacant();
            }
        }

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
        $property = Property::find($tenant->property_id);
        $tenant->delete();

        // Mark property as vacant when tenant is deleted
        if ($property) {
            $property->markAsVacant();
        }

        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully'
        ]);
    }
}
