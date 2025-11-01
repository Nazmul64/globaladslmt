<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AgentDeposite;
use App\Models\Userdepositerequest;
use App\Models\UserWidhrawrequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentController extends Controller
{
public function agent_dashboard()
{
    $auth_user_id = Auth::id();
    $total_deposite = AgentDeposite::where('agent_id', $auth_user_id)->where('status', 'approved')->sum('amount');
    $deposit_income = Userdepositerequest::where('agent_id', $auth_user_id)->where('status', 'agent_commission')->sum('agent_commission');
    $withdraw_income = UserWidhrawrequest::where('agent_id', $auth_user_id)->where('status', 'orderrelasce')->sum('agent_commission');
    $total_come = $deposit_income + $withdraw_income;

    return view('agent.index', compact('total_deposite', 'total_come', 'deposit_income', 'withdraw_income'));
}


}
