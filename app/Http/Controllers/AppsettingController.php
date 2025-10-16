<?php

namespace App\Http\Controllers;

use App\Models\Appsetting;
use Illuminate\Http\Request;

class AppsettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      $appsettings =Appsetting::all();
      return view('admin.appsetting.index',compact('appsettings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('admin.appsetting.create');
    }

    /**
     * Store a newly created resource in storage.
     */
 public function store(Request $request)
{
    // ভ্যালিডেশন (প্রয়োজন অনুযায়ী পরিবর্তন করতে পারো)
    $request->validate([
        'star_io_id' => 'nullable|integer',
        'app_theme' => 'nullable|string',
        'home_icon_themes' => 'nullable|string',
        'currency_symbol' => 'nullable|string',
        'enabled' => 'nullable|string',
        'task_rewards_level_1' => 'nullable|numeric',
        'task_rewards_level_2' => 'nullable|numeric',
        'task_rewards_level_3' => 'nullable|numeric',
        'task_rewards_level_4' => 'nullable|numeric',
        'task_rewards_level_5' => 'nullable|numeric',
        'task_limit_level_1' => 'nullable|integer',
        'task_limit_level_2' => 'nullable|integer',
        'task_limit_level_3' => 'nullable|integer',
        'task_limit_level_4' => 'nullable|integer',
        'task_limit_level_5' => 'nullable|integer',
        'refer_commission' => 'nullable|integer',
        'invalid_click_limit' => 'nullable|integer',
        'invalid_deduct' => 'nullable|integer',
        'view_before_click_view_target' => 'nullable|integer',
        'task_break_time_minutes' => 'nullable|integer',
        'button_timer_seconds' => 'nullable|integer',
        'statistics_point_rate' => 'nullable|integer',
        'paywell_point_rate' => 'nullable|integer',
        'fixed_withdraw' => 'nullable|string',
        'vpn_modes' => 'nullable|string',
        'vpn_required_in_task_only' => 'nullable|string',
        'allowed_country' => 'nullable|string',
        'info_api_key' => 'nullable|string',
        'telegram' => 'nullable|string',
        'whatsapp' => 'nullable|string',
        'email' => 'nullable|email',
        'how_to_work_link' => 'nullable|string',
        'privacy_policy' => 'nullable|string',
        'registration_status' => 'nullable|string',
        'same_device_login' => 'nullable|string',
        'maintenance_mode' => 'nullable|string',
        'app_version' => 'nullable|string',
        'app_link' => 'nullable|string',
    ]);

    $appsetting = new Appsetting();
    $appsetting->fill($request->all());
    $appsetting->save();

    return redirect()->back()->with('success', 'App settings saved successfully!');
}

    /**
     * Display the specified resource.
     */
    public function show(Appsetting $appsetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
  public function edit($id)
{
    $appsetting = Appsetting::findOrFail($id);
    return view('admin.appsetting.edit', compact('appsetting'));
}

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, Appsetting $appsetting)
{
    // Validate the request (optional, adjust rules as needed)
    $request->validate([
        'star_io_id' => 'nullable|integer',
        'app_theme' => 'nullable|string',
        'home_icon_themes' => 'nullable|string',
        'currency_symbol' => 'nullable|string',
        'enabled' => 'nullable|string',
        'task_rewards_level_1' => 'nullable|numeric',
        'task_rewards_level_2' => 'nullable|numeric',
        'task_rewards_level_3' => 'nullable|numeric',
        'task_rewards_level_4' => 'nullable|numeric',
        'task_rewards_level_5' => 'nullable|numeric',
        'task_limit_level_1' => 'nullable|integer',
        'task_limit_level_2' => 'nullable|integer',
        'task_limit_level_3' => 'nullable|integer',
        'task_limit_level_4' => 'nullable|integer',
        'task_limit_level_5' => 'nullable|integer',
        'refer_commission' => 'nullable|integer',
        'invalid_click_limit' => 'nullable|integer',
        'invalid_deduct' => 'nullable|integer',
        'view_before_click_view_target' => 'nullable|integer',
        'task_break_time_minutes' => 'nullable|integer',
        'button_timer_seconds' => 'nullable|integer',
        'statistics_point_rate' => 'nullable|integer',
        'paywell_point_rate' => 'nullable|integer',
        'fixed_withdraw' => 'nullable|string',
        'vpn_modes' => 'nullable|string',
        'vpn_required_in_task_only' => 'nullable|string',
        'allowed_country' => 'nullable|string',
        'info_api_key' => 'nullable|string',
        'telegram' => 'nullable|string',
        'whatsapp' => 'nullable|string',
        'email' => 'nullable|email',
        'how_to_work_link' => 'nullable|string',
        'privacy_policy' => 'nullable|string',
        'registration_status' => 'nullable|string',
        'same_device_login' => 'nullable|string',
        'maintenance_mode' => 'nullable|string',
        'app_version' => 'nullable|string',
        'app_link' => 'nullable|string',
    ]);

    // Update the Appsetting with request data
    $appsetting->update($request->all());

    // Redirect back with success message
    return redirect()->back()->with('success', 'App settings updated successfully!');
}


    /**
     * Remove the specified resource from storage.
     */
  public function destroy($id)
{
    $appsetting = Appsetting::findOrFail($id);
    $appsetting->delete();
    return redirect()->route('appsetting.index')->with('success', 'App Setting deleted successfully!');
}

}
