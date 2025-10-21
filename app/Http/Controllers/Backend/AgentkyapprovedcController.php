<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Agentkyc;
use Illuminate\Http\Request;

class AgentkyapprovedcController extends Controller
{
     public function agentkyclist()
    {
        $kycs =Agentkyc::all();
        return view('admin.agentkycapproved.index', compact('kycs'));
    }

    /**
     * Approve a user's KYC.
     */
    public function agentapprovedkey($id)
    {
        $kyc = Agentkyc::findOrFail($id);
        $kyc->status = 'approved';
        $kyc->save();

        return redirect()->back()->with('success', 'KYC approved successfully.');
    }

    /**
     * Reject a user's KYC.
     */
    public function agentrejectapprovedkey($id)
    {
        $kyc = Agentkyc::findOrFail($id);
        $kyc->status = 'rejected';
        $kyc->save();

        return redirect()->back()->with('error', 'KYC rejected successfully.');
    }
    public function agentapprovedkeylist() {
    $approvedKycs = Agentkyc::where('status', 'approved')->latest()->get();
    return view('admin.agentkycapproved.agent_kyc_approve_list', compact('approvedKycs'));
}

public function agentrejectapprovedkeylist() {
    $rejectedKycs = Agentkyc::where('status', 'rejected')->latest()->get();
    return view('admin.agentkycapproved.agent_kyc_reject_list', compact('rejectedKycs'));
}
}
