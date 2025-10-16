<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tenant::with(['organization', 'property'])
            ->where('org_id', auth()->user()->org_id);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('lease_status')) {
            $query->where('lease_status', $request->lease_status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $tenants = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tenants,
        ]);
    }

    /**
     * Store a newly created tenant
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'property_id' => 'required|exists:properties,id',
            'lease_start_date' => 'required|date',
            'lease_end_date' => 'required|date|after:lease_start_date',
            'monthly_rent' => 'required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if property belongs to user's organization
        $property = Property::where('id', $request->property_id)
            ->where('org_id', auth()->user()->org_id)
            ->first();

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found or unauthorized',
            ], 404);
        }

        $tenant = Tenant::create([
            'org_id' => auth()->user()->org_id,
            'property_id' => $request->property_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'lease_start_date' => $request->lease_start_date,
            'lease_end_date' => $request->lease_end_date,
            'monthly_rent' => $request->monthly_rent,
            'security_deposit' => $request->security_deposit,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'occupation' => $request->occupation,
            'company' => $request->company,
            'notes' => $request->notes,
            'status' => 'active',
            'lease_status' => 'active',
        ]);

        // Update property availability status
        $property->update(['availability_status' => 'occupied']);

        return response()->json([
            'success' => true,
            'message' => 'Tenant created successfully',
            'data' => $tenant->load(['organization', 'property']),
        ], 201);
    }

    /**
     * Display the specified tenant
     */
    public function show(Tenant $tenant): JsonResponse
    {
        // Check if tenant belongs to user's organization
        if ($tenant->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this tenant',
            ], 403);
        }

        $tenant->load(['organization', 'property', 'invoices', 'documents']);

        return response()->json([
            'success' => true,
            'data' => $tenant,
        ]);
    }

    /**
     * Update the specified tenant
     */
    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        // Check if tenant belongs to user's organization
        if ($tenant->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this tenant',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'property_id' => 'sometimes|required|exists:properties,id',
            'lease_start_date' => 'sometimes|required|date',
            'lease_end_date' => 'sometimes|required|date|after:lease_start_date',
            'monthly_rent' => 'sometimes|required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'sometimes|required|in:active,inactive,terminated',
            'lease_status' => 'sometimes|required|in:active,expired,terminated',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tenant->update($request->only([
            'name', 'email', 'phone', 'property_id', 'lease_start_date',
            'lease_end_date', 'monthly_rent', 'security_deposit',
            'emergency_contact_name', 'emergency_contact_phone',
            'occupation', 'company', 'notes', 'status', 'lease_status'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Tenant updated successfully',
            'data' => $tenant->load(['organization', 'property']),
        ]);
    }

    /**
     * Remove the specified tenant
     */
    public function destroy(Tenant $tenant): JsonResponse
    {
        // Check if tenant belongs to user's organization
        if ($tenant->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this tenant',
            ], 403);
        }

        // Update property availability status back to vacant
        $property = $tenant->property;
        if ($property) {
            $property->update(['availability_status' => 'vacant']);
        }

        $tenant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully',
        ]);
    }

    /**
     * Get tenant's payment history
     */
    public function paymentHistory(Tenant $tenant): JsonResponse
    {
        // Check if tenant belongs to user's organization
        if ($tenant->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this tenant',
            ], 403);
        }

        $payments = $tenant->invoices()
            ->with('payments')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Get tenant's documents
     */
    public function documents(Tenant $tenant): JsonResponse
    {
        // Check if tenant belongs to user's organization
        if ($tenant->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this tenant',
            ], 403);
        }

        $documents = $tenant->documents()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documents,
        ]);
    }

    /**
     * Terminate tenant lease
     */
    public function terminateLease(Request $request, Tenant $tenant): JsonResponse
    {
        // Check if tenant belongs to user's organization
        if ($tenant->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to modify this tenant',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'termination_date' => 'required|date',
            'termination_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tenant->update([
            'lease_status' => 'terminated',
            'status' => 'terminated',
            'lease_end_date' => $request->termination_date,
            'notes' => $tenant->notes . "\n\nLease terminated on " . $request->termination_date . ". Reason: " . $request->termination_reason,
        ]);

        // Update property availability status
        $property = $tenant->property;
        if ($property) {
            $property->update(['availability_status' => 'vacant']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lease terminated successfully',
            'data' => $tenant->load(['organization', 'property']),
        ]);
    }
}
