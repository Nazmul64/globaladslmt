<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agentbuysellpost;
use App\Models\AgentDeposite;
use App\Models\Category;
use App\Models\Kyc;
use App\Models\TakaandDollarsigend;
use App\Models\TakaandDollarsigned;
use App\Models\Userdepositerequest;
use App\Models\UserWidhrawrequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class P2PpostController extends Controller
{
   public function buysellpost()
{
    try {

        // ================= AUTH CHECK ====================
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized Request'
            ], 401);
        }

        // ================= CATEGORY LIST =================
        $categories = Category::select('id', 'category_name')
            ->orderBy('category_name', 'asc')
            ->get()
            ->map(fn ($cat) => [
                'id'   => $cat->id,
                'name' => $cat->category_name ?? ''
            ]);

        // ================= CURRENCY LIST =================
        $currencies = TakaandDollarsigend::select('id', 'dollarsigned')
            ->orderBy('dollarsigned', 'asc')
            ->get()
            ->map(fn ($cur) => [
                'id'   => $cur->id,
                'sign' => $cur->dollarsigned ?? ''
            ]);

        // ================= POSTS ==========================
        $posts = Agentbuysellpost::with([
            'category:id,category_name',
            'agent:id,name,email,last_active_at',
            'dollarsign:id,dollarsigned',
            'agentamounts:id,agent_id,amount',
            'deposits' => fn ($q) => $q->where('status', 'approved')
        ])
            ->where('status', 'approved')
            ->latest()
            ->get()
            ->map(function ($post) {

                $categoryName = strtolower(trim($post->category->category_name ?? ''));

                // POST TYPE SET
                $postType = str_contains($categoryName, 'deposit') || str_contains($categoryName, 'buy')
                    ? 'deposit'
                    : (str_contains($categoryName, 'withdraw') || str_contains($categoryName, 'sell')
                        ? 'withdraw'
                        : 'other');

                $agentId = $post->agent_id;

                // VERIFY CHECK
                $isVerified = Kyc::where('user_id', $agentId)
                    ->where('status', 'approved')
                    ->exists();

                // ONLINE CHECK
                $lastActive = $post->agent->last_active_at ?? null;
                $isOnline = $lastActive && Carbon::parse($lastActive)->gt(now()->subMinutes(5));

                // COMPLETED STATS
                $completedDeposit  = Userdepositerequest::where('agent_id', $agentId)
                    ->where('status', 'completed')
                    ->count();

                $completedWithdraw = UserWidhrawrequest::where('agent_id', $agentId)
                    ->where('status', 'completed')
                    ->count();

                $totalOrders = $completedDeposit + $completedWithdraw;

                $successRate = $totalOrders > 0
                    ? round(($totalOrders / max($totalOrders, 1)) * 100, 1)
                    : 0;

                // HOLD BALANCE
                $holdBalance = AgentDeposite::where('agent_id', $agentId)
                    ->where('status', 'approved')
                    ->sum('amount');

                // ============== IMAGE URL FIX ==============
                $photos = [];
                if (!empty($post->photo)) {
                    $decoded = is_string($post->photo) ? json_decode($post->photo, true) : $post->photo;

                    if (is_array($decoded)) {
                        foreach ($decoded as $img) {
                            if (!empty($img)) {
                                $photos[] = asset('uploads/agentbuysellpost/' . $img);
                            }
                        }
                    }
                }

                $currencySign = $post->dollarsign->dollarsigned ?? 'BDT';

                return [
                    'id'                => $post->id,
                    'post_type'         => $postType,
                    'trade_limit'       => (float) ($post->trade_limit ?? 0),
                    'trade_limit_two'   => (float) ($post->trade_limit_two ?? 0),
                    'available_balance' => (float) ($post->available_balance ?? 0),
                    'duration'          => $post->duration,
                    'payment_name'      => $post->payment_name ?? '',
                    'status'            => $post->status,
                    'photo'             => $photos, // UPDATED WITH IMAGE URL
                    'created_at'        => $post->created_at->format('Y-m-d H:i:s'),

                    'agent' => $post->agent ? [
                        'id'            => $post->agent->id,
                        'name'          => $post->agent->name ?? '',
                        'email'         => $post->agent->email ?? '',
                        'is_verified'   => (bool) $isVerified,
                        'is_online'     => (bool) $isOnline,
                        'last_active_at'=> $lastActive,
                        'total_balance' => number_format($holdBalance, 2, '.', '')
                    ] : null,

                    'category' => $post->category ? [
                        'id'   => $post->category->id,
                        'name' => $post->category->category_name
                    ] : null,

                    'amounts' => [
                        'hold'     => number_format($holdBalance, 2, '.', ''),
                        'currency' => $currencySign
                    ],

                    'payment' => [
                        'currency_sign' => $currencySign,
                        'payment_name'  => $post->payment_name ?? 'Bank Transfer',
                    ],

                    'orders' => [
                        'completed_deposit'  => $completedDeposit,
                        'completed_withdraw' => $completedWithdraw,
                        'total'              => $totalOrders,
                        'success_rate'       => (float) $successRate
                    ]
                ];
            });

        return response()->json([
            'status'      => true,
            'message'     => 'Posts loaded successfully',
            'categories'  => $categories,
            'currencies'  => $currencies,
            'posts'       => $posts,
        ], 200);

    } catch (\Throwable $e) {
        Log::error('P2PpostController::buysellpost ERROR', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString()
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong!',
            'error'   => config('app.debug') ? $e->getMessage() : 'Server error',
            'line'    => config('app.debug') ? $e->getLine() : null
        ], 500);
    }
}

}
