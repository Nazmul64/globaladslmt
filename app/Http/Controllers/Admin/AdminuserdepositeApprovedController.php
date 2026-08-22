<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agentcommissonsetup;
use App\Models\UserWidthraw;
use Illuminate\Support\Facades\DB;

class AdminuserdepositeApprovedController extends Controller
{
    /**
     * Pending Withdraw List
     */
    public function admin_widthraw_approvedindex()
    {
        $commission = Agentcommissonsetup::where('status', 1)
            ->value('withdraw_total_commission') ?? 0;

        $user_widthraw_request = UserWidthraw::with(['user','payment_name'])
            ->where('status','pending')
            ->latest()
            ->get();

        return view('admin.userwidthrawapproved.index', compact('user_widthraw_request','commission'));
    }

    /**
     * APPROVE Withdraw
     */
    public function approveWithdraw($id)
    {
        DB::beginTransaction();
        try {
            $withdraw = UserWidthraw::with('user')->lockForUpdate()->findOrFail($id);

            if ($withdraw->status !== 'pending') {
                return back()->with('error','This request is not pending.');
            }

            $commissionPercent = Agentcommissonsetup::where('status',1)
                ->value('withdraw_total_commission') ?? 0;

            $amount = (float)$withdraw->amount;
            $commission = round(($amount * $commissionPercent)/100,2);

            $withdraw->update([
                'commission' => $commission,
                'status' => 'approved',
            ]);

            DB::commit();

            return back()->with('success',"Withdraw approved. Amount: {$amount}, Commission: {$commission}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error',$e->getMessage());
        }
    }

    /**
     * REJECT Withdraw
     * ❌ No balance cut or refund here
     */
    public function rejectWithdraw($id)
    {
        $withdraw = UserWidthraw::findOrFail($id);

        if ($withdraw->status !== 'pending') {
            return back()->with('error','This request is not pending.');
        }

        $withdraw->update(['status' => 'rejected']);

        return back()->with('info','Withdraw rejected successfully.');
    }
}
