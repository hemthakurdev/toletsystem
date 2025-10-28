<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    /**
     * Display a listing of leads for the organization
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lead::with(['property', 'conversations'])
            ->where('org_id', auth()->user()->org_id);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        if ($request->has('high_priority') && $request->boolean('high_priority')) {
            $query->highPriority();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $leads = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $leads,
        ]);
    }

    /**
     * Store a newly created lead (from marketplace)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'message' => 'required|string|max:1000',
            'source' => 'nullable|in:public_listing,manual,referral',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $property = Property::findOrFail($request->property_id);
            
            $lead = Lead::create([
                'property_id' => $request->property_id,
                'org_id' => $property->org_id,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'message' => $request->message,
                'source' => $request->source ?? 'public_listing',
                'status' => 'new',
            ]);

            // Calculate initial lead score
            $lead->calculateLeadScore();

            // Load relationships
            $lead->load(['property', 'organization']);

            return response()->json([
                'success' => true,
                'message' => 'Lead created successfully',
                'data' => $lead,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create lead',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified lead
     */
    public function show(Lead $lead): JsonResponse
    {
        // Check if user has access to this lead (org member) OR lead owner (frontend user)
        $user = auth()->user();
        $isOrgUser = $user && $lead->org_id === $user->org_id;
        $isLeadOwner = $user && ($lead->user_id === $user->id);
        if (!$isOrgUser && !$isLeadOwner) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to lead',
            ], 403);
        }

        $lead->load(['property', 'organization', 'conversations']);

        return response()->json([
            'success' => true,
            'data' => $lead,
        ]);
    }

    /**
     * Update the specified lead
     */
    public function update(Request $request, Lead $lead): JsonResponse
    {
        // Check if user has access to this lead (org member) OR lead owner (frontend user)
        $user = auth()->user();
        $isOrgUser = $user && $lead->org_id === $user->org_id;
        $isLeadOwner = $user && $lead->user_id === $user->id;
        if (!$isOrgUser && !$isLeadOwner) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to lead',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'nullable|email|max:255',
            'message' => 'sometimes|required|string|max:1000',
            'status' => 'sometimes|in:new,contacted,interested,not_interested,converted',
            'notes' => 'nullable|string|max:1000',
            'lead_score' => 'sometimes|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $lead->update($request->only([
                'name', 'phone', 'email', 'message', 'status', 'notes', 'lead_score'
            ]));

            // If status is being updated to contacted, set contacted_at
            if ($request->has('status') && $request->status === 'contacted' && !$lead->contacted_at) {
                $lead->update(['contacted_at' => now()]);
            }

            // Recalculate lead score if relevant fields changed
            if ($request->hasAny(['name', 'phone', 'email', 'message', 'status'])) {
                $lead->calculateLeadScore();
            }

            $lead->load(['property', 'organization', 'conversations']);

            return response()->json([
                'success' => true,
                'message' => 'Lead updated successfully',
                'data' => $lead,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lead',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified lead
     */
    public function destroy(Lead $lead): JsonResponse
    {
        // Allow org member (same org) OR lead owner (frontend user) to cancel/delete
        $user = auth()->user();
        $isOrgUser = $user && $lead->org_id === $user->org_id;
        $isLeadOwner = $user && $lead->user_id === $user->id;
        if (!$isOrgUser && !$isLeadOwner) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to lead',
            ], 403);
        }

        try {
            $lead->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lead deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete lead',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark lead as contacted
     */
    public function markContacted(Lead $lead): JsonResponse
    {
        // Check if user has access to this lead
        if ($lead->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to lead',
            ], 403);
        }

        try {
            $lead->markAsContacted();
            $lead->calculateLeadScore();

            return response()->json([
                'success' => true,
                'message' => 'Lead marked as contacted',
                'data' => $lead->load(['property', 'organization']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lead status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark lead as converted
     */
    public function markConverted(Lead $lead): JsonResponse
    {
        // Check if user has access to this lead
        if ($lead->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to lead',
            ], 403);
        }

        try {
            $lead->markAsConverted();
            $lead->calculateLeadScore();

            return response()->json([
                'success' => true,
                'message' => 'Lead marked as converted',
                'data' => $lead->load(['property', 'organization']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lead status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark lead as not interested
     */
    public function markNotInterested(Lead $lead): JsonResponse
    {
        // Check if user has access to this lead
        if ($lead->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to lead',
            ], 403);
        }

        try {
            $lead->markAsNotInterested();

            return response()->json([
                'success' => true,
                'message' => 'Lead marked as not interested',
                'data' => $lead->load(['property', 'organization']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lead status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add conversation to lead
     */
    public function addConversation(Request $request, Lead $lead): JsonResponse
    {
        // Check if user has access to this lead (org member) OR lead owner (frontend user)
        $user = auth()->user();
        $isOrgUser = $user && $lead->org_id === $user->org_id;
        $isLeadOwner = $user && ($lead->user_id === $user->id);
        if (!$isOrgUser && !$isLeadOwner) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to lead',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();
            $senderType = (($user->user_type ?? null) === 'frontend') ? 'tenant' : 'owner';

            $conversation = $lead->addConversation(
                $request->message,
                $senderType,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Conversation added successfully',
                'data' => $conversation,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add conversation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get lead statistics for the organization
     */
    public function statistics(): JsonResponse
    {
        $orgId = auth()->user()->org_id;

        $stats = [
            'total_leads' => Lead::where('org_id', $orgId)->count(),
            'new_leads' => Lead::where('org_id', $orgId)->new()->count(),
            'contacted_leads' => Lead::where('org_id', $orgId)->contacted()->count(),
            'converted_leads' => Lead::where('org_id', $orgId)->converted()->count(),
            'high_priority_leads' => Lead::where('org_id', $orgId)->highPriority()->count(),
            'conversion_rate' => 0,
            'avg_response_time' => 0,
        ];

        // Calculate conversion rate
        $totalLeads = $stats['total_leads'];
        if ($totalLeads > 0) {
            $stats['conversion_rate'] = round(($stats['converted_leads'] / $totalLeads) * 100, 2);
        }

        // Calculate average response time
        $contactedLeads = Lead::where('org_id', $orgId)
            ->whereNotNull('contacted_at')
            ->get();

        if ($contactedLeads->count() > 0) {
            $totalResponseTime = $contactedLeads->sum(function ($lead) {
                return $lead->created_at->diffInHours($lead->contacted_at);
            });
            $stats['avg_response_time'] = round($totalResponseTime / $contactedLeads->count(), 1);
        }

        // Leads by source
        $stats['leads_by_source'] = Lead::where('org_id', $orgId)
            ->selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->get()
            ->pluck('count', 'source');

        // Recent leads (last 30 days)
        $stats['recent_leads'] = Lead::where('org_id', $orgId)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
