<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DepositInstruction;

class DepositInstructionController extends Controller
{
    public function index()
    {
        $instructions = DepositInstruction::all();
        return view('admin.depositeinstruction.index', compact('instructions'));
    }

    public function create()
    {
        return view('admin.depositeinstruction.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'video_url' => 'required|string',
            'member_ship_instructions_title' => 'required|string',
            'member_ship_instructions_description' => 'required|string',
            'deposite_instructions_title' => 'required|string',
            'deposite_instructions_description' => 'required|string',
        ]);

        DepositInstruction::create($request->all());

        return redirect()->route('depositeinstruction.index')
                         ->with('success', 'Deposit Instruction Added Successfully');
    }

 public function edit($id)
{
    $depositeinstruction = DepositInstruction::findOrFail($id);
    return view('admin.depositeinstruction.edit', compact('depositeinstruction'));
}

    public function update(Request $request, DepositInstruction $depositeinstruction)
    {
        $request->validate([
            'video_url' => 'required|string',
            'member_ship_instructions_title' => 'required|string',
            'member_ship_instructions_description' => 'required|string',
            'deposite_instructions_title' => 'required|string',
            'deposite_instructions_description' => 'required|string',
        ]);

        $depositeinstruction->update($request->all());

        return redirect()->route('depositeinstruction.index')
                         ->with('success', 'Deposit Instruction Updated Successfully');
    }

    public function destroy(DepositInstruction $depositeinstruction)
    {
        $depositeinstruction->delete();
        return redirect()->route('depositeinstruction.index')
                         ->with('success', 'Deposit Instruction Deleted Successfully');
    }
}
