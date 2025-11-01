<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agentcommissonsetup;
use App\Models\UserWidthraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminuserdepositeApprovedController extends Controller
{
    /**
     * Show all pending withdraw requests
     */
    public function admin_widthraw_approvedindex()
    {
        // ডাটাবেজ থেকে withdraw commission রেট ডায়নামিকভাবে নাও
        $commission = Agentcommissonsetup::where('status', 1)->value('withdraw_total_commission') ?? 0;

        $user_widthraw_request = UserWidthraw::with(['user', 'payment_name'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('admin.userwidthrawapproved.index', compact('user_widthraw_request', 'commission'));
    }

    /**
     * Approve withdraw request dynamically (based on DB commission)
     */
    public function approveWithdraw($id)
    {
        DB::beginTransaction();

        try {
            $withdraw = UserWidthraw::with('user')->findOrFail($id);

            if ($withdraw->status !== 'pending') {
                return redirect()->back()->with('error', 'This request is not pending.');
            }

            // ✅ ডাটাবেজ থেকে ডায়নামিক কমিশন %
            $commission_percent = Agentcommissonsetup::where('status', 1)->value('withdraw_total_commission') ?? 0;

            $user = $withdraw->user;
            $amount = $withdraw->amount;

            // কমিশন ও ফাইনাল এমাউন্ট হিসাব
            $fee = ($amount * $commission_percent) / 100;
            $amount_after_fee = $amount - $fee;

            // ইউজারের ব্যালেন্স চেক করো
            if ($user->balance < $amount) {
                return redirect()->back()->with('error', 'User does not have sufficient balance.');
            }

            // ইউজারের ব্যালেন্স থেকে টাকা কাটা
            $user->balance -= $amount;
            $user->save();

            // শুধু স্ট্যাটাস আপডেট করো
            $withdraw->status = 'approved';
            $withdraw->save();

            DB::commit();

            return redirect()->back()->with(
                'success',
                "Withdraw approved successfully. Commission: {$commission_percent}%, User will receive {$amount_after_fee} USD after fee."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Reject withdraw request
     */
    public function rejectWithdraw($id)
    {
        $withdraw = UserWidthraw::findOrFail($id);

        if ($withdraw->status !== 'pending') {
            return redirect()->back()->with('error', 'This request is not pending.');
        }

        $withdraw->status = 'rejected';
        $withdraw->save();

        return redirect()->back()->with('info', 'Withdraw request rejected successfully.');
    }

    /**
     * Approved withdraw requests list
     */
    public function approved_list()
    {
        $approved_requests = UserWidthraw::with(['user', 'payment_name'])
            ->where('status', 'approved')
            ->latest()
            ->get();

        return view('admin.userwidthrawapproved.approved', compact('approved_requests'));
    }

    /**
     * Rejected withdraw requests list
     */
    public function rejected_list()
    {
        $rejected_requests = UserWidthraw::with(['user', 'payment_name'])
            ->where('status', 'rejected')
            ->latest()
            ->get();

        return view('admin.userwidthrawapproved.rejected', compact('rejected_requests'));
    }
}
