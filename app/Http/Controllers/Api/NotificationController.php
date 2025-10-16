<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notification::where('user_id', auth()->id())
            ->orWhere('user_id', null); // System-wide notifications

        // Apply filters
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $notifications = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Store a newly created notification
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|string|max:50',
            'priority' => 'required|in:low,medium,high,urgent',
            'action_url' => 'nullable|string|max:500',
            'action_text' => 'nullable|string|max:100',
            'expires_at' => 'nullable|date|after:now',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $notification = Notification::create([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'priority' => $request->priority,
                'action_url' => $request->action_url,
                'action_text' => $request->action_text,
                'expires_at' => $request->expires_at,
                'data' => $request->data,
                'status' => 'unread',
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification created successfully',
                'data' => $notification,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create notification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified notification
     */
    public function show(Notification $notification): JsonResponse
    {
        // Check if notification belongs to user or is system-wide
        if ($notification->user_id && $notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this notification',
            ], 403);
        }

        // Mark as read when viewed
        if ($notification->status === 'unread') {
            $notification->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $notification,
        ]);
    }

    /**
     * Update the specified notification
     */
    public function update(Request $request, Notification $notification): JsonResponse
    {
        // Check if notification belongs to user or is system-wide
        if ($notification->user_id && $notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this notification',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:unread,read,archived',
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updateData = $request->only(['status', 'priority']);

            // Set read_at when marking as read
            if (isset($updateData['status']) && $updateData['status'] === 'read' && $notification->status === 'unread') {
                $updateData['read_at'] = now();
            }

            $notification->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Notification updated successfully',
                'data' => $notification,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified notification
     */
    public function destroy(Notification $notification): JsonResponse
    {
        // Check if notification belongs to user or is system-wide
        if ($notification->user_id && $notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this notification',
            ], 403);
        }

        try {
            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        // Check if notification belongs to user or is system-wide
        if ($notification->user_id && $notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this notification',
            ], 403);
        }

        try {
            $notification->update([
                'status' => 'read',
                'read_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'data' => $notification,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            Notification::where('user_id', auth()->id())
                ->orWhere('user_id', null)
                ->where('status', 'unread')
                ->update([
                    'status' => 'read',
                    'read_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Archive notification
     */
    public function archive(Notification $notification): JsonResponse
    {
        // Check if notification belongs to user or is system-wide
        if ($notification->user_id && $notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to archive this notification',
            ], 403);
        }

        try {
            $notification->update([
                'status' => 'archived',
                'archived_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification archived',
                'data' => $notification,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive notification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get notification statistics
     */
    public function statistics(): JsonResponse
    {
        $userId = auth()->id();

        $stats = [
            'total_notifications' => Notification::where('user_id', $userId)
                ->orWhere('user_id', null)
                ->count(),
            'unread_notifications' => Notification::where('user_id', $userId)
                ->orWhere('user_id', null)
                ->where('status', 'unread')
                ->count(),
            'read_notifications' => Notification::where('user_id', $userId)
                ->orWhere('user_id', null)
                ->where('status', 'read')
                ->count(),
            'archived_notifications' => Notification::where('user_id', $userId)
                ->orWhere('user_id', null)
                ->where('status', 'archived')
                ->count(),
            'notifications_by_type' => Notification::where('user_id', $userId)
                ->orWhere('user_id', null)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get(),
            'notifications_by_priority' => Notification::where('user_id', $userId)
                ->orWhere('user_id', null)
                ->selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->get(),
            'recent_notifications' => Notification::where('user_id', $userId)
                ->orWhere('user_id', null)
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
     * Get notification types
     */
    public function types(): JsonResponse
    {
        $types = [
            'system' => 'System Notification',
            'payment' => 'Payment Notification',
            'invoice' => 'Invoice Notification',
            'lead' => 'Lead Notification',
            'property' => 'Property Notification',
            'tenant' => 'Tenant Notification',
            'maintenance' => 'Maintenance Notification',
            'reminder' => 'Reminder',
            'alert' => 'Alert',
            'info' => 'Information',
        ];

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * Send notification to multiple users
     */
    public function sendBulk(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|string|max:50',
            'priority' => 'required|in:low,medium,high,urgent',
            'action_url' => 'nullable|string|max:500',
            'action_text' => 'nullable|string|max:100',
            'expires_at' => 'nullable|date|after:now',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $notifications = [];
            
            foreach ($request->user_ids as $userId) {
                $notifications[] = Notification::create([
                    'user_id' => $userId,
                    'title' => $request->title,
                    'message' => $request->message,
                    'type' => $request->type,
                    'priority' => $request->priority,
                    'action_url' => $request->action_url,
                    'action_text' => $request->action_text,
                    'expires_at' => $request->expires_at,
                    'data' => $request->data,
                    'status' => 'unread',
                    'created_by' => auth()->id(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notifications sent successfully',
                'data' => $notifications,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notifications: ' . $e->getMessage(),
            ], 500);
        }
    }
}
