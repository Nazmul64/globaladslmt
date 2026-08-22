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

            // সব direct referred users নিয়ে আসবো
            $referrals = User::where('referred_by', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Active users count (যারা verified বা active status আছে)
            $activeUsers = $referrals->where('status', 'active')->count();

            // Total earnings calculation
            // আপনার যদি earnings table থাকে তাহলে এখানে calculate করুন
            // উদাহরণ: $totalEarnings = $referrals->sum('referral_earning');
            $totalEarnings = 0; // Default 0, পরে update করবেন

            // Referral users data format করা
            $referralUsersData = $referrals->map(function ($referredUser) {
                return [
                    'id' => $referredUser->id,
                    'name' => $referredUser->name ?? 'Unknown User',
                    'email' => $referredUser->email ?? 'N/A',
                    'phone' => $referredUser->phone ?? 'N/A',
                    'status' => $referredUser->status ?? 'active',
                    'created_at' => $referredUser->created_at->toISOString(),
                    'earning' => 0, // পরে earning logic add করবেন

                    // Additional useful info
                    'profile_photo' => $referredUser->profile_photo_url ?? null,
                    'is_verified' => $referredUser->email_verified_at ? true : false,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Referrals fetched successfully',
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

            // Direct referrals
            $directReferrals = User::where('referred_by', $user->id)->count();

            // Active referrals (last 30 days activity)
            $activeReferrals = User::where('referred_by', $user->id)
                ->where('last_login', '>=', now()->subDays(30))
                ->count();

            // This month's referrals
            $thisMonthReferrals = User::where('referred_by', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            // Total earnings (আপনার earnings logic অনুযায়ী)
            $totalEarnings = 0;

            return response()->json([
                'status' => true,
                'message' => 'Statistics fetched successfully',
                'data' => [
                    'direct_referrals' => $directReferrals,
                    'active_referrals' => $activeReferrals,
                    'this_month_referrals' => $thisMonthReferrals,
                    'total_earnings' => $totalEarnings,
                    'referral_code' => $user->referral_code ?? null,
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
