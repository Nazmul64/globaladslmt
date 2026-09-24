<?php

namespace App\Http\Controllers;

use App\Models\Appsetting;
use Illuminate\Http\Request;

class AppsettingController extends Controller
{
  /**
     * Display a listing of app settings
     */
    public function index()
    {
        $appsettings = Appsetting::latest()->get();
        return view('admin.appsetting.index', compact('appsettings'));
    }

    /**
     * Show the form for creating new app settings
     */
    public function create()
    {
        return view('admin.appsetting.create');
    }

    /**
     * Store newly created app settings in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Google AdMob Settings
            'admob_app_id' => 'nullable|string|max:255',
            'admob_banner_id' => 'nullable|string|max:255',
            'admob_interstitial_id' => 'nullable|string|max:255',
            'admob_rewarded_interstitial_id' => 'nullable|string|max:255',
            'admob_rewarded_id' => 'nullable|string|max:255',
            'admob_native_id' => 'nullable|string|max:255',
            'admob_app_open_id' => 'nullable|string|max:255',
            'admob_status' => 'nullable|boolean',
            'admob_timer_status' => 'nullable|in:yes,no,1,0',

            // Basic App Settings
            'star_io_id' => 'nullable|string|max:255',
            'stario_timer_status' => 'nullable|in:yes,no,1,0',
            'invalid_click_limit' => 'nullable|numeric|min:0',
            'invalid_deduct' => 'nullable|numeric|min:0',
            'view_before_click_view_target' => 'nullable|numeric|min:0',

            // Timer Settings
            'task_break_time_minutes' => 'nullable|numeric|min:0',
            'button_timer_seconds' => 'nullable|numeric|min:0',
            'ad_timer_seconds' => 'nullable|numeric|min:0',

            // VPN & Country Settings
            'vpn_modes' => 'nullable|string|in:not_allowed,required,allowed,yes,no',
            'vpn_required_in_task_only' => 'nullable|in:yes,no,1,0',
            'allowed_country' => 'nullable|string|max:1000',

            // App Control Settings
            'registration_status' => 'nullable|in:open,closed',
            'same_device_login' => 'nullable|in:yes,no,1,0',
            'maintenance_mode' => 'nullable|in:yes,no,1,0',
            'app_version' => 'nullable|string|max:50',
            'app_link' => 'nullable|url|max:500',
        ]);

        if (empty($validated['button_timer_seconds'])) {
            $validated['button_timer_seconds'] = 30;
        }
        if (empty($validated['ad_timer_seconds'])) {
            $validated['ad_timer_seconds'] = 15;
        }

        Appsetting::create($validated);
        \Illuminate\Support\Facades\Cache::forget('app_settings_global');

        return redirect()
            ->route('appsetting.index')
            ->with('success', 'App settings created successfully!');
    }

    /**
     * Show the form for editing app settings
     */
    public function edit($id)
    {
        $appsetting = Appsetting::findOrFail($id);
        return view('admin.appsetting.edit', compact('appsetting'));
    }

    /**
     * Update the specified app settings in database
     */
    public function update(Request $request, $id)
    {
        $appsetting = Appsetting::findOrFail($id);

        $validated = $request->validate([
            // Google AdMob Settings
            'admob_app_id' => 'nullable|string|max:255',
            'admob_banner_id' => 'nullable|string|max:255',
            'admob_interstitial_id' => 'nullable|string|max:255',
            'admob_rewarded_interstitial_id' => 'nullable|string|max:255',
            'admob_rewarded_id' => 'nullable|string|max:255',
            'admob_native_id' => 'nullable|string|max:255',
            'admob_app_open_id' => 'nullable|string|max:255',
            'admob_status' => 'nullable|boolean',
            'admob_timer_status' => 'nullable|in:yes,no,1,0',

            // Basic App Settings
            'star_io_id' => 'nullable|string|max:255',
            'stario_timer_status' => 'nullable|in:yes,no,1,0',
            'invalid_click_limit' => 'nullable|numeric|min:0',
            'invalid_deduct' => 'nullable|numeric|min:0',
            'view_before_click_view_target' => 'nullable|numeric|min:0',

            // Timer Settings
            'task_break_time_minutes' => 'nullable|numeric|min:0',
            'button_timer_seconds' => 'nullable|numeric|min:0',
            'ad_timer_seconds' => 'nullable|numeric|min:0',

            // VPN & Country Settings
            'vpn_modes' => 'nullable|string|in:not_allowed,required,allowed,yes,no',
            'vpn_required_in_task_only' => 'nullable|in:yes,no,1,0',
            'allowed_country' => 'nullable|string|max:1000',

            // App Control Settings
            'registration_status' => 'nullable|in:open,closed',
            'same_device_login' => 'nullable|in:yes,no,1,0',
            'maintenance_mode' => 'nullable|in:yes,no,1,0',
            'app_version' => 'nullable|string|max:50',
            'app_link' => 'nullable|url|max:500',
        ]);

        if (empty($validated['button_timer_seconds'])) {
            $validated['button_timer_seconds'] = 30;
        }
        if (empty($validated['ad_timer_seconds'])) {
            $validated['ad_timer_seconds'] = 15;
        }

        $appsetting->update($validated);
        \Illuminate\Support\Facades\Cache::forget('app_settings_global');

        return redirect()
            ->route('appsetting.index')
            ->with('success', 'App settings updated successfully!');
    }

    /**
     * Remove the specified app settings from database
     */
    public function destroy($id)
    {
        $appsetting = Appsetting::findOrFail($id);
        $appsetting->delete();
        \Illuminate\Support\Facades\Cache::forget('app_settings_global');

        return redirect()
            ->route('appsetting.index')
            ->with('success', 'App settings deleted successfully!');
    }
}
