<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Userdepositerequest;
use App\Models\User;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentracceptuserandDeposite extends Controller
{
    /**
     * Show all deposit requests sent to the logged-in agent.
     */
    public function agentDepositRequests()
    {
        $agent_id = Auth::id();

        $requests = Userdepositerequest::where('agent_id', $agent_id)
            ->whereIn('status', ['pending', 'user_submitted'])
            ->with('user')
            ->latest()
            ->get();

        return view('agent.userdepositewidhrawaccept.index', compact('requests'));
    }

    /**
     * Agent accepts a user deposit request.
     */
    public function acceptDepositRequest($id)
    {
        $request = Userdepositerequest::findOrFail($id);

        // update status
        $request->update(['status' => 'agent_confirmed']);

        return back()->with('success', 'Deposit request accepted. Waiting for user payment details.');
    }

    /**
     * User submits deposit payment information after agent confirmation.
     */
    public function userSubmitDeposit(Request $request, $id)
    {
        $request->validate([
            'transaction_id' => 'required|string|max:255',
            'sender_account' => 'required|string|max:255',
            'photo' => 'required|image|max:2048',
        ]);

        $deposit = Userdepositerequest::findOrFail($id);

        // upload payment screenshot
        $photoName = time() . '.' . $request->photo->extension();
        $request->photo->move(public_path('uploads/deposit'), $photoName);

        // update deposit info
        $deposit->update([
            'transaction_id' => $request->transaction_id,
            'sender_account' => $request->sender_account,
            'photo' => $photoName,
            'status' => 'user_submitted',
        ]);

        return redirect()->back()->with('success', 'Payment information submitted successfully! Waiting for agent approval.');
    }

    /**
     * Agent finally confirms payment and updates balances.
     */
    public function finalDepositConfirm($id)
    {
        $deposit = Userdepositerequest::findOrFail($id);

        if ($deposit->status !== 'user_submitted') {
            return back()->with('error', 'Invalid deposit status!');
        }

        // find user and agent
        $user = User::findOrFail($deposit->user_id);
        $agent = User::findOrFail($deposit->agent_id); // assuming agent also in users table

        // check agent balance
        if ($agent->balance < $deposit->amount) {
            return back()->with('error', 'Agent does not have enough balance!');
        }

        // update balances
        $agent->balance -= $deposit->amount;
        $user->balance += $deposit->amount;

        $agent->save();
        $user->save();

        // update deposit status
        $deposit->update(['status' => 'completed']);

        return back()->with('success', 'Deposit completed successfully!');
    }
}
