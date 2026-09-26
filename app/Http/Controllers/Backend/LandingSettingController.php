<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\LandingSetting;
use Illuminate\Http\Request;

class LandingSettingController extends Controller
{
    /**
     * Display Landing Page Settings form in Admin Panel.
     */
    public function index()
    {
        $settings = LandingSetting::getSettings();
        return view('admin.landingsetting.index', compact('settings'));
    }

    /**
     * Update Landing Page Settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'hero_badge' => 'nullable|string|max:255',
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string',
            'app_name' => 'nullable|string|max:255',
            'app_publisher' => 'nullable|string|max:255',
            'app_meta' => 'nullable|string|max:255',
            'rating_score' => 'nullable|string|max:50',
            'rating_count' => 'nullable|string|max:100',
            'downloads_count' => 'nullable|string|max:100',
            'content_rating' => 'nullable|string|max:100',
            'device_compatibility' => 'nullable|string|max:255',
            'play_store_url' => 'nullable|url|max:1000',
            'stat_1_value' => 'nullable|string|max:100',
            'stat_1_label' => 'nullable|string|max:255',
            'stat_2_value' => 'nullable|string|max:100',
            'stat_2_label' => 'nullable|string|max:255',
            'stat_3_value' => 'nullable|string|max:100',
            'stat_3_label' => 'nullable|string|max:255',
            'features_badge' => 'nullable|string|max:255',
            'features_title' => 'nullable|string|max:255',
            'features_subtitle' => 'nullable|string',
            'feat_1_title' => 'nullable|string|max:255',
            'feat_1_desc' => 'nullable|string',
            'feat_2_title' => 'nullable|string|max:255',
            'feat_2_desc' => 'nullable|string',
            'feat_3_title' => 'nullable|string|max:255',
            'feat_3_desc' => 'nullable|string',
            'feat_4_title' => 'nullable|string|max:255',
            'feat_4_desc' => 'nullable|string',
            'feat_5_title' => 'nullable|string|max:255',
            'feat_5_desc' => 'nullable|string',
            'feat_6_title' => 'nullable|string|max:255',
            'feat_6_desc' => 'nullable|string',
            'cta_title' => 'nullable|string|max:255',
            'cta_subtitle' => 'nullable|string',
            'footer_about' => 'nullable|string',
            'footer_copyright' => 'nullable|string|max:255',
        ]);

        $settings = LandingSetting::getSettings();
        $settings->update($request->except(['_token', '_method']));

        return redirect()->back()->with('success', 'Website Landing Page texts & settings updated successfully!');
    }
}
