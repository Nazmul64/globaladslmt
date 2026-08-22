<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentDeposite;
use App\Models\Deposite;
use App\Models\User;
use App\Models\Userdepositerequest;
use App\Models\UserWidhrawrequest;
use Illuminate\Http\Request;

class AdminController extends Controller
{
public function admin_dashboard()
{
    // Total normal users
    $total_user_count = User::where('role', 'user')->count();

    // User list
    $user_details = User::where('role', 'user')->latest()->get();

    // Deposit calculation
    $deposit1 = Deposite::sum('amount');
    $deposit2 = Userdepositerequest::sum('amount');
    $total_deposit = $deposit1 + $deposit2;

    // Withdraw calculation
    $withdraw1 = UserWidhrawrequest::sum('amount');
    $withdraw2 = UserWidhrawrequest::sum('amount');
    $user_total_withdraw = $withdraw1 + $withdraw2;

    // Agent deposit
    $agent_total_deposite = AgentDeposite::sum('amount');

    return view('admin.index', compact(
        'total_user_count',
        'user_details',
        'total_deposit',
        'user_total_withdraw',
        'agent_total_deposite'
    ));
}


    // user list for admin
    public function userlistadmin(){
        $users=User::where('role','user')->get();
        return view('admin.userlist.index',compact('users'));
    }
public function updateStatus(Request $request, $id)
{
    $request->validate([
        'is_blocked' => 'required|boolean',
    ]);

    $user = User::findOrFail($id);
    $user->is_blocked = $request->is_blocked; // DB থেকে আসা value সরাসরি assign
    $user->save();

    return redirect()->back()->with('success', 'User status updated successfully!');
}


public function  userDelete(Request $request,$id){
   $user_delete=User::find($id);
   $user_delete->delete();
    return redirect()->back()->with('success', 'User Delete successfully!');
}
}
