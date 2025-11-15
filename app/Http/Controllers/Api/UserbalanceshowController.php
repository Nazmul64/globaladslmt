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

    // Check KYC approval status
    $kyc_approved = Kyc::where('user_id', $user->id)
                        ->where('status', 'approved')
                        ->exists();

    // Fetch balance and referral code from same user object
    $user_balance = $user->balance ?? 0;
    $ref_code     = $user->ref_code ?? '';

    // Profile photo URL build
    if (!empty($user->photo) && file_exists(public_path('uploads/profile/' . $user->photo))) {
        $profile_photo_url = asset('uploads/profile/' . $user->photo);
    } else {
        $profile_photo_url = '';
    }

    $data = [
        'user'            => $user,
        'balance'         => $user_balance,
        'kyc'             => $kyc,
        'kyc_approved'    => $kyc_approved,
        'ref_code'        => $ref_code,
        'profile_photo'   => $profile_photo_url,
    ];

    return $this->sendResponse($data, 'User data fetched successfully.');
}

}
