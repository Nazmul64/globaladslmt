<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Deposite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmindepositeApprovedController extends Controller
{
    /**
     * Show Pending Deposits
     */
    public function admin_deposite_pending()
    {
        $deposite_list = Deposite::with('user')->where('status', 'pending')->latest()->get();
        return view('admin.depositeaproved.index', compact('deposite_list'));
    }

    /**
     * Approve Deposit
     */
    public function admin_deposite_approve($id)
    {
        try {
            DB::beginTransaction();

            $deposit = Deposite::find($id);

            if (!$deposit) {
                return redirect()->back()->with('error', 'Deposit record not found.');
            }

            // Check if already approved
            if ($deposit->status === 'approved') {
                return redirect()->back()->with('error', 'This deposit is already approved.');
            }

            // Find the user
            $user = User::find($deposit->user_id);

            if (!$user) {
                DB::rollBack();
                return redirect()->back()->with('error', 'User not found.');
            }

            // Update deposit status to approved
            $deposit->update(['status' => 'approved']);

            // Add amount to user's balance
            $user->balance = ($user->balance ?? 0) + $deposit->amount;
            $user->save();

            DB::commit();

            return redirect()->back()->with('success', 'Deposit approved successfully and balance updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error approving deposit: ' . $e->getMessage());
        }
    }

    /**
     * Reject Deposit
     */
    public function admin_deposite_reject($id)
    {
        $deposit = Deposite::find($id);

        if (!$deposit) {
            return redirect()->back()->with('error', 'Deposit record not found.');
        }

        // Check if already rejected
        if ($deposit->status === 'rejected') {
            return redirect()->back()->with('error', 'This deposit is already rejected.');
        }

        // Check if already approved (prevent rejection of approved deposits)
        if ($deposit->status === 'approved') {
            return redirect()->back()->with('error', 'Cannot reject an already approved deposit.');
        }

        $deposit->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Deposit rejected successfully.');
    }

    /**
     * Approved Deposit List
     */
    public function admin_deposite_approved_list()
    {
        $approved = Deposite::with('user')->where('status', 'approved')->latest()->get();
        return view('admin.depositeaproved.approved_list', compact('approved'));
    }

    /**
     * Rejected Deposit List
     */
    public function admin_deposite_reject_list()
    {
        $rejected = Deposite::with('user')->where('status', 'rejected')->latest()->get();
        return view('admin.depositeaproved.reject_list', compact('rejected'));
    }
}
