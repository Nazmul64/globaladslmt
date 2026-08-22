<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RegisterController extends BaseController
{
    /**
     * User Registration
     */
    public function register(Request $request)
    {
        $settings = \App\Models\Appsetting::first();
        if ($settings) {
            if ($settings->maintenance_mode === 'yes' || $settings->maintenance_mode === '1') {
                return $this->sendError('App is currently under maintenance. Please try again later.', [], 503);
            }
            if ($settings->registration_status === 'closed') {
                return $this->sendError('Registration is currently closed by admin.', [], 403);
            }
        }

        // Sanitize request data (remove empty ref_code string so exists validation is skipped)
        if (!$request->filled('ref_code') || trim((string)$request->ref_code) === '') {
            $request->request->remove('ref_code');
        }

        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'mobile'                => 'required|string|max:20',
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string',
            'ref_code'              => 'nullable|string|exists:users,ref_code',
            'device_id'             => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return response()->json([
                'success' => false,
                'status'  => false,
                'message' => $firstError ?: 'Validation Error',
                'errors'  => $validator->errors(),
                'data'    => $validator->errors(),
            ], 422);
        }

        // Referral handling
        $referredBy = null;
        if ($request->filled('ref_code')) {
            $referredBy = User::where('ref_code', $request->ref_code)->value('id');
        }

        // Generate unique referral code
        do {
            $newRefCode = (string) random_int(10000000, 99999999);
        } while (User::where('ref_code', $newRefCode)->exists());

        $deviceId = trim($request->input('device_id') ?? $request->header('device-id') ?? '');

        // Prepare user creation data
        $userData = [
            'name'        => trim($request->name),
            'email'       => strtolower(trim($request->email)),
            'mobile'      => trim($request->mobile),
            'role'        => 'user',
            'ref_code'    => $newRefCode,
            'referred_by' => $referredBy,
            'password'    => Hash::make($request->password),
        ];

        if (!empty($deviceId) && Schema::hasColumn('users', 'device_id')) {
            $userData['device_id'] = $deviceId;
        }

        // Create user
        $user = User::create($userData);

        // Create Sanctum token (no expiry)
        $token = $user->createToken('RestApi')->plainTextToken;

        return $this->sendResponse([
            'token' => $token,
            'user'  => $user,
        ], 'User registered successfully');
    }

    /**
     * User Login (Supports Email or Mobile Number)
     */
    public function login(Request $request)
    {
        $settings = \App\Models\Appsetting::first();
        if ($settings && ($settings->maintenance_mode === 'yes' || $settings->maintenance_mode === '1')) {
            return $this->sendError('App is currently under maintenance. Please try again later.', [], 503);
        }

        $loginInput = trim($request->input('email') ?? $request->input('mobile') ?? $request->input('username') ?? '');
        $password = $request->input('password');

        if (empty($loginInput) || empty($password)) {
            return $this->sendError(
                'Validation Error',
                ['email' => ['Email/Mobile and Password are required.']],
                422
            );
        }

        // Search user by email (case-insensitive) or mobile number
        $user = User::whereRaw('LOWER(email) = ?', [strtolower($loginInput)])
                    ->orWhere('mobile', $loginInput)
                    ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return $this->sendError(
                'Invalid email or password',
                [],
                401
            );
        }

        if ($user->is_blocked ?? false) {
            return $this->sendError(
                'Your account is blocked. Please contact admin.',
                [],
                403
            );
        }

        $deviceId = trim($request->input('device_id') ?? $request->header('device-id') ?? '');
        $hasDeviceIdColumn = Schema::hasColumn('users', 'device_id');

        // Same Device Login Enforcement Check
        if ($hasDeviceIdColumn && $settings && ($settings->same_device_login === 'yes' || $settings->same_device_login === '1')) {
            if (!empty($user->device_id) && !empty($deviceId) && $user->device_id !== $deviceId) {
                return $this->sendError(
                    'Login rejected: Same device login is required. You can only log in from your registered device.',
                    [],
                    403
                );
            }
        }

        if ($hasDeviceIdColumn && !empty($deviceId) && empty($user->device_id)) {
            $user->device_id = $deviceId;
            $user->save();
        }

        $token = $user->createToken('RestApi')->plainTextToken;

        return $this->sendResponse([
            'token' => $token,
            'user'  => $user,
        ], 'User logged in successfully');
    }

    /**
     * Logout User
     */
    public function logout(Request $request)
    {
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return $this->sendResponse([], 'User logged out successfully');
    }

    /**
     * Get Authenticated User Profile
     */
    public function profile(Request $request)
    {
        return $this->sendResponse(
            $request->user(),
            'User profile retrieved successfully'
        );
    }

    /**
     * Refresh Token
     */
    public function refreshToken(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }

        if ($user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        $token = $user->createToken('RestApi')->plainTextToken;

        return $this->sendResponse([
            'token' => $token,
            'user'  => $user,
        ], 'Token refreshed successfully');
    }
}
