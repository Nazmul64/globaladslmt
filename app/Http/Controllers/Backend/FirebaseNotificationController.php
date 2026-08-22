<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\FirebaseApp;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;
use Exception;

class FirebaseNotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Display Firebase Apps Management Page
     */
    public function firebaseIndex()
    {
        try {
            $apps = FirebaseApp::withCount(['users' => function($query) {
                $query->whereNotNull('fcm_token');
            }])->latest()->get();

            return view('admin.firebase.index', compact('apps'));
        } catch (Exception $e) {
            Log::error('Firebase Index Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load Firebase apps: ' . $e->getMessage());
        }
    }

    /**
     * Add New Firebase App
     */
    public function addApp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'app_name' => 'required|string|max:255',
            'package_name' => 'required|string|unique:firebase_apps,package_name|max:255',
            'firebase_json' => 'required|file|mimes:json|max:2048',
            'server_key' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed. Please check your inputs.');
        }

        try {
            $jsonContent = file_get_contents($request->file('firebase_json')->getRealPath());
            $credentials = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->with('error', 'Invalid JSON file format');
            }

            if (!isset($credentials['project_id']) || !isset($credentials['type'])) {
                return back()->with('error', 'Invalid Firebase credentials file. Missing required fields.');
            }

            FirebaseApp::create([
                'app_name' => $request->app_name,
                'package_name' => $request->package_name,
                'firebase_credentials' => $credentials,
                'server_key' => $request->server_key,
                'is_active' => true
            ]);

            return back()->with('success', 'Firebase App successfully added!');
        } catch (Exception $e) {
            Log::error('Add Firebase App Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to add Firebase App: ' . $e->getMessage());
        }
    }

    /**
     * Edit Firebase App
     */
    public function editApp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'app_id' => 'required|exists:firebase_apps,id',
            'app_name' => 'required|string|max:255',
            'package_name' => 'required|string|max:255',
            'firebase_json' => 'nullable|file|mimes:json|max:2048',
            'server_key' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed. Please check your inputs.');
        }

        try {
            $app = FirebaseApp::findOrFail($request->app_id);

            $existingApp = FirebaseApp::where('package_name', $request->package_name)
                ->where('id', '!=', $request->app_id)
                ->first();

            if ($existingApp) {
                return back()->with('error', 'Package name already exists for another app');
            }

            $data = [
                'app_name' => $request->app_name,
                'package_name' => $request->package_name,
                'server_key' => $request->server_key,
            ];

            if ($request->hasFile('firebase_json')) {
                $jsonContent = file_get_contents($request->file('firebase_json')->getRealPath());
                $credentials = json_decode($jsonContent, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()->with('error', 'Invalid JSON file format');
                }

                if (!isset($credentials['project_id'])) {
                    return back()->with('error', 'Invalid Firebase credentials file');
                }

                $data['firebase_credentials'] = $credentials;
            }

            $app->update($data);

            return back()->with('success', 'Firebase App successfully updated!');
        } catch (Exception $e) {
            Log::error('Edit Firebase App Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to update Firebase App: ' . $e->getMessage());
        }
    }

    /**
     * Toggle App Active Status
     */
    public function toggleApp($id)
    {
        try {
            $app = FirebaseApp::findOrFail($id);
            $app->update(['is_active' => !$app->is_active]);

            $status = $app->is_active ? 'activated' : 'deactivated';
            return back()->with('success', "App has been {$status} successfully!");
        } catch (Exception $e) {
            Log::error('Toggle App Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to toggle app status');
        }
    }

    /**
     * Delete Firebase App
     */
    public function deleteApp($id)
    {
        try {
            $app = FirebaseApp::findOrFail($id);

            // Clear FCM data from users
            User::where('firebase_app_id', $id)->update([
                'firebase_app_id' => null,
                'fcm_token' => null,
                'device_type' => null,
                'fcm_updated_at' => null,
            ]);

            $app->notifications()->delete();
            $app->delete();

            return back()->with('success', 'Firebase App and all related data deleted successfully!');
        } catch (Exception $e) {
            Log::error('Delete Firebase App Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete Firebase App');
        }
    }

    /**
     * Send Notification Page
     */
    public function index()
    {
        try {
            DB::connection()->getPdo();

            $apps = Schema::hasTable('firebase_apps') ? FirebaseApp::where('is_active', true)->get() : collect();

            $totalUsers = User::count();
            $onesignalUsers = User::whereNotNull('onesignal_player_id')->count();
            $fcmUsers = User::whereNotNull('fcm_token')->count();

            $sentNotifications = Schema::hasTable('notifications')
                ? Notification::with('firebaseApp')->latest('sent_at')->take(10)->get()
                : collect();

            $oneSignalAppId = config('services.onesignal.app_id', '19355887-8178-4d10-a8a3-3a6cc499968c');
            $oneSignalApiKey = config('services.onesignal.rest_api_key', '');

            return view('admin.notification.send', compact('apps', 'totalUsers', 'onesignalUsers', 'fcmUsers', 'sentNotifications', 'oneSignalAppId', 'oneSignalApiKey'));

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database Query Error: ' . $e->getMessage());
            return back()->with('error', 'Database error: ' . $e->getMessage());

        } catch (Exception $e) {
            Log::error('Notification Index Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load notification page: ' . $e->getMessage());
        }
    }

    /**
     * Send Notification to Users
     */
    public function sendNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_platform' => 'nullable|in:onesignal,firebase,both',
            'firebase_app_id' => 'nullable|exists:firebase_apps,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'image_url' => 'nullable|url|max:500',
            'action_url' => 'nullable|string|max:500',
            'send_to' => 'required|in:all,specific',
            'user_ids' => 'required_if:send_to,specific|array',
            'user_ids.*' => 'exists:users,id',
            'onesignal_app_id' => 'nullable|string',
            'onesignal_api_key' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please check your inputs and try again');
        }

        try {
            $platform = $request->input('notification_platform', 'onesignal');
            $title = $request->title;
            $body = $request->message;
            $imageUrl = $request->image_url;
            $actionUrl = $request->action_url ?? '/notifications';
            $sendTo = $request->send_to;
            $userIds = $request->user_ids;

            $totalSent = 0;
            $totalFailed = 0;
            $statusMessages = [];

            // 1. SEND VIA ONESIGNAL
            if (in_array($platform, ['onesignal', 'both'])) {
                $appId = $request->filled('onesignal_app_id')
                    ? $request->onesignal_app_id
                    : config('services.onesignal.app_id', '19355887-8178-4d10-a8a3-3a6cc499968c');

                $apiKey = $request->filled('onesignal_api_key')
                    ? $request->onesignal_api_key
                    : config('services.onesignal.rest_api_key', '');

                $payload = [
                    'app_id' => $appId,
                    'headings' => ['en' => $title],
                    'contents' => ['en' => $body],
                    'data' => [
                        'action_url' => $actionUrl,
                        'type' => 'admin_notification',
                    ],
                ];

                if (!empty($imageUrl)) {
                    $payload['big_picture'] = $imageUrl;
                    $payload['large_icon'] = $imageUrl;
                    $payload['ios_attachments'] = ['id' => $imageUrl];
                }

                if ($sendTo === 'all') {
                    $payload['included_segments'] = ['Subscribed Users', 'Total Subscriptions'];
                } else {
                    $playerIds = User::whereIn('id', $userIds)
                        ->whereNotNull('onesignal_player_id')
                        ->pluck('onesignal_player_id')
                        ->toArray();

                    if (!empty($playerIds)) {
                        $payload['include_player_ids'] = array_values(array_unique($playerIds));
                    } else {
                        $emails = User::whereIn('id', $userIds)->pluck('email')->toArray();
                        $payload['include_aliases'] = ['external_id' => $emails];
                    }
                }

                $headers = ['Content-Type' => 'application/json'];
                if (!empty($apiKey)) {
                    $headers['Authorization'] = 'Basic ' . $apiKey;
                }

                $osResponse = Http::withHeaders($headers)
                    ->post('https://onesignal.com/api/v1/notifications', $payload);

                $osResult = $osResponse->json();
                Log::info('OneSignal Push Broadcast', ['payload' => $payload, 'response' => $osResult]);

                if ($osResponse->successful() && isset($osResult['id'])) {
                    $recipients = $osResult['recipients'] ?? ($sendTo === 'all' ? User::count() : count($userIds));
                    $totalSent += max($recipients, 1);
                    $statusMessages[] = "OneSignal: Notification successfully sent (ID: {$osResult['id']})";
                } elseif (isset($osResult['errors'])) {
                    $errStr = is_array($osResult['errors']) ? json_encode($osResult['errors']) : $osResult['errors'];
                    $totalFailed++;
                    $statusMessages[] = "OneSignal Warning: {$errStr}";
                } else {
                    $totalSent++;
                    $statusMessages[] = "OneSignal: Sent";
                }
            }

            // 2. SEND VIA FIREBASE (if selected and configured)
            if (in_array($platform, ['firebase', 'both']) && $request->filled('firebase_app_id')) {
                $app = FirebaseApp::find($request->firebase_app_id);
                if ($app && $app->is_active) {
                    $additionalData = [];
                    if ($imageUrl) $additionalData['image_url'] = $imageUrl;
                    if ($actionUrl) $additionalData['action_url'] = $actionUrl;

                    if ($sendTo === 'all') {
                        $fbResult = $this->firebaseService->sendToAll($app->id, $title, $body, $additionalData);
                    } else {
                        $fbResult = $this->firebaseService->sendToSpecificUsers($app->id, $userIds, $title, $body, $additionalData);
                    }

                    $totalSent += ($fbResult['total_sent'] ?? 0);
                    $totalFailed += ($fbResult['total_failed'] ?? 0);
                    $statusMessages[] = "Firebase: " . ($fbResult['message'] ?? 'Sent');
                }
            }

            // 3. RECORD IN DATABASE
            Notification::create([
                'firebase_app_id' => $request->firebase_app_id,
                'title' => $title,
                'message' => $body,
                'image_url' => $imageUrl,
                'action_url' => $actionUrl,
                'notification_type' => $platform,
                'send_to' => $sendTo,
                'user_ids' => $sendTo === 'specific' ? $userIds : null,
                'total_sent' => max($totalSent, 1),
                'total_failed' => $totalFailed,
                'sent_at' => now(),
            ]);

            $messageSummary = implode(' | ', $statusMessages);
            return back()->with('success', !empty($messageSummary) ? $messageSummary : 'Notification sent successfully!');

        } catch (Exception $e) {
            Log::error('Send Notification Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to send notification: ' . $e->getMessage());
        }
    }

    /**
     * Notification History Page
     */
    public function history(Request $request)
    {
        try {
            $query = Notification::with('firebaseApp')->latest('sent_at');

            if ($request->filled('firebase_app_id')) {
                $query->where('firebase_app_id', $request->firebase_app_id);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('sent_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('sent_at', '<=', $request->date_to);
            }

            $notifications = $query->paginate(20);
            $apps = FirebaseApp::all();

            return view('admin.notification.history', compact('notifications', 'apps'));
        } catch (Exception $e) {
            Log::error('Notification History Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load notification history');
        }
    }

    /**
     * App Users List Page
     */
    public function usersIndex(Request $request)
    {
        try {
            $query = User::with('firebaseApp')->whereNotNull('fcm_token');

            if ($request->filled('firebase_app_id')) {
                $query->where('firebase_app_id', $request->firebase_app_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            if ($request->filled('device_type')) {
                $query->where('device_type', $request->device_type);
            }

            $users = $query->latest('fcm_updated_at')->paginate(50);
            $apps = FirebaseApp::all();
            $totalUsers = User::whereNotNull('fcm_token')->count();
            $activeTokens = User::whereNotNull('fcm_token')->count();

            return view('admin.notification.users', compact('users', 'apps', 'totalUsers', 'activeTokens'));
        } catch (Exception $e) {
            Log::error('Users Index Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load users');
        }
    }

    /**
     * Get Users by App (AJAX)
     */
    public function getUsersByAppAjax($appId)
    {
        try {
            $users = User::where('firebase_app_id', $appId)
                ->whereNotNull('fcm_token')
                ->select('id', 'name', 'email', 'device_type')
                ->get();

            return response()->json([
                'success' => true,
                'users' => $users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name ?? 'Unknown User',
                        'email' => $user->email ?? 'No Email',
                        'device_type' => $user->device_type ?? 'Unknown',
                    ];
                })
            ]);
        } catch (Exception $e) {
            Log::error('Get Users By App Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load users'
            ], 500);
        }
    }

    /**
     * API: Update FCM Token (for mobile apps)
     */
    public function updateFcmToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'fcm_token' => 'required|string',
            'device_type' => 'nullable|in:android,ios,web',
            'firebase_app_id' => 'required|exists:firebase_apps,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $user->update([
                'firebase_app_id' => $request->firebase_app_id,
                'fcm_token' => $request->fcm_token,
                'device_type' => $request->device_type ?? $user->device_type ?? 'android',
                'fcm_updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token updated successfully',
                'data' => [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Update FCM Token Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Update failed'
            ], 500);
        }
    }

    /**
     * Clear User FCM Token
     */
    public function clearFcmToken($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update([
                'fcm_token' => null,
                'fcm_updated_at' => null,
            ]);

            return back()->with('success', 'FCM token cleared successfully!');
        } catch (Exception $e) {
            Log::error('Clear FCM Token Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to clear FCM token');
        }
    }

    /**
     * Get User Statistics
     */
    public function userStats()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'users_with_fcm' => User::whereNotNull('fcm_token')->count(),
                'android_users' => User::where('device_type', 'android')->whereNotNull('fcm_token')->count(),
                'ios_users' => User::where('device_type', 'ios')->whereNotNull('fcm_token')->count(),
                'active_users' => User::whereNotNull('fcm_token')->count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            Log::error('User Stats Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics'
            ], 500);
        }
    }
}
