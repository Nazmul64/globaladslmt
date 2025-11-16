<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Agentbuysellpost;
use App\Models\Category;
use App\Models\Paymentmethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuyandsellposController extends Controller
{
public function buysellpost()
{
    $categories = Category::all();
    $amount = Auth::user()->balance ?? 0;
    $agent_id = Auth::user()->agent_id;
    $payment_method =Paymentmethod::all();

    // সব approved পোস্ট with relations
    $all_agentbuysellpost = Agentbuysellpost::with(['category', 'user', 'agentamounts', 'dollarsign'])
        ->where('status', 'approved')
        ->latest()
        ->get();

    // ইউজারের latest deposit request
    $depositRequest = \App\Models\Userdepositerequest::where('user_id', Auth::id())
        ->latest()
        ->first();

    return view('frontend.buyandsellpost.index', compact('categories', 'all_agentbuysellpost', 'amount', 'agent_id', 'depositRequest','payment_method'));
}





}
