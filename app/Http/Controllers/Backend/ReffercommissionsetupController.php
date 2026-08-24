<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Reffercommissionsetup;
use Illuminate\Http\Request;

class ReffercommissionsetupController extends Controller
{
    public function index()
    {
        $setups =Reffercommissionsetup::latest()->get();
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

        Reffercommissionsetup::create($request->all());

        return redirect()->route('reffercommission.index')
                         ->with('success', 'Referral commission added successfully.');
    }

    public function edit(Reffercommissionsetup $reffercommission)
    {
        return view('admin.reffercommission.edit', compact('reffercommission'));
    }

    public function update(Request $request, Reffercommissionsetup $reffercommission)
    {
        $request->validate([
            'reffer_level' => 'required|string|max:255',
            'commission_percentage' => 'required|numeric|min:0',
        ]);

        $reffercommission->update($request->all());

        return redirect()->route('reffercommission.index')
                         ->with('success', 'Referral commission updated successfully.');
    }

    public function destroy($id)
    {
        $setup = Reffercommissionsetup::findOrFail($id);
        $setup->delete();

        return redirect()->route('reffercommission.index')
                         ->with('success', 'Commission Setup deleted successfully.');
    }
}

class_alias(ReffercommissionsetupController::class, 'App\Http\Controllers\Backend\RefferCommissionSetupController');
