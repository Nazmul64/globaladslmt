<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Deposite;
use Illuminate\Http\Request;

class AdmindepositeApprovedController extends Controller
{
    /**
     * Show Pending Deposits
     */
    public function admin_deposite_pending()
    {
        $deposite_list = Deposite::where('status', 'pending')->latest()->get();
        return view('admin.depositeaproved.index', compact('deposite_list'));
    }

    /**
     * Approve Deposit
     */
    public function admin_deposite_approve($id)
    {
        $deposit = Deposite::find($id);

        if (!$deposit) {
            return redirect()->back()->with('error', 'Deposit record not found.');
        }

        $deposit->update(['status' => 'approved']);

        return redirect()->back()->with('success', 'Deposit approved successfully.');
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

        $deposit->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Deposit rejected successfully.');
    }

    /**
     * Approved Deposit List
     */
    public function admin_deposite_approved_list()
    {
        $approved = Deposite::where('status', 'approved')->latest()->get(); // Variable name matches Blade
        return view('admin.depositeaproved.approved_list', compact('approved'));
    }

    /**
     * Rejected Deposit List
     */
    public function admin_deposite_reject_list()
    {
        $rejected = Deposite::where('status', 'rejected')->latest()->get();
        return view('admin.depositeaproved.reject_list', compact('rejected'));
    }
}
