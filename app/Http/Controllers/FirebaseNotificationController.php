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

            if (!Schema::hasTable('firebase_apps')) {
                Log::error('firebase_apps table does not exist');
                return redirect()->route('firebase.index')
                    ->with('error', 'Database tables are missing. Please run: php artisan migrate');
            }

            if (!Schema::hasTable('notifications')) {
                Log::error('notifications table does not exist');
                return redirect()->route('firebase.index')
                    ->with('error', 'notifications table is missing. Please run migrations.');
            }

            $apps = FirebaseApp::where('is_active', true)->get();

            if ($apps->isEmpty()) {
                return redirect()->route('firebase.index')
                    ->with('warning', 'Please add and activate at least one Firebase App first');
            }

            $totalUsers = User::whereNotNull('fcm_token')->count();

            $sentNotifications = Notification::with('firebaseApp')
                ->latest('sent_at')
                ->take(10)
                ->get();

            if (!view()->exists('admin.notification.send')) {
                Log::error('View admin.notification.send not found');
                return back()->with('error', 'Notification page view file is missing');
            }

            return view('admin.notification.send', compact('apps', 'totalUsers', 'sentNotifications'));

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
            'firebase_app_id' => 'required|exists:firebase_apps,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'image_url' => 'nullable|url|max:500',
            'action_url' => 'nullable|string|max:500',
            'send_to' => 'required|in:all,specific',
            'user_ids' => 'required_if:send_to,specific|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please check your inputs and try again');
        }

        try {
            $app = FirebaseApp::findOrFail($request->firebase_app_id);
            if (!$app->is_active) {
                return back()->with('error', 'Selected Firebase App is not active');
            }

            $firebaseAppId = $request->firebase_app_id;
            $title = $request->title;
            $body = $request->message;

            $additionalData = [];
            if ($request->filled('image_url')) {
                $additionalData['image_url'] = $request->image_url;
            }
            if ($request->filled('action_url')) {
                $additionalData['action_url'] = $request->action_url;
            }

            // Send notification
            if ($request->send_to === 'all') {
                $result = $this->firebaseService->sendToAll(
                    $firebaseAppId,
                    $title,
                    $body,
                    $additionalData
                );
            } else {
                $result = $this->firebaseService->sendToSpecificUsers(
                    $firebaseAppId,
                    $request->user_ids,
                    $title,
                    $body,
                    $additionalData
                );
            }

            // Save notification record
            Notification::create([
                'firebase_app_id' => $firebaseAppId,
                'title' => $title,
                'message' => $body,
                'image_url' => $request->image_url,
                'action_url' => $request->action_url,
                'send_to' => $request->send_to,
                'user_ids' => $request->send_to === 'specific' ? $request->user_ids : null,
                'total_sent' => $result['total_sent'] ?? 0,
                'total_failed' => $result['total_failed'] ?? 0,
            ]);

            if ($result['success']) {
                return back()->with('success', $result['message']);
            }

            return back()->with('warning', $result['message']);
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
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('mobile', 'LIKE', "%{$search}%");
                });
            }

            if ($request->filled('device_type')) {
                $query->where('device_type', $request->device_type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $users = $query->latest('fcm_updated_at')->paginate(50);
            $apps = FirebaseApp::all();
            $totalUsers = User::whereNotNull('fcm_token')->count();
            $activeTokens = User::whereNotNull('fcm_token')
                ->where('is_blocked', false)
                ->count();

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
                ->where('is_blocked', false)
                ->select('id', 'name', 'email', 'device_type', 'status', 'role')
                ->get();

            return response()->json([
                'success' => true,
                'users' => $users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name ?? 'Unknown User',
                        'email' => $user->email ?? 'No Email',
                        'device_type' => $user->device_type ?? 'Unknown',
                        'status' => $user->status,
                        'role' => $user->role,
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
                'active_users' => User::where('is_blocked', false)->whereNotNull('fcm_token')->count(),
                'blocked_users' => User::where('is_blocked', true)->count(),
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
