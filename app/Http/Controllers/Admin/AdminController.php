<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
public function admin_dashboard()
{
    // Total users with role = 'user'
    $total_user_count = User::where('role', 'user')->count();

    // All users with role = 'user'
    $user_details = User::where('role', 'user')->get();

    return view('admin.index', compact('total_user_count', 'user_details'));
}
}
