<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AgentWithdrawCommissionsetup;
use Illuminate\Http\Request;

class AgentWithdrawCommissionsetupController extends Controller
{
    // List all commissions
    public function index()
    {
        $commissions = AgentWithdrawCommissionsetup::latest()->get();
        return view('admin.agentwidrawcommissionsetup.index', compact('commissions'));
    }

    // Show create form
    public function create()
    {
        return view('admin.agentwidrawcommissionsetup.create');
    }

    // Store new commission
    public function store(Request $request)
    {
        $validated = $request->validate([
            'min_widthraw'        => 'required|numeric|min:0',
            'max_widthraw'        => 'required|numeric|min:0|gte:min_widthraw',
            'widthraw_charge'     => 'required|numeric|min:0',
        ]);

        AgentWithdrawCommissionsetup::create($validated);

        return redirect()
            ->route('agentwidthrawcommission.index')
            ->with('success', 'Withdraw commission added successfully.');
    }

    // Show edit form
    public function edit($id)
    {
        $commission = AgentWithdrawCommissionsetup::findOrFail($id);
        return view('admin.agentwidrawcommissionsetup.edit', compact('commission'));
    }

    // Update commission
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'min_widthraw'        => 'required|numeric|min:0',
            'max_widthraw'        => 'required|numeric|min:0|gte:min_widthraw',
            'widthraw_charge'     => 'required|numeric|min:0',
        ]);

        $commission = AgentWithdrawCommissionsetup::findOrFail($id);
        $commission->update($validated);

        return redirect()
            ->route('agentwidthrawcommission.index')
            ->with('success', 'Withdraw commission updated successfully.');
    }

    // Delete commission
    public function destroy($id)
    {
        AgentWithdrawCommissionsetup::findOrFail($id)->delete();

        return redirect()
            ->route('agentwidthrawcommission.index')
            ->with('success', 'Withdraw commission deleted successfully.');
    }
}
