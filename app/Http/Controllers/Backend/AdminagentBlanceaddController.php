<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AgentDeposite;
use Illuminate\Http\Request;

class AdminagentBlanceaddController extends Controller
{
    // Agent list
    public function index()
    {
        $users = User::where('role', 'agent')->get();
        return view('admin.agentblanceedit.index', compact('users'));
    }

    // Edit page (load deposit record)
    public function edit($id)
    {
        $blance_edit = AgentDeposite::where('agent_id', $id)->firstOrFail();
        return view('admin.agentblanceedit.edit', compact('blance_edit'));
    }

    // Update deposit amount
    public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $deposit = AgentDeposite::where('agent_id', $id)->firstOrFail();
        $deposit->amount = $request->amount;
        $deposit->save();

        return redirect()
            ->route('admin.agent.balance.index')
            ->with('success', 'Agent deposit amount updated successfully!');
    }

    // Delete agent
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'Agent deleted successfully!');
    }
}
