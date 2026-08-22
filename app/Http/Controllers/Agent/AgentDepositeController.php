<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentDeposite;
use App\Models\Paymentmethod;
use App\Models\Depositelimite;
use App\Models\LockedSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentDepositeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // 🧾 Show deposit form
    public function agentdeposite()
    {
        $payment_methods = Paymentmethod::latest()->get();
        return view('agent.deposite.index', compact('payment_methods'));
    }

    // 💾 Store deposit
    public function store(Request $request)
    {
        $request->validate([
            'amount'             => 'required|numeric|min:1',
            'sender_account'     => 'required|string|max:255',
            'transaction_id'     => 'required|string|max:255|unique:agent_deposites,transaction_id',
            'payment_method_id'  => 'required|exists:paymentmethods,id',
            'photo'              => 'required|image|max:2048',
        ]);

        // 🔒 Check deposit limits
        $limit = Depositelimite::first();
        if ($limit) {
            if ($request->amount < $limit->min_deposit) {
                return back()->withErrors([
                    'amount' => 'Minimum deposit amount is ' . $limit->min_deposit . ' ৳'
                ]);
            }

            if ($request->amount > $limit->max_deposit) {
                return back()->withErrors([
                    'amount' => 'Maximum deposit amount is ' . $limit->max_deposit . ' ৳'
                ]);
            }
        }

        // 🖼 Upload image
        $photoName = null;
        if ($request->hasFile('photo')) {
            $photoName = time() . '_' . $request->photo->getClientOriginalName();
            $request->photo->move(public_path('uploads/agentdeposite'), $photoName);
        }

        // 🧠 CREATE DEPOSIT (NO LOCK HERE)
        AgentDeposite::create([
            'agent_id'          => Auth::id(),
            'amount'            => $request->amount,
            'sender_account'    => $request->sender_account,
            'transaction_id'    => $request->transaction_id,
            'payment_method_id' => $request->payment_method_id,
            'photo'             => $photoName,
            'status'            => 'pending',
        ]);

        return redirect()->back()->with('success', 'Deposit submitted successfully!');
    }

    // ✅ Approved list
    public function agent_deposite_approved_list()
    {
        $approved = AgentDeposite::where('agent_id', Auth::id())
            ->where('status', 'approved')
            ->latest()
            ->get();

        return view('agent.deposite.approved', compact('approved'));
    }

    // ❌ Rejected list
    public function agent_deposite_reject_list()
    {
        $rejected = AgentDeposite::where('agent_id', Auth::id())
            ->where('status', 'rejected')
            ->latest()
            ->get();

        return view('agent.deposite.rejected', compact('rejected'));
    }

    // ⏳ Pending list
    public function agent_deposite_pending_list()
    {
        $pending = AgentDeposite::where('agent_id', Auth::id())
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('agent.deposite.pending', compact('pending'));
    }
}
