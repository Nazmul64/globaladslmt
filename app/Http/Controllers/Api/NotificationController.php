<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class NotificationController extends Controller
{
    /**
     * Update FCM Token for the authenticated user
     * POST /api/update-fcm-token
     */
    public function updateFcmToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fcm_token'   => 'required|string',
                'device_type' => 'nullable|string|in:android,ios,web',
                'email'       => 'nullable|email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $user = $request->user() ?? Auth::user();
            if (!$user && $request->email) {
                $user = User::where('email', $request->email)->first();
            }

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated or user not found',
                ], 401);
            }

            $user->update([
                'fcm_token'      => $request->fcm_token,
                'fcm_updated_at' => now(),
                'device_type'    => $request->device_type ?? $user->device_type ?? 'android',
            ]);

            Log::info("FCM Token updated for user {$user->id}");

            return response()->json([
                'status'  => true,
                'message' => 'FCM Token Updated',
            ], 200);

        } catch (Throwable $e) {
            Log::error("Update FCM Token Error: " . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Failed to update FCM token',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List user notifications (Paginated)
     * GET /api/notifications
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user() ?? Auth::user();
            if (!$user && $request->email) {
                $user = User::where('email', $request->email)->first();
            }

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated or user not found',
                ], 401);
            }

            $perPage = $request->input('per_page', 20);
            $notifications = UserNotification::where('user_id', $user->id)
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'status' => true,
                'data'   => $notifications,
            ], 200);

        } catch (Throwable $e) {
            Log::error("Fetch Notifications Error: " . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve notifications',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark single notification as read
     * POST /api/notifications/{id}/mark-as-read
     */
    public function markAsRead($id, Request $request)
    {
        try {
            $user = $request->user() ?? Auth::user();

            $query = UserNotification::where('id', $id);
            if ($user) {
                $query->where('user_id', $user->id);
            }

            $notification = $query->first();

            if (!$notification) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Notification not found',
                ], 404);
            }

            $notification->update(['is_read' => true]);

            return response()->json([
                'status'  => true,
                'message' => 'Marked as read',
            ], 200);

        } catch (Throwable $e) {
            Log::error("Mark as read error: " . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Failed to mark notification as read',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark all notifications as read for current user
     * POST /api/notifications/mark-all-read
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = $request->user() ?? Auth::user();
            if (!$user && $request->email) {
                $user = User::where('email', $request->email)->first();
            }

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            UserNotification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'status'  => true,
                'message' => 'All notifications marked as read',
            ], 200);

        } catch (Throwable $e) {
            Log::error("Mark all as read error: " . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Failed to mark all as read',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unread notification count
     * GET /api/notifications/unread-count
     */
    public function getUnreadCount(Request $request)
    {
        try {
            $user = $request->user() ?? Auth::user();
            if (!$user && $request->email) {
                $user = User::where('email', $request->email)->first();
            }

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $count = UserNotification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'status' => true,
                'unread_count' => $count,
            ], 200);

        } catch (Throwable $e) {
            Log::error("Get unread count error: " . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Failed to get unread count',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send test notification (for debugging / development)
     * POST /api/notifications/test-send
     */
    public function testSend(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title'   => 'required|string',
            'body'    => 'required|string',
            'type'    => 'nullable|string',
        ]);

        $res = PushNotificationService::send(
            $request->user_id,
            $request->title,
            $request->body,
            $request->type ?? 'test_notification',
            $request->payload ?? []
        );

        return response()->json([
            'status'  => true,
            'message' => 'Notification triggered',
            'data'    => $res,
        ]);
    }
}
