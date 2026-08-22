<?php

// ====================================================================
// 🔔 UNIFIED NOTIFICATION CONTROLLER (Firebase + OneSignal)
// 📁 File: app/Http/Controllers/Api/NotificationController.php
// ✅ Handles both Firebase and OneSignal notifications
// ====================================================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Notification;

class NotificationController extends Controller
{
    // ====================================================================
    // 🔥 FIREBASE CONFIGURATION
    // ====================================================================
    private $firebaseServerKey = 'YOUR_FIREBASE_SERVER_KEY'; // ⚠️ Firebase Console থেকে নিন

    // ====================================================================
    // 🔔 ONESIGNAL CONFIGURATION
    // ====================================================================
    private function getOneSignalAppId()
    {
        return config('services.onesignal.app_id', '19355887-8178-4d10-a8a3-3a6cc499968c');
    }

    private function getOneSignalApiKey()
    {
        return config('services.onesignal.rest_api_key', '');
    }

    // ====================================================================
    // 🔥 FIREBASE: UPDATE FCM TOKEN
    // ====================================================================
    public function updateFcmToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'fcm_token' => 'required|string',
                'device_type' => 'nullable|in:android,ios,web',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ Update or create user
            $user = DB::table('users')
                ->where('email', $request->email)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            DB::table('users')
                ->where('email', $request->email)
                ->update([
                    'fcm_token' => $request->fcm_token,
                    'device_type' => $request->device_type ?? 'android',
                    'fcm_updated_at' => now(),
                    'updated_at' => now(),
                ]);

