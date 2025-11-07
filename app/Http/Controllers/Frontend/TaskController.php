<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\PackageBuy;
use App\Models\AdSetting;
use App\Models\TaskCompletion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskController extends Controller
{
    /**
     * Display task page
     */
    public function index()
    {
        $user = Auth::user();

        // Get user's active package
        $packageBuy = PackageBuy::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('package')
            ->first();

        // Get ad settings
        $ads = Ad::first();

        // Count today's completed tasks
        $completedTasks = TaskCompletion::where('user_id', $user->id)
            ->whereDate('completed_at', Carbon::today())
            ->count();

        return view('frontend.addview.addview', compact('packageBuy', 'ads', 'completedTasks'));
    }

    /**
     * Show advertisement page
     */
    public function showAd(Request $request)
    {
        $user = Auth::user();
        $taskId = $request->get('task_id', 0);

        // Get package
        $packageBuy = PackageBuy::where('id', $taskId)
            ->where('user_id', $user->id)
            ->first();

        if (!$packageBuy) {
            return redirect()->route('task.index')->with('error', 'Invalid package');
        }

        // Check if daily limit reached
        $completedToday = TaskCompletion::where('user_id', $user->id)
            ->where('package_buy_id', $packageBuy->id)
            ->whereDate('completed_at', Carbon::today())
            ->count();

        if ($completedToday >= $packageBuy->daily_limit) {
            return redirect()->route('task.index')->with('warning', 'Daily task limit reached!');
        }

        // Get ad settings
        $ads = Ad::first();

        // Ad duration in seconds (default 30)
        $adDuration = $ads->ad_duration ?? 30;

        return view('frontend.task.ad-view', compact('ads', 'packageBuy', 'taskId', 'adDuration'));
    }

    /**
     * Complete task after ad view
     */
    public function complete(Request $request)
    {
        $user = Auth::user();
        $packageBuyId = $request->input('package_buy_id');

        // Get package
        $packageBuy = PackageBuy::where('id', $packageBuyId)
            ->where('user_id', $user->id)
            ->first();

        if (!$packageBuy) {
            return redirect()->route('task.index')->with('error', 'Invalid package');
        }

        // Check if daily limit reached
        $completedToday = TaskCompletion::where('user_id', $user->id)
            ->where('package_buy_id', $packageBuy->id)
            ->whereDate('completed_at', Carbon::today())
            ->count();

        if ($completedToday >= $packageBuy->daily_limit) {
            return redirect()->route('task.index')->with('warning', 'Daily task limit already reached!');
        }

        // Create task completion record
        TaskCompletion::create([
            'user_id' => $user->id,
            'package_buy_id' => $packageBuy->id,
            'coins_earned' => 1, // or calculate based on package
            'completed_at' => Carbon::now(),
            'ip_address' => $request->ip(),
        ]);

        // Update user balance
        $user->increment('balance', $packageBuy->daily_income / $packageBuy->daily_limit);

        // Update package buy total earned
        $packageBuy->increment('total_earned', $packageBuy->daily_income / $packageBuy->daily_limit);

        return redirect()->route('task.index')->with('success', 'Task completed successfully! Reward added to your balance.');
    }

    /**
     * Handle bonus reward from rewarded video ad
     */
    public function bonusReward(Request $request)
    {
        $user = Auth::user();
        $packageBuyId = $request->input('package_buy_id');

        // Get package
        $packageBuy = PackageBuy::find($packageBuyId);

        if (!$packageBuy || $packageBuy->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Invalid package']);
        }

        // Calculate bonus (e.g., 10% of daily income)
        $bonusAmount = ($packageBuy->daily_income * 0.1) / $packageBuy->daily_limit;

        // Add bonus to user balance
        $user->increment('balance', $bonusAmount);

        // Log bonus reward
        TaskCompletion::create([
            'user_id' => $user->id,
            'package_buy_id' => $packageBuy->id,
            'coins_earned' => 0,
            'bonus_earned' => $bonusAmount,
            'completed_at' => Carbon::now(),
            'ip_address' => $request->ip(),
            'type' => 'bonus_reward'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bonus reward added!',
            'bonus_amount' => $bonusAmount
        ]);
    }
}
