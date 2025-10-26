<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Agentbuysellpost;
use App\Models\Category;
use Illuminate\Http\Request;

class BuyandsellposController extends Controller
{
public function buysellpost()
{
    // Fetch all categories
    $categories = Category::all();

    // Fetch all approved posts with their category and agent info
    $all_agentbuysellpost = Agentbuysellpost::with('category', 'user')
        ->where('status', 'approved')
        ->get();

    return view('frontend.buyandsellpost.index', compact('categories', 'all_agentbuysellpost'));
}



}
