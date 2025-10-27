<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agentcommissonsetup;
use App\Models\AgentDeposite;
use App\Models\Userdepositerequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgentracceptuserandDeposite extends Controller
{
    /**
     * Show all deposit requests sent to the logged-in agent.
     */
public function agentDepositRequests()
{
    $agent_id = Auth::id();

    // এজেন্টের সব pending এবং user_submitted রিকোয়েস্টগুলো ফেচ করব
    $requests = Userdepositerequest::where('agent_id', $agent_id)
        ->whereIn('status', ['pending', 'user_submitted'])
        ->with('user') // ইউজার রিলেশন সহ আনব
        ->latest()
        ->get();

    return view('agent.userdepositewidhrawaccept.index', compact('requests'));
}


    /**
     * Agent accepts a user deposit request (pending -> agent_confirmed)
     */
    public function acceptDepositRequest($id)
    {
        $request = Userdepositerequest::findOrFail($id);

        if ($request->agent_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->update(['status' => 'agent_confirmed']);

        return back()->with('success', 'Deposit request accepted. Waiting for user payment details.');
    }

    /**
     * Agent confirms user payment (user_submitted -> completed)
     * Deduct from agent balance, add to user balance
     */

public function finalDepositConfirm($id)
{
    $deposit = Userdepositerequest::findOrFail($id);

    if ($deposit->status !== 'user_submitted') {
        return back()->with('error', 'Invalid deposit status!');
    }

    DB::beginTransaction();

    try {
        $user = User::findOrFail($deposit->user_id);
        $agentDeposit = AgentDeposite::where('agent_id', $deposit->agent_id)->first();

        if (!$agentDeposit) {
            DB::rollBack();
            return back()->with('error', 'Agent deposit record not found!');
        }

        // ✅ Agent-এর balance পর্যাপ্ত আছে কি না চেক করো
        if ($agentDeposit->amount < $deposit->amount) {
            DB::rollBack();
            return back()->with('error', 'Agent does not have enough balance!');
        }

        // 🧮 Commission setup load
        $commissionSetup = Agentcommissonsetup::where('status', 1)->latest()->first();
        $agentCommission = 0;
        $adminCommission = 0;

        if ($commissionSetup) {
            if ($deposit->type === 'deposit') {
                // Deposit commission (এজেন্ট পাবে)
                $agentCommission = $commissionSetup->commission_type === 'percent'
                    ? ($deposit->amount * $commissionSetup->deposit_agent_commission) / 100
                    : $commissionSetup->deposit_agent_commission;

                $adminCommission = 0;
            } elseif ($deposit->type === 'withdraw') {
                // Withdraw commission (এজেন্ট+এডমিন ভাগ)
                $totalCommission = $commissionSetup->commission_type === 'percent'
                    ? ($deposit->amount * $commissionSetup->withdraw_total_commission) / 100
                    : $commissionSetup->withdraw_total_commission;

                $agentCommission = $totalCommission / 2;
                $adminCommission = $totalCommission / 2;
            }
        }

        // ✅ Balance Update Logic
        $agentOldBalance = $agentDeposit->amount;

        // Agent ফান্ড থেকে ইউজারের টাকা কমাও
        $agentDeposit->amount = $agentOldBalance - $deposit->amount;

        // Agent কমিশন যুক্ত করো
        $agentDeposit->amount += $agentCommission;

        // User ব্যালেন্স বাড়াও
        $user->balance += $deposit->amount;

        // Save all updates
        $agentDeposit->save();
        $user->save();

        // ✅ Update deposit record
        $deposit->update([
            'status' => 'completed',
            'agent_commission' => $agentCommission,
            'admin_commission' => $adminCommission,
        ]);

        DB::commit();

        return back()->with('success', "Deposit completed successfully! Agent earned $agentCommission commission.");

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error: ' . $e->getMessage());
    }
}






}
