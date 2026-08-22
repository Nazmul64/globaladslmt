<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Kyc;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class KycsubmitforuserController extends BaseController
{
    /**
     * Submit KYC
     */
    public function kycsubmit(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->sendError('User not authenticated', [], 401);
            }

            // check existing pending / approved
            $exists = Kyc::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            if ($exists) {
                return $this->sendError(
                    'You have already submitted KYC',
                    [
                        'kyc_id' => $exists->id,
                        'status' => $exists->status
                    ],
                    403
                );
            }

            // validation
            $request->validate([
                'document_type' => 'required|string|max:255',
                'document_first_part_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
                'document_secound_part_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            // upload images (ONLY filename)
            $frontImage = $this->uploadKycImage(
                $request->file('document_first_part_photo'),
                $user->id,
                'front'
            );

            $backImage = $this->uploadKycImage(
                $request->file('document_secound_part_photo'),
                $user->id,
                'back'
            );

            // store KYC
            $kyc = Kyc::create([
                'user_id' => $user->id,
                'document_type' => $request->document_type,
                'document_first_part_photo' => $frontImage,
                'document_secound_part_photo' => $backImage,
                'status' => 'pending',
            ]);

            return $this->sendResponse([
                'kyc_id' => $kyc->id,
                'status' => $kyc->status,
                'document_type' => $kyc->document_type,
                'first_photo_url' => url('uploads/kyc/' . $frontImage),
                'second_photo_url' => url('uploads/kyc/' . $backImage),
                'submitted_at' => $kyc->created_at->format('Y-m-d H:i:s'),
            ], 'KYC submitted successfully');

        } catch (ValidationException $e) {
            return $this->sendError('Validation failed', $e->errors(), 422);

        } catch (Exception $e) {
            Log::error('KYC submit error', ['error' => $e->getMessage()]);
            return $this->sendError('Something went wrong', [], 500);
        }
    }

    /**
     * Upload KYC Image
     * Location: public/uploads/kyc
     */
    private function uploadKycImage($file, $userId, $side): string
    {
        $path = public_path('uploads/kyc');

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $filename = 'kyc_' . $userId . '_' . $side . '_' . time() . '_' . uniqid() . '.' .
            $file->getClientOriginalExtension();

        $file->move($path, $filename);

        return $filename; // only filename
    }

    /**
     * Get KYC Status
     */
    public function kycStatus()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->sendError('User not authenticated', [], 401);
            }

            $kyc = Kyc::where('user_id', $user->id)->latest()->first();

            if (!$kyc) {
                return $this->sendResponse([
                    'status' => 'not_submitted'
                ], 'No KYC found');
            }

            $data = [
                'kyc_id' => $kyc->id,
                'document_type' => $kyc->document_type,
                'status' => $kyc->status,
                'status_message' => $this->statusMessage($kyc->status),
                'submitted_at' => $kyc->created_at->format('Y-m-d H:i:s'),
            ];

            if (in_array($kyc->status, ['pending', 'approved'])) {
                $data['first_photo_url'] = url('uploads/kyc/' . $kyc->document_first_part_photo);
                $data['second_photo_url'] = url('uploads/kyc/' . $kyc->document_secound_part_photo);
            }

            if ($kyc->status === 'rejected') {
                $data['can_resubmit'] = true;
                $data['rejection_reason'] = $kyc->rejection_reason ?? 'Please submit valid documents';
            }

            return $this->sendResponse($data, 'KYC status fetched');

        } catch (Exception $e) {
            return $this->sendError('Failed to fetch KYC status', [], 500);
        }
    }

    /**
     * Resubmit KYC
     */
    public function kycResubmit(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->sendError('User not authenticated', [], 401);
        }

        $kyc = Kyc::where('user_id', $user->id)->latest()->first();

        if (!$kyc || $kyc->status !== 'rejected') {
            return $this->sendError('Only rejected KYC can be resubmitted', [], 400);
        }

        $this->deleteOldImages($kyc);
        $kyc->delete();

        return $this->kycsubmit($request);
    }

    /**
     * Delete old images
     */
    private function deleteOldImages(Kyc $kyc): void
    {
        $files = [
            public_path('uploads/kyc/' . $kyc->document_first_part_photo),
            public_path('uploads/kyc/' . $kyc->document_secound_part_photo),
        ];

        foreach ($files as $file) {
            if (File::exists($file)) {
                File::delete($file);
            }
        }
    }

    /**
     * Status Message
     */
    private function statusMessage(string $status): string
    {
        return match ($status) {
            'pending' => 'Your KYC is under review',
            'approved' => 'Your KYC has been approved',
            'rejected' => 'Your KYC was rejected',
            default => 'Unknown status',
        };
    }
}
