<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\UserWidhrawrequest;
use App\Models\User;
use App\Models\AgentDeposite;
use App\Models\Agentcommissonsetup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UserWidhrawrequestAgentController extends Controller
{
    // 1️⃣ Agent Accept Withdraw Request
    public function acceptWithdrawRequest($id)
    {
        $withdraw = UserWidhrawrequest::findOrFail($id);

        if ($withdraw->agent_id != Auth::id()) {
            return back()->with('error','Unauthorized');
        }

        $withdraw->update(['status'=>'agent_confirmed']);

        return back()->with('success','Withdraw accepted. User can now release.');
    }

    // 2️⃣ Polling for user: check if agent confirmed
    public function checkWithdrawStatus()
    {
        $withdraw = UserWidhrawrequest::where('user_id', Auth::id())
            ->where('status', 'agent_confirmed')
            ->latest()
            ->first();

        if ($withdraw) {
            return response()->json(['status'=>'agent_confirmed', 'withdraw_id'=>$withdraw->id]);
        }

        return response()->json(['status'=>'pending']);
    }

    // 3️⃣ User releases the withdraw (no transaction_id / photo)
    public function userSubmitWithdraw(Request $request, $id)
    {
        $withdraw = UserWidhrawrequest::findOrFail($id);

        // Ensure only confirmed withdraws can be released
        if ($withdraw->status !== 'agent_confirmed') {
            return response()->json(['success'=>false,'message'=>'Withdraw not confirmed by agent']);
        }

        DB::beginTransaction();
        try {
            $user = User::findOrFail($withdraw->user_id);
            $agentDeposit = AgentDeposite::firstOrCreate(
                ['agent_id' => $withdraw->agent_id],
                ['amount' => 0]
            );

            // Check user balance
            if ($user->balance < $withdraw->amount) {
                DB::rollBack();
                return response()->json(['success'=>false,'message'=>'Insufficient balance']);
            }

            // Commission setup
            $commissionSetup = Agentcommissonsetup::where('status', 1)->latest()->first();
            $agentCommission = $adminCommission = 0;

            if ($commissionSetup) {
                $totalCommission = $commissionSetup->commission_type == 'percent'
                    ? ($withdraw->amount * $commissionSetup->withdraw_total_commission) / 100
                    : $commissionSetup->withdraw_total_commission;

                $agentCommission = $totalCommission / 2;
                $adminCommission = $totalCommission / 2;
            }

            // Update balances
            $user->balance -= $withdraw->amount;
            $agentDeposit->amount += ($withdraw->amount + $agentCommission);

            $user->save();
            $agentDeposit->save();

            // Update withdraw info
            $withdraw->status = 'completed';
            $withdraw->agent_commission = $agentCommission;
            $withdraw->admin_commission = $adminCommission;
            $withdraw->save();

            DB::commit();

            return response()->json(['success'=>true,'message'=>'Withdraw released successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }
}
