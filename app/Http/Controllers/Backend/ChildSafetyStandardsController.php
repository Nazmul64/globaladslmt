<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Childsafety;
use Illuminate\Http\Request;

class ChildSafetyStandardsController extends Controller
{
     /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $policies = Childsafety::latest()->paginate(10);
        return view('admin.childsafety.index', compact('policies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.childsafety.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        Childsafety::create($validated);

        return redirect()
            ->route('Childsafety.index')
            ->with('success', 'Child safety standard created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $privacyPolicy = Childsafety::findOrFail($id);
        return view('admin.childsafety.edit', compact('privacyPolicy'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $privacyPolicy = Childsafety::findOrFail($id);
        $privacyPolicy->update($validated);

        return redirect()
            ->route('Childsafety.index')
            ->with('success', 'Child safety standard updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $privacyPolicy = Childsafety::findOrFail($id);
        $privacyPolicy->delete();

        return redirect()
            ->route('Childsafety.index')
            ->with('success', 'Child safety standard deleted successfully.');
    }
}
