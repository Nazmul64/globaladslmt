<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Deposite;
use Illuminate\Http\Request;

class AdmindepositeApprovedController extends Controller
{
    // 🔸 Show Pending Deposits
    public function admin_deposite_pending()
    {
        $deposite_list = Deposite::where('status', 'pending')->get();
        return view('admin.depositeaproved.index', compact('deposite_list'));
    }

    // 🔸 Approve Deposit
    public function admin_deposite_approve($id)
    {
        $deposit = Deposite::findOrFail($id);
        $deposit->status = 'approved';
        $deposit->save();

        return redirect()->back()->with('success', 'Deposit approved successfully.');
    }

    // 🔸 Reject Deposit
    public function admin_deposite_reject($id)
    {
        $deposit = Deposite::findOrFail($id);
        $deposit->status = 'rejected';
        $deposit->save();

        return redirect()->back()->with('error', 'Deposit rejected successfully.');
    }

    // 🔸 Approved Deposit List
    public function admin_deposite_approved_list()
    {
        $deposite_list = Deposite::where('status', 'approved')->get();
        return view('admin.depositeaproved.approved_list', compact('deposite_list'));
    }
    public function admin_deposite_reject_list()
{
    $deposite_list = Deposite::where('status', 'rejected')->get();
    return view('admin.depositeaproved.reject_list', compact('deposite_list'));
}
}
