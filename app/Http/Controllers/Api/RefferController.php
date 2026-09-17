<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefferController extends Controller
{
    /**
     * ✅ Total Direct Referrals API with Complete Information
     */
    public function totalreffer()
    {
        try {
            // Auth user
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            // Ensure user has ref_code
            if (empty($user->ref_code)) {
                do {
                    $newRefCode = (string) random_int(10000000, 99999999);
                } while (User::where('ref_code', $newRefCode)->exists());
                $user->ref_code = $newRefCode;
                $user->save();
            }

            // সব direct referred users নিয়ে আসবো
            $referrals = User::where('referred_by', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Active users count (যারা verified বা active status আছে)
            $activeUsers = $referrals->where('status', 'active')->count();

            // Total earnings calculation
            $totalEarnings = (float)($user->refer_income ?? 0);

            // Referral users data format করা
            $referralUsersData = $referrals->map(function ($referredUser) {
                return [
                    'id' => $referredUser->id,
                    'name' => $referredUser->name ?? 'Unknown User',
                    'email' => $referredUser->email ?? 'N/A',
                    'phone' => $referredUser->mobile ?? $referredUser->phone ?? 'N/A',
                    'status' => $referredUser->status ?? 'active',
                    'created_at' => $referredUser->created_at ? $referredUser->created_at->toISOString() : null,
                    'earning' => 0,
                    'profile_photo' => $referredUser->photo_url ?? null,
                    'is_verified' => $referredUser->email_verified_at ? true : false,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Referrals fetched successfully',
                'referral_code' => (string)$user->ref_code,
                'ref_code' => (string)$user->ref_code,
                'referral_link' => url('/register?ref=' . $user->ref_code),
                'total_referrals' => $referrals->count(),
                'active_users' => $activeUsers,
                'total_earnings' => $totalEarnings,
                'referral_users' => $referralUsersData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch referrals: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ✅ Get Referral Statistics (Optional - Extra API)
     */
    public function referralStats()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            // Ensure user has ref_code
            if (empty($user->ref_code)) {
                do {
                    $newRefCode = (string) random_int(10000000, 99999999);
                } while (User::where('ref_code', $newRefCode)->exists());
                $user->ref_code = $newRefCode;
                $user->save();
            }

            // Direct referrals
            $directReferrals = User::where('referred_by', $user->id)->count();

            // Active referrals (last 30 days activity)
            $activeReferrals = User::where('referred_by', $user->id)
                ->where('status', 'active')
                ->count();

            // This month's referrals
            $thisMonthReferrals = User::where('referred_by', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            // Total earnings
            $totalEarnings = (float)($user->refer_income ?? 0);

            return response()->json([
                'status' => true,
                'message' => 'Statistics fetched successfully',
                'data' => [
                    'direct_referrals' => $directReferrals,
                    'active_referrals' => $activeReferrals,
                    'this_month_referrals' => $thisMonthReferrals,
                    'total_earnings' => $totalEarnings,
                    'referral_code' => (string)$user->ref_code,
                    'ref_code' => (string)$user->ref_code,
                    'referral_link' => url('/register?ref=' . $user->ref_code),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch statistics: ' . $e->getMessage(),
            ], 500);
        }
    }
}
