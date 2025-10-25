<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\cr;
use App\Models\Depositelimite;
use Illuminate\Http\Request;

class DepositelimiteController extends Controller
{
    public function index()
    {
        $limits = Depositelimite::all();
        return view('admin.depositelimit.index', compact('limits'));
    }

    public function create()
    {
        return view('admin.depositelimit.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'max_deposit' => 'required|numeric|min:0',
            'min_deposit' => 'required|numeric|min:0',
        ]);

        Depositelimite::create($request->only('max_deposit','min_deposit'));

        return redirect()->route('depositelimit.index')->with('success', 'Deposit limits created successfully.');
    }

    public function edit(Depositelimite $depositelimit)
    {
        return view('admin.depositelimit.edit', compact('depositelimit'));
    }

    public function update(Request $request, Depositelimite $depositelimit)
    {
        $request->validate([
            'max_deposit' => 'required|numeric|min:0',
            'min_deposit' => 'required|numeric|min:0',
        ]);

        $depositelimit->update($request->only('max_deposit','min_deposit'));

        return redirect()->route('depositelimit.index')->with('success', 'Deposit limits updated successfully.');
    }

    public function destroy(Depositelimite $depositelimit)
    {
        $depositelimit->delete();
        return redirect()->route('depositelimit.index')->with('success', 'Deposit limit deleted.');
    }
}
