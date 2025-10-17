<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminApproveController extends Controller
{
    // Show all pending agents
    public function pendingAgents()
    {
        $agents = User::where('role','agent')->where('status','pending')->get();
        return view('admin.agentlist.agentlist', compact('agents'));
    }

    // Approve an agent
    public function approveAgent($id)
    {
        $agent = User::findOrFail($id);
        $agent->status = 'approved';
        $agent->save();

        return back()->with('success', 'Agent approved successfully!');
    }

    // Reject an agent
    public function rejectAgent($id)
    {
        $agent = User::findOrFail($id);
        $agent->status = 'rejected';
        $agent->save();

        return back()->with('error', 'Agent rejected.');
    }
    public function agentapprovedlist()
    {
        // Fetch agents with status 'approved'
        $agents = User::where('role', 'agent')->where('status', 'approved')->get();

        return view('admin.agentlist.approved_list', compact('agents'));
    }
   public function agentrejectlist()
{
    $agents = User::where('role', 'agent')
                  ->where('status', 'rejected')
                  ->get();

    return view('admin.agentlist.rejected', compact('agents'));
}


}
