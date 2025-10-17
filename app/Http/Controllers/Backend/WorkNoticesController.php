<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\cr;
use App\Models\Wornotice;
use Illuminate\Http\Request;

class WorkNoticesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notices = Wornotice::latest()->get();
        return view('admin.worknotice.index', compact('notices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.worknotice.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        Wornotice::create([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()->route('worknotice.index')->with('success', 'Notice created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $notice = Wornotice::findOrFail($id);
        return view('admin.worknotice.edit', compact('notice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $notice = Wornotice::findOrFail($id);
        $notice->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()->route('worknotice.index')->with('success', 'Notice updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $notice =Wornotice::findOrFail($id);
        $notice->delete();

        return redirect()->route('worknotice.index')->with('success', 'Notice deleted successfully!');
    }
}
