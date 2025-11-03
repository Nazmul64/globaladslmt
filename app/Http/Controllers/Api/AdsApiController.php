<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;

class AdsApiController extends Controller
{
    // Return all ads
    public function index()
    {
        $ads = Ad::all();
        return response()->json([
            'status' => 'success',
            'data' => $ads
        ]);
    }

    // Return single ad
    public function show($id)
    {
        $ad = Ad::find($id);

        if (!$ad) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ad not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $ad
        ]);
    }

    // Return latest ad settings (for Flutter app)
    public function latest()
    {
        $ad = Ad::latest()->first();

        if (!$ad) {
            return response()->json([
                'status' => 'error',
                'message' => 'No Ad settings found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'startapp_app_id' => $ad->startapp_app_id ?? '',
            'show_mrce_ads' => $ad->show_mrce_ads ?? 'disabled',
            'show_button_timer_ads' => $ad->show_button_timer_ads ?? 'disabled',
            'show_banner_ads' => $ad->show_banner_ads ?? 'disabled',
            'banner_ad_1' => $ad->banner_ad_1 ?? '',
            'banner_ad_2' => $ad->banner_ad_2 ?? '',
            'interstitial' => $ad->interstitial ?? '',
            'rewarded_video' => $ad->rewarded_video ?? '',
            'native' => $ad->native ?? '',
        ]);
    }
}
