<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Support;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $supports = Support::orderBy('name', 'asc')->get();
        return view('admin.support.index', compact('supports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.support.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url_link' => 'required|url',
            'icon' => 'required|string|max:255',
        ]);

        Support::create($request->only('name', 'url_link', 'icon'));

        return redirect()->route('support.index')->with('success', 'Support created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Support $support)
    {
        return view('admin.support.edit', compact('support'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Support $support)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url_link' => 'required|url',
            'icon' => 'required|string|max:255',
        ]);

        $support->update($request->only('name', 'url_link', 'icon'));

        return redirect()->route('support.index')->with('success', 'Support updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Support $support)
    {
        $support->delete();

        return redirect()->route('support.index')->with('success', 'Support deleted successfully.');
    }
}
