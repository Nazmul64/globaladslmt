<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Agentwidthraw;
use App\Models\AgentDeposite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminagentdepositeApprovedController extends Controller
{
    /**
     * Show all pending agent withdraw requests
     */
    public function widthrawlist()
    {
        $withdraws = Agentwidthraw::with('agent', 'paymentMethod')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('admin.agentwidthraw.index', compact('withdraws'));
    }

    /**
     * Approve an agent withdraw request
     */
    public function agentwidthrawapproved($id)
    {
        $withdraw = Agentwidthraw::findOrFail($id);

        DB::transaction(function () use ($withdraw) {
            // 1️⃣ Update withdraw status
            $withdraw->status = 'approved';
            $withdraw->save();

            // 2️⃣ Deduct FULL AMOUNT from AgentDeposite balance
            // ✅ withdraw->amount তে পুরো requested amount আছে (charge সহ)
            // ✅ এই পুরো amount টাই AgentDeposite থেকে কাটা হবে

            $deduct_amount = $withdraw->amount;

            $total_deposit = AgentDeposite::where('agent_id', $withdraw->user_id)
                ->where('status', 'approved')
                ->sum('amount');

            if ($total_deposit < $deduct_amount) {
                throw new \Exception('Agent balance insufficient. Required: ' . number_format($deduct_amount, 2) . ' BDT, Available: ' . number_format($total_deposit, 2) . ' BDT');
            }

            // ✅ Reduce balance (FIFO - oldest deposit first)
            $remaining = $deduct_amount;

            $deposits = AgentDeposite::where('agent_id', $withdraw->user_id)
                ->where('status', 'approved')
                ->where('amount', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($deposits as $deposit) {
                if ($remaining <= 0) break;

                if ($deposit->amount <= $remaining) {
                    // পুরো deposit টা কাটতে হবে
                    $remaining -= $deposit->amount;
                    $deposit->amount = 0;
                } else {
                    // আংশিক কাটতে হবে
                    $deposit->amount -= $remaining;
                    $remaining = 0;
                }

                $deposit->save();
            }

            // নিশ্চিত করা যে সব amount কাটা হয়েছে
            if ($remaining > 0) {
                throw new \Exception('Could not deduct full amount. Remaining: ' . number_format($remaining, 2) . ' BDT');
            }
        });

        return redirect()->back()->with('success', 'Withdraw approved successfully. Full amount (' . number_format($withdraw->amount, 2) . ' BDT) deducted from agent balance.');
    }

    /**
     * Reject an agent withdraw request
     */
    public function agentwidthrawrejectapproved($id)
    {
        $withdraw = Agentwidthraw::findOrFail($id);
        $withdraw->status = 'rejected';
        $withdraw->save();

        return redirect()->back()->with('success', 'Withdraw rejected successfully.');
    }

    /**
     * List of approved withdraws
     */
    public function agentwidthrawapprovedlist()
    {
        $withdraws = Agentwidthraw::with('agent', 'paymentMethod')
            ->where('status', 'approved')
            ->latest()
            ->get();

        return view('admin.agentwidthraw.approved', compact('withdraws'));
    }

    /**
     * List of rejected withdraws
     */
    public function agentrejectwidthrawapprovedlist()
    {
        $withdraws = Agentwidthraw::with('agent', 'paymentMethod')
            ->where('status', 'rejected')
            ->latest()
            ->get();

        return view('admin.agentwidthraw.rejected', compact('withdraws'));
    }
}
