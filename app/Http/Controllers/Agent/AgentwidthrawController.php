<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentDeposite;
use App\Models\Agentwidthraw;
use App\Models\AgentWithdrawCommissionsetup;
use App\Models\Paymentmethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentwidthrawController extends Controller
{
    /**
     * Display the withdraw page
     */
    public function agentwidhraw()
    {
        $agent = Auth::user();

        // Total balance of the logged-in agent
        $total_deposite = AgentDeposite::where('agent_id', $agent->id)
            ->sum('amount');

        // ✅ Available balance (Total - Locked Amount)
        $locked_amount = $agent->locked_amount ?? 0;
        $available_balance = $total_deposite - $locked_amount;

        // Available payment methods
        $payment_method = Paymentmethod::all();

        // Admin withdraw configuration (min, max, charge)
        $Agentwidthraw_setup = AgentWithdrawCommissionsetup::first();

        return view(
            'agent.agentwidthraw.index',
            compact('payment_method', 'Agentwidthraw_setup', 'total_deposite', 'locked_amount', 'available_balance')
        );
    }

    /**
     * Store a withdraw request
     */
    public function agentwithdrawstore(Request $request)
    {
        $agent = Auth::user();

        // Get admin withdraw configuration
        $setup = AgentWithdrawCommissionsetup::first();

        if (!$setup) {
            return back()->withErrors([
                'setup' => 'Withdraw configuration not found.'
            ]);
        }

        // Agent current balance
        $total_deposite = AgentDeposite::where('agent_id', $agent->id)
            ->sum('amount');

        // ✅ Calculate available balance (excluding locked amount)
        $locked_amount = $agent->locked_amount ?? 0;
        $available_balance = $total_deposite - $locked_amount;

        // Validation using admin-defined min & max withdraw limits
        $request->validate([
            'payment_method_id' => 'required|exists:paymentmethods,id',
            'account'           => 'required|string|max:255',
            'account_number'    => 'required|string|max:255',
            'amount'            => 'required|numeric|min:' . $setup->min_widthraw . '|max:' . $setup->max_widthraw,
        ]);

        $requested_amount = $request->amount;

        // ✅ Check 1: Sufficient available balance (not locked amount)
        if ($requested_amount > $available_balance) {
            return back()->withErrors([
                'amount' => 'Insufficient available balance. Your locked amount is ' . number_format($locked_amount, 2) . ' USD. Available: ' . number_format($available_balance, 2) . ' USD.'
            ])->withInput();
        }

        // ✅ Check 2: Total balance check (extra safety)
        if ($requested_amount > $total_deposite) {
            return back()->withErrors([
                'amount' => 'Insufficient balance.'
            ])->withInput();
        }

        // =========================
        // Charge Calculation
        // =========================
        $charge_amount = ($requested_amount * $setup->widthraw_charge) / 100;
        $final_amount = $requested_amount - $charge_amount;

        if ($final_amount <= 0) {
            return back()->withErrors([
                'amount' => 'Invalid withdraw amount after charge deduction.'
            ])->withInput();
        }

        // =========================
        // Save Withdraw Request
        // =========================
        Agentwidthraw::create([
            'payment_method_id' => $request->payment_method_id,
            'account_number'    => $request->account_number,
            'wallet_address'    => $request->account,
            'user_id'           => $agent->id,
            'amount'            => $requested_amount,
            'status'            => 'pending',
        ]);

        return redirect()->back()->with(
            'success',
            'Withdraw request submitted successfully. You will receive ' . number_format($final_amount, 2) . ' USD after ' . number_format($charge_amount, 2) . ' USD charge.'
        );
    }
}
