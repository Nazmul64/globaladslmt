<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kyc;
use Exception;
use Illuminate\Support\Facades\Log;
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
                Log::warning('KYC Submit: User not authenticated');
                return $this->sendError('User not authenticated', [], 401);
            }

            Log::info('KYC Submit Request', [
                'user_id' => $user->id,
                'document_type' => $request->document_type,
            ]);

            // Check for existing pending or approved KYC
            $existing = Kyc::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            if ($existing) {
                Log::warning('KYC already exists', [
                    'user_id' => $user->id,
                    'status' => $existing->status
                ]);

                return $this->sendError(
                    'আপনি ইতিমধ্যে KYC জমা দিয়েছেন। অনুগ্রহ করে এডমিন রিভিউ এর জন্য অপেক্ষা করুন।',
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
            ], [
                'document_type.required' => 'ডকুমেন্ট টাইপ নির্বাচন করুন',
                'document_first_part_photo.required' => 'সামনের ফটো আপলোড করুন',
                'document_first_part_photo.image' => 'সামনের ফাইল অবশ্যই একটি ছবি হতে হবে',
                'document_first_part_photo.max' => 'সামনের ফটো সর্বোচ্চ 2MB হতে পারে',
                'document_secound_part_photo.required' => 'পিছনের ফটো আপলোড করুন',
                'document_secound_part_photo.image' => 'পিছনের ফাইল অবশ্যই একটি ছবি হতে হবে',
                'document_secound_part_photo.max' => 'পিছনের ফটো সর্বোচ্চ 2MB হতে পারে',
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

            Log::info('KYC submitted successfully', [
                'kyc_id' => $kyc->id,
                'user_id' => $user->id,
                'status' => $kyc->status
            ]);

            // Return response
            return $this->sendResponse([
                'kyc_id' => $kyc->id,
                'user_id' => $user->id,
                'document_type' => $kyc->document_type,
                'status' => $kyc->status,
                'first_photo_url' => url($firstPhotoPath),
                'second_photo_url' => url($secondPhotoPath),
                'submitted_at' => $kyc->created_at->format('Y-m-d H:i:s'),
            ], 'KYC সফলভাবে জমা হয়েছে। আপনার ডকুমেন্ট ২৪-৪৮ ঘণ্টার মধ্যে যাচাই করা হবে।');

        } catch (ValidationException $e) {
            Log::warning('KYC validation failed', [
                'user_id' => Auth::id(),
                'errors' => $e->errors()
            ]);

            return $this->sendError(
                'ভ্যালিডেশন ব্যর্থ হয়েছে',
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
                'KYC জমা দিতে ব্যর্থ হয়েছে। দয়া করে আবার চেষ্টা করুন।',
                ['error' => $e->getMessage()],
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
                Log::info('Created KYC upload directory', ['path' => $uploadPath]);
            }

            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = 'kyc_' . $userId . '_' . $side . '_' . time() . '_' . uniqid() . '.' . $extension;

            // Move file to public/uploads/kyc
            $file->move($uploadPath, $filename);

            $relativePath = 'uploads/kyc/' . $filename;

            Log::info('File uploaded successfully', [
                'user_id' => $userId,
                'side' => $side,
                'filename' => $filename,
                'path' => $relativePath
            ]);

            // Return relative path
            return $relativePath;

        } catch (Exception $e) {
            Log::error('File upload failed', [
                'user_id' => $userId,
                'side' => $side,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new Exception('ফাইল আপলোড ব্যর্থ হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Get KYC status for authenticated user
     */
    public function kycStatus()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                Log::warning('KYC Status: User not authenticated');
                return $this->sendError('User not authenticated', [], 401);
            }

            Log::info('Fetching KYC status', ['user_id' => $user->id]);

            // Get latest KYC submission
            $kyc = Kyc::where('user_id', $user->id)
                ->latest()
                ->first();

            if (!$kyc) {
                Log::info('No KYC found for user', ['user_id' => $user->id]);

                return $this->sendResponse([
                    'status' => 'not_submitted',
                    'message' => 'আপনি এখনো KYC জমা দেননি।'
                ], 'No KYC submitted');
            }

            // Prepare response data
            $data = [
                'kyc_id' => $kyc->id,
                'document_type' => $kyc->document_type,
                'status' => $kyc->status,
                'status_message' => $this->getStatusMessage($kyc->status),
                'submitted_at' => $kyc->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $kyc->updated_at->format('Y-m-d H:i:s'),
            ];

            // Add photo URLs if status is pending or approved
            if (in_array($kyc->status, ['pending', 'approved'])) {
                $data['first_photo_url'] = url($kyc->document_first_part_photo);
                $data['second_photo_url'] = url($kyc->document_secound_part_photo);
            }

            // Add rejection info if rejected
            if ($kyc->status === 'rejected') {
                $data['rejection_reason'] = $kyc->rejection_reason ?? 'আপনার KYC প্রত্যাখ্যাত হয়েছে। সঠিক ডকুমেন্ট দিয়ে পুনরায় জমা দিন।';
                $data['can_resubmit'] = true;
            }

            // Add verified date if approved
            if ($kyc->status === 'approved' && isset($kyc->approved_at)) {
                $data['verified_at'] = $kyc->approved_at;
            }

            Log::info('KYC status retrieved', [
                'user_id' => $user->id,
                'status' => $kyc->status
            ]);

            return $this->sendResponse($data, 'KYC status retrieved successfully');

        } catch (Exception $e) {
            Log::error('Error fetching KYC status', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError(
                'KYC স্ট্যাটাস লোড করতে ব্যর্থ হয়েছে',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Resubmit KYC after rejection
     */
    public function kycResubmit(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                Log::warning('KYC Resubmit: User not authenticated');
                return $this->sendError('User not authenticated', [], 401);
            }

            Log::info('KYC Resubmit Request', ['user_id' => $user->id]);

            // Get latest KYC
            $latestKyc = Kyc::where('user_id', $user->id)
                ->latest()
                ->first();

            if (!$latestKyc) {
                Log::warning('No existing KYC found for resubmit', ['user_id' => $user->id]);
                return $this->sendError(
                    'কোন KYC রেকর্ড পাওয়া যায়নি',
                    [],
                    404
                );
            }

            // Check if status is rejected
            if ($latestKyc->status !== 'rejected') {
                Log::warning('Cannot resubmit non-rejected KYC', [
                    'user_id' => $user->id,
                    'current_status' => $latestKyc->status
                ]);

                return $this->sendError(
                    'শুধুমাত্র প্রত্যাখ্যাত KYC পুনরায় জমা দেওয়া যাবে',
                    ['current_status' => $latestKyc->status],
                    400
                );
            }

            // Delete old rejected files
            $this->deleteOldFiles($latestKyc);

            // Delete the rejected KYC record
            $latestKyc->delete();

            Log::info('Old rejected KYC deleted', [
                'user_id' => $user->id,
                'old_kyc_id' => $latestKyc->id
            ]);

            // Submit new KYC using the same method
            return $this->kycsubmit($request);

        } catch (Exception $e) {
            Log::error('KYC resubmission error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError(
                'KYC পুনরায় জমা দিতে ব্যর্থ হয়েছে',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Delete old KYC files from storage
     */
    private function deleteOldFiles($kyc)
    {
        try {
            // Delete first photo
            if ($kyc->document_first_part_photo) {
                $firstPath = public_path($kyc->document_first_part_photo);
                if (File::exists($firstPath)) {
                    File::delete($firstPath);
                    Log::info('Deleted old file', ['path' => $kyc->document_first_part_photo]);
                }
            }

            // Delete second photo
            if ($kyc->document_secound_part_photo) {
                $secondPath = public_path($kyc->document_secound_part_photo);
                if (File::exists($secondPath)) {
                    File::delete($secondPath);
                    Log::info('Deleted old file', ['path' => $kyc->document_secound_part_photo]);
                }
            }
        } catch (Exception $e) {
            Log::warning('Failed to delete old KYC files', [
                'kyc_id' => $kyc->id,
                'error' => $e->getMessage()
            ]);
            // Don't throw exception, just log warning
        }
    }

    /**
     * Get user-friendly status message in Bengali
     */
    private function getStatusMessage(string $status): string
    {
        return match($status) {
            'pending' => 'আপনার KYC রিভিউ হচ্ছে।',
            'approved' => 'আপনার KYC অনুমোদিত হয়েছে।',
            'rejected' => 'আপনার KYC প্রত্যাখ্যাত হয়েছে। অনুগ্রহ করে পুনরায় জমা দিন।',
            default => 'KYC স্ট্যাটাস অজানা।'
        };
    }
}
