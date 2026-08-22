<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kyc;
use App\Models\User;

class AdminkeyapprovedController extends Controller
{
     public function kyclist()
    {
        $kycs = Kyc::with('kycagent')->get();
        return view('admin.key.approved_list', compact('kycs'));
    }

    /**
     * Approve a user's KYC.
     */
    public function approvedkey($id)
    {
        $kyc = Kyc::findOrFail($id);
        $kyc->status = 'approved';
        $kyc->save();

        return redirect()->back()->with('success', 'KYC approved successfully.');
    }

    /**
     * Reject a user's KYC.
     */
    public function rejectapprovedkey($id)
    {
        $kyc = Kyc::findOrFail($id);
        $kyc->status = 'rejected';
        $kyc->save();

        return redirect()->back()->with('error', 'KYC rejected successfully.');
    }
}
