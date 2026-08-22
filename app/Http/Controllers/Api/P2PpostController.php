<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agentbuysellpost;
use App\Models\AgentDeposite;
use App\Models\Agentpaymentmethod;
use App\Models\Category;
use App\Models\Agentkyc;
use App\Models\TakaandDollarsigend;
use App\Models\Userdepositerequest;
use App\Models\UserWidhrawrequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class P2PpostController extends Controller
{
    /**
     * ==================== GET P2P BUY/SELL POSTS ====================
     * ✅ Returns all approved posts with agent payment methods
     * ✅ Each agent's payment methods are included in their post
     * ✅ Fixed: Payment method numbers are included
     */
    public function buysellpost()
    {
        try {
            $currentUser = Auth::user();

            if (!$currentUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            $currentUserId = $currentUser->id;

            // ✅ LOAD CATEGORIES
            $categories = $this->loadCategories();

            // ✅ LOAD CURRENCIES
            $currencies = $this->loadCurrencies();

            // ✅ LOAD CURRENT USER'S PAYMENT METHODS (for reference, if needed)
            $currentUserPaymentMethods = $this->loadUserPaymentMethods($currentUserId);

            Log::info("📱 Current User #{$currentUserId} Payment Methods:", $currentUserPaymentMethods->toArray());

            // ✅ LOAD ALL APPROVED POSTS WITH AGENT PAYMENT METHODS
            $posts = $this->loadPosts();

            Log::info("✅ P2P Posts Loaded Successfully", [
                'total_posts' => $posts->count(),
                'categories' => $categories->count(),
                'currencies' => $currencies->count()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Posts loaded successfully',
                'categories' => $categories,
                'currencies' => $currencies,
                'user_payment_methods' => $currentUserPaymentMethods, // Current user's methods (optional)
                'posts' => $posts,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('❌ P2P Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to load posts. Please try again.'
            ], 500);
        }
    }

    /**
     * ==================== LOAD CATEGORIES ====================
     */
    private function loadCategories()
    {
        return Category::select('id', 'category_name')
            ->orderBy('category_name', 'asc')
            ->get()
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->category_name ?? 'Unknown'
            ]);
    }

    /**
     * ==================== LOAD CURRENCIES ====================
     */
    private function loadCurrencies()
    {
        return TakaandDollarsigend::select('id', 'dollarsigned')
            ->orderBy('dollarsigned', 'asc')
            ->get()
            ->map(fn ($cur) => [
                'id' => $cur->id,
                'sign' => $cur->dollarsigned ?? 'BDT'
            ]);
    }

    /**
     * ==================== LOAD USER PAYMENT METHODS ====================
     * ✅ Load payment methods for a specific user
     */
   private function loadUserPaymentMethods($userId)
{
    return Agentpaymentmethod::select(
            'id',
            'agent_id',        // ✅ ADD
            'method_name',
            'method_number'
        )
        ->where('status', 'active')
        ->where('agent_id', $userId)
        ->orderBy('method_name', 'asc')
        ->get()
        ->map(fn ($method) => [
            'id' => $method->id,
            'agent_id' => $method->agent_id,      // ✅ ADD
            'method_name' => $method->method_name ?? 'Unknown',
            'method_number' => $method->method_number ?? ''
        ]);
}


    /**
     * ==================== LOAD ALL POSTS ====================
     * ✅ Load all approved posts with agent details and payment methods
     */
    private function loadPosts()
    {
        return Agentbuysellpost::with([
            'category:id,category_name',
            'agent:id,name,email,last_active_at',
            'dollarsign:id,dollarsigned',
            'agentamounts:id,agent_id,amount',
        ])
        ->where('status', 'approved')
        ->latest()
        ->get()
        ->map(function ($post) {
            return $this->parsePost($post);
        })
        ->filter()
        ->values();
    }

    /**
     * ==================== PARSE POST DATA ====================
     * ✅ Parse single post with all details including agent payment methods
     */
    private function parsePost($post)
    {
        try {
            $agentId = $post->agent_id;

            // ✅ DETERMINE POST TYPE
            $postType = $this->determinePostType($post);

            // ✅ CHECK AGENT VERIFICATION
            $isVerified = $this->isAgentVerified($agentId);

            // ✅ CHECK AGENT ONLINE STATUS
            $isOnline = $this->isAgentOnline($post->agent);

            // ✅ LOAD AGENT'S PAYMENT METHODS (THIS IS THE KEY!)
            $agentPaymentMethods = $this->loadUserPaymentMethods($agentId);

            Log::info("💳 Post #{$post->id} | Agent #{$agentId} ({$post->agent->name}) | Payment Methods: {$agentPaymentMethods->count()}");

            // ✅ CALCULATE ORDER STATISTICS
            $orderStats = $this->calculateOrderStats($agentId);

            // ✅ CALCULATE HOLD BALANCE
            $holdBalance = $this->calculateHoldBalance($agentId);

            // ✅ PARSE PHOTOS
            $photos = $this->parsePhotos($post);

            // ✅ PARSE PAYMENT NAMES
            $paymentNames = $this->parsePaymentNames($post);

            // ✅ GET CURRENCY INFO
            $currencySign = $post->dollarsign->dollarsigned ?? 'BDT';
            $rateBalance = (float) ($post->rate_balance ?? 0);

            return [
                'id' => $post->id,
                'post_type' => $postType,
                'trade_limit' => (float) ($post->trade_limit ?? 0),
                'trade_limit_two' => (float) ($post->trade_limit_two ?? 0),
                'available_balance' => (float) ($post->available_balance ?? 0),
                'rate_balance' => $rateBalance,
                'duration' => $post->duration ?? 30,
                'status' => $post->status ?? 'approved',
                'photo' => $photos,
                'payment_names' => $paymentNames,
                'created_at' => $post->created_at
                    ? $post->created_at->format('Y-m-d H:i:s')
                    : now()->format('Y-m-d H:i:s'),

                // ✅ AGENT INFO WITH PAYMENT METHODS
                'agent' => [
                    'id' => $post->agent->id ?? 0,
                    'name' => $post->agent->name ?? 'Unknown Agent',
                    'email' => $post->agent->email ?? '',
                    'is_verified' => (bool) $isVerified,
                    'is_online' => (bool) $isOnline,
                    'last_active_at' => $post->agent->last_active_at ?? null,
                    'total_balance' => number_format($holdBalance, 2, '.', ''),
                    // ✅ THIS IS THE MOST IMPORTANT PART!
                    'payment_methods' => $agentPaymentMethods
                ],

                // ✅ CATEGORY INFO
                'category' => [
                    'id' => $post->category->id ?? 0,
                    'name' => $post->category->category_name ?? 'Unknown'
                ],

                // ✅ LIMITS
                'limits' => [
                    'min' => (float) ($post->trade_limit ?? 0),
                    'max' => (float) ($post->trade_limit_two ?? 0)
                ],

                // ✅ AMOUNTS
                'amounts' => [
                    'hold' => number_format($holdBalance, 2, '.', ''),
                    'currency' => $currencySign
                ],

                // ✅ PAYMENT INFO
                'payment' => [
                    'currency_sign' => $currencySign,
                ],

                // ✅ ORDER STATS
                'orders' => [
                    'completed_deposit' => $orderStats['completed_deposit'],
                    'completed_withdraw' => $orderStats['completed_withdraw'],
                    'total' => $orderStats['total'],
                    'success_rate' => $orderStats['success_rate']
                ]
            ];

        } catch (\Exception $e) {
            Log::error("❌ Failed to parse post #{$post->id}: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * ==================== DETERMINE POST TYPE ====================
     */
    private function determinePostType($post)
    {
        $categoryName = strtolower(trim($post->category->category_name ?? ''));

        $postType = 'other';

        if (str_contains($categoryName, 'deposit') || str_contains($categoryName, 'buy')) {
            $postType = 'deposit';
        } elseif (str_contains($categoryName, 'withdraw') || str_contains($categoryName, 'sell')) {
            $postType = 'withdraw';
        }

        return $postType;
    }

    /**
     * ==================== CHECK AGENT VERIFICATION ====================
     */
    private function isAgentVerified($agentId)
    {
        $isVerified = Agentkyc::where('user_id', $agentId)
            ->where('status', 'approved')
            ->exists();

        Log::info("✅ Agent #{$agentId} | Is Verified: " . ($isVerified ? 'YES ✔' : 'NO ✘'));

        return $isVerified;
    }

    /**
     * ==================== CHECK AGENT ONLINE STATUS ====================
     */
    private function isAgentOnline($agent)
    {
        if (!$agent || !$agent->last_active_at) {
            Log::warning("⚠️ Agent #{$agent->id} | No last_active_at - Marking as OFFLINE");
            return false;
        }

        try {
            $lastActiveTime = Carbon::parse($agent->last_active_at);
            $fiveMinutesAgo = Carbon::now()->subMinutes(5);
            $isOnline = $lastActiveTime->greaterThan($fiveMinutesAgo);

            Log::info("✅ Agent #{$agent->id} | Name: {$agent->name} | Last Active: {$agent->last_active_at} | Is Online: " . ($isOnline ? 'YES ✔' : 'NO ✘'));

            return $isOnline;

        } catch (\Exception $e) {
            Log::error("❌ Agent #{$agent->id} | Failed to parse last_active_at: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * ==================== CALCULATE ORDER STATISTICS ====================
     */
    private function calculateOrderStats($agentId)
    {
        $completedDeposit = Userdepositerequest::where('agent_id', $agentId)
            ->where('status', 'completed')
            ->count();

        $completedWithdraw = UserWidhrawrequest::where('agent_id', $agentId)
            ->where('status', 'completed')
            ->count();

        $totalOrders = $completedDeposit + $completedWithdraw;
        $successRate = $totalOrders > 0 ? 100 : 0;

        return [
            'completed_deposit' => $completedDeposit,
            'completed_withdraw' => $completedWithdraw,
            'total' => $totalOrders,
            'success_rate' => $successRate
        ];
    }

    /**
     * ==================== CALCULATE HOLD BALANCE ====================
     */
    private function calculateHoldBalance($agentId)
    {
        return AgentDeposite::where('agent_id', $agentId)
            ->where('status', 'approved')
            ->sum('amount');
    }

    /**
     * ==================== PARSE PHOTOS ====================
     */
    private function parsePhotos($post)
    {
        $photos = [];
        $uploadPath = public_path('uploads/agentbuysellpost/');

        // Process photo field
        if (!empty($post->photo)) {
            $photos = array_merge($photos, $this->processPhotoField($post->photo, $uploadPath, $post->id));
        }

        // Process new_photo field
        if (!empty($post->new_photo)) {
            $photos = array_merge($photos, $this->processPhotoField($post->new_photo, $uploadPath, $post->id));
        }

        return array_values(array_unique($photos));
    }

    /**
     * ==================== PROCESS PHOTO FIELD ====================
     */
    private function processPhotoField($photoData, $uploadPath, $postId)
    {
        $photos = [];

        // Convert string to array if needed
        if (is_string($photoData)) {
            $decoded = json_decode($photoData, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $photoData = $decoded;
            } else {
                $photoData = [$photoData];
            }
        }

        // Process each photo
        if (is_array($photoData)) {
            foreach ($photoData as $img) {
                $url = $this->processImage($img, $uploadPath, $postId);
                if ($url) {
                    $photos[] = $url;
                }
            }
        }

        return $photos;
    }

    /**
     * ==================== PROCESS SINGLE IMAGE ====================
     */
    private function processImage($imageName, $uploadPath, $postId)
    {
        if (empty($imageName) || !is_string($imageName)) {
            return null;
        }

        $filename = trim($imageName);
        $filename = str_replace('uploads/agentbuysellpost/', '', $filename);
        $filename = ltrim($filename, '/');

        $filePath = $uploadPath . $filename;

        // Check if file exists
        if (!File::exists($filePath)) {
            Log::warning("⚠️ Post #{$postId} | File not found: {$filePath}");
            return null;
        }

        // Check file size (max 5MB)
        $fileSize = File::size($filePath);
        if ($fileSize > 5 * 1024 * 1024) {
            Log::warning("⚠️ Post #{$postId} | File too large: {$filename} ({$fileSize} bytes)");
            return null;
        }

        return url('uploads/agentbuysellpost/' . $filename);
    }

    /**
     * ==================== PARSE PAYMENT NAMES ====================
     */
    private function parsePaymentNames($post)
    {
        $paymentNames = [];

        if (empty($post->payment_name)) {
            return $paymentNames;
        }

        // Convert to array
        $paymentNamesRaw = is_string($post->payment_name)
            ? explode(',', $post->payment_name)
            : (is_array($post->payment_name) ? $post->payment_name : [$post->payment_name]);

        // Clean and deduplicate
        foreach ($paymentNamesRaw as $name) {
            $trimmed = trim($name);
            if (!empty($trimmed) && !in_array($trimmed, $paymentNames)) {
                $paymentNames[] = $trimmed;
            }
        }

        return $paymentNames;
    }

    /**
     * ==================== GET HISTORY ====================
     * ✅ Get user's deposit and withdraw history
     */
    public function getHistory(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            // ✅ GET DEPOSIT HISTORY
            $deposits = Userdepositerequest::where('user_id', $user->id)
                ->with(['agent:id,name'])
                ->latest()
                ->get()
                ->map(function ($deposit) {
                    return [
                        'id' => $deposit->id,
                        'type' => 'deposit',
                        'amount' => $deposit->amount ?? '0.00',
                        'currency' => 'USDT',
                        'status' => $deposit->status ?? 'pending',
                        'agent_id' => $deposit->agent_id ?? 0,
                        'agent_name' => $deposit->agent->name ?? 'Unknown',
                        'transaction_id' => $deposit->transaction_id ?? '',
                        'sender_account' => $deposit->sender_account ?? '',
                        'created_at' => $deposit->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $deposit->updated_at->format('Y-m-d H:i:s'),
                    ];
                });

            // ✅ GET WITHDRAW HISTORY
            $withdraws = UserWidhrawrequest::where('user_id', $user->id)
                ->with(['agent:id,name'])
                ->latest()
                ->get()
                ->map(function ($withdraw) {
                    return [
                        'id' => $withdraw->id,
                        'type' => 'withdraw',
                        'amount' => $withdraw->amount ?? '0.00',
                        'currency' => 'USDT',
                        'status' => $withdraw->status ?? 'pending',
                        'agent_id' => $withdraw->agent_id ?? 0,
                        'agent_name' => $withdraw->agent->name ?? 'Unknown',
                        'transaction_id' => $withdraw->transaction_id ?? '',
                        'sender_account' => $withdraw->sender_account ?? '',
                        'created_at' => $withdraw->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $withdraw->updated_at->format('Y-m-d H:i:s'),
                    ];
                });

            // ✅ MERGE AND SORT BY DATE
            $history = $deposits->merge($withdraws)
                ->sortByDesc('created_at')
                ->values();

            Log::info("✅ History loaded for user #{$user->id}", [
                'deposits' => $deposits->count(),
                'withdraws' => $withdraws->count(),
                'total' => $history->count()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'History loaded successfully',
                'history' => $history,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('❌ History Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to load history. Please try again.'
            ], 500);
        }
    }

public function checkDepositStatus(Request $request)
{
    $user = Auth::user();

    $deposit = Userdepositerequest::where('user_id', $user->id)
        ->where('status', 'agent_confirmed')
        ->latest()
        ->first();

    if ($deposit) {
        return response()->json([
            'success' => true,
            'message' => 'Deposit status retrieved',
            'status' => $deposit->status,
            'deposit_id' => $deposit->id,
            'amount' => $deposit->amount,
            'agent_id' => $deposit->agent_id,  // ✅ ADD THIS LINE
            'can_cancel' => false
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'No pending deposit found',
        'status' => null,
        'deposit_id' => null,
        'agent_id' => null
    ]);
}

}
