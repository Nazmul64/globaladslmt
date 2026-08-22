<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Settinglogo;

class SettingLogoController extends Controller
{
    // ðŸ”¹ Show logo (normally only 1 row)
    public function index()
    {
        $logo = Settinglogo::first(); // âœ… Model call
        return view('admin.logosetting.index', compact('logo'));
    }

    // ðŸ”¹ Show create form
    public function create()
    {
        return view('admin.logosetting.create');
    }

    // ðŸ”¹ Store logo
    public function store(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $image = $request->file('photo');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('uploads/logo'), $imageName);

        Settinglogo::create([   // âœ… FIXED (not S::create)
            'photo' => $imageName,
        ]);

        return redirect()->route('logosetting.index')
            ->with('success', 'Logo added successfully');
    }

    // ðŸ”¹ Edit logo
    public function edit($id)
    {
        $logo = SettingLogo::findOrFail($id);
        return view('admin.logosetting.edit', compact('logo'));
    }

    // ðŸ”¹ Update logo
    public function update(Request $request, $id)
    {
        $logo = Settinglogo::findOrFail($id);

        if ($request->hasFile('photo')) {

            if ($logo->photo && file_exists(public_path('uploads/logo/' . $logo->photo))) {
                unlink(public_path('uploads/logo/' . $logo->photo));
            }

            $image = $request->file('photo');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/logo'), $imageName);

            $logo->photo = $imageName;
        }

        $logo->save();

        return redirect()->route('logosetting.index')
            ->with('success', 'Logo updated successfully');
    }

    // ðŸ”¹ Delete logo
    public function destroy($id)
    {
        $logo = Settinglogo::findOrFail($id);

        if ($logo->photo && file_exists(public_path('uploads/logo/' . $logo->photo))) {
            unlink(public_path('uploads/logo/' . $logo->photo));
        }

        $logo->delete();

        return redirect()->route('logosetting.index')
            ->with('success', 'Logo deleted successfully');
    }
}
