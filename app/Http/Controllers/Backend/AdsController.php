<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;

class AdsController extends Controller
{
    /**
     * Display a listing of all ads.
     */
    public function index()
    {
        $ads = Ad::orderBy('id', 'desc')->get();
        return view('admin.ads.index', compact('ads'));
    }

    /**
     * Show the form for creating a new ad.
     */
    public function create()
    {
        return view('admin.ads.create');
    }

    /**
     * Store a newly created ad in the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'banner_ad_1' => 'nullable|string',
            'banner_ad_2' => 'nullable|string',
            'interstitial' => 'nullable|string',
            'rewarded_video' => 'nullable|string',
            'native' => 'nullable|string',
            'show_mrce_ads' => 'required|in:enabled,disabled',
            'show_button_timer_ads' => 'required|in:enabled,disabled',
            'show_banner_ads' => 'required|in:enabled,disabled',
        ]);

        Ad::create($request->only([
            'banner_ad_1',
            'banner_ad_2',
            'interstitial',
            'rewarded_video',
            'native',
            'show_mrce_ads',
            'show_button_timer_ads',
            'show_banner_ads',
        ]));

        return redirect()->route('ads.index')->with('success', 'Ad successfully created.');
    }

    /**
     * Show the form for editing the specified ad.
     */
    public function edit($id)
    {
        $ad = Ad::findOrFail($id);
        return view('admin.ads.edit', compact('ad'));
    }

    /**
     * Update the specified ad in the database.
     */
    public function update(Request $request, $id)
    {
        $ad = Ad::findOrFail($id);

        $request->validate([
            'banner_ad_1' => 'nullable|string',
            'banner_ad_2' => 'nullable|string',
            'interstitial' => 'nullable|string',
            'rewarded_video' => 'nullable|string',
            'native' => 'nullable|string',
            'show_mrce_ads' => 'required|in:enabled,disabled',
            'show_button_timer_ads' => 'required|in:enabled,disabled',
            'show_banner_ads' => 'required|in:enabled,disabled',
        ]);

        $ad->update($request->only([
            'banner_ad_1',
            'banner_ad_2',
            'interstitial',
            'rewarded_video',
            'native',
            'show_mrce_ads',
            'show_button_timer_ads',
            'show_banner_ads',
        ]));

        return redirect()->route('ads.index')->with('success', 'Ad successfully updated.');
    }

    /**
     * Remove the specified ad from the database.
     */
    public function destroy($id)
    {
        $ad = Ad::findOrFail($id);
        $ad->delete();

        return redirect()->route('ads.index')->with('success', 'Ad successfully deleted.');
    }
}
