<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }
    /**
     * Display a listing of users
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['organization', 'roles']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('org_id')) {
            $query->where('org_id', $request->org_id);
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
        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'org_id' => 'required|exists:organizations,id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
            'status' => 'nullable|in:active,inactive,suspended',
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
                'org_id' => $request->org_id,
                'status' => $request->status ?? 'active',
                'email_verified_at' => now(),
            ]);

            // Assign roles
            if ($request->has('roles')) {
                $user->assignRole($request->roles);
            }

            // Send welcome email
            $this->emailService->sendWelcomeEmail($user, $request->password);

            $user->load(['organization', 'roles']);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['organization', 'roles', 'permissions']);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'org_id' => 'sometimes|exists:organizations,id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
            'status' => 'nullable|in:active,inactive,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updateData = $request->only(['name', 'email', 'phone', 'org_id', 'status']);
            
            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Update roles
            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            }

            $user->load(['organization', 'roles']);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            // Prevent deletion of super admin users
            if ($user->hasRole('super_admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete super admin users',
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Suspend a user
     */
    public function suspend(User $user): JsonResponse
    {
        try {
            if ($user->hasRole('super_admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot suspend super admin users',
                ], 403);
            }

            $user->update(['status' => 'suspended']);

            return response()->json([
                'success' => true,
                'message' => 'User suspended successfully',
                'data' => $user,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to suspend user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate a user
     */
    public function activate(User $user): JsonResponse
    {
        try {
            $user->update(['status' => 'active']);

            return response()->json([
                'success' => true,
                'message' => 'User activated successfully',
                'data' => $user,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'inactive_users' => User::where('status', 'inactive')->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),
            'users_by_organization' => User::select('org_id', \DB::raw('COUNT(*) as count'))
                ->with('organization:id,name')
                ->groupBy('org_id')
                ->get(),
            'recent_users' => User::with(['organization:id,name'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Bulk activate users
     */
    public function bulkActivate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updated = User::whereIn('id', $request->user_ids)
                ->where('status', '!=', 'active')
                ->update(['status' => 'active']);

            return response()->json([
                'success' => true,
                'message' => "Successfully activated {$updated} users",
                'data' => ['activated_count' => $updated],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk activate users: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk suspend users
     */
    public function bulkSuspend(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updated = User::whereIn('id', $request->user_ids)
                ->where('status', '!=', 'suspended')
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'super_admin');
                })
                ->update(['status' => 'suspended']);

            return response()->json([
                'success' => true,
                'message' => "Successfully suspended {$updated} users",
                'data' => ['suspended_count' => $updated],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk suspend users: ' . $e->getMessage(),
            ], 500);
        }
    }
}
