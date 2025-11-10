<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deposite;
use App\Models\Package;
use App\Models\Packagebuy;
use App\Models\Reffercommissionsetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PackagesbuyuserController extends Controller
{
    /**
     * Buy Package
     *
     * Fixed: Now checks available balance properly before purchase
     */
    /**
     * Buy Package
     */
    public function packagebuy(Request $request, $package_id)
    {
        // Get authenticated user
        $user = Auth::user();

        // Debug log
        Log::info('Package Buy Request', [
            'user_id' => $user ? $user->id : null,
            'package_id' => $package_id,
            'token_present' => $request->hasHeader('Authorization')
        ]);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated. Please login again.'
            ], 401);
        }

        $package = Package::find($package_id);

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Package not found!'
            ], 404);
        }

        if (!$package->price || $package->price <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid package price!'
            ], 400);
        }

        // Calculate available balance
        $totalApprovedDeposit = Deposite::where('user_id', $user->id)
            ->where('status', 'approved')
            ->sum('amount') ?? 0;

        $totalUsed = Packagebuy::where('user_id', $user->id)
            ->where('status', 'approved')
            ->sum('amount') ?? 0;

        $availableBalance = $totalApprovedDeposit - $totalUsed;

        Log::info('Balance Check', [
            'user_id' => $user->id,
            'total_deposit' => $totalApprovedDeposit,
            'total_used' => $totalUsed,
            'available' => $availableBalance,
            'required' => $package->price
        ]);

        // Check if user has sufficient balance
        if ($availableBalance < $package->price) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance. Available: ৳' . number_format($availableBalance, 2) . ', Required: ৳' . number_format($package->price, 2)
            ], 400);
        }

        try {
            DB::transaction(function () use ($user, $package) {
                // 1. Create package buy record
                Packagebuy::create([
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'amount' => $package->price,
                    'daily_income' => $package->daily_income,
                    'daily_limit' => $package->daily_limit,
                    'status' => 'approved',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 2. Referral commission on first purchase
                $totalPurchases = Packagebuy::where('user_id', $user->id)->count();

                if ($totalPurchases === 1) {
                    $this->giveReferralCommission($user, $package->price);
                }
            });

            Log::info('Package Purchase Success', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => $package->price
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Package purchased successfully!',
                'data' => [
                    'package_name' => $package->package_name,
                    'amount_paid' => (float)$package->price,
                    'daily_income' => (float)$package->daily_income,
                    'daily_limit' => (int)$package->daily_limit,
                    'total_return' => (float)($package->daily_income * $package->daily_limit),
                ]
            ], 200);

        } catch (Throwable $e) {
            Log::error('Package Purchase Failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Give Referral Commission on First Purchase
     */
    private function giveReferralCommission($user, $packagePrice)
    {
        try {
            $referrer = $user->referrer;
            if (!$referrer) {
                Log::info('No referrer found for user', ['user_id' => $user->id]);
                return;
            }

            $commissionLevels = Reffercommissionsetup::orderBy('reffer_level', 'asc')->get();

            if ($commissionLevels->isEmpty()) {
                Log::warning('No commission levels configured');
                return;
            }

            foreach ($commissionLevels as $commission) {
                if (!$referrer) break;

                $commissionAmount = ($commission->commission_percentage / 100) * $packagePrice;

                $referrer->balance += $commissionAmount;
                $referrer->refer_income += $commissionAmount;
                $referrer->save();

                Log::info('Referral Commission Given', [
                    'referrer_id' => $referrer->id,
                    'level' => $commission->reffer_level,
                    'amount' => $commissionAmount
                ]);

                // Move to next level referrer
                $referrer = $referrer->referrer;
            }
        } catch (Throwable $e) {
            Log::error('Referral Commission Error', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
