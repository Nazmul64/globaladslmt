<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentDeposite;
use App\Models\Paymentmethod;
use App\Models\Depositelimite; // ✅ Import limit model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentDepositeController extends Controller
{
    public function agentdeposite()
    {
        $payment_method = Paymentmethod::all();
        return view('agent.deposite.index', compact('payment_method'));
    }

    // Handle deposit submission
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'sender_account' => 'required|string|max:255',
            'transaction_id' => 'required|string|max:255|unique:agent_deposites,transaction_id',
            'photo' => 'required|image|max:2048', // max 2MB
        ]);

        // ✅ Get deposit limit
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
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('agent_deposites', 'public');
        }

        // Store deposit
        AgentDeposite::create([
            'agent_id' => Auth::id(),
            'payment_method_id' => $request->payment_method_id,
            'amount' => $request->amount,
            'sender_account' => $request->sender_account,
            'transaction_id' => $request->transaction_id,
            'photo' => $photoPath,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Deposit submitted successfully!');
    }
}
