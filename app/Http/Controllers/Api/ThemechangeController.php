<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Themechange;
use Illuminate\Http\Request;

class ThemechangeController extends Controller
{
    /**
     * ✅ থিম কালার API (কোন Authentication লাগবে না)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function themechange()
    {
        try {
            $theme = Themechange::where('is_active', true)
                ->latest('published_at')
                ->first();
            if (!$theme) {
                $theme = Themechange::latest()->first();
            }

            // Step 3: কোন theme না থাকলে default return করো
            if (!$theme) {
                return response()->json([
                    'status'  => true,
                    'message' => 'No theme found, using default',
                    'data'    => [
                        'color_code' => '#4361EE',
                        'name'       => 'Default Blue',
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]
                ], 200);
            }

            // Step 4: Theme পাওয়া গেলে তা return করো
            return response()->json([
                'status'  => true,
                'message' => 'Theme fetched successfully',
                'data'    => [
                    'color_code' => $theme->color_code,
                    'name'       => $theme->name ?? 'Custom Theme',
                    'updated_at' => $theme->updated_at->format('Y-m-d H:i:s'),
                ]
            ], 200);

        } catch (\Exception $e) {
            // Error হলে default theme return করো
            return response()->json([
                'status'  => false,
                'message' => 'Error fetching theme: ' . $e->getMessage(),
                'data'    => [
                    'color_code' => '#4361EE',
                    'name'       => 'Default Blue',
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]
            ], 500);
        }
    }

    /**
     * ✅ Admin Panel থেকে theme update করার API
     */
    public function updateTheme(Request $request)
    {
        $request->validate([
            'color_code' => 'required|string|max:10',
            'name' => 'nullable|string|max:255',
        ]);

        try {
            // সব theme inactive করো
            Themechange::query()->update(['is_active' => false]);

            // নতুন theme তৈরি করো
            $theme = Themechange::create([
                'color_code' => $request->color_code,
                'name' => $request->name ?? 'Custom Theme',
                'is_active' => true,
                'published_at' => now(),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Theme updated successfully',
                'data' => $theme
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update theme: ' . $e->getMessage()
            ], 500);
        }
    }
}
