<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RefferCommissionSetup;
use Illuminate\Http\Request;

class RefferCommissionSetupController extends Controller
{
    public function index()
    {
        $setups = RefferCommissionSetup::latest()->get();
        return view('admin.reffercommission.index', compact('setups'));
    }

    public function create()
    {
        return view('admin.reffercommission.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'reffer_level' => 'required|string|max:255',
            'commission_percentage' => 'required|numeric|min:0',
        ]);

        RefferCommissionSetup::create($request->all());

        return redirect()->route('reffercommission.index')
                         ->with('success', 'Referral commission added successfully.');
    }

    public function edit(RefferCommissionSetup $reffercommission)
    {
        return view('admin.reffercommission.edit', compact('reffercommission'));
    }

    public function update(Request $request, RefferCommissionSetup $reffercommission)
    {
        $request->validate([
            'reffer_level' => 'required|string|max:255',
            'commission_percentage' => 'required|numeric|min:0',
        ]);

        $reffercommission->update($request->all());

        return redirect()->route('reffercommission.index')
                         ->with('success', 'Referral commission updated successfully.');
    }

    public function destroy(RefferCommissionSetup $reffercommission)
    {
        $reffercommission->delete();
        return redirect()->route('reffercommission.index')
                         ->with('success', 'Referral commission deleted successfully.');
    }
}
