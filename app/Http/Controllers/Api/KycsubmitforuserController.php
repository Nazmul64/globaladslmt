<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kyc;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class KycsubmitforuserController extends BaseController
{
    /**
     * Submit KYC for authenticated user
     */
    public function kycsubmit(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->sendError('User not authenticated', [], 401);
            }

            // Check for existing KYC submission
            $existing = Kyc::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            if ($existing) {
                return $this->sendError(
                    'KYC already submitted. Please wait for admin review.',
                    [
                        'kyc_id' => $existing->id,
                        'status' => $existing->status
                    ],
                    403
                );
            }

            // Validate request
            $validated = $request->validate([
                'document_type' => 'required|string|max:255',
                'document_first_part_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
                'document_secound_part_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            // Upload files
            $firstPhotoPath = $this->uploadFile(
                $request->file('document_first_part_photo'),
                $user->id,
                'front'
            );

            $secondPhotoPath = $this->uploadFile(
                $request->file('document_secound_part_photo'),
                $user->id,
                'back'
            );

            // Create KYC record
            $kyc = Kyc::create([
                'user_id' => $user->id,
                'document_type' => $validated['document_type'],
                'document_first_part_photo' => $firstPhotoPath,
                'document_secound_part_photo' => $secondPhotoPath,
                'status' => 'pending',
            ]);

            // Return response
            return $this->sendResponse([
                'kyc_id' => $kyc->id,
                'user_id' => $user->id,
                'document_type' => $kyc->document_type,
                'status' => $kyc->status,
                'first_photo_url' => url($firstPhotoPath),
                'second_photo_url' => url($secondPhotoPath),
                'submitted_at' => $kyc->created_at->toDateTimeString(),
            ], 'KYC submitted successfully');

        } catch (ValidationException $e) {
            return $this->sendError(
                'Validation failed',
                ['errors' => $e->errors()],
                422
            );
        } catch (Exception $e) {
            Log::error('KYC submission failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError(
                'Failed to submit KYC',
                ['error' => 'Internal server error'],
                500
            );
        }
    }

    /**
     * Upload file to public/uploads/kyc directory
     */
    private function uploadFile($file, $userId, $side)
    {
        try {
            // Create directory if it doesn't exist
            $uploadPath = public_path('uploads/kyc');
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }

            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = 'kyc_' . $userId . '_' . $side . '_' . time() . '_' . uniqid() . '.' . $extension;

            // Move file to public/uploads/kyc
            $file->move($uploadPath, $filename);

            // Return relative path
            return 'uploads/kyc/' . $filename;

        } catch (Exception $e) {
            Log::error('File upload failed', [
                'user_id' => $userId,
                'side' => $side,
                'error' => $e->getMessage()
            ]);
            throw new Exception('File upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Get KYC status
     */
    public function getKycStatus()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->sendError('User not authenticated', [], 401);
            }

            $kyc = Kyc::where('user_id', $user->id)->latest()->first();

            if (!$kyc) {
                return $this->sendResponse([
                    'status' => 'not_submitted',
                    'message' => 'You have not submitted KYC yet.'
                ], 'No KYC submitted');
            }

            $data = [
                'kyc_id' => $kyc->id,
                'document_type' => $kyc->document_type,
                'status' => $kyc->status,
                'status_message' => $this->getStatusMessage($kyc->status),
                'submitted_at' => $kyc->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $kyc->updated_at->format('Y-m-d H:i:s'),
            ];

            if (in_array($kyc->status, ['pending', 'approved'])) {
                $data['first_photo_url'] = url($kyc->document_first_part_photo);
                $data['second_photo_url'] = url($kyc->document_secound_part_photo);
            }

            if ($kyc->status === 'rejected') {
                $data['rejection_reason'] = $kyc->rejection_reason ?? null;
                $data['can_resubmit'] = true;
            }

            return $this->sendResponse($data, 'KYC status retrieved successfully');

        } catch (Exception $e) {
            Log::error('Error fetching KYC status', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return $this->sendError('Failed to fetch KYC status', [], 500);
        }
    }

    /**
     * Resubmit KYC
     */
    public function resubmitKyc(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->sendError('User not authenticated', [], 401);
            }

            $latestKyc = Kyc::where('user_id', $user->id)->latest()->first();

            if (!$latestKyc || $latestKyc->status !== 'rejected') {
                return $this->sendError(
                    'Only rejected KYC can be resubmitted',
                    ['current_status' => $latestKyc->status ?? 'not_submitted'],
                    400
                );
            }

            // Delete old rejected files
            $this->deleteOldFiles($latestKyc);

            // Submit new KYC
            return $this->kycsubmit($request);

        } catch (Exception $e) {
            Log::error('KYC resubmission error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return $this->sendError('Failed to resubmit KYC', [], 500);
        }
    }

    /**
     * Delete old KYC files
     */
    private function deleteOldFiles($kyc)
    {
        try {
            if ($kyc->document_first_part_photo && File::exists(public_path($kyc->document_first_part_photo))) {
                File::delete(public_path($kyc->document_first_part_photo));
            }

            if ($kyc->document_secound_part_photo && File::exists(public_path($kyc->document_secound_part_photo))) {
                File::delete(public_path($kyc->document_secound_part_photo));
            }
        } catch (Exception $e) {
            Log::warning('Failed to delete old KYC files', [
                'kyc_id' => $kyc->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Helper: Get user-friendly status message
     */
    private function getStatusMessage(string $status): string
    {
        return match($status) {
            'pending' => 'Your KYC is under review.',
            'approved' => 'Your KYC has been approved.',
            'rejected' => 'Your KYC was rejected. Please resubmit.',
            default => 'KYC status unknown.'
        };
    }
}
