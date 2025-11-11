<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agentbuysellpost;
use App\Models\Category;
use App\Models\Userdepositerequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class P2PshowforuserController extends Controller
{

public function buysellpost(Request $request)
{
    try {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        // Get all categories
        $categories = Category::select('id', 'category_name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->category_name, // Rename field for frontend
                ];
            });

        // Get user balance
        $balance = $user->balance ?? 0;
        $agent_id = $user->agent_id;

        // Get all approved posts with category and agent details
        $all_agentbuysellpost = Agentbuysellpost::with(['category', 'agent'])
            ->where('status', 'approved')
            ->select([
                'id',
                'agent_id',
                'category_id',
                'trade_limit',
                'trade_limit_two',
                'available_balance',
                'duration',
                'payment_name',
                'status',
                'photo',
                'created_at'
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'agent_id' => $post->agent_id,
                    'category_id' => $post->category_id,
                    'trade_limit' => $post->trade_limit,
                    'trade_limit_two' => $post->trade_limit_two,
                    'available_balance' => $post->available_balance,
                    'duration' => $post->duration,
                    'payment_name' => $post->payment_name,
                    'status' => $post->status,
                    'photo' => $post->photo,
                    'created_at' => $post->created_at,
                    'agent' => $post->agent ? [
                        'id' => $post->agent->id,
                        'name' => $post->agent->name,
                        'email' => $post->agent->email ?? null,
                    ] : null,
                    'category' => $post->category ? [
                        'id' => $post->category->id,
                        'name' => $post->category->category_name,
                    ] : null,
                ];
            });

        // Get latest deposit request
        $depositRequest = Userdepositerequest::where('user_id', $user->id)
            ->select('id', 'user_id', 'amount', 'status', 'created_at')
            ->latest()
            ->first();

        // Log for debugging
        Log::info('P2P Data Loaded', [
            'user_id' => $user->id,
            'categories_count' => $categories->count(),
            'posts_count' => $all_agentbuysellpost->count(),
            'balance' => $balance
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Data loaded successfully',
            'categories' => $categories,
            'balance' => $balance,
            'agent_id' => $agent_id,
            'posts' => $all_agentbuysellpost,
            'latest_deposit_request' => $depositRequest,
        ], 200);

    } catch (\Exception $e) {
        Log::error('P2P Data Load Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Failed to load data: ' . $e->getMessage(),
            'categories' => [],
            'balance' => 0,
            'posts' => [],
            'latest_deposit_request' => null,
        ], 500);
    }
}
}
