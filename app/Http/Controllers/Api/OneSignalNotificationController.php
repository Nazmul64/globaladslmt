<?php

// ====================================================================
// ✅ ONESIGNAL PUSH NOTIFICATION - LARAVEL CONTROLLER
// 📁 File: app/Http/Controllers/Api/OneSignalNotificationController.php
// ✅ Handle OneSignal Player ID updates and notifications
// ====================================================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OneSignalNotificationController extends Controller
{
    // ✅ Your OneSignal REST API Key
    private $oneSignalRestApiKey = 'YOUR_ONESIGNAL_REST_API_KEY';

    // ✅ Your OneSignal App ID
    private $oneSignalAppId = 'YOUR_ONESIGNAL_APP_ID';

    /*
    |--------------------------------------------------------------------------
    | ✅ METHOD 1: UPDATE ONESIGNAL PLAYER ID
    |--------------------------------------------------------------------------
    | Called from Flutter app when user logs in
    | Stores the OneSignal Player ID (Subscription ID) in database
    |--------------------------------------------------------------------------
    */

    public function updateOneSignalPlayerId(Request $request)
    {
        try {
            Log::info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            Log::info('📥 UPDATE ONESIGNAL PLAYER ID REQUEST');
            Log::info('Request Data:', $request->all());

            // ✅ Validate incoming request
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'onesignal_player_id' => 'required|string',
                'device_type' => 'nullable|in:android,ios,web',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation Failed', [
                    'errors' => $validator->errors()->toArray()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ Find user by email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                Log::error('User not found', ['email' => $request->email]);

                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // ✅ Update user's OneSignal Player ID
            $user->update([
                'onesignal_player_id' => $request->onesignal_player_id,
                'device_type' => $request->device_type ?? 'android',
                'onesignal_updated_at' => now(),
            ]);

            Log::info('✅ ONESIGNAL PLAYER ID UPDATED SUCCESSFULLY', [
                'user_id' => $user->id,
                'email' => $user->email,
                'player_id' => substr($request->onesignal_player_id, 0, 20) . '...',
                'device_type' => $user->device_type,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OneSignal Player ID updated successfully',
                'data' => [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'device_type' => $user->device_type,
                    'updated_at' => $user->onesignal_updated_at->toDateTimeString()
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ UPDATE ONESIGNAL PLAYER ID ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update OneSignal Player ID: ' . $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ✅ METHOD 2: GET NOTIFICATIONS
    |--------------------------------------------------------------------------
    | Fetch notification history from database
    | Called from Flutter app to show notification list
    |--------------------------------------------------------------------------
    */

    public function getNotifications(Request $request)
    {
        try {
            Log::info('📤 Fetching notifications from database');

            $limit = $request->input('limit', 50);

            // ✅ Fetch notifications from database
            $notifications = Notification::latest('sent_at')
                ->limit($limit)
                ->get(['id', 'title', 'message', 'image_url', 'action_url', 'sent_at']);

            Log::info('✅ Notifications fetched', [
                'count' => $notifications->count()
            ]);

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'count' => $notifications->count()
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Get Notifications Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications'
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ✅ METHOD 3: SEND NOTIFICATION TO ALL USERS
    |--------------------------------------------------------------------------
    | Send OneSignal notification to all users
    | Called from admin panel
    |--------------------------------------------------------------------------
    */

    public function sendToAllUsers(Request $request)
    {
        try {
            Log::info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            Log::info('📤 SENDING NOTIFICATION TO ALL USERS');

            // ✅ Validate request
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'message' => 'required|string|max:1000',
                'image_url' => 'nullable|url',
                'action_url' => 'nullable|url',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ Get all player IDs from database
            $playerIds = User::whereNotNull('onesignal_player_id')
                ->pluck('onesignal_player_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            if (empty($playerIds)) {
                Log::warning('No users with OneSignal Player IDs found');

                return response()->json([
                    'success' => false,
                    'message' => 'No users found with OneSignal Player IDs'
                ], 404);
            }

            Log::info('Found users with Player IDs', [
                'count' => count($playerIds)
            ]);

            // ✅ Prepare OneSignal notification payload
            $payload = [
                'app_id' => $this->oneSignalAppId,
                'include_player_ids' => $playerIds,
                'headings' => ['en' => $request->title],
                'contents' => ['en' => $request->message],
                'data' => [
                    'type' => 'admin_notification',
                    'action_url' => $request->action_url ?? '',
                    'sent_at' => now()->toIso8601String(),
                ],
            ];

            // ✅ Add image if provided
            if ($request->image_url) {
                $payload['big_picture'] = $request->image_url;
                $payload['large_icon'] = $request->image_url;
                $payload['ios_attachments'] = ['image' => $request->image_url];
            }

            // ✅ Add action URL
            if ($request->action_url) {
                $payload['url'] = $request->action_url;
            }

            Log::info('OneSignal Payload', $payload);

            // ✅ Send notification via OneSignal REST API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . $this->oneSignalRestApiKey,
            ])->post('https://onesignal.com/api/v1/notifications', $payload);

            $responseData = $response->json();

            Log::info('OneSignal API Response', [
                'status' => $response->status(),
                'response' => $responseData
            ]);

            // ✅ Save notification to database
            $notification = Notification::create([
                'title' => $request->title,
                'message' => $request->message,
                'image_url' => $request->image_url,
                'action_url' => $request->action_url,
                'sent_at' => now(),
                'recipients_count' => $responseData['recipients'] ?? count($playerIds),
            ]);

            Log::info('✅ Notification saved to database', [
                'notification_id' => $notification->id
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification sent successfully to all users',
                    'data' => [
                        'notification_id' => $notification->id,
                        'onesignal_id' => $responseData['id'] ?? null,
                        'recipients' => $responseData['recipients'] ?? 0,
                        'total_users' => count($playerIds),
                    ]
                ], 200);
            } else {
                Log::error('OneSignal API Error', [
                    'status' => $response->status(),
                    'response' => $responseData
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send notification via OneSignal',
                    'error' => $responseData
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('❌ Send Notification Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ✅ METHOD 4: SEND NOTIFICATION TO SPECIFIC USERS
    |--------------------------------------------------------------------------
    | Send notification to specific user emails
    |--------------------------------------------------------------------------
    */

    public function sendToSpecificUsers(Request $request)
    {
        try {
            // ✅ Validate request
            $validator = Validator::make($request->all(), [
                'emails' => 'required|array|min:1',
                'emails.*' => 'email|exists:users,email',
                'title' => 'required|string|max:255',
                'message' => 'required|string|max:1000',
                'image_url' => 'nullable|url',
                'action_url' => 'nullable|url',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ Get player IDs for specific users
            $playerIds = User::whereIn('email', $request->emails)
                ->whereNotNull('onesignal_player_id')
                ->pluck('onesignal_player_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            if (empty($playerIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid OneSignal Player IDs found for specified users'
                ], 404);
            }

            // ✅ Prepare OneSignal payload
            $payload = [
                'app_id' => $this->oneSignalAppId,
                'include_player_ids' => $playerIds,
                'headings' => ['en' => $request->title],
                'contents' => ['en' => $request->message],
                'data' => [
                    'type' => 'admin_notification',
                    'action_url' => $request->action_url ?? '',
                ],
            ];

            if ($request->image_url) {
                $payload['big_picture'] = $request->image_url;
                $payload['large_icon'] = $request->image_url;
                $payload['ios_attachments'] = ['image' => $request->image_url];
            }

            if ($request->action_url) {
                $payload['url'] = $request->action_url;
            }

            // ✅ Send via OneSignal
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . $this->oneSignalRestApiKey,
            ])->post('https://onesignal.com/api/v1/notifications', $payload);

            $responseData = $response->json();

            // ✅ Save to database
            Notification::create([
                'title' => $request->title,
                'message' => $request->message,
                'image_url' => $request->image_url,
                'action_url' => $request->action_url,
                'sent_at' => now(),
                'recipients_count' => count($playerIds),
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification sent successfully',
                    'data' => [
                        'recipients' => $responseData['recipients'] ?? 0,
                        'total_users' => count($playerIds),
                    ]
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send notification',
                    'error' => $responseData
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Send to Specific Users Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ✅ METHOD 5: GET USER STATS
    |--------------------------------------------------------------------------
    | Get statistics about users with OneSignal Player IDs
    |--------------------------------------------------------------------------
    */

    public function getUserStats()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'users_with_onesignal' => User::whereNotNull('onesignal_player_id')->count(),
                'android_users' => User::where('device_type', 'android')
                    ->whereNotNull('onesignal_player_id')
                    ->count(),
                'ios_users' => User::where('device_type', 'ios')
                    ->whereNotNull('onesignal_player_id')
                    ->count(),
                'active_users' => User::where('is_blocked', false)
                    ->whereNotNull('onesignal_player_id')
                    ->count()
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ], 200);

        } catch (\Exception $e) {
            Log::error('Get User Stats Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get user stats'
            ], 500);
        }
    }
}

/*
|--------------------------------------------------------------------------
| 🎯 NEXT STEPS
|--------------------------------------------------------------------------
| 1. Add this controller to your Laravel project
| 2. Update routes in api.php (see below)
| 3. Add migration for database columns (see below)
| 4. Get your OneSignal REST API Key and App ID from:
|    https://app.onesignal.com/ → Settings → Keys & IDs
|--------------------------------------------------------------------------
*/
