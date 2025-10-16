<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class OrganizationController extends Controller
{
    /**
     * Display a listing of organizations
     */
    public function index(Request $request): JsonResponse
    {
        $query = Organization::with(['plan', 'subscription', 'users']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $organizations = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $organizations,
        ]);
    }

    /**
     * Store a newly created organization
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:organizations,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'plan_id' => 'required|exists:plans,id',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Create organization
            $organization = Organization::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'plan_id' => $request->plan_id,
                'status' => 'active',
            ]);

            // Create admin user
            $admin = User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'org_id' => $organization->id,
                'phone' => $request->phone,
                'email_verified_at' => now(),
            ]);

            // Assign admin role
            $admin->assignRole('admin');

            $organization->load(['plan', 'users']);

            return response()->json([
                'success' => true,
                'message' => 'Organization created successfully',
                'data' => $organization,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create organization: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified organization
     */
    public function show(Organization $organization): JsonResponse
    {
        $organization->load([
            'plan',
            'subscription',
            'users',
            'properties',
            'tenants',
            'invoices',
            'payments'
        ]);

        // Get statistics
        $stats = [
            'total_users' => $organization->users()->count(),
            'total_properties' => $organization->properties()->count(),
            'total_tenants' => $organization->tenants()->count(),
            'total_invoices' => $organization->invoices()->count(),
            'total_payments' => $organization->payments()->count(),
            'total_revenue' => $organization->payments()->where('payment_status', 'completed')->sum('amount'),
            'active_properties' => $organization->properties()->where('published', true)->count(),
            'occupied_properties' => $organization->properties()->where('availability_status', 'occupied')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'organization' => $organization,
                'statistics' => $stats,
            ],
        ]);
    }

    /**
     * Update the specified organization
     */
    public function update(Request $request, Organization $organization): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:organizations,email,' . $organization->id,
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'pincode' => 'sometimes|string|max:10',
            'plan_id' => 'sometimes|exists:plans,id',
            'status' => 'sometimes|in:active,inactive,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $organization->update($request->only([
                'name', 'email', 'phone', 'address', 'city', 'state', 'pincode', 'plan_id', 'status'
            ]));

            $organization->load(['plan', 'subscription']);

            return response()->json([
                'success' => true,
                'message' => 'Organization updated successfully',
                'data' => $organization,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update organization: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified organization
     */
    public function destroy(Organization $organization): JsonResponse
    {
        try {
            // Check if organization has active data
            $hasActiveData = $organization->properties()->count() > 0 || 
                           $organization->tenants()->count() > 0 || 
                           $organization->invoices()->count() > 0;

            if ($hasActiveData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete organization with active data. Please deactivate instead.',
                ], 400);
            }

            $organization->delete();

            return response()->json([
                'success' => true,
                'message' => 'Organization deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete organization: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Suspend organization
     */
    public function suspend(Organization $organization): JsonResponse
    {
        try {
            $organization->update(['status' => 'suspended']);

            return response()->json([
                'success' => true,
                'message' => 'Organization suspended successfully',
                'data' => $organization,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to suspend organization: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate organization
     */
    public function activate(Organization $organization): JsonResponse
    {
        try {
            $organization->update(['status' => 'active']);

            return response()->json([
                'success' => true,
                'message' => 'Organization activated successfully',
                'data' => $organization,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate organization: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get organization statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_organizations' => Organization::count(),
            'active_organizations' => Organization::where('status', 'active')->count(),
            'suspended_organizations' => Organization::where('status', 'suspended')->count(),
            'inactive_organizations' => Organization::where('status', 'inactive')->count(),
            'total_users' => User::whereNotNull('org_id')->count(),
            'total_properties' => Property::count(),
            'total_tenants' => Tenant::count(),
            'total_invoices' => Invoice::count(),
            'total_payments' => Payment::where('payment_status', 'completed')->count(),
            'total_revenue' => Payment::where('payment_status', 'completed')->sum('amount'),
            'recent_organizations' => Organization::with(['plan'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
