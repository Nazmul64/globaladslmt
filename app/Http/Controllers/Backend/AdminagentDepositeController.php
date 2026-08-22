<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AgentDeposite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminagentDepositeController extends Controller
{
    /**
     * 🟡 Show all pending deposits
     */
    public function admin_agemt_deposite_pending()
    {
          $agent_deposite = AgentDeposite::with('agentname','paymentMethodname')
                        ->where('status', 'pending')
                        ->latest()
                        ->get();
        return view('admin.agentdeposite.index', compact('agent_deposite'));
    }

    /**
     * ✅ Approve deposit
     */
    public function approve($id)
    {
        $deposit = AgentDeposite::findOrFail($id);
        $deposit->status = 'approved';
        $deposit->save();

        return redirect()->back()->with('success', 'Deposit approved successfully!');
    }

    /**
     * ❌ Reject deposit
     */
    public function reject($id)
    {
        $deposit = AgentDeposite::findOrFail($id);
        $deposit->status = 'rejected';
        $deposit->save();

        return redirect()->back()->with('success', 'Deposit rejected successfully!');
    }
public function admin_agemt_deposite_approved_list()
{
    $approved = AgentDeposite::with('agentname','paymentMethodname')->where('status', 'approved')
                              ->latest()
                              ->get();

    return view('admin.depositeaproved.approved_list', compact('approved'));
}



public function admin_agemt_deposite_reject_list()
{
    $rejected = AgentDeposite::with('agentname','paymentMethodname')->where('status', 'rejected')
                              ->latest()
                              ->get();

    return view('admin.depositeaproved.reject_list', compact('rejected'));
}

}
