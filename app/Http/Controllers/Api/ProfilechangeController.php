<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;


class ProfileChangeController extends BaseController
{
    /**
     * Get authenticated user profile
     *
     * @return \Illuminate\Http\JsonResponse
     */

 public function chatProfileUser(Request $request): JsonResponse
    {
        try {
            /* ------------------------------------------------
             | 1️⃣ Validate Request
             ------------------------------------------------ */
            $validated = $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
            ]);

            /* ------------------------------------------------
             | 2️⃣ Fetch User
             ------------------------------------------------ */
            $user = User::select(
                    'id',
                    'name',
                    'photo',
                    'role',
                    'created_at'
                )
                ->where('id', $validated['user_id'])
                ->first();

            if (!$user) {
                return $this->sendError('User not found', [], 404);
            }

            /* ------------------------------------------------
             | 3️⃣ Profile Photo Handling (Safe)
             ------------------------------------------------ */
            $defaultAvatar = asset('uploads/avator.jpg');
            $photoUrl = $defaultAvatar;

            if (!empty($user->photo)) {
                $photoPath = public_path('uploads/profile/' . $user->photo);

                if (file_exists($photoPath)) {
                    $photoUrl = asset('uploads/profile/' . $user->photo);
                }
            }

            /* ------------------------------------------------
             | 4️⃣ Prepare Response Data
             ------------------------------------------------ */
            $profileData = [
                'id'         => $user->id,
                'name'       => $user->name,
                'photo'      => $photoUrl,
                'photo_name' => $user->photo,
                'role'       => $user->role ?? 'user',
                'created_at' => $user->created_at->toDateTimeString(),
            ];

            /* ------------------------------------------------
             | 5️⃣ Success Response
             ------------------------------------------------ */
            return $this->sendResponse(
                $profileData,
                'User profile retrieved successfully'
            );

        } catch (\Illuminate\Validation\ValidationException $e) {

            return $this->sendError(
                'Validation failed',
                $e->errors(),
                422
            );

        } catch (\Throwable $e) {

            Log::error('ChatProfileUser Error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return $this->sendError(
                'Server error',
                ['error' => 'Unable to fetch user profile'],
                500
            );
        }
    }

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
                'photo'      => $user->photo ? asset('uploads/profile/' . $user->photo) : null,
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
     * Supports partial updates - only provided fields will be updated
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

            // Build validation rules dynamically
            $rules = [];
            $messages = [];

            // Only validate name if provided
            if ($request->has('name') && $request->name !== null) {
                $rules['name'] = 'required|string|min:2|max:255';
                $messages['name.required'] = 'Name cannot be empty if provided.';
                $messages['name.min'] = 'Name must be at least 2 characters.';
                $messages['name.max'] = 'Name cannot exceed 255 characters.';
            }

            // Only validate email if provided
            if ($request->has('email') && $request->email !== null) {
                $rules['email'] = 'required|email|unique:users,email,' . $user->id;
                $messages['email.required'] = 'Email cannot be empty if provided.';
                $messages['email.email'] = 'Please enter a valid email address.';
                $messages['email.unique'] = 'This email is already taken by another user.';
            }

            // Always validate photo if file is uploaded
            if ($request->hasFile('photo')) {
                $rules['photo'] = 'image|mimes:jpeg,png,jpg,gif,webp|max:5120'; // 5MB max
                $messages['photo.image'] = 'File must be an image.';
                $messages['photo.mimes'] = 'Image must be jpeg, png, jpg, gif or webp format.';
                $messages['photo.max'] = 'Image size cannot exceed 5MB.';
            }

            // Validate request
            $validator = Validator::make($request->all(), $rules, $messages);

            // Check validation errors
            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            // Track if any changes were made
            $changesMade = false;

            // Update name if provided and different from current
            if ($request->has('name') && $request->name !== null) {
                $newName = trim($request->name);
                if ($newName !== $user->name) {
                    $user->name = $newName;
                    $changesMade = true;
                    Log::info('Name updated', ['old' => $user->name, 'new' => $newName]);
                }
            }

            // Update email if provided and different from current
            if ($request->has('email') && $request->email !== null) {
                $newEmail = strtolower(trim($request->email));
                if ($newEmail !== $user->email) {
                    $user->email = $newEmail;
                    $changesMade = true;
                    Log::info('Email updated', ['old' => $user->email, 'new' => $newEmail]);
                }
            }

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $uploadedPhoto = $this->handlePhotoUpload($request->file('photo'), $user->photo);

                if ($uploadedPhoto['success']) {
                    $user->photo = $uploadedPhoto['filename'];
                    $changesMade = true;
                    Log::info('Photo updated', ['filename' => $uploadedPhoto['filename']]);
                } else {
                    Log::error('Photo upload failed', ['error' => $uploadedPhoto['error']]);
                    return $this->sendError('Photo upload failed.', ['error' => $uploadedPhoto['error']], 500);
                }
            }

            // Save user if changes were made
            if ($changesMade) {
                if ($user->save()) {
                    // Prepare response data
                    $responseData = [
                        'id'         => $user->id,
                        'name'       => $user->name,
                        'email'      => $user->email,
                        'photo'      => $user->photo ? asset('uploads/profile/' . $user->photo) : null,
                        'photo_name' => $user->photo,
                        'role'       => $user->role ?? 'user',
                        'updated_at' => $user->updated_at,
                    ];

                    Log::info('Profile updated successfully', ['user_id' => $user->id]);

                    return $this->sendResponse($responseData, 'Profile updated successfully.');
                } else {
                    Log::error('Failed to save user profile', ['user_id' => $user->id]);
                    return $this->sendError('Failed to update profile.', [], 500);
                }
            } else {
                // No changes detected
                $responseData = [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'photo'      => $user->photo ? asset('uploads/profile/' . $user->photo) : null,
                    'photo_name' => $user->photo,
                    'role'       => $user->role ?? 'user',
                    'updated_at' => $user->updated_at,
                ];

                Log::info('No changes detected in profile update', ['user_id' => $user->id]);
                return $this->sendResponse($responseData, 'No changes were made to the profile.');
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
            $extension = strtolower($file->getClientOriginalExtension());

            // Create clean filename without original name to avoid double extensions
            $timestamp = time();
            $uniqueId = uniqid();
            $filename = "profile_{$timestamp}_{$uniqueId}.{$extension}";

            // Additional sanitization (should already be clean but just in case)
            $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $filename);

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
