<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\cr;
use App\Models\DepositeInstruction;
use Illuminate\Http\Request;

class DepositeInstructionsController extends Controller
{
    // Display a listing of the resource
    public function index()
    {
        $instructions =DepositeInstruction::latest()->get();
        return view('admin.depositelimitinstructions.index', compact('instructions'));
    }

    // Show the form for creating a new resource
    public function create()
    {
        return view('admin.depositelimitinstructions.create');
    }

    // Store a newly created resource in storage
    public function store(Request $request)
    {
        $request->validate([
            'deposite_instructions' => 'required|string',
        ]);

        DepositeInstruction::create([
            'deposite_instructions' => $request->deposite_instructions
        ]);

        return redirect()->route('depositeinstructions.index')->with('success', 'Instruction added successfully.');
    }

    // Show the form for editing the specified resource
    public function edit(DepositeInstruction $depositeinstruction)
    {
        return view('admin.depositelimitinstructions.edit', compact('depositeinstruction'));
    }

    // Update the specified resource in storage
    public function update(Request $request, DepositeInstruction $depositeinstruction)
    {
        $request->validate([
            'deposite_instructions' => 'required|string',
        ]);

        $depositeinstruction->update([
            'deposite_instructions' => $request->deposite_instructions
        ]);

        return redirect()->route('depositeinstructions.index')->with('success', 'Instruction updated successfully.');
    }

    // Remove the specified resource from storage
    public function destroy(DepositeInstruction $depositeinstruction)
    {
        $depositeinstruction->delete();
        return redirect()->route('depositeinstructions.index')->with('success', 'Instruction deleted successfully.');
    }
}
