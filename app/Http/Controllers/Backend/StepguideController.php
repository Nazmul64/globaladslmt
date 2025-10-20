<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Stepguide;
use Illuminate\Http\Request;

class StepguideController extends Controller
{
    public function index()
    {
        $stepguides = Stepguide::orderBy('serial_number', 'asc')->get();
        return view('admin.stepguide.index', compact('stepguides'));
    }

    public function create()
    {
        return view('admin.stepguide.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:255',
            'serial_number' => 'required|integer|unique:stepguides,serial_number',
        ]);

        Stepguide::create($request->all());

        return redirect()->route('stepguide.index')->with('success', 'Stepguide created successfully!');
    }

    public function edit($id)
    {
        $edit = Stepguide::findOrFail($id);
        return view('admin.stepguide.edit', compact('edit'));
    }

    public function update(Request $request, $id)
    {
        $stepguide = Stepguide::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:255',
            'serial_number' => 'required|integer|unique:stepguides,serial_number,' . $id,
        ]);

        $stepguide->update($request->all());

        return redirect()->route('stepguide.index')->with('success', 'Stepguide updated successfully!');
    }

    public function destroy($id)
    {
        $stepguide = Stepguide::findOrFail($id);
        $stepguide->delete();

        return redirect()->route('stepguide.index')->with('success', 'Stepguide deleted successfully!');
    }
}
