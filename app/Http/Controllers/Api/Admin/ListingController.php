<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Lead;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ListingController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }
    /**
     * Get pending properties for approval
     */
    public function pending(Request $request): JsonResponse
    {
        $query = Property::with(['organization', 'tenant'])
            ->where('published', false)
            ->where('status', 'pending');

        // Apply filters
        if ($request->has('org_id')) {
            $query->where('org_id', $request->org_id);
        }

        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
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
        $properties = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $properties,
        ]);
    }

    /**
     * Approve a property listing
     */
    public function approve(Property $property): JsonResponse
    {
        try {
            $property->update([
                'published' => true,
                'status' => 'active',
                'approved_at' => now(),
            ]);

            // Send approval email to property owner
            $propertyOwner = $property->organization->users()->whereHas('roles', function($query) {
                $query->where('name', 'admin');
            })->first();
            
            if ($propertyOwner) {
                $this->emailService->sendPropertyApprovalEmail($property, $propertyOwner, 'approved');
            }

            return response()->json([
                'success' => true,
                'message' => 'Property approved successfully',
                'data' => $property,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve property: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject a property listing
     */
    public function reject(Request $request, Property $property): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $property->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => now(),
            ]);

            // Send rejection email to property owner
            $propertyOwner = $property->organization->users()->whereHas('roles', function($query) {
                $query->where('name', 'admin');
            })->first();
            
            if ($propertyOwner) {
                $this->emailService->sendPropertyApprovalEmail($property, $propertyOwner, 'rejected', $request->rejection_reason);
            }

            return response()->json([
                'success' => true,
                'message' => 'Property rejected successfully',
                'data' => $property,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject property: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get flagged content
     */
    public function flagged(Request $request): JsonResponse
    {
        $query = Property::with(['organization'])
            ->where('is_flagged', true);

        // Apply filters
        if ($request->has('org_id')) {
            $query->where('org_id', $request->org_id);
        }

        if ($request->has('flag_reason')) {
            $query->where('flag_reason', 'like', "%{$request->flag_reason}%");
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'flagged_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $properties = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $properties,
        ]);
    }

    /**
     * Resolve flagged content
     */
    public function resolveFlag(Request $request, Property $property): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject,remove',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            switch ($request->action) {
                case 'approve':
                    $property->update([
                        'is_flagged' => false,
                        'flag_reason' => null,
                        'flagged_at' => null,
                        'admin_notes' => $request->admin_notes,
                        'published' => true,
                        'status' => 'active',
                    ]);
                    break;

                case 'reject':
                    $property->update([
                        'is_flagged' => false,
                        'flag_reason' => null,
                        'flagged_at' => null,
                        'admin_notes' => $request->admin_notes,
                        'status' => 'rejected',
                    ]);
                    break;

                case 'remove':
                    $property->update([
                        'is_flagged' => false,
                        'flag_reason' => null,
                        'flagged_at' => null,
                        'admin_notes' => $request->admin_notes,
                        'status' => 'removed',
                        'published' => false,
                    ]);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Flag resolved successfully',
                'data' => $property,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve flag: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get content moderation statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'pending_approvals' => Property::where('published', false)
                ->where('status', 'pending')
                ->count(),
            'flagged_content' => Property::where('is_flagged', true)->count(),
            'total_properties' => Property::count(),
            'published_properties' => Property::where('published', true)->count(),
            'rejected_properties' => Property::where('status', 'rejected')->count(),
            'properties_by_status' => Property::select('status', \DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get(),
            'recent_approvals' => Property::where('approved_at', '>=', now()->subDays(7))
                ->count(),
            'recent_rejections' => Property::where('rejected_at', '>=', now()->subDays(7))
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Bulk approve properties
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_ids' => 'required|array',
            'property_ids.*' => 'exists:properties,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updated = Property::whereIn('id', $request->property_ids)
                ->where('published', false)
                ->where('status', 'pending')
                ->update([
                    'published' => true,
                    'status' => 'active',
                    'approved_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully approved {$updated} properties",
                'data' => ['approved_count' => $updated],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk approve properties: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk reject properties
     */
    public function bulkReject(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_ids' => 'required|array',
            'property_ids.*' => 'exists:properties,id',
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updated = Property::whereIn('id', $request->property_ids)
                ->where('published', false)
                ->where('status', 'pending')
                ->update([
                    'status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason,
                    'rejected_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully rejected {$updated} properties",
                'data' => ['rejected_count' => $updated],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk reject properties: ' . $e->getMessage(),
            ], 500);
        }
    }
}
