<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomeCardSetting;
use Illuminate\Http\JsonResponse;

class HomeCardSettingApiController extends Controller
{
    /**
     * Get all active home card settings for app home screen.
     */
    public function getHomeCards(): JsonResponse
    {
        $cards = HomeCardSetting::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cards,
        ]);
    }
}
