<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Kyc;

class UserbalanceshowController extends BaseController
{
    public function userbalanceshow()
    {
        $user = Auth::user();

        if (!$user) {
            return $this->sendError('Unauthorized User', [], 401);
        }

        // Fetch user KYC record
        $kyc = Kyc::where('user_id', $user->id)->first();

        $is_verified = (bool) $user->is_verified;

        // Fetch balance and referral code from users table
        $user_balance = (float) ($user->balance ?? 0);
        $ref_code     = $user->ref_code ?? '';

        // ✅ Profile photo URL - Direct from asset() helper
        $photo = $user->photo ?? $user->new_photo ?? $user->profile_photo ?? null;
        if (!empty($photo) && file_exists(public_path('uploads/profile/' . $photo))) {
            $profile_photo_url = asset('uploads/profile/' . $photo);
        } elseif (!empty($photo) && (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://'))) {
            $profile_photo_url = $photo;
        } else {
            $profile_photo_url = asset('uploads/avator.jpg');
        }

        $data = [
            'user'                => $user,
            'balance'             => $user_balance,
            'user_balance'        => $user_balance,
            'kyc'                 => $kyc,
            'kyc_approved'        => $is_verified,
            'is_verified'         => $is_verified,
            'kyc_status'          => $is_verified ? 'verified' : 'unverified',
            'verification_status' => $is_verified ? 'verified' : 'unverified',
            'ref_code'            => $ref_code,
            'referral_code'       => $ref_code,
            'profile_photo'       => $profile_photo_url,
        ];

        return $this->sendResponse($data, 'User data fetched successfully.');
    }
}
