<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agentkyc;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AgentVerifitController extends Controller
{
    /**
     * Check if an agent/user is verified (has approved KYC)
     *
     * @param int $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function userVerifyStatus($user_id)
    {
        try {
            // Log the request for debugging
            Log::info("Checking verification status for user_id: {$user_id}");

            // Validate user_id
            if (!is_numeric($user_id) || $user_id <= 0) {
                return response()->json([
                    'status' => false,
                    'verified' => false,
                    'message' => 'Invalid user ID'
                ], 400);
            }

            // Check if user exists
            $user = User::find($user_id);

            if (!$user) {
                Log::warning("User not found: {$user_id}");
                return response()->json([
                    'status' => false,
                    'verified' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Check if user has approved KYC
            $verified = (bool) $user->is_verified;

            // Log the result
            Log::info("User {$user_id} verification status: " . ($verified ? 'VERIFIED' : 'NOT VERIFIED'));

            return response()->json([
                'status' => true,
                'verified' => $verified,
                'is_verified' => $verified,
                'kyc_status' => $verified ? 'verified' : 'unverified',
                'message' => $verified ? 'User is verified' : 'User is not verified',
                'user_id' => (int) $user_id,
                'user_name' => $user->name ?? 'Unknown'
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error checking verification for user {$user_id}: " . $e->getMessage());

            return response()->json([
                'status' => false,
                'verified' => false,
                'message' => 'An error occurred while checking verification status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
