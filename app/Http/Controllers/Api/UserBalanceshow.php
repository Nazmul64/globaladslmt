<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserBalanceshow extends Controller
{
    /**
     * ✅ FIXED: Method name matches route
     * Returns user balance and profile info
     */
    public function userbalanceshows(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                    'data' => []
                ], 401);
            }

            Log::info('User balance fetched', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'balance' => $user->balance ?? 0
            ]);

            $isVerified = (bool) $user->is_verified;

            // ✅ Return complete user data
            return response()->json([
                'success' => true,
                'status' => true,
                'message' => 'User data retrieved successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'mobile' => $user->mobile,
                        'is_verified' => $isVerified,
                        'kyc_approved' => $isVerified,
                        'kyc_status' => $isVerified ? 'verified' : 'unverified',
                        'verification_status' => $isVerified ? 'verified' : 'unverified',
                    ],
                    'balance' => (float) ($user->balance ?? 0),
                    'user_balance' => (float) ($user->balance ?? 0),
                    'ref_code' => $user->ref_code ?? '---',
                    'referral_code' => $user->ref_code ?? '---',
                    'is_verified' => $isVerified,
                    'kyc_approved' => $isVerified,
                    'kyc_status' => $isVerified ? 'verified' : 'unverified',
                    'verification_status' => $isVerified ? 'verified' : 'unverified',
                    'profile_photo' => $this->resolveProfilePhoto($user->photo ?? $user->new_photo ?? $user->profile_photo),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('User balance error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving user data',
                'data' => []
            ], 500);
        }
    }

    /**
     * ✅ Resolve profile photo URL
     */
    private function resolveProfilePhoto(?string $photo): string
    {
        // Default avatar
        if (!$photo || $photo === '' || $photo === 'default.png') {
            return asset('uploads/avator.jpg');
        }

        // Already full URL
        if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
            return $photo;
        }

        // Clean filename - remove any existing path
        $photo = basename($photo);

        // Return full asset URL
        return asset('uploads/profile/' . $photo);
    }
}
