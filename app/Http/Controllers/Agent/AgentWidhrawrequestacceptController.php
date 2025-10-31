<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\UserWidhrawrequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgentWidhrawrequestacceptController extends Controller
{
    public function agentwidhrawRequests()
    {
        $requests = UserWidhrawrequest::with('user')
            ->where('agent_id', Auth::id())
            ->whereIn('status', ['pending', 'agent_confirmed'])
            ->latest()
            ->get();

        return view('agent.userwidhrawrequestaccept.index', compact('requests'));
    }

    /**
     * Agent accepts a withdraw request.
     */
    public function acceptagentwidhrawRequest($id)
    {
        $withdrawRequest = UserWidhrawrequest::findOrFail($id);

        if ($withdrawRequest->agent_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized request.');
        }

        if ($withdrawRequest->status !== 'pending') {
            return back()->with('error', 'This request cannot be accepted again.');
        }

        $withdrawRequest->update(['status' => 'agent_confirmed']);

        return back()->with('success', 'Withdraw request accepted successfully.');
    }

    /**
     * Agent releases withdraw amount to user.
     */
    public function releaseWithdraw($id)
    {
        $withdrawRequest = UserWidhrawrequest::with('user')->findOrFail($id);

        if ($withdrawRequest->agent_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized access.');
        }

        if ($withdrawRequest->status !== 'agent_confirmed') {
            return back()->with('error', 'Invalid status for release.');
        }

        DB::transaction(function () use ($withdrawRequest) {
            $user = $withdrawRequest->user;
            $agent = Auth::user();

            // Verify user balance
            if ($user->balance < $withdrawRequest->amount) {
                throw new \Exception('User has insufficient balance.');
            }

            // Deduct from user
            $user->decrement('balance', $withdrawRequest->amount);

            // Add to agent
            $agent->increment('balance', $withdrawRequest->amount);

            // Update withdraw status
            $withdrawRequest->update(['status' => 'completed']);
        });

        return back()->with('success', 'Withdraw released successfully.');
    }
}
