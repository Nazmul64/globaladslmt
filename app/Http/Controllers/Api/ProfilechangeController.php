<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileChangeController extends BaseController
{
    /**
     * Get authenticated user profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->sendError('User not authenticated.', [], 401);
            }

            $profileData = [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'photo'      => $user->photo ? url('uploads/profile/' . $user->photo) : null,
                'photo_name' => $user->photo,
                'role'       => $user->role ?? 'user',
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];

            return $this->sendResponse($profileData, 'Profile retrieved successfully.');

        } catch (\Exception $e) {
            Log::error('Get Profile Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Server Error', ['error' => 'Unable to retrieve profile.'], 500);
        }
    }

    /**
     * Update user profile (name, email, photo)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profileUpdate(Request $request)
    {
        try {
            // Get authenticated user
            $user = Auth::user();

            if (!$user) {
                return $this->sendError('User not authenticated.', [], 401);
            }

            // Log the incoming request for debugging
            Log::info('Profile Update Request', [
                'user_id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
                'has_photo' => $request->hasFile('photo')
            ]);

            // Validate request
            $validator = Validator::make($request->all(), [
                'name'  => 'required|string|min:2|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            ], [
                'name.required'     => 'Name is required.',
                'name.min'          => 'Name must be at least 2 characters.',
                'name.max'          => 'Name cannot exceed 255 characters.',
                'email.required'    => 'Email is required.',
                'email.email'       => 'Please enter a valid email address.',
                'email.unique'      => 'This email is already taken by another user.',
                'photo.image'       => 'File must be an image.',
                'photo.mimes'       => 'Image must be jpeg, png, jpg, gif or webp format.',
                'photo.max'         => 'Image size cannot exceed 5MB.',
            ]);

            // Check validation errors
            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            // Store old values for rollback if needed
            $oldName = $user->name;
            $oldEmail = $user->email;
            $oldPhoto = $user->photo;

            // Update name and email
            $user->name = trim($request->name);
            $user->email = strtolower(trim($request->email));

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $uploadedPhoto = $this->handlePhotoUpload($request->file('photo'), $user->photo);

                if ($uploadedPhoto['success']) {
                    $user->photo = $uploadedPhoto['filename'];
                } else {
                    Log::error('Photo upload failed', ['error' => $uploadedPhoto['error']]);
                    return $this->sendError('Photo upload failed.', ['error' => $uploadedPhoto['error']], 500);
                }
            }

            // Save user
            if ($user->save()) {
                // Prepare response data
                $responseData = [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'photo'      => $user->photo ? url('uploads/profile/' . $user->photo) : null,
                    'photo_name' => $user->photo,
                    'role'       => $user->role ?? 'user',
                    'updated_at' => $user->updated_at,
                ];

                Log::info('Profile updated successfully', ['user_id' => $user->id]);

                return $this->sendResponse($responseData, 'Profile updated successfully.');
            } else {
                // Rollback on failure
                Log::error('Failed to save user profile', ['user_id' => $user->id]);
                return $this->sendError('Failed to update profile.', [], 500);
            }

        } catch (\Exception $e) {
            Log::error('Profile Update Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return $this->sendError('Server Error', ['error' => 'Unable to update profile. Please try again.'], 500);
        }
    }

    /**
     * Handle photo upload with validation and cleanup
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string|null $oldPhoto
     * @return array
     */
    private function handlePhotoUpload($file, $oldPhoto = null)
    {
        try {
            // Validate file
            if (!$file->isValid()) {
                return ['success' => false, 'error' => 'Invalid file upload.'];
            }

            // Create upload directory if not exists
            $uploadPath = public_path('uploads/profile');
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }

            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            // Sanitize filename
            $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $filename);
            $filename = substr($filename, 0, 100) . '.' . $extension;

            // Move file to upload directory
            if ($file->move($uploadPath, $filename)) {
                // Delete old photo after successful upload
                if ($oldPhoto) {
                    $oldPhotoPath = public_path('uploads/profile/' . $oldPhoto);
                    if (File::exists($oldPhotoPath)) {
                        File::delete($oldPhotoPath);
                        Log::info('Old photo deleted', ['filename' => $oldPhoto]);
                    }
                }

                return ['success' => true, 'filename' => $filename];
            } else {
                return ['success' => false, 'error' => 'Failed to save file.'];
            }

        } catch (\Exception $e) {
            Log::error('Photo Upload Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete user profile photo
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePhoto()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->sendError('User not authenticated.', [], 401);
            }

            if ($user->photo) {
                $photoPath = public_path('uploads/profile/' . $user->photo);
                if (File::exists($photoPath)) {
                    File::delete($photoPath);
                }

                $user->photo = null;
                $user->save();

                return $this->sendResponse([], 'Photo deleted successfully.');
            }

            return $this->sendError('No photo to delete.', [], 404);

        } catch (\Exception $e) {
            Log::error('Delete Photo Error: ' . $e->getMessage());
            return $this->sendError('Server Error', ['error' => 'Unable to delete photo.'], 500);
        }
    }
}
