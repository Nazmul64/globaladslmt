<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\UserWidhrawrequest;
use Illuminate\Support\Facades\Auth;

class AgentWidhrawrequestacceptController extends Controller
{
    /**
     * Show withdraw requests for logged-in agent
     */
    public function agentwidhrawRequests()
    {
        $requests = UserWidhrawrequest::with('user')
            ->where('agent_id', Auth::id())
            ->orderByRaw("FIELD(status, 'pending', 'agent_confirmed', 'completed', 'rejected')")
            ->latest()
            ->paginate(5);

        return view('agent.userwidhrawrequestaccept.index', compact('requests'));
    }

    /**
     * Accept withdraw request
     */
    public function acceptagentwidhrawRequest($id)
    {
        $request = UserWidhrawrequest::where('id', $id)
            ->where('agent_id', Auth::id())
            ->firstOrFail();

        if ($request->status !== 'pending') {
            return back()->with('error', 'এই রিকোয়েস্ট আর একসেপ্ট করা যাবে না।');
        }

        $request->update([
            'status' => 'agent_confirmed'
        ]);

        return back()->with('success', 'রিকোয়েস্ট একসেপ্ট হয়েছে। এখন ইউজার রিলিজ দিবে।');
    }

    /**
     * Reject withdraw request
     */
    public function agentRejected($id)
    {
        $request = UserWidhrawrequest::where('id', $id)
            ->where('agent_id', Auth::id())
            ->firstOrFail();

        if (in_array($request->status, ['completed', 'rejected'])) {
            return back()->with('info', 'এই রিকোয়েস্ট আর পরিবর্তন করা যাবে না।');
        }

        $request->update([
            'status' => 'rejected'
        ]);

        return back()->with('success', 'রিকোয়েস্ট রিজেক্ট করা হয়েছে।');
    }
}
