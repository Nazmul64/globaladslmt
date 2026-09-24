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
        $ad = \Illuminate\Support\Facades\Cache::remember('ad_latest_global', 60, function () {
            return Ad::latest()->first();
        });
        $approval = \Illuminate\Support\Facades\Cache::remember('google_ads_approval_global', 60, function () {
            return \App\Models\Googleadsapproval::first();
        });
        $appsetting = \Illuminate\Support\Facades\Cache::remember('app_settings_global', 60, function () {
            return \App\Models\Appsetting::first();
        });

        $startAppId = (string) ($appsetting->star_io_id ?? ($ad->startapp_app_id ?? ''));
        $admobAppId = (string) ($appsetting->admob_app_id ?? '');

        $data = [
            'id' => $ad->id ?? 1,
            'star_io_id' => $startAppId,
            'startapp_app_id' => $startAppId,
            'admob_app_id' => $admobAppId,
            'admob_banner_id' => (string) ($appsetting->admob_banner_id ?? ''),
            'admob_interstitial_id' => (string) ($appsetting->admob_interstitial_id ?? ''),
            'admob_rewarded_interstitial_id' => (string) ($appsetting->admob_rewarded_interstitial_id ?? ''),
            'admob_rewarded_id' => (string) ($appsetting->admob_rewarded_id ?? ''),
            'admob_native_id' => (string) ($appsetting->admob_native_id ?? ''),
            'admob_app_open_id' => (string) ($appsetting->admob_app_open_id ?? ''),
            'admob_status' => (bool) ($appsetting->admob_status ?? true),
            'stario_timer_status' => (string) ($appsetting->stario_timer_status ?? 'yes'),
            'admob_timer_status' => (string) ($appsetting->admob_timer_status ?? 'yes'),
            'show_mrce_ads' => $ad->show_mrce_ads ?? 'enabled',
            'show_button_timer_ads' => $ad->show_button_timer_ads ?? 'enabled',
            'show_banner_ads' => $ad->show_banner_ads ?? 'enabled',
            'banner_ad_1' => $ad->banner_ad_1 ?? '',
            'banner_ad_2' => $ad->banner_ad_2 ?? '',
            'interstitial' => $ad->interstitial ?? '',
            'rewarded_video' => $ad->rewarded_video ?? '',
            'native' => $ad->native ?? '',
            'code' => $ad->code ?? '',
            'approval_text' => $approval->approval_text ?? '',
            'task_break_time_minutes' => (int) ($appsetting->task_break_time_minutes ?? 1),
            'button_timer_seconds' => (int) ($appsetting->button_timer_seconds ?? 30),
            'ad_timer_seconds' => (int) ($appsetting->ad_timer_seconds ?? 15),
            'vpn_modes' => (string) ($appsetting->vpn_modes ?? 'not_allowed'),
            'vpn_required_in_task_only' => (string) ($appsetting->vpn_required_in_task_only ?? 'yes'),
            'allowed_country' => (string) ($appsetting->allowed_country ?? 'us,uk,au,bangladesh,india'),
        ];

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $data,
            'star_io_id' => $startAppId,
            'startapp_app_id' => $startAppId,
            'admob_app_id' => $admobAppId,
            'admob_banner_id' => $data['admob_banner_id'],
            'admob_interstitial_id' => $data['admob_interstitial_id'],
            'admob_rewarded_interstitial_id' => $data['admob_rewarded_interstitial_id'],
            'admob_rewarded_id' => $data['admob_rewarded_id'],
            'admob_native_id' => $data['admob_native_id'],
            'admob_app_open_id' => $data['admob_app_open_id'],
            'admob_status' => $data['admob_status'],
            'stario_timer_status' => $data['stario_timer_status'],
            'admob_timer_status' => $data['admob_timer_status'],
            'show_mrce_ads' => $data['show_mrce_ads'],
            'show_button_timer_ads' => $data['show_button_timer_ads'],
            'show_banner_ads' => $data['show_banner_ads'],
            'banner_ad_1' => $data['banner_ad_1'],
            'banner_ad_2' => $data['banner_ad_2'],
            'interstitial' => $data['interstitial'],
            'rewarded_video' => $data['rewarded_video'],
            'native' => $data['native'],
            'approval_text' => $data['approval_text'],
        ], 200);
    }
}
