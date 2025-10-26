<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Agentbuysellpost;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuyandsellposController extends Controller
{
public function buysellpost()
{
    $categories = Category::all();
    $amount = Auth::user()->balance;
    $agent_id = Auth::user()->agent_id;

    // সব approved পোস্ট
    $all_agentbuysellpost = Agentbuysellpost::with('category', 'user')
        ->where('status', 'approved')
        ->get();

    // ইউজারের latest deposit request নাও
    $depositRequest = \App\Models\Userdepositerequest::where('user_id', Auth::id())
        ->latest()
        ->first();

    return view('frontend.buyandsellpost.index', compact('categories', 'all_agentbuysellpost', 'amount', 'agent_id', 'depositRequest'));
}





}
