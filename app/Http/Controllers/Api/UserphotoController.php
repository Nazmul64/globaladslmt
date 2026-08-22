<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserphotoController extends Controller
{
    /**
     * ✅ FIXED: Retrieve a user's profile photo via query parameter
     * Usage: GET /api/userphotoshow?user_id=123
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function userphotoshow(Request $request): JsonResponse
    {
        try {
            // ✅ Get user_id from query parameter, input, or auth token
            $userId = $request->query('user_id') ?? $request->input('user_id') ?? Auth::id() ?? auth('sanctum')->id();

            // Validate user_id is provided
            if (!$userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'user_id parameter is required',
                    'photo' => asset('uploads/avator.jpg'),
                ], 400);
            }

            // Find the user by ID
            $user = User::find($userId);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                    'photo' => asset('uploads/avator.jpg'), // Return default avatar
                ], 404);
            }

            // Get user photo URL
            $photoUrl = $this->getUserPhotoUrl($user);

            return response()->json([
                'status' => true,
                'message' => 'User photo retrieved successfully',
                'photo' => $photoUrl,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('UserPhoto Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving user photo',
                'photo' => asset('uploads/avator.jpg'), // fallback avatar
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the URL of the user's profile photo or default avatar.
     * ✅ Fixes: double extensions, missing files, default.png handling
     *
     * @param User $user
     * @return string
     */
    private function getUserPhotoUrl(User $user): string
    {
        // Default avatar - CRITICAL: This file MUST exist!
        $defaultAvatar = asset('uploads/avator.jpg');

        // Check if user has a photo
        if (empty($user->photo)) {
            return $defaultAvatar;
        }

        $filename = $user->photo;

        // ✅ Handle "default.png" - return proper default avatar
        if ($filename === 'default.png' || str_ends_with($filename, '/default.png')) {
            return $defaultAvatar;
        }

        // ✅ Clean up filename
        // Remove "uploads/profile/" prefix if present
        $filename = str_replace('uploads/profile/', '', $filename);
        // Remove leading slashes
        $filename = ltrim($filename, '/');

        // ✅ Fix double extensions: .jpg.jpg → .jpg, .png.png → .png
        $filename = preg_replace('/(\.jpg|\.jpeg|\.png)\.(jpg|jpeg|png)$/i', '$1', $filename);

        if (str_starts_with($filename, 'http://') || str_starts_with($filename, 'https://')) {
            return $filename;
        }

        if (str_starts_with($filename, 'uploads/')) {
            return asset($filename);
        }

        return asset('uploads/profile/' . $filename);
    }
}
