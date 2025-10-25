<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AgentDeposite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentController extends Controller
{
    public function agent_dashboard() {
    $auth_user_id =Auth::id();
    $total_deposite = AgentDeposite::where('agent_id', $auth_user_id)->where('status', 'approved')->sum('amount');
    return view('agent.index', compact('total_deposite'));
}

}
