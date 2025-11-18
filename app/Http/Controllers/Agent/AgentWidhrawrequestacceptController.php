<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\UserWidhrawrequest;
use Illuminate\Support\Facades\Auth;

class AgentWidhrawrequestacceptController extends Controller
{
    /**
     * Display paginated withdraw requests for logged-in agent
     * Custom order: pending > agent_confirmed > completed > rejected
     */
    public function agentwidhrawRequests()
    {
        $requests = UserWidhrawrequest::with('user')
            ->where('agent_id', Auth::id())
            ->whereIn('status', ['pending', 'agent_confirmed', 'completed', 'rejected'])
            ->orderByRaw("FIELD(status, 'pending', 'agent_confirmed', 'completed', 'rejected')")
            ->latest()
            ->paginate(5); // 10 requests per page

        return view('agent.userwidhrawrequestaccept.index', compact('requests'));
    }

    /**
     * Accept a withdraw request (pending -> agent_confirmed)
     */
    public function acceptagentwidhrawRequest($id)
    {
        $request = UserWidhrawrequest::findOrFail($id);

        if ($request->agent_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized request.');
        }

        if ($request->status !== 'pending') {
            return back()->with('error', 'This request cannot be accepted again.');
        }

        $request->update(['status' => 'agent_confirmed']);

        return back()->with('success', 'Withdraw request accepted. Waiting for user release.');
    }

    /**
     * Reject a withdraw request
     */
    public function agentRejected($id)
    {
        $request = UserWidhrawrequest::findOrFail($id);

        if ($request->agent_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized action.');
        }

        if (in_array($request->status, ['completed', 'rejected'])) {
            return back()->with('info', 'This request cannot be changed.');
        }

        $request->update(['status' => 'rejected']);

        return back()->with('success', 'Withdraw request rejected successfully.');
    }
}
