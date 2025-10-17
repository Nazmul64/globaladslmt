<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Package; // Make sure your model is Package
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages =Package::latest()->get();
        return view('admin.package.index', compact('packages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.package.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'package_name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'daily_income' => 'required|numeric',
            'daily_limit' => 'required|integer',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['package_name', 'price', 'daily_income', 'daily_limit']);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = Str::slug($request->package_name) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/package'), $filename);
            $data['photo'] = $filename;
        }

        Package::create($data);

        return redirect()->route('package.index')->with('success', 'Package created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        return view('admin.package.edit', compact('package'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package)
    {
        $request->validate([
            'package_name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'daily_income' => 'required|numeric',
            'daily_limit' => 'required|integer',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['package_name', 'price', 'daily_income', 'daily_limit']);

        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($package->photo && file_exists(public_path('uploads/package/' . $package->photo))) {
                unlink(public_path('uploads/package/' . $package->photo));
            }
            $file = $request->file('photo');
            $filename = Str::slug($request->package_name) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/package'), $filename);
            $data['photo'] = $filename;
        }

        $package->update($data);

        return redirect()->route('package.index')->with('success', 'Package updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package)
    {
        if ($package->photo && file_exists(public_path('uploads/package/' . $package->photo))) {
            unlink(public_path('uploads/package/' . $package->photo));
        }

        $package->delete();
        return redirect()->route('package.index')->with('success', 'Package deleted successfully!');
    }
}
