<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Logosetting;
use Illuminate\Http\Request;

class LogosettingController extends Controller
{
   // 🔹 Show logo list (normally 1 row)
    public function index()
    {
        $logo =Logosetting::first();
        return view('admin.logosetting.index', compact('logo'));
    }

    // 🔹 Show create form
    public function create()
    {
        return view('admin.logosetting.create');
    }

    // 🔹 Store logo
    public function store(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $image = $request->file('photo');
        $imageName = time().'.'.$image->getClientOriginalExtension();
        $image->move(public_path('uploads/logo'), $imageName);

        Logosetting::create([
            'photo' => $imageName,
        ]);

        return redirect()->route('logosetting.index')->with('success','Logo added successfully');
    }

    // 🔹 Edit logo
    public function edit(string $id)
    {
        $logo = Logosetting::findOrFail($id);
        return view('admin.logosetting.edit', compact('logo'));
    }

    // 🔹 Update logo
    public function update(Request $request, string $id)
    {
        $logo = Logosetting::findOrFail($id);

        if ($request->hasFile('photo')) {
            // old image delete
            if ($logo->photo && file_exists(public_path('uploads/logo/'.$logo->photo))) {
                unlink(public_path('uploads/logo/'.$logo->photo));
            }

            $image = $request->file('photo');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/logo'), $imageName);

            $logo->photo = $imageName;
        }

        $logo->save();

        return redirect()->route('logosetting.index')->with('success','Logo updated successfully');
    }

    // 🔹 Delete logo
    public function destroy(string $id)
    {
        $logo = Logosetting::findOrFail($id);

        if ($logo->photo && file_exists(public_path('uploads/logo/'.$logo->photo))) {
            unlink(public_path('uploads/logo/'.$logo->photo));
        }

        $logo->delete();

        return redirect()->route('logosetting.index')->with('success','Logo deleted successfully');
    }
}
