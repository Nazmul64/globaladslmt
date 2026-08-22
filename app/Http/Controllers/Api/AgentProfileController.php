<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Themechange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AgentProfileController extends Controller
{
    /**
     * ✅ Get Agent Photo (Dynamic)
     * যদি এজেন্ট ফটো আপলোড করে তাহলে সেটা দেখাবে, না হলে default avatar দেখাবে
     */
    public function userphotoshow(Request $request)
    {
        try {
            // Validate user_id
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return $this->jsonResponse(false, 'Invalid user_id parameter', [
                    'photo' => $this->getDefaultAvatar()
                ], 400);
            }

            $userId = (int) $request->input('user_id');

            // Find user
            $user = User::select('id', 'photo')->find($userId);

            if (!$user) {
                return $this->jsonResponse(false, 'User not found', [
                    'photo' => $this->getDefaultAvatar()
                ], 404);
            }

            // Get photo URL (dynamic or default)
            $photoUrl = $this->resolveUserPhoto($user->photo);

            return $this->jsonResponse(true, 'User photo retrieved successfully', [
                'user_id' => $user->id,
                'photo' => $photoUrl,
            ]);

        } catch (\Throwable $e) {
            Log::error('User Photo API Error', [
                'user_id' => $request->input('user_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->jsonResponse(false, 'Server error occurred', [
                'photo' => $this->getDefaultAvatar()
            ], 500);
        }
    }

    /**
     * ✅ Get Theme Color (Fully Dynamic)
     * ডাটাবেস থেকে active theme আসবে, না থাকলে default theme
     */
    public function themechange(Request $request)
    {
        try {
            // Step 1: Try to get active theme
            $theme = Themechange::where('is_active', true)
                ->latest('published_at')
                ->first();

            // Step 2: If no active theme, get latest theme
            if (!$theme) {
                $theme = Themechange::latest('created_at')->first();
            }

            // Step 3: If no theme exists at all, return default
            if (!$theme) {
                return $this->jsonResponse(true, 'No theme found, using default', [
                    'color_code' => '#4361EE',
                    'name' => 'Default Blue',
                    'is_active' => true,
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
            }

            // Step 4: Return found theme
            return $this->jsonResponse(true, 'Theme fetched successfully', [
                'color_code' => $theme->color_code,
                'name' => $theme->name ?? 'Custom Theme',
                'is_active' => (bool) $theme->is_active,
                'updated_at' => $theme->updated_at->format('Y-m-d H:i:s'),
            ]);

        } catch (\Throwable $e) {
            Log::error('Theme API Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return default theme on error
            return $this->jsonResponse(false, 'Error fetching theme', [
                'color_code' => '#4361EE',
                'name' => 'Default Blue',
                'is_active' => true,
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ], 500);
        }
    }

    /**
     * ✅ Admin Panel থেকে Theme Update করার API
     * Admin এখান থেকে নতুন theme color সেট করতে পারবে
     */
    public function updateTheme(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'color_code' => 'required|string|regex:/^#[A-Fa-f0-9]{6}$/|max:7',
                'name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->jsonResponse(false, 'Validation failed', [
                    'errors' => $validator->errors()
                ], 422);
            }

            // Deactivate all existing themes
            Themechange::query()->update(['is_active' => false]);

            // Create new active theme
            $theme = Themechange::create([
                'color_code' => strtoupper($request->color_code),
                'name' => $request->name ?? 'Custom Theme',
                'is_active' => true,
                'published_at' => now(),
            ]);

            return $this->jsonResponse(true, 'Theme updated successfully', [
                'color_code' => $theme->color_code,
                'name' => $theme->name,
                'is_active' => true,
                'updated_at' => $theme->updated_at->format('Y-m-d H:i:s'),
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Update Theme API Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->jsonResponse(false, 'Failed to update theme', null, 500);
        }
    }

    /**
     * ✅ Get All Themes (Optional - for admin panel)
     */
    public function getAllThemes(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $themes = Themechange::orderBy('created_at', 'desc')
                ->paginate($perPage);

            return $this->jsonResponse(true, 'Themes fetched successfully', [
                'themes' => $themes->items(),
                'pagination' => [
                    'current_page' => $themes->currentPage(),
                    'total_pages' => $themes->lastPage(),
                    'total_items' => $themes->total(),
                    'per_page' => $themes->perPage(),
                ]
            ]);

        } catch (\Throwable $e) {
            Log::error('Get All Themes Error', [
                'error' => $e->getMessage()
            ]);

            return $this->jsonResponse(false, 'Failed to fetch themes', null, 500);
        }
    }

    /**
     * 🔧 PRIVATE HELPER METHODS
     */

    /**
     * Resolve user photo - return dynamic or default
     */
    private function resolveUserPhoto(?string $photo): string
    {
        // If no photo, return default
        if (empty($photo)) {
            return $this->getDefaultAvatar();
        }

        // Extract filename
        $filename = $this->extractFilename($photo);

        // Validate image extension
        if (!$this->isValidImageExtension($filename)) {
            return $this->getDefaultAvatar();
        }

        // Check if file exists in public/uploads/agent/
        $path = public_path('uploads/agent/' . $filename);

        if (file_exists($path) && is_file($path)) {
            return asset('uploads/agent/' . $filename);
        }

        // File doesn't exist, return default
        return $this->getDefaultAvatar();
    }

    /**
     * Get default avatar URL
     */
    private function getDefaultAvatar(): string
    {
        return asset('uploads/avator.jpg');
    }

    /**
     * Extract filename from various formats
     */
    private function extractFilename(string $value): string
    {
        // If it's a full URL, extract filename
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return basename(parse_url($value, PHP_URL_PATH));
        }

        // Remove path prefixes if exists
        $value = str_replace(['uploads/agent/', 'uploads/agent', 'uploads/', 'uploads'], '', $value);

        // Remove leading/trailing slashes
        return trim($value, '/');
    }

    /**
     * Validate image file extension
     */
    private function isValidImageExtension(string $filename): bool
    {
        return preg_match('/\.(jpg|jpeg|png|webp|gif|svg)$/i', $filename) === 1;
    }

    /**
     * Standard JSON response helper
     */
    private function jsonResponse(bool $status, string $message, $data = null, int $httpCode = 200)
    {
        $response = [
            'status' => $status,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $httpCode);
    }
}
