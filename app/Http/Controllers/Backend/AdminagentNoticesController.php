<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agentnotice;

class AdminagentNoticesController extends Controller
{
    public function index()
    {
        $notices = Agentnotice::latest()->get();
        return view('admin.agentnotices.index', compact('notices'));
    }

    public function create()
    {
        return view('admin.agentnotices.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'notices' => 'required'
        ]);

        Agentnotice::create([
            'notices' => $request->notices
        ]);

        return redirect()->route('agentnotices.index')
            ->with('success', 'Notice created successfully');
    }

    public function edit($id)
    {
        $notice = Agentnotice::findOrFail($id);
        return view('admin.agentnotices.edit', compact('notice'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'notices' => 'required'
        ]);

        $notice = Agentnotice::findOrFail($id);
        $notice->update([
            'notices' => $request->notices
        ]);

        return redirect()->route('agentnotices.index')
            ->with('success', 'Notice updated successfully');
    }

    public function destroy($id)
    {
        $notice = Agentnotice::findOrFail($id);
        $notice->delete();

        return back()->with('success', 'Notice deleted');
    }
}
