<?php

// app/Http/Controllers/Backend/WhyChooseUsController.php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Whychooseu;
use App\Models\WhyChooseUs;
use Illuminate\Http\Request;

class WhychooseusControllerController extends Controller
{
    public function index()
    {
        $items = Whychooseu::orderBy('title', 'asc')->get();
        return view('admin.whychooseus.index', compact('items'));
    }

    public function create()
    {
        return view('admin.whychooseus.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
        ]);

        Whychooseu::create($request->all());

        return redirect()->route('whychooseu.index')->with('success', 'Item created successfully.');
    }

    public function edit(Whychooseu $whychooseus)
    {
        return view('admin.whychooseus.edit', compact('whychooseus'));
    }

    public function update(Request $request, Whychooseu $whychooseus)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
        ]);

        $whychooseus->update($request->all());

        return redirect()->route('whychooseu.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(Whychooseu $whychooseus)
    {
        $whychooseus->delete();
        return redirect()->route('whychooseu.index')->with('success', 'Item deleted successfully.');
    }
}
