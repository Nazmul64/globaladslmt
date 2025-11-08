<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class RegisterController extends BaseController
{
    /**
     * User Registration
     */
    public function register(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'mobile'                => 'required|string|max:100',
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
            'ref_code'              => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        // Handle referral
        $referredBy = null;
        if (!empty($request->ref_code)) {
            $referrer = User::where('ref_code', $request->ref_code)->first();
            $referredBy = $referrer ? $referrer->id : null;
        }

        // Generate unique ref code
        do {
            $newRefCode = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        } while (User::where('ref_code', $newRefCode)->exists());

        // Create user
        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'mobile'      => $request->mobile,
            'ref_code'    => $newRefCode,
            'referred_by' => $referredBy,
            'password'    => Hash::make($request->password),
        ]);

        // Generate token
        $token = $user->createToken('RestApi')->plainTextToken;

        $success = [
            'token' => $token,
            'name'  => $user->name,
        ];

        return $this->sendResponse($success, 'User Registered Successfully');
    }

    /**
     * User Login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->sendError('Invalid credentials', [], 401);
        }

        // Generate new token
        $token = $user->createToken('RestApi')->plainTextToken;

        $success = [
            'token' => $token,
            'name'  => $user->name,
        ];

        return $this->sendResponse($success, 'User Logged In Successfully');
    }

    /**
     * User Logout
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->sendError('User not found', [], 404);
        }

        // Revoke all tokens
        $user->tokens()->delete();

        return $this->sendResponse([], 'User logged out successfully');
    }
}
