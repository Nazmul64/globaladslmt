<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agentwidthraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentwidthrawControllerstatuscheckController extends Controller
{
    // Pending withdraw list (GET)
public function pendingList()
{
    $agentId = Auth::id(); // dd($agentId) দিয়ে confirm করো

    $withdraws = Agentwidthraw::where('user_id', $agentId)
        ->with('paymentMethod')
        ->latest()
        ->get();

    $summary = $this->summary();

    return view(
        'agent.agentwithrawstatuscheck.index',
        compact('withdraws', 'summary')
    );
}





    // Approve withdraw (POST)
    public function approve($id)
    {
        $withdraw = Agentwidthraw::findOrFail($id);
        $withdraw->status = 'approved';
        $withdraw->save();

        return redirect()->back()->with('success', 'Withdraw approved successfully.');
    }

    // Reject withdraw (POST)
    public function reject($id)
    {
        $withdraw = Agentwidthraw::findOrFail($id);
        $withdraw->status = 'rejected';
        $withdraw->save();

        return redirect()->back()->with('success', 'Withdraw rejected successfully.');
    }

    // Summary helper
    private function summary()
    {
        return [
            'pending'  => Agentwidthraw::where('status', 'pending')->count(),
            'approved' => Agentwidthraw::where('status', 'approved')->count(),
            'rejected' => Agentwidthraw::where('status', 'rejected')->count(),
            'total'    => Agentwidthraw::count(),
        ];
    }
}
