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
     * Get User's Current Active Package
     */
    public function getCurrentPackage(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $package = Packagebuy::with('package')
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->latest()
                ->first();

            if (!$package || !$package->package) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active package found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $package->id,
                    'user_id' => $package->user_id,
                    'package_id' => $package->package_id,
                    'package_name' => $package->package->package_name,
                    'amount' => (float)$package->amount,
                    'daily_income' => (float)$package->daily_income,
                    'daily_limit' => (int)$package->daily_limit,
                    'status' => $package->status,
                    'created_at' => $package->created_at?->toIso8601String(),
                    'updated_at' => $package->updated_at?->toIso8601String(),
                ]
            ]);

        } catch (Throwable $e) {
            Log::error('Get Current Package Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch current package'
            ], 500);
        }
    }

    /**
     * Get User's Balance Information
     */
    public function getUserBalance(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $totalDeposit = Deposite::where('user_id', $user->id)
                ->where('status', 'approved')
                ->sum('amount') ?? 0;

            $totalUsed = Packagebuy::where('user_id', $user->id)
                ->where('status', 'approved')
                ->sum('amount') ?? 0;

            $available = $totalDeposit - $totalUsed;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_deposit' => (float)$totalDeposit,
                    'total_used' => (float)$totalUsed,
                    'available' => (float)$available
                ]
            ]);

        } catch (Throwable $e) {
            Log::error('Get User Balance Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch balance'
            ], 500);
        }
    }

    /**
     * Buy or Update Package
     *
     * ✅ FIXED ISSUES:
     * - Proper balance calculation for updates (previous amount + available)
     * - Commission only on FIRST purchase, NOT on updates
     * - Better transaction handling
     * - Comprehensive logging
     */
    public function packagebuy(Request $request, $package_id)
    {
        $user = Auth::user();

        Log::info('🛒 Package Buy/Update Request Started', [
            'user_id' => $user?->id,
            'package_id' => $package_id,
            'token_present' => $request->hasHeader('Authorization')
        ]);

        // ================================
        // 1. Authentication Check
        // ================================
        if (!$user) {
            Log::warning('❌ Unauthenticated request');
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated. Please login again.'
            ], 401);
        }

        // ================================
        // 2. Package Validation
        // ================================
        $package = Package::find($package_id);

        if (!$package) {
            Log::warning('❌ Package not found', ['package_id' => $package_id]);
            return response()->json([
                'success' => false,
                'message' => 'Package not found!'
            ], 404);
        }

        if (!$package->price || $package->price <= 0) {
            Log::warning('❌ Invalid package price', [
                'package_id' => $package_id,
                'price' => $package->price
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid package price!'
            ], 400);
        }

        // ================================
        // 3. Check Existing Package
        // ================================
        $existingPurchase = Packagebuy::where('user_id', $user->id)
            ->where('status', 'approved')
            ->first();

        $isUpdate = $existingPurchase !== null;
        $isFirstPurchase = !$isUpdate;

        Log::info('📦 Package Status', [
            'is_update' => $isUpdate,
            'is_first_purchase' => $isFirstPurchase,
            'existing_package_id' => $existingPurchase?->package_id,
            'existing_amount' => $existingPurchase?->amount
        ]);

        // Don't allow "updating" to the same package
        if ($isUpdate && $existingPurchase->package_id == $package_id) {
            Log::info('⚠️ User already has this package');
            return response()->json([
                'success' => false,
                'message' => 'You already have this package!'
            ], 400);
        }

        // ================================
        // 4. Balance Calculation
        // ================================
        $totalApprovedDeposit = Deposite::where('user_id', $user->id)
            ->where('status', 'approved')
            ->sum('amount') ?? 0;

        $totalUsed = Packagebuy::where('user_id', $user->id)
            ->where('status', 'approved')
            ->sum('amount') ?? 0;

        // ✅ CRITICAL FIX: For updates, we need to add back the previous package amount
        // because it will be replaced, not added
        $availableBalance = $totalApprovedDeposit - $totalUsed;

        if ($isUpdate) {
            // Add back the previous package amount since we're replacing it
            $availableBalance += $existingPurchase->amount;
        }

        $requiredAmount = $package->price;

        Log::info('💰 Balance Calculation', [
            'user_id' => $user->id,
            'total_deposit' => $totalApprovedDeposit,
            'total_used' => $totalUsed,
            'previous_package_amount' => $existingPurchase?->amount ?? 0,
            'available_after_adjustment' => $availableBalance,
            'required' => $requiredAmount,
            'sufficient' => $availableBalance >= $requiredAmount
        ]);

        // ================================
        // 5. Balance Check
        // ================================
        if ($availableBalance < $requiredAmount) {
            Log::warning('❌ Insufficient balance', [
                'available' => $availableBalance,
                'required' => $requiredAmount,
                'shortfall' => $requiredAmount - $availableBalance
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance. Available: ৳' . number_format($availableBalance, 2) .
                           ', Required: ৳' . number_format($requiredAmount, 2) .
                           ', Shortfall: ৳' . number_format($requiredAmount - $availableBalance, 2)
            ], 400);
        }

        // ================================
        // 6. Process Transaction
        // ================================
        try {
            DB::beginTransaction();

            if ($isUpdate) {
                // UPDATE existing package
                Log::info('🔄 Updating existing package', [
                    'old_package_id' => $existingPurchase->package_id,
                    'new_package_id' => $package->id,
                    'old_amount' => $existingPurchase->amount,
                    'new_amount' => $package->price
                ]);

                $existingPurchase->update([
                    'package_id'   => $package->id,
                    'amount'       => $package->price,
                    'daily_income' => $package->daily_income,
                    'daily_limit'  => $package->daily_limit,
                    'status'       => 'approved',
                    'updated_at'   => now(),
                ]);

                $actionType = 'updated';

            } else {
                // CREATE new package purchase
                Log::info('✨ Creating new package purchase');

                Packagebuy::create([
                    'user_id'      => $user->id,
                    'package_id'   => $package->id,
                    'amount'       => $package->price,
                    'daily_income' => $package->daily_income,
                    'daily_limit'  => $package->daily_limit,
                    'status'       => 'approved',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                $actionType = 'purchased';
            }

            // ✅ CRITICAL FIX: Give referral commission ONLY on FIRST purchase
            if ($isFirstPurchase) {
                Log::info('🎁 Processing referral commission (First Purchase)');
                $this->giveReferralCommission($user, $package->price);
            } else {
                Log::info('ℹ️ Skipping referral commission (Update/Upgrade)');
            }

            DB::commit();

            Log::info('✅ Package Transaction Success', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => $package->price,
                'action' => $actionType,
                'commission_given' => $isFirstPurchase
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Package ' . $actionType . ' successfully!',
                'data' => [
                    'action' => $actionType,
                    'package_name' => $package->package_name,
                    'amount_paid' => (float)$package->price,
                    'daily_income' => (float)$package->daily_income,
                    'daily_limit' => (int)$package->daily_limit,
                    'total_return' => (float)($package->daily_income * $package->daily_limit),
                    'profit' => (float)(($package->daily_income * $package->daily_limit) - $package->price),
                    'is_first_purchase' => $isFirstPurchase,
                    'commission_given' => $isFirstPurchase,
                ]
            ], 200);

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('❌ Package Transaction Failed', [
                'user_id' => $user->id,
                'package_id' => $package_id,
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
     * Give Referral Commission on First Purchase Only
     *
     * ✅ This should ONLY be called for first-time purchases
     * ✅ NOT for package updates/upgrades
     */
    private function giveReferralCommission($user, $packagePrice)
    {
        try {
            $referrer = $user->referrer;

            if (!$referrer) {
                Log::info('ℹ️ No referrer found', ['user_id' => $user->id]);
                return;
            }

            $commissionLevels = Reffercommissionsetup::orderBy('reffer_level', 'asc')->get();

            if ($commissionLevels->isEmpty()) {
                Log::warning('⚠️ No commission levels configured');
                return;
            }

            $currentReferrer = $referrer;
            $level = 1;

            foreach ($commissionLevels as $commission) {
                if (!$currentReferrer) {
                    Log::info('ℹ️ No more referrers at level ' . $level);
                    break;
                }

                $commissionAmount = ($commission->commission_percentage / 100) * $packagePrice;

                // Update referrer's balance and refer_income
                $currentReferrer->balance += $commissionAmount;
                $currentReferrer->refer_income += $commissionAmount;
                $currentReferrer->save();

                Log::info('🎁 Referral Commission Given', [
                    'referrer_id' => $currentReferrer->id,
                    'referrer_name' => $currentReferrer->name ?? 'N/A',
                    'level' => $level,
                    'commission_percentage' => $commission->commission_percentage,
                    'package_price' => $packagePrice,
                    'commission_amount' => $commissionAmount,
                    'new_balance' => $currentReferrer->balance,
                    'new_refer_income' => $currentReferrer->refer_income
                ]);

                // Move to next level referrer
                $currentReferrer = $currentReferrer->referrer;
                $level++;
            }

            Log::info('✅ Referral Commission Distribution Complete', [
                'levels_processed' => $level - 1,
                'package_price' => $packagePrice
            ]);

        } catch (Throwable $e) {
            Log::error('❌ Referral Commission Error', [
                'user_id' => $user->id,
                'package_price' => $packagePrice,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Don't throw - commission failure shouldn't stop the purchase
            // But log it for investigation
        }
    }

    /**
     * Get All Available Packages
     */
    public function getPackages(Request $request)
    {
        try {
            $packages = Package::where('status', 'active')
                ->orderBy('price', 'asc')
                ->get()
                ->map(function ($package) {
                    return [
                        'id' => $package->id,
                        'package_name' => $package->package_name,
                        'price' => (float)$package->price,
                        'daily_income' => (float)$package->daily_income,
                        'daily_limit' => (int)$package->daily_limit,
                        'total_return' => (float)($package->daily_income * $package->daily_limit),
                        'profit' => (float)(($package->daily_income * $package->daily_limit) - $package->price),
                        'profit_percentage' => (float)((($package->daily_income * $package->daily_limit) - $package->price) / $package->price * 100),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $packages
            ]);

        } catch (Throwable $e) {
            Log::error('Get Packages Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch packages'
            ], 500);
        }
    }
}
