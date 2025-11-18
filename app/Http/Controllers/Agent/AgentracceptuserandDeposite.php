<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agentcommissonsetup;
use App\Models\AgentDeposite;
use App\Models\Userdepositerequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AgentracceptuserandDeposite extends Controller
{
    // Show deposit requests
    public function agentDepositRequests(Request $request)
    {
        $agentId = Auth::id();

        // Show rejected only if requested
        if ($request->has('rejected') && $request->rejected == 1) {
            $requests = Userdepositerequest::where('agent_id', $agentId)
                ->where('status', 'rejected')
                ->with('user:id,name')
                ->orderBy('id', 'desc')
                ->paginate(5)
                ->withQueryString();
        } else {
            // Pending/User Submitted first
            $requests = Userdepositerequest::where('agent_id', $agentId)
                ->with('user:id,name')
                ->orderByRaw("CASE WHEN status IN ('pending','user_submitted') THEN 0 ELSE 1 END ASC, id DESC")
                ->paginate(5)
                ->withQueryString();
        }

        return view('agent.userdepositewidhrawaccept.index', compact('requests'));
    }

    // Accept deposit
    public function acceptDepositRequest($id)
    {
        $request = Userdepositerequest::findOrFail($id);

        if ($request->agent_id !== Auth::id()) return back()->with('error','Unauthorized action.');
        if ($request->status !== 'pending') return back()->with('error','Request is not pending.');

        $agentWallet = AgentDeposite::firstOrCreate(['agent_id' => $request->agent_id]);
        if ($agentWallet->amount < $request->amount) return back()->with('error','আপনার ওয়ালেটে পর্যাপ্ত ব্যালেন্স নেই!');

        $request->update(['status' => 'agent_confirmed']);
        return back()->with('success','Request accepted. Waiting for user payment proof.');
    }

    // Reject deposit
    public function agentRejected($id)
    {
        $request = Userdepositerequest::findOrFail($id);
        if ($request->agent_id !== Auth::id()) return back()->with('error','Unauthorized action.');
        if (in_array($request->status,['completed','rejected'])) return back()->with('info','This request cannot be changed.');

        $request->update(['status'=>'rejected']);
        return back()->with('success','Deposit request rejected successfully.');
    }

    // Final confirm
    public function finalDepositConfirm($id)
    {
        $deposit = Userdepositerequest::findOrFail($id);
        if ($deposit->status !== 'user_submitted') return back()->with('error','User has not submitted payment info yet.');

        DB::beginTransaction();
        try {
            $user = User::findOrFail($deposit->user_id);
            $agentWallet = AgentDeposite::firstOrCreate(['agent_id'=>$deposit->agent_id]);
            if ($agentWallet->amount < $deposit->amount) { DB::rollBack(); return back()->with('error','Insufficient balance!'); }

            $commissionSetup = Agentcommissonsetup::where('status',1)->latest()->first();
            $agentCommission = 0;

            if ($commissionSetup && $deposit->type==='deposit') {
                $agentCommission = $commissionSetup->commission_type==='percent'
                    ? ($deposit->amount * $commissionSetup->deposit_agent_commission)/100
                    : $commissionSetup->deposit_agent_commission;
            }

            $agentWallet->amount = ($agentWallet->amount - $deposit->amount) + $agentCommission;
            $user->balance += $deposit->amount;

            $agentWallet->save();
            $user->save();

            $deposit->update([
                'status'=>'completed',
                'agent_commission'=>$agentCommission,
                'admin_commission'=>0
            ]);

            DB::commit();
            return back()->with('success',"Deposit completed! Agent earned $agentCommission commission.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error','Something went wrong! '.$e->getMessage());
        }
    }
}
