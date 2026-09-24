<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Earning;
use App\Models\Package;
use App\Models\User;
use App\Models\Appsetting;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserearningController extends Controller
{
    /**
     * GET USER EARNING DATA
     * ✅ Returns user's current package and all app settings
     */
    public function userEarning()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            }

            $settings = \Illuminate\Support\Facades\Cache::remember('app_settings_global', 60, function () {
                return Appsetting::first();
            });

            if (!$settings) {
                return response()->json(['status' => false, 'message' => 'App not configured'], 500);
            }

            // ========== GET ALL SETTINGS FROM DATABASE ==========

            // Google AdMob Settings
            $admobAppId = $settings->admob_app_id ?? null;
            $admobBannerId = $settings->admob_banner_id ?? null;
            $admobInterstitialId = $settings->admob_interstitial_id ?? null;
            $admobRewardedInterstitialId = $settings->admob_rewarded_interstitial_id ?? null;
            $admobRewardedId = $settings->admob_rewarded_id ?? null;
            $admobNativeId = $settings->admob_native_id ?? null;
            $admobAppOpenId = $settings->admob_app_open_id ?? null;
            $admobStatus = (bool) ($settings->admob_status ?? true);

            // Basic App Settings
            $startIoId = $settings->star_io_id ?? null;
            $invalidClickLimit = $settings->invalid_click_limit ?? null;
            $invalidDeduct = $settings->invalid_deduct ?? null;
            $viewBeforeClickViewTarget = $settings->view_before_click_view_target ?? null;

            // ⏱️ CRITICAL: Time Settings
            // 🔥 FIX: Convert to INTEGER (not float/string)
            $taskBreakMinutes = $settings->task_break_time_minutes !== null
                ? (int) round((float) $settings->task_break_time_minutes)
                : 1;

            // 🔥🔥🔥 SUPER CRITICAL FIX: button_timer_seconds MUST be INTEGER
            $buttonTimerSeconds = $settings->button_timer_seconds !== null
                ? (int) round((float) $settings->button_timer_seconds)
                : 30; // Default fallback

            // VPN Settings
            $vpnModes = $settings->vpn_modes ?? 'yes';
            $vpnRequiredInTaskOnly = $settings->vpn_required_in_task_only ?? 'yes';
            $allowedCountry = $settings->allowed_country ?? 'us,uk,au,bangladesh,india';

            // App Control Settings
            $registrationStatus = $settings->registration_status ?? null;
            $sameDeviceLogin = $settings->same_device_login ?? 'yes';
            $maintenanceMode = $settings->maintenance_mode ?? 'yes';
            $appVersion = $settings->app_version ?? null;
            $appLink = $settings->app_link ?? null;

            // ========== GET USER'S PURCHASED PACKAGE ==========
            $package = $this->getUserPackage($user);

            if (!$package) {
                return response()->json([
                    'status' => false,
                    'message' => 'No package purchased. Please buy a package first.'
                ], 404);
            }

            // ========== GET TODAY'S EARNING DATA ==========
            $earning = $this->getTodayEarning($user, $package);

            // Package Configuration
            $dailyLimit = (int) $package->daily_limit;
            $adBrack = (int) $package->ad_brack;
            $dailyIncome = (float) $package->daily_income;

            if ($adBrack <= 0) {
                $adBrack = 10;
            }
            if ($dailyLimit <= 0) {
                $dailyLimit = $adBrack;
            }

            // If daily_limit entered in package is less than ad_brack (e.g. daily_limit = 1 cycle, ad_brack = 57 ads),
            // treat daily_limit as number of cycles and compute total daily ads = ad_brack * daily_limit
            if ($dailyLimit < $adBrack) {
                $dailyLimit = $adBrack * max(1, $dailyLimit);
            }

            // ========== CALCULATE EARNING METRICS ==========

            // Determine if timer is enabled for the active ad network
            $isAdmobActive = ($admobStatus && !empty($admobAppId));
            $admobTimerEnabled = (($settings->admob_timer_status ?? 'yes') === 'yes');
            $starioTimerEnabled = (($settings->stario_timer_status ?? 'yes') === 'yes');
            $isTimerEnabled = $isAdmobActive ? $admobTimerEnabled : $starioTimerEnabled;

            // Calculate total cycles and income per cycle
            $totalCycles = (int) max(1, floor($dailyLimit / $adBrack));
            $incomePerBrack = $totalCycles > 0 ? ($dailyIncome / $totalCycles) : $dailyIncome;

            // Current cycle position
            $adsWatchedInCurrentCycle = $earning->ads_watched_today % $adBrack;

            // Current cycle number (1-based)
            $currentCycleNumber = $earning->ads_watched_today > 0
                ? (int) ceil($earning->ads_watched_today / $adBrack)
                : 0;

            // Check if currently in break time and compute elapsed time
            $isBreakActive = false;
            $breakRemainingSeconds = 0;

            if ($isTimerEnabled && !empty($earning->last_break_started)) {
                $breakTotalSeconds = (int)($taskBreakMinutes * 60);
                $breakStarted = Carbon::parse($earning->last_break_started);
                $elapsedSeconds = Carbon::now()->diffInSeconds($breakStarted);

                if ($elapsedSeconds >= $breakTotalSeconds) {
                    // Break timer has already completed while user was away!
                    $earning->last_break_started = null;
                    $earning->save();
                    $isBreakActive = false;
                    $breakRemainingSeconds = 0;
                } else {
                    $isBreakActive = true;
                    $breakRemainingSeconds = $breakTotalSeconds - $elapsedSeconds;
                }
            } elseif (!$isTimerEnabled && !empty($earning->last_break_started)) {
                // If timer disabled, clear any pending break
                $earning->last_break_started = null;
                $earning->save();
                $isBreakActive = false;
                $breakRemainingSeconds = 0;
            }

            // Daily limit reached ONLY after completing all cycles and last claim
            $dailyLimitReached = ($earning->ads_watched_today >= $dailyLimit) &&
                                 ($earning->last_claimed_cycle >= $totalCycles);

            // ========== CLAIM BUTTON LOGIC ==========
            $showClaimButton = false;

            if ($adsWatchedInCurrentCycle == 0 &&
                $earning->ads_watched_today > 0 &&
                (!$isTimerEnabled || !$isBreakActive) &&
                $currentCycleNumber > $earning->last_claimed_cycle) {
                $showClaimButton = true;
            }

            // ========== 🔥 DETAILED DEBUG LOG FOR TIMER ==========
            Log::info('📊 USER EARNING DATA', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'package_name' => $package->package_name,
                'total_ads_watched' => $earning->ads_watched_today,
                'ads_in_current_cycle' => $adsWatchedInCurrentCycle,
                'current_cycle_number' => $currentCycleNumber,
                'last_claimed_cycle' => $earning->last_claimed_cycle,
                'ad_brack' => $adBrack,
                'daily_limit' => $dailyLimit,
                'is_break_active' => $isBreakActive,
                'break_remaining_seconds' => $breakRemainingSeconds,
                'show_claim_button' => $showClaimButton,
                'daily_limit_reached' => $dailyLimitReached,
            ]);

            Log::info('⏱️ TIMER SETTINGS DEBUG', [
                'button_timer_seconds_from_db' => $settings->button_timer_seconds,
                'button_timer_seconds_type_db' => gettype($settings->button_timer_seconds),
                'button_timer_seconds_final' => $buttonTimerSeconds,
                'button_timer_seconds_type_final' => gettype($buttonTimerSeconds),
                'task_break_minutes_from_db' => $settings->task_break_time_minutes,
                'task_break_minutes_final' => $taskBreakMinutes,
            ]);

            // ========== PREPARE RESPONSE DATA ==========
            $responseData = [
                // ========== USER INFO ==========
                'user_name' => $user->name ?? 'Guest',
                'is_blocked' => (bool) ($user->is_blocked ?? false),
                'is_withdraw_blocked' => (bool) ($user->is_blocked ?? false),

                // ========== EARNING STATS ==========
                'ads_watched_today' => (int) $earning->ads_watched_today,
                'ads_watched_in_current_cycle' => (int) $adsWatchedInCurrentCycle,
                'current_cycle_number' => (int) $currentCycleNumber,
                'last_claimed_cycle' => (int) $earning->last_claimed_cycle,
                'daily_limit' => (int) $dailyLimit,
                'is_break_active' => (bool) $isBreakActive,
                'break_remaining_seconds' => (int) $breakRemainingSeconds,
                'server_time' => Carbon::now()->toIso8601String(),
                'last_break_started' => $earning->last_break_started ? Carbon::parse($earning->last_break_started)->toIso8601String() : null,
                'show_claim_button' => (bool) $showClaimButton,
                'daily_limit_reached' => (bool) $dailyLimitReached,

                // ========== INCOME INFO ==========
                'income_per_brack' => number_format($incomePerBrack, 2, '.', ''),
                'next_reward' => number_format($incomePerBrack, 2, '.', ''),
                'total_cycles' => (int) $totalCycles,
                'today_earning' => number_format((float) $earning->today_earning, 2, '.', ''),
                'total_earning' => number_format((float) $earning->total_earning, 2, '.', ''),
                'user_balance' => number_format((float) $user->balance, 2, '.', ''),

                // ========== GOOGLE ADMOB SETTINGS ==========
                'admob_app_id' => (string) ($admobAppId ?? ''),
                'admob_banner_id' => (string) ($admobBannerId ?? ''),
                'admob_interstitial_id' => (string) ($admobInterstitialId ?? ''),
                'admob_rewarded_interstitial_id' => (string) ($admobRewardedInterstitialId ?? ''),
                'admob_rewarded_id' => (string) ($admobRewardedId ?? ''),
                'admob_native_id' => (string) ($admobNativeId ?? ''),
                'admob_app_open_id' => (string) ($admobAppOpenId ?? ''),
                'admob_status' => (bool) $admobStatus,
                'admob_timer_status' => (string) ($settings->admob_timer_status ?? 'yes'),

                // ========== BASIC APP SETTINGS ==========
                'star_io_id' => (string) ($startIoId ?? ''),
                'startapp_app_id' => (string) ($startIoId ?? ''),
                'stario_timer_status' => (string) ($settings->stario_timer_status ?? 'yes'),
                'invalid_click_limit' => $invalidClickLimit !== null ? (int) $invalidClickLimit : null,
                'invalid_deduct' => $invalidDeduct !== null ? number_format((float) $invalidDeduct, 2, '.', '') : null,
                'view_before_click_view_target' => $viewBeforeClickViewTarget !== null ? number_format((float) $viewBeforeClickViewTarget, 2, '.', '') : null,

                // ========== ⏱️ TIME SETTINGS (🔥 CRITICAL FIX) ==========
                'task_break_time_minutes' => (int) $taskBreakMinutes, // ✅ Must be INTEGER
                'button_timer_seconds' => (int) $buttonTimerSeconds,  // ✅ Must be INTEGER (NOT string/float)
                'ad_timer_seconds' => $settings->ad_timer_seconds !== null ? (int) round((float) $settings->ad_timer_seconds) : 15,

                // ========== VPN SETTINGS ==========
                'vpn_modes' => (string) $vpnModes,
                'vpn_required_in_task_only' => (string) $vpnRequiredInTaskOnly,
                'allowed_country' => (string) $allowedCountry,

                // ========== APP CONTROL SETTINGS ==========
                'registration_status' => (string) ($registrationStatus ?? ''),
                'same_device_login' => (string) $sameDeviceLogin,
                'maintenance_mode' => (string) $maintenanceMode,
                'app_version' => (string) ($appVersion ?? ''),
                'app_link' => (string) ($appLink ?? ''),

                // ========== PACKAGE INFO ==========
                'package_id' => (int) $package->id,
                'package_name' => (string) ($package->package_name ?? 'Package'),
                'package_price' => number_format((float) $package->price, 2, '.', ''),
                'ad_brack' => (int) $adBrack,
                'daily_income' => number_format($dailyIncome, 2, '.', ''),
            ];

            // 🔥 LOG FINAL RESPONSE DATA (for debugging)
            Log::info('📤 API RESPONSE DATA', [
                'button_timer_seconds' => $responseData['button_timer_seconds'],
                'task_break_time_minutes' => $responseData['task_break_time_minutes'],
            ]);

            return response()->json([
                'status' => true,
                'data' => $responseData,
                'message' => 'Data loaded successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ USER EARNING ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json(['status' => false, 'message' => 'Server error'], 500);
        }
    }

    /**
     * TRACK AD VIEW
     * ✅ Increments ad counter sequentially and starts break timer only if timer enabled
     */
    public function trackAdView()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            }

            $package = $this->getUserPackage($user);
            if (!$package) {
                return response()->json(['status' => false, 'message' => 'Package not found'], 500);
            }

            $earning = $this->getTodayEarning($user, $package);

            $adBrack = (int) $package->ad_brack;
            if ($adBrack <= 0) $adBrack = 10;
            $dailyLimit = (int) $package->daily_limit;
            if ($dailyLimit < $adBrack) $dailyLimit = $adBrack * max(1, $dailyLimit);

            // Check daily limit BEFORE incrementing
            if ($earning->ads_watched_today >= $dailyLimit) {
                return response()->json(['status' => false, 'message' => 'Daily limit reached'], 429);
            }

            // Prevent rapid duplicate skipping (debounce within 1.5 seconds)
            $cacheKey = 'user_ad_track_lock_' . $user->id;
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                $currentAds = (int) $earning->ads_watched_today;
                return response()->json([
                    'status' => true,
                    'data' => [
                        'ads_watched_today' => $currentAds,
                        'ads_watched_in_current_cycle' => ($currentAds % $adBrack),
                        'ad_brack' => $adBrack,
                        'cycle_completed' => ($currentAds > 0 && ($currentAds % $adBrack) == 0),
                        'start_break_timer' => false,
                    ],
                    'message' => 'Ad view already in progress'
                ], 200);
            }
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addSeconds(2));

            $settings = \App\Models\Appsetting::first();
            $isAdmobActive = ($settings && $settings->admob_status && !empty($settings->admob_app_id));
            $admobTimerEnabled = ($settings && ($settings->admob_timer_status ?? 'yes') === 'yes');
            $starioTimerEnabled = ($settings && ($settings->stario_timer_status ?? 'yes') === 'yes');
            $isTimerEnabled = $isAdmobActive ? $admobTimerEnabled : $starioTimerEnabled;

            DB::beginTransaction();

            try {
                // Increment ad counter strictly by 1
                $earning->ads_watched_today = $earning->ads_watched_today + 1;

                $adsWatchedInCurrentCycle = $earning->ads_watched_today % $adBrack;

                // Check: Cycle complete?
                $cycleCompleted = ($adsWatchedInCurrentCycle == 0);
                $startBreakTimer = false;

                if ($cycleCompleted) {
                    if ($isTimerEnabled) {
                        // Start break timer only if timer is enabled for this network
                        $earning->last_break_started = now();
                        $startBreakTimer = true;
                    } else {
                        // No break time for Google Ads when timer is disabled
                        $earning->last_break_started = null;
                        $startBreakTimer = false;
                    }

                    Log::info('✅ CYCLE COMPLETED', [
                        'user_id' => $user->id,
                        'total_ads_watched' => $earning->ads_watched_today,
                        'ad_brack' => $adBrack,
                        'cycle_number' => ceil($earning->ads_watched_today / $adBrack),
                        'timer_enabled' => $isTimerEnabled,
                    ]);
                }

                $earning->save();
                DB::commit();

                Log::info('📈 AD VIEW TRACKED', [
                    'user_id' => $user->id,
                    'total_ads' => $earning->ads_watched_today,
                    'cycle_position' => $adsWatchedInCurrentCycle,
                    'cycle_completed' => $cycleCompleted,
                    'break_started' => $startBreakTimer,
                ]);

                return response()->json([
                    'status' => true,
                    'data' => [
                        'ads_watched_today' => (int) $earning->ads_watched_today,
                        'ads_watched_in_current_cycle' => (int) $adsWatchedInCurrentCycle,
                        'ad_brack' => (int) $adBrack,
                        'cycle_completed' => (bool) $cycleCompleted,
                        'start_break_timer' => (bool) $startBreakTimer,
                    ],
                    'message' => 'View tracked successfully'
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('❌ TRACK VIEW ERROR', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to track view'], 500);
        }
    }

    /**
     * TRACK INVALID CLICK
     * ✅ Deducts balance, tracks invalid clicks count, and auto-blocks user if limit reached
     */
    public function trackInvalidClick()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            }

            $settings = \App\Models\Appsetting::first();
            $limit = (int) ($settings->invalid_click_limit ?? 5);
            if ($limit <= 0) $limit = 5;
            $deduct = (float) ($settings->invalid_deduct ?? 0);

            // Fetch current user invalid clicks
            $cacheKey = 'user_invalid_clicks_' . $user->id;
            $currentClicks = (int) \Illuminate\Support\Facades\Cache::get($cacheKey, 0) + 1;
            \Illuminate\Support\Facades\Cache::put($cacheKey, $currentClicks, now()->addDays(30));

            // Deduct balance if configured
            if ($deduct > 0) {
                $user->balance = max(0, round((float)$user->balance - $deduct, 2));
            }

            $isBlocked = false;
            if ($currentClicks >= $limit) {
                $user->is_blocked = true;
                $isBlocked = true;
                Log::warning('🚨 USER AUTO-BLOCKED DUE TO INVALID CLICKS', [
                    'user_id' => $user->id,
                    'invalid_clicks' => $currentClicks,
                    'limit' => $limit,
                ]);
            }

            $user->save();

            Log::warning('⚠️ INVALID CLICK DETECTED', [
                'user_id' => $user->id,
                'invalid_clicks' => $currentClicks,
                'limit' => $limit,
                'deducted' => $deduct,
                'is_blocked' => $isBlocked,
                'timestamp' => now(),
            ]);

            if ($isBlocked) {
                return response()->json([
                    'status' => false,
                    'is_blocked' => true,
                    'invalid_clicks' => $currentClicks,
                    'limit' => $limit,
                    'deducted' => $deduct,
                    'balance' => (float) $user->balance,
                    'message' => 'আপনার একাউন্টে সর্বোচ্চ ইনভ্যালিড ক্লিক হওয়ায় একাউন্ট ব্লক করা হয়েছে। অনুগ্রহ করে এডমিনের সাথে সাপোর্টে যোগাযোগ করুন।'
                ], 403);
            }

            return response()->json([
                'status' => true,
                'is_blocked' => false,
                'invalid_clicks' => $currentClicks,
                'limit' => $limit,
                'deducted' => $deduct,
                'balance' => (float) $user->balance,
                'message' => "ইনভ্যালিড ক্লিক সনাক্ত হয়েছে ({$currentClicks}/{$limit})। সতর্ক থাকুন, লিমিট পার হলে একাউন্ট ব্লক হবে।"
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ TRACK INVALID CLICK ERROR', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to track'], 500);
        }
    }

    /**
     * BREAK COMPLETE
     * ✅ Clears break timer and shows claim button if cycle is complete
     */
    public function breakComplete()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            }

            $package = $this->getUserPackage($user);
            $earning = $this->getTodayEarning($user, $package);

            DB::beginTransaction();

            try {
                // Clear break timer
                $earning->last_break_started = null;
                $earning->save();

                DB::commit();

                $adBrack = (int) $package->ad_brack;
                $dailyLimit = (int) $package->daily_limit;
                $adsWatchedInCurrentCycle = $earning->ads_watched_today % $adBrack;
                $currentCycleNumber = $earning->ads_watched_today > 0
                    ? ceil($earning->ads_watched_today / $adBrack)
                    : 0;

                // Limit complete ONLY after claim
                $dailyLimitReached = ($earning->ads_watched_today >= $dailyLimit) &&
                                     ($earning->last_claimed_cycle >= $currentCycleNumber);

                // Check if this cycle's reward is already claimed
                $showClaimButton = ($adsWatchedInCurrentCycle == 0 &&
                                   $earning->ads_watched_today > 0 &&
                                   $currentCycleNumber > $earning->last_claimed_cycle);

                Log::info('✅ BREAK COMPLETE', [
                    'user_id' => $user->id,
                    'total_ads' => $earning->ads_watched_today,
                    'cycle_position' => $adsWatchedInCurrentCycle,
                    'current_cycle_number' => $currentCycleNumber,
                    'last_claimed_cycle' => $earning->last_claimed_cycle,
                    'show_claim_button' => $showClaimButton,
                    'daily_limit_reached' => $dailyLimitReached,
                ]);

                return response()->json([
                    'status' => true,
                    'data' => [
                        'show_claim_button' => (bool) $showClaimButton,
                        'ads_watched_today' => (int) $earning->ads_watched_today,
                        'ads_watched_in_current_cycle' => (int) $adsWatchedInCurrentCycle,
                        'current_cycle_number' => (int) $currentCycleNumber,
                        'last_claimed_cycle' => (int) $earning->last_claimed_cycle,
                        'daily_limit_reached' => (bool) $dailyLimitReached,
                    ],
                    'message' => 'Break complete'
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('❌ BREAK COMPLETE ERROR', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to complete break'], 500);
        }
    }

    /**
     * CLAIM REWARD
     * ✅ Processes reward claim after cycle completion
     */
    public function claimReward()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            }

            $package = $this->getUserPackage($user);
            if (!$package) {
                return response()->json(['status' => false, 'message' => 'No active package found'], 404);
            }

            $earning = $this->getTodayEarning($user, $package);

            $dailyLimit = (int) $package->daily_limit;
            $adBrack = (int) $package->ad_brack;
            $dailyIncome = (float) $package->daily_income;

            if ($adBrack <= 0) {
                $adBrack = 10;
            }
            if ($dailyLimit <= 0) {
                $dailyLimit = $adBrack;
            }

            if ($dailyLimit < $adBrack) {
                $dailyLimit = $adBrack * max(1, $dailyLimit);
            }

            // Calculate reward per cycle
            $totalCycles = (int) max(1, floor($dailyLimit / $adBrack));
            $incomePerBrack = $totalCycles > 0 ? ($dailyIncome / $totalCycles) : $dailyIncome;

            $adsWatchedInCurrentCycle = $earning->ads_watched_today % $adBrack;
            $currentCycleNumber = $earning->ads_watched_today > 0
                ? (int) ceil($earning->ads_watched_today / $adBrack)
                : 0;
            $isCycleComplete = ($adsWatchedInCurrentCycle == 0 && $earning->ads_watched_today > 0);

            // ========== BREAK CHECK WITH ELAPSED TIME ==========
            $settings = Appsetting::first();
            $taskBreakMinutes = $settings && $settings->task_break_time_minutes !== null
                ? (int) round((float) $settings->task_break_time_minutes)
                : 1;

            $isAdmobActive = ($settings && $settings->admob_status && !empty($settings->admob_app_id));
            $admobTimerEnabled = ($settings && ($settings->admob_timer_status ?? 'yes') === 'yes');
            $starioTimerEnabled = ($settings && ($settings->stario_timer_status ?? 'yes') === 'yes');
            $isTimerEnabled = $isAdmobActive ? $admobTimerEnabled : $starioTimerEnabled;

            if ($isTimerEnabled && !empty($earning->last_break_started)) {
                $breakTotalSeconds = (int)($taskBreakMinutes * 60);
                $breakStarted = Carbon::parse($earning->last_break_started);
                $elapsedSeconds = Carbon::now()->diffInSeconds($breakStarted);

                if ($elapsedSeconds >= $breakTotalSeconds) {
                    // Break has elapsed, clear it automatically
                    $earning->last_break_started = null;
                    $earning->save();
                } else {
                    $remaining = $breakTotalSeconds - $elapsedSeconds;
                    return response()->json([
                        'status' => false,
                        'message' => "Break timer is still running. Please wait {$remaining} seconds.",
                        'break_remaining_seconds' => $remaining
                    ], 400);
                }
            } else {
                $earning->last_break_started = null;
            }

            if (!$isCycleComplete) {
                return response()->json([
                    'status' => false,
                    'message' => 'Complete the cycle first! Watch ' . ($adBrack - $adsWatchedInCurrentCycle) . ' more ads.'
                ], 400);
            }

            // Check if already claimed
            if ($currentCycleNumber <= $earning->last_claimed_cycle) {
                return response()->json([
                    'status' => false,
                    'message' => 'You have already claimed this cycle reward!'
                ], 400);
            }

            if ($earning->ads_watched_today > $dailyLimit) {
                return response()->json(['status' => false, 'message' => 'Daily limit exceeded'], 429);
            }

            DB::beginTransaction();

            try {
                // Lock rows for update
                $user = User::lockForUpdate()->find($user->id);
                $earning = Earning::lockForUpdate()
                    ->where('user_id', $user->id)
                    ->where('earning_date', Carbon::today()->toDateString())
                    ->first();

                if (!$earning) {
                    throw new \Exception('Earning record not found');
                }

                // Calculate and add reward
                $rewardAmount = round($incomePerBrack, 2);
                $newBalance = round((float) $user->balance + $rewardAmount, 2);
                $newTodayEarning = round((float) $earning->today_earning + $rewardAmount, 2);
                $newTotalEarning = round((float) $earning->total_earning + $rewardAmount, 2);

                // Update user balance
                $user->balance = $newBalance;
                $user->save();

                // Update earnings
                $earning->today_earning = $newTodayEarning;
                $earning->total_earning = $newTotalEarning;
                $earning->last_break_started = null;

                // CRITICAL: Mark this cycle as claimed
                $earning->last_claimed_cycle = $currentCycleNumber;

                $earning->save();

                DB::commit();

                // Refresh data
                $user->refresh();
                $earning->refresh();

                // Check limit AFTER claim
                $dailyLimitReached = ($earning->ads_watched_today >= $dailyLimit) &&
                                     ($earning->last_claimed_cycle >= $currentCycleNumber);

                $hasMoreAds = !$dailyLimitReached;

                // Calculate remaining cycles
                $remainingAds = $dailyLimit - $earning->ads_watched_today;
                $remainingCycles = $hasMoreAds ? floor($remainingAds / $adBrack) : 0;

                Log::info('✅ REWARD CLAIMED SUCCESSFULLY', [
                    'user_id' => $user->id,
                    'reward_amount' => $rewardAmount,
                    'new_balance' => $user->balance,
                    'new_today_earning' => $earning->today_earning,
                    'ads_watched' => $earning->ads_watched_today,
                    'last_claimed_cycle' => $earning->last_claimed_cycle,
                    'daily_limit' => $dailyLimit,
                    'has_more_ads' => $hasMoreAds,
                    'daily_limit_reached' => $dailyLimitReached,
                    'remaining_cycles' => $remainingCycles,
                ]);

                return response()->json([
                    'status' => true,
                    'success' => true,
                    'message' => "🎉 $" . number_format($rewardAmount, 2) . " Earned!",
                    'data' => [
                        'earned' => number_format($rewardAmount, 2, '.', ''),
                        'reward_amount' => (float) $rewardAmount,
                        'user_balance' => number_format((float) $user->balance, 2, '.', ''),
                        'balance' => (float) $user->balance,
                        'ads_watched_today' => (int) $earning->ads_watched_today,
                        'ads_watched_in_current_cycle' => ($earning->ads_watched_today % $adBrack),
                        'current_cycle_number' => (int) $currentCycleNumber,
                        'last_claimed_cycle' => (int) $earning->last_claimed_cycle,
                        'ad_brack' => (int) $adBrack,
                        'daily_limit' => (int) $dailyLimit,
                        'today_earning' => number_format((float) $earning->today_earning, 2, '.', ''),
                        'total_earning' => number_format((float) $earning->total_earning, 2, '.', ''),
                        'has_more_ads' => (bool) $hasMoreAds,
                        'daily_limit_reached' => (bool) $dailyLimitReached,
                        'remaining_cycles' => (int) $remainingCycles,
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('❌ CLAIM TRANSACTION FAILED', [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('❌ CLAIM REWARD ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['status' => false, 'message' => 'Failed to process reward'], 500);
        }
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get user's PURCHASED package from packagebuys table
     * ✅ Returns only approved packages
     */
    private function getUserPackage(User $user): ?Package
    {
        // Check if user has purchased package in packagebuys table
        $purchasedPackage = DB::table('packagebuys')
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($purchasedPackage && $purchasedPackage->package_id) {
            $package = Package::find($purchasedPackage->package_id);
            if ($package) {
                // Check validity expiration
                if (!empty($package->validity)) {
                    preg_match('/\d+/', (string)$package->validity, $matches);
                    $days = isset($matches[0]) ? (int)$matches[0] : 0;
                    if ($days > 0) {
                        $purchaseDate = Carbon::parse($purchasedPackage->updated_at ?? $purchasedPackage->created_at);
                        if ($purchaseDate->addDays($days)->isPast()) {
                            Log::warning('⚠️ User package expired', ['user_id' => $user->id, 'package_id' => $package->id]);
                            return null;
                        }
                    }
                }

                Log::info('✅ User purchased package found', [
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'package_name' => $package->package_name,
                ]);
                return $package;
            }
        }

        // If no active or valid purchased package found
        Log::warning('⚠️ No valid active package found', ['user_id' => $user->id]);
        return null;
    }

    /**
     * Get or create today's earning record
     * ✅ Creates new record if doesn't exist for today
     */
    private function getTodayEarning(User $user, Package $package): Earning
    {
        $today = Carbon::today()->toDateString();

        $earning = Earning::firstOrCreate(
            [
                'user_id' => $user->id,
                'earning_date' => $today,
            ],
            [
                'package_id' => $package->id,
                'daily_ad_limit' => (int) $package->daily_limit,
                'ads_watched_today' => 0,
                'last_claimed_cycle' => 0,
                'today_earning' => 0.00,
                'total_earning' => 0.00,
                'last_break_started' => null,
            ]
        );

        Log::info('📊 Today Earning Record', [
            'user_id' => $user->id,
            'earning_id' => $earning->id,
            'date' => $today,
            'ads_watched' => $earning->ads_watched_today,
            'last_claimed_cycle' => $earning->last_claimed_cycle,
            'today_earning' => $earning->today_earning,
            'was_recently_created' => $earning->wasRecentlyCreated,
        ]);

        return $earning;
    }
}
