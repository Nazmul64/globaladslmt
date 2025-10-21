<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kyc;

class KeyController extends Controller
{
    // Show KYC form
    public function frontend_key()
    {
        $user = Auth::user()->load('kyc'); // fetch KYC if exists
        return view('frontend.frontendpages.key', compact('user'));
    }

    // Handle KYC submission
    public function frontend_key_submit(Request $request)
    {
        $user = Auth::user();

        // Check if user already has a pending or verified KYC
        $existingKyc = Kyc::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingKyc) {
            return redirect()->back()->with('error', 'You cannot upload KYC again until admin reviews your previous submission.');
        }

        $request->validate([
            'document_type' => 'required|string',
            'document_first_part_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'document_secound_part_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Upload first photo
        $firstPhotoName = time().'_first.'.$request->document_first_part_photo->extension();
        $request->document_first_part_photo->move(public_path('uploads/kyc'), $firstPhotoName);

        // Upload second photo
        $secondPhotoName = time().'_second.'.$request->document_secound_part_photo->extension();
        $request->document_secound_part_photo->move(public_path('uploads/kyc'), $secondPhotoName);

        // Save KYC
        Kyc::create([
            'user_id' => $user->id,
            'document_type' => $request->document_type,
            'document_first_part_photo' => $firstPhotoName,
            'document_secound_part_photo' => $secondPhotoName,
            'status' => 'pending', // initial status pending
        ]);

        return redirect()->back()->with('success', 'KYC documents submitted successfully! Status: Pending.');
    }
public function frontend_kyc_approved()
{
    $kycs =Kyc::with('user')->where('status', 'approved')->latest()->get();
    return view('admin.key.frontend_kyc_approved', compact('kycs'));
}

public function frontend_kyc_reject_list()
{
    $kycs =Kyc::with('user')->where('status', 'rejected')->latest()->get();
    return view('admin.key.frontend_kyc_reject_list', compact('kycs'));
}

}
