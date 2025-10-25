<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AgentDeposite;
use Illuminate\Http\Request;

class AdminagentDepositeController extends Controller
{
    /**
     * 🟡 Show all pending deposits
     */
    public function admin_agemt_deposite_pending()
    {
        $agent_deposite = AgentDeposite::where('status', 'pending')->latest()->get();
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
        $agent_deposite = AgentDeposite::where('status', 'approved')->latest()->get();
        return view('admin.agentdeposite.approved', compact('agent_deposite'));
    }

    /**
     * ❌ Show rejected deposit list
     */
    public function admin_agemt_deposite_reject_list()
    {
        $agent_deposite = AgentDeposite::where('status', 'rejected')->latest()->get();
        return view('admin.agentdeposite.rejected', compact('agent_deposite'));
    }
}
