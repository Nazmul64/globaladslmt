<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Agentkyc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentkycController extends Controller
{
    /**
     * Show Agent KYC Page
     */
    public function agent_key()
    {
        $agent = Auth::user()->load('agentkyc'); // Load relation
        return view('agent.agent_key.kyc', compact('agent'));
    }

    /**
     * Handle KYC Submission
     */
    public function agent_key_submit(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string',
            'document_first_part_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'document_secound_part_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Check if existing KYC pending or approved
        $existingKyc = Agentkyc::where('user_id', $user->id)->first();
        if ($existingKyc && in_array($existingKyc->status, ['pending', 'approved'])) {
            return back()->with('error', 'আপনি পুনরায় KYC জমা দিতে পারবেন না যতক্ষণ না এটি পর্যালোচনা করা হয়।');
        }

        // Upload first photo
        $firstPhoto = $request->file('document_first_part_photo');
        $firstPhotoName = time() . '_first_' . $firstPhoto->getClientOriginalName();
        $firstPhoto->move(public_path('uploads/agent_kyc'), $firstPhotoName);

        // Upload second photo
        $secondPhoto = $request->file('document_secound_part_photo');
        $secondPhotoName = time() . '_second_' . $secondPhoto->getClientOriginalName();
        $secondPhoto->move(public_path('uploads/agent_kyc'), $secondPhotoName);

        // Store or update KYC
        Agentkyc::updateOrCreate(
            ['user_id' => $user->id],
            [
                'document_type' => $request->document_type,
                'document_first_part_photo' => $firstPhotoName,
                'document_secound_part_photo' => $secondPhotoName,
                'status' => 'pending',
            ]
        );

        return redirect()->back()->with('success', 'KYC সফলভাবে জমা হয়েছে! অ্যাডমিনের অনুমোদনের জন্য অপেক্ষা করুন।');
    }
}
