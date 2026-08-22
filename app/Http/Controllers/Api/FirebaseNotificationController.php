<?php

// ====================================================================
// 🔥 FIREBASE PUSH NOTIFICATION - PART 2/5
// 📁 File: app/Http/Controllers/Api/FirebaseNotificationController.php
// ✅ API Controller for Firebase Notifications
// ====================================================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Notification;
use App\Models\FirebaseApp;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ✅ METHOD 1: UPDATE FCM TOKEN
    |--------------------------------------------------------------------------
    | Called from Flutter app when:
    | - User logs in
    | - App starts for the first time
    | - FCM token refreshes
    |--------------------------------------------------------------------------
    */

    public function updateFcmToken(Request $request)
    {
        try {
            // ✅ Validate incoming request
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'fcm_token' => 'required|string',
                'device_type' => 'nullable|in:android,ios,web',
                'firebase_app_id' => 'required|exists:firebase_apps,id'
            ]);

            if ($validator->fails()) {
                Log::warning('FCM Token Update - Validation Failed', [
                    'errors' => $validator->errors(),
                    'request' => $request->all()
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
                Log::error('FCM Token Update - User not found', [
                    'email' => $request->email
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // ✅ Check if Firebase app is active
            $firebaseApp = FirebaseApp::find($request->firebase_app_id);

            if (!$firebaseApp || !$firebaseApp->is_active) {
                Log::error('FCM Token Update - Firebase app not active', [
                    'firebase_app_id' => $request->firebase_app_id
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Firebase app is not active'
                ], 400);
            }

            // ✅ Update user's FCM token
            $user->update([
                'firebase_app_id' => $request->firebase_app_id,
                'fcm_token' => $request->fcm_token,
                'device_type' => $request->device_type ?? 'android',
                'fcm_updated_at' => now(),
            ]);

            Log::info('FCM Token Updated Successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'device_type' => $user->device_type,
                'firebase_app_id' => $request->firebase_app_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token updated successfully',
                'data' => [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'device_type' => $user->device_type,
                    'fcm_updated_at' => $user->fcm_updated_at->toDateTimeString()
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('FCM Token Update - Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update FCM token: ' . $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ✅ METHOD 2: GET NOTIFICATIONS
    |--------------------------------------------------------------------------
    | Fetch notification history for the app
    | Called from Flutter app to show notification list
    |--------------------------------------------------------------------------
    */

    public function getNotifications(Request $request)
    {
        try {
            // ✅ Validate request
            $validator = Validator::make($request->all(), [
                'firebase_app_id' => 'required|exists:firebase_apps,id',
                'limit' => 'nullable|integer|min:1|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $limit = $request->input('limit', 50);

            // ✅ Fetch notifications
            $notifications = Notification::where('firebase_app_id', $request->firebase_app_id)
                ->latest('sent_at')
                ->limit($limit)
                ->get(['id', 'title', 'message', 'image_url', 'action_url', 'sent_at']);

            Log::info('Notifications Fetched', [
                'firebase_app_id' => $request->firebase_app_id,
                'count' => $notifications->count()
            ]);

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'count' => $notifications->count()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Get Notifications - Exception', [
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
    | ✅ METHOD 3: TEST NOTIFICATION
    |--------------------------------------------------------------------------
    | Send a test notification to verify setup
    | Optional - for testing purposes
    |--------------------------------------------------------------------------
    */

    public function testNotification(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'title' => 'required|string|max:255',
                'message' => 'required|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !$user->fcm_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or FCM token not available'
                ], 404);
            }

            // ✅ Send notification using FirebaseNotificationService
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);

            $result = $firebaseService->sendToSpecificUsers(
                $user->firebase_app_id,
                [$user->id],
                $request->title,
                $request->message,
                []
            );

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => [
                    'total_sent' => $result['total_sent'] ?? 0,
                    'total_failed' => $result['total_failed'] ?? 0
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Test Notification - Exception', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ✅ METHOD 4: GET USER STATS
    |--------------------------------------------------------------------------
    | Get statistics about users with FCM tokens
    | Optional - for admin dashboard
    |--------------------------------------------------------------------------
    */

    public function getUserStats()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'users_with_fcm' => User::whereNotNull('fcm_token')->count(),
                'android_users' => User::where('device_type', 'android')
                    ->whereNotNull('fcm_token')
                    ->count(),
                'ios_users' => User::where('device_type', 'ios')
                    ->whereNotNull('fcm_token')
                    ->count(),
                'active_users' => User::where('is_blocked', false)
                    ->whereNotNull('fcm_token')
                    ->count()
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ], 200);

        } catch (\Exception $e) {
            Log::error('Get User Stats - Exception', [
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
| 🎯 NEXT STEP
|--------------------------------------------------------------------------
| Go to PART 3 and check Database Migration
| Make sure 'users' table has required columns
|--------------------------------------------------------------------------
*/
