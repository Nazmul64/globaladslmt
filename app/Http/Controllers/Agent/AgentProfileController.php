<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentProfileController extends Controller
{
    public function agent_profile(){
       return view('agent.profile.index');
    }

public function agent_profile_update(Request $request)
{
    $user = Auth::user();

    // Check if user is an agent
    if ($user->role !== 'agent') {
        return redirect()->back()->with('error', 'You are not authorized to update this profile.');
    }

    // Validate the request
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
    ]);

    // Update name and email
    $user->name = $request->name;
    $user->email = $request->email;

    // Handle photo upload
    if ($request->hasFile('photo')) {
        // Delete old photo if exists
        if ($user->photo && file_exists(public_path('uploads/agent/' . $user->photo))) {
            unlink(public_path('uploads/agent/' . $user->photo));
        }

        $file = $request->file('photo');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/agent'), $filename);
        $user->photo = $filename;
    }

    $user->save();

    return redirect()->back()->with('success', 'Profile updated successfully!');
}

}
