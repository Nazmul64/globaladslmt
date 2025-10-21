<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Ad; // মডেল নাম নিশ্চিত করো
use Illuminate\Http\Request;

class AdsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ads =Ad::orderBy('id', 'desc')->get();
        return view('admin.ads.index', compact('ads'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.ads.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'show_mrce_ads' => 'required|in:enabled,disabled',
            'show_button_timer_ads' => 'required|in:enabled,disabled',
            'show_banner_ads' => 'required|in:enabled,disabled',
        ]);

        Ad::create([
            'code' => $request->code,
            'show_mrce_ads' => $request->show_mrce_ads,
            'show_button_timer_ads' => $request->show_button_timer_ads,
            'show_banner_ads' => $request->show_banner_ads,
        ]);

        return redirect()->route('ads.index')->with('success', 'Ads successfully created.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $ad = Ad::findOrFail($id);
        return view('admin.ads.show', compact('ad'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $ad = Ad::findOrFail($id);
        return view('admin.ads.edit', compact('ad'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $ad = Ad::findOrFail($id);

        $request->validate([
            'code' => 'required|string',
            'show_mrce_ads' => 'required|in:enabled,disabled',
            'show_button_timer_ads' => 'required|in:enabled,disabled',
            'show_banner_ads' => 'required|in:enabled,disabled',
        ]);

        $ad->update([
            'code' => $request->code,
            'show_mrce_ads' => $request->show_mrce_ads,
            'show_button_timer_ads' => $request->show_button_timer_ads,
            'show_banner_ads' => $request->show_banner_ads,
        ]);

        return redirect()->route('ads.index')->with('success', 'Ads successfully updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $ad = Ad::findOrFail($id);
        $ad->delete();

        return redirect()->route('ads.index')->with('success', 'Ads successfully deleted.');
    }
}
