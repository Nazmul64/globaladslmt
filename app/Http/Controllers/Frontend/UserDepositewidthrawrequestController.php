<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Userdepositerequest;
use App\Models\UserWidhrawrequest;
use App\Models\Agentbuysellpost;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class UserDepositewidthrawrequestController extends Controller
{
    // Deposit / Withdraw request
    public function userwidhraw_request(Request $request)
    {
        $request->validate([
            'type' => 'required|in:deposit,withdraw',
            'agent_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $user_id = Auth::id();

        $data = [
            'user_id' => $user_id,
            'agent_id' => $request->agent_id,
            'amount' => $request->amount,
            'status' => 'pending',
        ];

        if ($request->type === 'deposit') {
            Userdepositerequest::create($data);
        } else {
            UserWidhrawrequest::create($data);
        }

        return redirect()->back()->with('success', ucfirst($request->type) . ' request sent successfully!');
    }

    // Ajax polling: check if latest deposit accepted by agent
    public function checkDepositStatus()
    {
        $depositRequest = Userdepositerequest::where('user_id', Auth::id())
            ->latest()
            ->first();

        if ($depositRequest && $depositRequest->status === 'agent_confirmed') {
            return response()->json([
                'status' => 'agent_confirmed',
                'deposit_id' => $depositRequest->id
            ]);
        }

        return response()->json(['status' => 'pending']);
    }

    // Buy/Sell posts page
    public function buysellpost()
    {
        $categories = Category::all();
        $all_agentbuysellpost = Agentbuysellpost::with('category', 'user')
            ->where('status', 'approved')
            ->get();

        return view('frontend.buyandsellpost.index', compact('categories', 'all_agentbuysellpost'));
    }
}
