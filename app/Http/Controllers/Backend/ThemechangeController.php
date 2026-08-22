<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Themechange;
use Illuminate\Http\Request;

class ThemechangeController extends Controller
{
    /**
     * Show Theme List
     */
    public function index()
    {
        $themes = Themechange::latest()->get();
        return view('admin.themechange.index', compact('themes'));
    }

    /**
     * Show Create Form
     */
    public function create()
    {
        return view('admin.themechange.create');
    }

    /**
     * Store New Theme
     */
    public function store(Request $request)
    {
        $request->validate([
            'color_code' => 'required|string|max:50'
        ]);

        Themechange::create([
            'color_code' => $request->color_code
        ]);

        return redirect()->route('themechange.index')->with('success', 'Theme added successfully!');
    }

    /**
     * Show Edit Form
     */
    public function edit($id)
    {
        $theme = Themechange::findOrFail($id);
        return view('admin.themechange.edit', compact('theme'));
    }

    /**
     * Update Theme
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'color_code' => 'required|string|max:50'
        ]);

        $theme = Themechange::findOrFail($id);
        $theme->update([
            'color_code' => $request->color_code
        ]);

        return redirect()->route('themechange.index')->with('success', 'Theme updated successfully!');
    }

    /**
     * Delete Theme
     */
    public function destroy($id)
    {
        Themechange::findOrFail($id)->delete();
        return redirect()->route('themechange.index')->with('success', 'Theme deleted successfully!');
    }
}
