<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agentcommissonsetup;

class DepositewidhrawComissionagetController extends Controller
{
    /**
     * কমিশন সেটিং লিস্ট দেখাবে।
     */
    public function index()
    {
        $commissions = Agentcommissonsetup::latest()->get();
        return view('admin.agentdepositewidhawcommission.index', compact('commissions'));
    }

    /**
     * নতুন কমিশন সেটআপ ফর্ম।
     */
    public function create()
    {
        return view('admin.agentdepositewidhawcommission.create');
    }

    /**
     * কমিশন সেটিং সংরক্ষণ করবে।
     */
    public function store(Request $request)
    {
        $request->validate([
            'deposit_agent_commission' => 'required|numeric|min:0',
            'withdraw_total_commission' => 'required|numeric|min:0',
            'commission_type' => 'required|in:fixed,percent',
            'agent_share_percent' => 'required|numeric|min:0|max:100',
            'admin_share_percent' => 'required|numeric|min:0|max:100',
        ]);

        if ($request->agent_share_percent + $request->admin_share_percent > 100) {
            return back()->with('error', 'Agent + Admin share 100% এর বেশি হতে পারবে না!');
        }

        Agentcommissonsetup::create($request->all());
        return redirect()->route('agentcommission.index')->with('success', 'Commission setup সফলভাবে তৈরি হয়েছে।');
    }

    /**
     * কমিশন সেটআপ এডিট ফর্ম।
     */
    public function edit($id)
    {
        $commission = Agentcommissonsetup::findOrFail($id);
        return view('admin.agentdepositewidhawcommission.edit', compact('commission'));
    }

    /**
     * কমিশন আপডেট করবে।
     */
    public function update(Request $request, $id)
    {
        $commission = Agentcommissonsetup::findOrFail($id);

        $request->validate([
            'deposit_agent_commission' => 'required|numeric|min:0',
            'withdraw_total_commission' => 'required|numeric|min:0',
            'commission_type' => 'required|in:fixed,percent',
            'agent_share_percent' => 'required|numeric|min:0|max:100',
            'admin_share_percent' => 'required|numeric|min:0|max:100',
        ]);

        if ($request->agent_share_percent + $request->admin_share_percent > 100) {
            return back()->with('error', 'Agent + Admin share 100% এর বেশি হতে পারবে না!');
        }

        $commission->update($request->all());
        return redirect()->route('agentcommission.index')->with('success', 'Commission setup আপডেট হয়েছে।');
    }

    /**
     * কমিশন সেটিং ডিলিট করবে।
     */
    public function destroy($id)
    {
        $commission = Agentcommissonsetup::findOrFail($id);
        $commission->delete();
        return back()->with('success', 'Commission setup ডিলিট করা হয়েছে।');
    }
}
