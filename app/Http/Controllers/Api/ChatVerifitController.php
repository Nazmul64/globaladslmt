<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kyc;
use App\Models\User;
use Illuminate\Http\Request;

class ChatVerifitController extends Controller
{
    /**
     * Get user verification status
     *
     * @param int $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function userVerifyStatus($user_id)
    {
        try {
            // Check if user exists
            $user = User::find($user_id);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'verified' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Check if user has approved KYC
            $verified = (bool) $user->is_verified;

            return response()->json([
                'status' => true,
                'verified' => $verified,
                'is_verified' => $verified,
                'kyc_status' => $verified ? 'verified' : 'unverified',
                'message' => $verified ? 'User is verified' : 'User is not verified'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'verified' => false,
                'message' => 'An error occurred while checking verification status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
