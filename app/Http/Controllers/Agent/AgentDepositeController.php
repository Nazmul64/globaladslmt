<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentDeposite;
use App\Models\Paymentmethod;
use App\Models\Depositelimite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentDepositeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // ensure agent is logged in
    }

    // Deposit form
    public function agentdeposite()
    {
        $payment_method = Paymentmethod::all();
        return view('agent.deposite.index', compact('payment_method'));
    }

    // Store deposit
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'sender_account' => 'required|string|max:255',
            'transaction_id' => 'required|string|max:255|unique:agent_deposites,transaction_id',
            'photo' => 'required|image|max:2048',
        ]);

        // Deposit limits
        $limit = Depositelimite::first();
        if ($limit) {
            if ($request->amount < $limit->min_deposit) {
                return back()->withErrors(['amount' => 'Minimum deposit amount is ' . $limit->min_deposit . '৳']);
            }
            if ($request->amount > $limit->max_deposit) {
                return back()->withErrors(['amount' => 'Maximum deposit amount is ' . $limit->max_deposit . '৳']);
            }
        }

        // Upload photo
        $photoName = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = time().'_'.$photo->getClientOriginalName();
            $photo->move(public_path('uploads/agentdeposite'), $photoName);
        }

        // Get logged-in agent id
        $agentId = Auth::id();
        if (!$agentId) {
            return redirect()->route('login')->withErrors(['auth' => 'Please login to submit deposit.']);
        }

        // Create deposit
        AgentDeposite::create([
            'agent_id' => $agentId,
            'amount' => $request->amount,
            'sender_account' => $request->sender_account,
            'transaction_id' => $request->transaction_id,
            'photo' => $photoName,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Deposit submitted successfully!');
    }

    // Approved deposits
    public function agent_deposite_approved_list()
    {
        $auth_user_id = Auth::id();
        $approved = AgentDeposite::where('agent_id', $auth_user_id)
                                  ->where('status', 'approved')
                                  ->latest()
                                  ->get();

        return view('agent.deposite.approved', compact('approved'));
    }

    // Rejected deposits
    public function agent_deposite_reject_list()
    {
        $auth_user_id = Auth::id();
        $rejected = AgentDeposite::where('agent_id', $auth_user_id)
                                  ->where('status', 'rejected')
                                  ->latest()
                                  ->get();

        return view('agent.deposite.rejected', compact('rejected'));
    }
}