            Log::info('FCM Token Updated', [
                'email' => $request->email,
                'device_type' => $request->device_type ?? 'android'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('FCM Token Update Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ====================================================================
    // 🔔 ONESIGNAL: UPDATE PLAYER ID
    // ====================================================================
    public function updateOneSignalPlayer(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'player_id' => 'required|string',
                'device_type' => 'nullable|in:android,ios,web',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ Update user
            $user = DB::table('users')
                ->where('email', $request->email)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            DB::table('users')
                ->where('email', $request->email)
                ->update([
                    'onesignal_player_id' => $request->player_id,
                    'device_type' => $request->device_type ?? 'android',
                    'updated_at' => now(),
                ]);

            Log::info('OneSignal Player ID Updated', [
                'email' => $request->email,
                'player_id' => substr($request->player_id, 0, 20) . '...'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Player ID updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('OneSignal Player ID Update Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ====================================================================
    // 🔥 FIREBASE: SEND NOTIFICATION
    // ====================================================================
    public function sendFirebaseNotification(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'email' => 'nullable|email', // Optional: specific user
                'image_url' => 'nullable|url',
                'action_url' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ Get FCM tokens
            $query = DB::table('users')->whereNotNull('fcm_token');

            if ($request->email) {
                $query->where('email', $request->email);
            }

            $users = $query->get();

            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No users found with FCM tokens'
                ], 404);
            }

            $fcmTokens = $users->pluck('fcm_token')->toArray();
            $successCount = 0;
            $failureCount = 0;

            // ✅ Send to Firebase
            foreach (array_chunk($fcmTokens, 1000) as $tokenChunk) {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $this->firebaseServerKey,
                    'Content-Type' => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send', [
                    'registration_ids' => $tokenChunk,
                    'notification' => [
                        'title' => $request->title,
                        'body' => $request->message,
                        'sound' => 'default',
                        'badge' => '1',
                        'icon' => 'ic_launcher',
                    ],
                    'data' => [
                        'action_url' => $request->action_url ?? '/notifications',
                        'image_url' => $request->image_url,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                    'priority' => 'high',
                    'content_available' => true,
                ]);

                $result = $response->json();
                $successCount += $result['success'] ?? 0;
                $failureCount += $result['failure'] ?? 0;
            }

            // ✅ Save to database
            DB::table('notifications')->insert([
                'title' => $request->title,
                'message' => $request->message,
                'image_url' => $request->image_url,
                'action_url' => $request->action_url ?? '/notifications',
                'notification_type' => 'firebase',
                'sent_at' => now(),
                'created_at' => now(),
            ]);

            Log::info('Firebase Notification Sent', [
                'title' => $request->title,
                'success' => $successCount,
                'failure' => $failureCount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Firebase notification sent successfully',
                'sent_to' => $successCount,
                'failed' => $failureCount,
                'total_users' => count($fcmTokens)
            ]);

        } catch (\Exception $e) {
            Log::error('Firebase Send Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ====================================================================
    // 🔔 ONESIGNAL: SEND NOTIFICATION
    // ====================================================================
    public function sendOneSignalNotification(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'email' => 'nullable|email', // Optional: specific user
                'image_url' => 'nullable|url',
                'action_url' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ Get player IDs
            $query = DB::table('users')->whereNotNull('onesignal_player_id');

            if ($request->email) {
                $query->where('email', $request->email);
            }

            $appId = $this->getOneSignalAppId();
            $apiKey = $this->getOneSignalApiKey();

            $payload = [
                'app_id' => $appId,
                'headings' => ['en' => $request->title],
                'contents' => ['en' => $request->message],
                'data' => [
                    'action_url' => $request->action_url ?? '/notifications',
                    'type' => 'admin_notification',
                ],
                'android_channel_id' => 'high_importance_channel',
                'priority' => 10,
                'ttl' => 86400,
            ];

            if (!empty($request->image_url)) {
                $payload['big_picture'] = $request->image_url;
                $payload['large_icon'] = $request->image_url;
                $payload['ios_attachments'] = ['id' => $request->image_url];
            }

            if ($request->filled('email')) {
                $user = DB::table('users')->where('email', $request->email)->first();
                if ($user && !empty($user->onesignal_player_id)) {
                    $payload['include_player_ids'] = [$user->onesignal_player_id];
                } else {
                    $payload['include_aliases'] = ['external_id' => [$request->email]];
                }
            } else {
                $payload['included_segments'] = ['Subscribed Users', 'Total Subscriptions'];
            }

            $headers = ['Content-Type' => 'application/json'];
            if (!empty($apiKey)) {
                $headers['Authorization'] = 'Basic ' . $apiKey;
            }

            $response = Http::withHeaders($headers)
                ->post('https://onesignal.com/api/v1/notifications', $payload);

            $result = $response->json();

            // ✅ Save to database
            DB::table('notifications')->insert([
                'title' => $request->title,
                'message' => $request->message,
                'image_url' => $request->image_url,
                'action_url' => $request->action_url ?? '/notifications',
                'notification_type' => 'onesignal',
                'sent_at' => now(),
                'created_at' => now(),
            ]);

            Log::info('OneSignal Notification Sent', [
                'title' => $request->title,
                'payload' => $payload,
                'onesignal_response' => $result
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OneSignal notification sent successfully',
                'onesignal_response' => $result,
                'sent_to' => $result['recipients'] ?? 1
            ]);

        } catch (\Exception $e) {
            Log::error('OneSignal Send Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ====================================================================
    // 📥 GET NOTIFICATIONS (Works for both Firebase & OneSignal)
    // ====================================================================
    public function getNotifications(Request $request)
    {
        try {
            $limit = $request->input('limit', 50);
            $email = $request->input('email'); // Optional

            $notifications = DB::table('notifications')
                ->orderBy('sent_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'count' => $notifications->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Get Notifications Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ====================================================================
    // ✅ MARK NOTIFICATION AS READ
    // ====================================================================
    public function markNotificationRead(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'notification_id' => 'required|integer|exists:notifications,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::table('notifications')
                ->where('id', $request->notification_id)
                ->update(['is_read' => 1]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ====================================================================
    // 🗑️ DELETE NOTIFICATION
    // ====================================================================
    public function deleteNotification(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'notification_id' => 'required|integer|exists:notifications,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::table('notifications')
                ->where('id', $request->notification_id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ====================================================================
    // 📊 GET NOTIFICATION COUNT (Unread)
    // ====================================================================
    public function getNotificationCount(Request $request)
    {
        try {
            $unreadCount = DB::table('notifications')
                ->where('is_read', 0)
                ->count();

            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ====================================================================
    // ✅ MARK ALL AS READ
    // ====================================================================
    public function markAllNotificationsRead(Request $request)
    {
        try {
            DB::table('notifications')
                ->where('is_read', 0)
                ->update(['is_read' => 1]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ====================================================================
    // 🚀 SEND TO ALL USERS (Select platform)
    // ====================================================================
    public function sendNotificationToAll(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'platform' => 'required|in:firebase,onesignal,both',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'image_url' => 'nullable|url',
                'action_url' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $results = [];

            // ✅ Send via Firebase
            if (in_array($request->platform, ['firebase', 'both'])) {
                $firebaseResult = $this->sendFirebaseNotification($request);
                $results['firebase'] = $firebaseResult->getData();
            }

            // ✅ Send via OneSignal
            if (in_array($request->platform, ['onesignal', 'both'])) {
                $oneSignalResult = $this->sendOneSignalNotification($request);
                $results['onesignal'] = $oneSignalResult->getData();
            }

            return response()->json([
                'success' => true,
                'message' => 'Notifications sent successfully',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ====================================================================
    // 👤 SEND TO SPECIFIC USER
    // ====================================================================
    public function sendNotificationToUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'platform' => 'required|in:firebase,onesignal,both',
                'email' => 'required|email|exists:users,email',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'image_url' => 'nullable|url',
                'action_url' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $results = [];

            // ✅ Send via Firebase
            if (in_array($request->platform, ['firebase', 'both'])) {
                $firebaseResult = $this->sendFirebaseNotification($request);
                $results['firebase'] = $firebaseResult->getData();
            }

            // ✅ Send via OneSignal
            if (in_array($request->platform, ['onesignal', 'both'])) {
                $oneSignalResult = $this->sendOneSignalNotification($request);
                $results['onesignal'] = $oneSignalResult->getData();
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification sent to user successfully',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ====================================================================
    // 🧪 TEST FIREBASE NOTIFICATION
    // ====================================================================
    public function testFirebaseNotification(Request $request)
    {
        $request->merge([
            'title' => 'Firebase Test Notification',
            'message' => 'This is a test notification from Firebase',
        ]);

        return $this->sendFirebaseNotification($request);
    }

    // ====================================================================
    // 🧪 TEST ONESIGNAL NOTIFICATION
    // ====================================================================
    public function testOneSignalNotification(Request $request)
    {
        $request->merge([
            'title' => 'OneSignal Test Notification',
            'message' => 'This is a test notification from OneSignal',
        ]);

        return $this->sendOneSignalNotification($request);
    }
}

