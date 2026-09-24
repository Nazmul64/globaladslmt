<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settinglogo;
use App\Models\Logosetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SettinglogoController extends Controller
{
    // 🔹 Show logo (normally only 1 row)
    public function index()
    {
        $logo = Settinglogo::first() ?? Logosetting::first();
        return view('admin.logosetting.index', compact('logo'));
    }

    // 🔹 Show create form
    public function create()
    {
        $logo = Settinglogo::first() ?? Logosetting::first();
        return view('admin.logosetting.create', compact('logo'));
    }

    // 🔹 Store logo
    public function store(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:png,jpg,jpeg,svg,webp|max:5120',
        ], [
            'photo.required' => 'Please select an image file to upload.',
            'photo.image' => 'Uploaded file must be a valid image.',
            'photo.mimes' => 'Supported formats are PNG, JPG, JPEG, SVG, WEBP.',
            'photo.max' => 'Image size cannot exceed 5MB.',
        ]);

        try {
            $path = public_path('uploads/logo');
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }

            $image = $request->file('photo');
            $imageName = 'logo_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move($path, $imageName);

            // Update or create in Settinglogo
            $logo = Settinglogo::first();
            if ($logo) {
                if ($logo->photo && File::exists($path . '/' . $logo->photo)) {
                    @File::delete($path . '/' . $logo->photo);
                }
                $logo->photo = $imageName;
                $logo->save();
            } else {
                Settinglogo::create(['photo' => $imageName]);
            }

            // Also keep Logosetting in sync
            if (\Illuminate\Support\Facades\Schema::hasTable('logosettings')) {
                $ls = Logosetting::first();
                if ($ls) {
                    $ls->photo = $imageName;
                    $ls->save();
                } else {
                    Logosetting::create(['photo' => $imageName]);
                }
            }

            return redirect()->route('logosetting.index')
                ->with('success', 'Platform logo uploaded and saved successfully!');

        } catch (\Exception $e) {
            Log::error('Logo Upload Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to upload logo: ' . $e->getMessage());
        }
    }

    // 🔹 Edit logo
    public function edit($id)
    {
        $logo = Settinglogo::find($id) ?? Logosetting::find($id) ?? Settinglogo::first();
        return view('admin.logosetting.edit', compact('logo'));
    }

    // 🔹 Update logo
    public function update(Request $request, $id)
    {
        $request->validate([
            'photo' => 'required|image|mimes:png,jpg,jpeg,svg,webp|max:5120',
        ], [
            'photo.required' => 'Please select an image file to upload.',
            'photo.image' => 'Uploaded file must be a valid image.',
            'photo.mimes' => 'Supported formats are PNG, JPG, JPEG, SVG, WEBP.',
            'photo.max' => 'Image size cannot exceed 5MB.',
        ]);

        try {
            $logo = Settinglogo::find($id) ?? Settinglogo::first();
            $path = public_path('uploads/logo');
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }

            if ($request->hasFile('photo')) {
                if ($logo && $logo->photo && File::exists($path . '/' . $logo->photo)) {
                    @File::delete($path . '/' . $logo->photo);
                }

                $image = $request->file('photo');
                $imageName = 'logo_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move($path, $imageName);

                if ($logo) {
                    $logo->photo = $imageName;
                    $logo->save();
                } else {
                    Settinglogo::create(['photo' => $imageName]);
                }

                // Sync Logosetting
                if (\Illuminate\Support\Facades\Schema::hasTable('logosettings')) {
                    $ls = Logosetting::first();
                    if ($ls) {
                        $ls->photo = $imageName;
                        $ls->save();
                    } else {
                        Logosetting::create(['photo' => $imageName]);
                    }
                }
            }

            return redirect()->route('logosetting.index')
                ->with('success', 'Platform logo updated successfully!');

        } catch (\Exception $e) {
            Log::error('Logo Update Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update logo: ' . $e->getMessage());
        }
    }

    // 🔹 Delete logo
    public function destroy($id)
    {
        $logo = Settinglogo::find($id);

        if ($logo) {
            $path = public_path('uploads/logo/' . $logo->photo);
            if ($logo->photo && File::exists($path)) {
                @File::delete($path);
            }
            $logo->delete();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('logosettings')) {
            Logosetting::truncate();
        }

        return redirect()->route('logosetting.index')
            ->with('success', 'Logo deleted successfully');
    }
}

