<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Deposite;
use App\Models\Userdepositerequest;
use App\Models\UserWidhrawrequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class P2pwidthrawhistoryController extends Controller
{
   public function p2p_widthraw_history()
{
    $user_p2pwidthraw_history = UserWidhrawrequest::where('user_id', Auth::id())
        ->where('status', 'completed')
        ->latest()
        ->get();

    return view('frontend.p2phistory.p2pwidthrawhistory', compact('user_p2pwidthraw_history'));
}


   public function p2p_diposite_history()
{
    $userdepositerequest =Userdepositerequest::where('user_id', Auth::id())
        ->where('status', 'completed')
        ->latest()
        ->get();

    return view('frontend.p2phistory.p2pdepositehistory', compact('userdepositerequest'));
}


}
