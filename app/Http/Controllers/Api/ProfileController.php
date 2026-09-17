<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kyc;
use Exception;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Get authenticated user profile with KYC verification status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        try {
            // Get authenticated user
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. Please login again.'
                ], 401);
            }

            // Fetch KYC status
            $kyc = Kyc::where('user_id', $user->id)->first();
            $kyc_status = ($kyc && strtolower(trim($kyc->status)) === 'approved') ? 'verified' : 'unverified';

            // Profile photo full URL
            $profile_photo_url = null;
            if ($user->profile_photo) {
                $profile_photo_url = filter_var($user->profile_photo, FILTER_VALIDATE_URL)
                    ? $user->profile_photo
                    : url('uploads/profile/' . $user->profile_photo);
            }

            return response()->json([
                'status' => true,
                'message' => 'Profile fetched successfully',
                'data' => [
                    'id'                  => $user->id,
                    'name'                => $user->name ?? 'N/A',
                    'mobile'              => $user->mobile ?? 'N/A',
                    'email'               => $user->email ?? 'N/A',
                    'phone'               => $user->mobile ?? $user->phone ?? '',
                    'ref_code'            => $user->ref_code ?? '',
                    'referral_code'       => $user->ref_code ?? '',
                    'referral_link'       => url('/register?ref=' . ($user->ref_code ?? '')),
                    'is_blocked'          => (bool) ($user->is_blocked ?? false),
                    'is_withdraw_blocked' => (bool) ($user->is_blocked ?? false),
                    'kyc_status'          => $kyc_status,
                    'created_at'          => $user->created_at ? $user->created_at->toDateTimeString() : null,
                    'profile_photo'       => $profile_photo_url,
                    'total_coins'         => $user->balance ?? 0,
                    'tasks_done'          => $user->tasks_done ?? 0,
                ],
            ], 200);

        } catch (Exception $e) {
            Log::error('Profile API Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Server error occurred',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
