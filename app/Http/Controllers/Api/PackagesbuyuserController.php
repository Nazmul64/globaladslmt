<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
                    'validity' => (int)($package->package->validity ?? 0),
                    'photo' => $package->package->photo,
                    'status' => $package->status,
                    'created_at' => $package->created_at?->toIso8601String(),
                    'updated_at' => $package->updated_at?->toIso8601String(),
                ]
            ]);

        } catch (Throwable $e) {
            Log::error('Get Current Package Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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

            $balance = (float)($user->balance ?? 0);

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => $balance
                ]
            ]);

        } catch (Throwable $e) {
            Log::error('Get User Balance Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch balance'
            ], 500);
        }
    }

    /**
     * Buy or Update Package
     * ✅ Fixed: Properly calculates difference for updates
     */
    public function packagebuy(Request $request, $package_id)
    {
        $user = Auth::user();

        Log::info('🛒 Package Buy/Update Request Started', [
            'user_id' => $user?->id,
            'package_id' => $package_id,
            'current_balance' => $user?->balance,
        ]);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.'
            ], 401);
        }

        // ======================================
        //  ✅ VALIDATE PACKAGE
        // ======================================
        $package = Package::find($package_id);

        if (!$package || !$package->price || $package->price <= 0) {
            Log::warning('Invalid package requested', [
                'user_id' => $user->id,
                'package_id' => $package_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid package!'
            ], 404);
        }

        // ======================================
        //  ✅ CHECK EXISTING PACKAGE
        // ======================================
        $existing = Packagebuy::where('user_id', $user->id)
            ->where('status', 'approved')
            ->first();

        $isUpdate = $existing !== null;
        $isFirstPurchase = !$isUpdate;

        // প্রতিরোধ: একই প্যাকেজ আবার কিনতে চাচ্ছে কিনা
        if ($isUpdate && $existing->package_id == $package_id) {
            Log::info('User already has this package', [
                'user_id' => $user->id,
                'package_id' => $package_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'You already have this package!'
            ], 400);
        }

        // ======================================
        //  ✅ CALCULATE REQUIRED AMOUNT
        // ======================================
        $requiredAmount = 0;

        if ($isUpdate) {
            // আপডেটের ক্ষেত্রে: শুধু পার্থক্য (difference) দিতে হবে
            $requiredAmount = $package->price - $existing->amount;

            Log::info('🔄 Package Update - Calculating difference', [
                'user_id' => $user->id,
                'old_package_id' => $existing->package_id,
                'old_package_amount' => $existing->amount,
                'new_package_id' => $package->id,
                'new_package_amount' => $package->price,
                'difference_to_pay' => $requiredAmount,
            ]);
        } else {
            // নতুন কেনার ক্ষেত্রে: পুরো দাম দিতে হবে
            $requiredAmount = $package->price;

            Log::info('🆕 New Package Purchase - Full payment', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'full_price' => $requiredAmount,
            ]);
        }

        // ======================================
        //  ✅ BALANCE CHECK
        // ======================================
        $availableBalance = (float)($user->balance ?? 0);

        Log::info('💰 Balance Verification', [
            'user_id' => $user->id,
            'available_balance' => $availableBalance,
            'required_amount' => $requiredAmount,
            'is_sufficient' => $availableBalance >= $requiredAmount,
        ]);

        if ($availableBalance < $requiredAmount) {
            Log::warning('Insufficient balance', [
                'user_id' => $user->id,
                'available' => $availableBalance,
                'required' => $requiredAmount,
                'shortage' => $requiredAmount - $availableBalance,
            ]);

            $message = $isUpdate
                ? "Insufficient balance. Available: $" . number_format($availableBalance, 2) . ", Additional needed: $" . number_format($requiredAmount, 2)
                : "Insufficient balance. Available: $" . number_format($availableBalance, 2) . ", Required: $" . number_format($requiredAmount, 2);

            return response()->json([
                'success' => false,
                'message' => $message
            ], 400);
        }

        // ======================================
        //  ✅ PROCESS TRANSACTION
        // ======================================
        try {
            DB::beginTransaction();

            $balanceBefore = $user->balance;

            // ✅ Deduct required amount from balance
            $user->balance -= $requiredAmount;
            $user->save();

            Log::info('💵 Balance deducted', [
                'user_id' => $user->id,
                'balance_before' => $balanceBefore,
                'amount_deducted' => $requiredAmount,
                'balance_after' => $user->balance,
            ]);

            // ✅ Create or Update package record
            if ($isUpdate) {
                Log::info('🔄 Updating package record', [
                    'package_buy_id' => $existing->id,
                    'old_package_id' => $existing->package_id,
                    'new_package_id' => $package->id,
                ]);

                $existing->update([
                    'package_id' => $package->id,
                    'amount' => $package->price,
                    'daily_income' => $package->daily_income,
                    'daily_limit' => $package->daily_limit,
                    'status' => 'approved',
                ]);

                $action = "updated";
                $packageBuyRecord = $existing;

            } else {
                Log::info('🆕 Creating new package record', [
                    'package_id' => $package->id,
                    'amount' => $package->price,
                ]);

                $packageBuyRecord = Packagebuy::create([
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'amount' => $package->price,
                    'daily_income' => $package->daily_income,
                    'daily_limit' => $package->daily_limit,
                    'status' => 'approved',
                ]);

                $action = "purchased";
            }

            // ======================================
            //  ✅ REFERRAL COMMISSION (শুধুমাত্র প্রথম ক্রয়ে)
            // ======================================
            if ($isFirstPurchase) {
                Log::info('🎁 Processing referral commission for first purchase', [
                    'user_id' => $user->id,
                    'package_price' => $package->price,
                ]);

                $this->giveReferralCommission($user, $package->price);
            } else {
                Log::info('ℹ️ Referral commission skipped (not first purchase)', [
                    'user_id' => $user->id,
                ]);
            }

            DB::commit();

            Log::info('✅ Package ' . $action . ' successfully', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'package_name' => $package->package_name,
                'amount_paid' => $requiredAmount,
                'final_balance' => $user->balance,
                'action' => $action,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Package {$action} successfully!",
                'data' => [
                    'package_id' => $package->id,
                    'package_name' => $package->package_name,
                    'package_price' => (float)$package->price,
                    'amount_paid' => (float)$requiredAmount,
                    'new_balance' => (float)$user->balance,
                    'daily_income' => (float)$package->daily_income,
                    'daily_limit' => (int)$package->daily_limit,
                ]
            ]);

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('❌ Package Buy/Update Transaction Failed', [
                'user_id' => $user->id,
                'package_id' => $package_id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Transaction failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Give Referral Commission to Uplines
     * ✅ Multi-level referral commission distribution
     */
    private function giveReferralCommission($user, $packagePrice)
    {
        try {
            $referrer = $user->referrer;

            if (!$referrer) {
                Log::info('ℹ️ No referrer found', [
                    'user_id' => $user->id,
                ]);
                return;
            }

            $levels = Reffercommissionsetup::orderBy('reffer_level', 'asc')->get();

            if ($levels->isEmpty()) {
                Log::warning('⚠️ No referral commission levels configured in database');
                return;
            }

            Log::info('🎁 Starting referral commission distribution', [
                'from_user_id' => $user->id,
                'package_price' => $packagePrice,
                'total_levels_configured' => $levels->count(),
            ]);

            $current = $referrer;
            $level = 1;
            $totalCommissionGiven = 0;

            foreach ($levels as $commission) {
                if (!$current) {
                    Log::info('ℹ️ No more upline referrers available', [
                        'stopped_at_level' => $level,
                    ]);
                    break;
                }

                $amount = ($commission->commission_percentage / 100) * $packagePrice;

                // আপলাইনের ব্যালেন্স এবং রেফার আয় বৃদ্ধি
                $current->balance += $amount;
                $current->refer_income += $amount;
                $current->save();

                $totalCommissionGiven += $amount;

                Log::info('💸 Referral commission given', [
                    'from_user_id' => $user->id,
                    'to_user_id' => $current->id,
                    'to_user_name' => $current->name ?? 'N/A',
                    'level' => $level,
                    'commission_percentage' => $commission->commission_percentage,
                    'package_price' => $packagePrice,
                    'commission_amount' => $amount,
                    'new_balance' => $current->balance,
                    'total_refer_income' => $current->refer_income,
                ]);

                // পরবর্তী লেভেলে যাওয়া
                $current = $current->referrer;
                $level++;
            }

            Log::info('✅ Referral commission distribution completed', [
                'from_user_id' => $user->id,
                'total_levels_processed' => $level - 1,
                'total_commission_distributed' => $totalCommissionGiven,
            ]);

        } catch (Throwable $e) {
            Log::error('❌ Referral Commission Distribution Error', [
                'user_id' => $user->id,
                'package_price' => $packagePrice,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // রেফারেল কমিশনে সমস্যা হলেও মূল ট্রানজেকশন ফেইল হবে না
        }
    }

    /**
     * Get All Available Packages
     * ✅ Sorted by price (highest first)
     */
    public function getPackages(Request $request)
    {
        try {
            $packages = Package::where('status', 'active')
                ->orderBy('price', 'desc')
                ->get()
                ->map(function ($p) {
                    $totalReturn = $p->daily_income * $p->daily_limit;
                    $profit = $totalReturn - $p->price;
                    $profitPercentage = ($p->price > 0)
                        ? (($profit / $p->price) * 100)
                        : 0;

                    return [
                        'id' => $p->id,
                        'package_name' => $p->package_name,
                        'price' => (float)$p->price,
                        'daily_income' => (float)$p->daily_income,
                        'daily_limit' => (int)$p->daily_limit,
                        'validity' => (int)($p->validity ?? 0),
                        'photo' => $p->photo,
                        'total_return' => (float)$totalReturn,
                        'profit' => (float)$profit,
                        'profit_percentage' => round($profitPercentage, 2),
                        'status' => $p->status,
                    ];
                });

            Log::info('📦 Packages fetched successfully', [
                'total_packages' => $packages->count(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $packages
            ]);

        } catch (Throwable $e) {
            Log::error('Get Packages Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch packages'
            ], 500);
        }
    }
}
