<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mailsetting;
use App\Models\Googleadsapproval;
use Illuminate\Http\JsonResponse;

class NewSettingsApiController extends Controller
{
    /**
     * Get Mail Configuration (public info, excluding password for security)
     */
    public function getMailSetting(): JsonResponse
    {
        $mail = Mailsetting::first();
        if (!$mail) {
            return response()->json([
                'success' => false,
                'message' => 'Mail configuration not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'mail_mailer' => $mail->mail_mailer,
                'mail_host' => $mail->mail_host,
                'mail_port' => $mail->mail_port,
                'mail_username' => $mail->mail_username,
                'mail_encryption' => $mail->mail_encryption,
                'mail_from_address' => $mail->mail_from_address,
                'mail_from_name' => $mail->mail_from_name,
            ]
        ]);
    }

    /**
     * Get Google Ads Approval Text
     */
    public function getGoogleAdsApproval(): JsonResponse
    {
        $approval = Googleadsapproval::first();
        if (!$approval) {
            return response()->json([
                'success' => false,
                'message' => 'Google Ads Approval configuration not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'approval_text' => $approval->approval_text
            ]
        ]);
    }

    /**
     * Get App Settings (Start.io ID, AdMob App ID, etc.)
     */
    public function getAppSetting(): JsonResponse
    {
        $setting = \Illuminate\Support\Facades\Cache::remember('app_settings_global', 60, function () {
            return \App\Models\Appsetting::first();
        });
        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'App settings configuration not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'status' => true,
            'data' => [
                'star_io_id' => (string) ($setting->star_io_id ?? ''),
                'startapp_app_id' => (string) ($setting->star_io_id ?? ''),
                'admob_app_id' => (string) ($setting->admob_app_id ?? ''),
                'admob_banner_id' => (string) ($setting->admob_banner_id ?? ''),
                'admob_interstitial_id' => (string) ($setting->admob_interstitial_id ?? ''),
                'admob_rewarded_interstitial_id' => (string) ($setting->admob_rewarded_interstitial_id ?? ''),
                'admob_rewarded_id' => (string) ($setting->admob_rewarded_id ?? ''),
                'admob_native_id' => (string) ($setting->admob_native_id ?? ''),
                'admob_app_open_id' => (string) ($setting->admob_app_open_id ?? ''),
                'admob_status' => (bool) ($setting->admob_status ?? true),
                'stario_timer_status' => (string) ($setting->stario_timer_status ?? 'yes'),
                'admob_timer_status' => (string) ($setting->admob_timer_status ?? 'yes'),
                'task_break_time_minutes' => (int) ($setting->task_break_time_minutes ?? 1),
                'button_timer_seconds' => (int) ($setting->button_timer_seconds ?? 30),
                'ad_timer_seconds' => (int) ($setting->ad_timer_seconds ?? 15),
                'invalid_click_limit' => $setting->invalid_click_limit !== null ? (int)$setting->invalid_click_limit : null,
                'invalid_deduct' => $setting->invalid_deduct !== null ? (float)$setting->invalid_deduct : null,
                'view_before_click_view_target' => $setting->view_before_click_view_target !== null ? (int)$setting->view_before_click_view_target : null,
                'vpn_modes' => (string) ($setting->vpn_modes ?? 'not_allowed'),
                'vpn_required_in_task_only' => (string) ($setting->vpn_required_in_task_only ?? 'yes'),
                'allowed_country' => (string) ($setting->allowed_country ?? 'us,uk,au,bangladesh,india'),
                'registration_status' => (string) ($setting->registration_status ?? 'open'),
                'same_device_login' => (string) ($setting->same_device_login ?? 'yes'),
                'maintenance_mode' => (string) ($setting->maintenance_mode ?? 'no'),
                'app_version' => (string) ($setting->app_version ?? '1.0.0'),
                'app_link' => (string) ($setting->app_link ?? ''),
            ]
        ]);
    }
}
