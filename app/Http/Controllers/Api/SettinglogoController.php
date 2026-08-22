<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SettingLogo;
use Illuminate\Support\Facades\Log;

class SettinglogoController extends Controller
{
    /**
     * Fetch site logo
     */
   public function index()
    {
        try {
            // Fetch the first logo from database
            $logo = SettingLogo::first();

            // If no logo found
            if (!$logo) {
                return response()->json([
                    'status' => false,
                    'message' => 'Logo not found'
                ], 404);
            }

            // Return logo data with full URL
            return response()->json([
                'status' => true,
                'data' => [
                    'id' => $logo->id,
                    'photo' => asset('uploads/logo/' . $logo->photo),
                ],
                'message' => 'Logo fetched successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Logo fetch error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Server error occurred',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
