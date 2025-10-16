<?php

namespace App\Http\Controllers\Api;

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
use Illuminate\Support\Facades\Storage;

class OrganizationController extends Controller
{
    /**
     * Display the current user's organization
     */
    public function show(): JsonResponse
    {
        $organization = auth()->user()->organization;
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
            ], 404);
        }

        $organization->load(['users', 'subscription']);

        return response()->json([
            'success' => true,
            'data' => $organization,
        ]);
    }

    /**
     * Update the current user's organization
     */
    public function update(Request $request): JsonResponse
    {
        $organization = auth()->user()->organization;
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updateData = $request->only([
                'name', 'email', 'phone', 'address', 'city', 
                'state', 'pincode', 'website', 'description', 'settings'
            ]);

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($organization->logo) {
                    Storage::disk('public')->delete($organization->logo);
                }

                $file = $request->file('logo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('organizations/logos', $filename, 'public');
                $updateData['logo'] = $path;
            }

            $organization->update($updateData);
            $organization->load(['users', 'subscription']);

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
     * Get organization statistics
     */
    public function statistics(): JsonResponse
    {
        $organization = auth()->user()->organization;
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
            ], 404);
        }

        $stats = [
            'total_properties' => Property::where('org_id', $organization->id)->count(),
            'total_tenants' => Tenant::where('org_id', $organization->id)->count(),
            'total_users' => User::where('org_id', $organization->id)->count(),
            'total_invoices' => Invoice::where('org_id', $organization->id)->count(),
            'total_payments' => Payment::where('org_id', $organization->id)->count(),
            'monthly_revenue' => Payment::where('org_id', $organization->id)
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
            'pending_invoices' => Invoice::where('org_id', $organization->id)
                ->where('status', 'pending')
                ->count(),
            'active_properties' => Property::where('org_id', $organization->id)
                ->where('status', 'active')
                ->count(),
            'occupied_properties' => Property::where('org_id', $organization->id)
                ->whereHas('tenant')
                ->count(),
            'vacant_properties' => Property::where('org_id', $organization->id)
                ->whereDoesntHave('tenant')
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get organization settings
     */
    public function settings(): JsonResponse
    {
        $organization = auth()->user()->organization;
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
            ], 404);
        }

        $defaultSettings = [
            'currency' => 'INR',
            'date_format' => 'd/m/Y',
            'timezone' => 'Asia/Kolkata',
            'invoice_prefix' => 'INV',
            'invoice_number_format' => 'INV-{year}-{month}-{number}',
            'payment_terms' => 30,
            'late_fee_percentage' => 2,
            'auto_generate_invoices' => true,
            'invoice_due_reminder_days' => [7, 3, 1],
            'email_notifications' => [
                'new_lead' => true,
                'payment_received' => true,
                'invoice_overdue' => true,
                'property_approved' => true,
            ],
            'sms_notifications' => [
                'payment_reminder' => false,
                'invoice_overdue' => false,
            ],
            'theme' => [
                'primary_color' => '#3B82F6',
                'secondary_color' => '#6B7280',
                'logo_url' => null,
            ],
        ];

        $settings = array_merge($defaultSettings, $organization->settings ?? []);

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Update organization settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $organization = auth()->user()->organization;
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'currency' => 'sometimes|string|max:3',
            'date_format' => 'sometimes|string|max:20',
            'timezone' => 'sometimes|string|max:50',
            'invoice_prefix' => 'sometimes|string|max:10',
            'invoice_number_format' => 'sometimes|string|max:100',
            'payment_terms' => 'sometimes|integer|min:1|max:365',
            'late_fee_percentage' => 'sometimes|numeric|min:0|max:100',
            'auto_generate_invoices' => 'sometimes|boolean',
            'invoice_due_reminder_days' => 'sometimes|array',
            'invoice_due_reminder_days.*' => 'integer|min:1|max:30',
            'email_notifications' => 'sometimes|array',
            'email_notifications.*' => 'boolean',
            'sms_notifications' => 'sometimes|array',
            'sms_notifications.*' => 'boolean',
            'theme' => 'sometimes|array',
            'theme.primary_color' => 'sometimes|string|max:7',
            'theme.secondary_color' => 'sometimes|string|max:7',
            'theme.logo_url' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $currentSettings = $organization->settings ?? [];
            $newSettings = array_merge($currentSettings, $request->all());
            
            $organization->update(['settings' => $newSettings]);

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
                'data' => $newSettings,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get organization users
     */
    public function users(): JsonResponse
    {
        $organization = auth()->user()->organization;
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
            ], 404);
        }

        $users = User::where('org_id', $organization->id)
            ->with(['roles'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Add user to organization
     */
    public function addUser(Request $request): JsonResponse
    {
        $organization = auth()->user()->organization;
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'org_id' => $organization->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            // Assign roles
            if ($request->has('roles')) {
                $user->assignRole($request->roles);
            } else {
                $user->assignRole('staff'); // Default role
            }

            $user->load(['roles']);

            return response()->json([
                'success' => true,
                'message' => 'User added successfully',
                'data' => $user,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove user from organization
     */
    public function removeUser(User $user): JsonResponse
    {
        $organization = auth()->user()->organization;
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
            ], 404);
        }

        // Check if user belongs to organization
        if ($user->org_id !== $organization->id) {
            return response()->json([
                'success' => false,
                'message' => 'User does not belong to this organization',
            ], 403);
        }

        // Prevent removing the last admin
        if ($user->hasRole('admin') && $organization->users()->whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove the last admin user',
            ], 403);
        }

        try {
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User removed successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get organization subscription details
     */
    public function subscription(): JsonResponse
    {
        $organization = auth()->user()->organization;
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
            ], 404);
        }

        $subscription = $organization->subscription;
        
        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found',
            ], 404);
        }

        $subscription->load(['plan']);

        return response()->json([
            'success' => true,
            'data' => $subscription,
        ]);
    }
}
