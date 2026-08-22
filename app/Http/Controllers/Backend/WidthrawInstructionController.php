<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\cr;
use App\Models\WidthrawInstruction;
use Illuminate\Http\Request;

class WidthrawInstructionController extends Controller
{
   // Display a listing of the resource
    public function index()
    {
        $instructions =WidthrawInstruction::all();
        return view('admin.widthrawinstructions.index', compact('instructions'));
    }

    // Show the form for creating a new resource
    public function create()
    {
        return view('admin.widthrawinstructions.create');
    }





 public function store(Request $request)
    {
        $request->validate([
            'instructions' => 'required|string',
        ]);

        WidthrawInstruction::create([
            'instructions' => $request->instructions,
        ]);

        return redirect()->route('widthrawInstruction.index')
                         ->with('success', 'Instruction added successfully.');
    }




public function edit($id)
{
    $depositeinstruction = WidthrawInstruction::findOrFail($id);

    return view('admin.widthrawinstructions.edit', compact('depositeinstruction'));
}


    // Update the specified resource in storage
public function update(Request $request, $id)
{
    $request->validate([
        'instructions' => 'required|string',
    ]);

    $depositeinstruction = WidthrawInstruction::findOrFail($id);

    $depositeinstruction->update([
        'instructions' => $request->instructions
    ]);

    return redirect()->route('widthrawInstruction.index')
        ->with('success', 'Instruction updated successfully.');
}


    // Remove the specified resource from storage
    public function destroy(Request $request, $id)
    {
        $depositeinstruction = WidthrawInstruction::findOrFail($id);
        $depositeinstruction->delete();
        return redirect()->route('widthrawInstruction.index')->with('success', 'Instruction deleted successfully.');
    }
}
