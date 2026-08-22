<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Privacy;
use Illuminate\Http\Request;

class PrivacyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $policies = Privacy::latest()->paginate(10);
        return view('admin.privacy.index', compact('policies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.privacy.create');
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

        Privacy::create($validated);

        return redirect()
            ->route('privacy.index')
            ->with('success', 'Privacy policy created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $privacyPolicy = Privacy::findOrFail($id);
        return view('admin.privacy.edit', compact('privacyPolicy'));
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

        $privacyPolicy = Privacy::findOrFail($id);
        $privacyPolicy->update($validated);

        return redirect()
            ->route('privacy.index')
            ->with('success', 'Privacy policy updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $privacyPolicy = Privacy::findOrFail($id);
        $privacyPolicy->delete();

        return redirect()
            ->route('privacy.index')
            ->with('success', 'Privacy policy deleted successfully.');
    }
}
