<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File; // Correct import
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{

    public function frontend_main_profile()
    {
        $user = Auth::user();
        return view('frontend.frontendpages.main_profile', compact('user'));
    }
    public function frontend_profile_update(Request $request)
    {
        // Validate input
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.Auth::id(),
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = Auth::user();

        // Update name and email
        $user->name  = $request->name;
        $user->email = $request->email;

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');

            // Delete old photo if exists
            if ($user->photo && File::exists(public_path('uploads/profile/'.$user->photo))) {
                File::delete(public_path('uploads/profile/'.$user->photo));
            }

            // Store new photo
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/profile'), $filename);

            $user->photo = $filename;
        }

        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
    public function frontend_password_change()
    {
        return view('frontend.frontendpages.password_change');
    }
    public function frontend_password_submit(Request $request)
    {
        // Validate the request
        $request->validate([
            'new_password' => 'required|string|min:6',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|same:password',
        ]);

        $user = Auth::user();

        // Check if new password matches current password
        if (!Hash::check($request->new_password, $user->password)) {
            return back()->with('error', 'Your old password is incorrect.');
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }
}
