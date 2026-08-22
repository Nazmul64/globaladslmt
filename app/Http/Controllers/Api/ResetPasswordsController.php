<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ResetPasswordsController extends Controller
{
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ], [
            'token.required' => 'Reset token is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email.',
            'email.exists' => 'This email is not registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Passwords do not match.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check token in database
        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$tokenData) {
            Log::warning('Invalid reset attempt for email: ' . $request->email);

            return response()->json([
                'success' => false,
                'message' => 'Invalid token or email. Please request a new reset code.',
            ], 400);
        }

        // Check if token expired (60 minutes)
        $createdAt = Carbon::parse($tokenData->created_at);
        $minutesElapsed = Carbon::now()->diffInMinutes($createdAt);

        if ($minutesElapsed > 60) {
            // Delete expired token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            Log::info('Expired token deleted for: ' . $request->email);

            return response()->json([
                'success' => false,
                'message' => 'Token has expired. Please request a new one.',
            ], 400);
        }

        // Update user password
        try {
            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            // Delete used token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            Log::info('Password reset successful for: ' . $request->email);

            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully!',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password. Please try again.',
            ], 500);
        }
    }
}
