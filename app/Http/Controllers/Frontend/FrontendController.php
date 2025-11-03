<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Deposite;
use App\Models\Depositelimite;
use App\Models\Package;
use App\Models\Packagebuy;
use App\Models\Paymentmethod;
use App\Models\Stepguide;
use App\Models\Support;
use App\Models\User;
use App\Models\Userdepositerequest;
use App\Models\UserWidthraw;
use App\Models\Whychooseu;
use App\Models\Wornotice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontendController extends Controller
{
public function frontend() {
    $auth_user_id = Auth::id();
    $has_active_package = Packagebuy::where('user_id', $auth_user_id)->where('status', 'approved') ->exists();
    $work_notices=Wornotice::all();
    return view('frontend.index', compact('has_active_package','work_notices'));
}

    public function frontend_options(){
          return view('frontend.frontendpages.options');
    }

       public function frontend_adblance(){
          return view('frontend.frontendpages.adblance');
    }

         public function frontend_deposite(){
             $payment_methods = Paymentmethod::all();
             return view('frontend.frontendpages.deposite',compact('payment_methods'));
     }

        public function frontend_packages(Request $request){
            $user = Auth::user();
            $package_type =Package::all();
            return view('frontend.frontendpages.packages', compact('package_type','user'));
        }


            public function frontend_profile(){
                return view('frontend.frontendpages.profile');
            }

       public function frontend_payment_history()
{
    $user = Auth::user();
    $deposits = Deposite::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    return view('frontend.frontendpages.payment_history', compact('deposits', 'user'));
}


public function frontend_support()
{
    $supports = \App\Models\Support::orderBy('id', 'asc')->get();

    return view('frontend.frontendpages.support', compact('supports'));
}


public function frontend_stepguide()
{
    $step_guides =Stepguide::all();
     $why_choose_us_items = Whychooseu::all();

    return view('frontend.frontendpages.stepguide', compact('step_guides','why_choose_us_items'));
}
public function total_deposite()
{
    $user_id = Auth::id();

    // ✅ Direct admin deposits
    $adminDeposits =Deposite::where('user_id', $user_id)->where('status', 'approved')->sum('amount');

    // ✅ Agent deposits
    $agentDeposits =Userdepositerequest::where('user_id', $user_id)->whereIn('status', ['completed', 'user_submitted', 'agent_confirmed'])->sum('amount');

    // ✅ Total
    $total_deposite = $adminDeposits + $agentDeposits;

    return view('frontend.totaldeposite.total_deposite', compact('total_deposite'));
}




public function friend_request(Request $request)
{
    $query = $request->input('query');
    $users = collect();

    if ($query) {
        $users = User::where('name', 'like', "%{$query}%")
                     ->orWhere('email', 'like', "%{$query}%")
                     ->orWhere('mobile', 'like', "%{$query}%")
                     ->limit(50) // Limit results
                     ->get(['id', 'name', 'email', 'mobile']); // Select only needed columns
    }

    // If AJAX request, return JSON
    if ($request->ajax() || $request->has('ajax')) {
        return response()->json($users);
    }

    // Example categories (load from DB if needed)
    $categories = ['Developers', 'Designers', 'Marketers', 'Students'];

    // Return view for full page
    return view('frontend.frontendpages.friend_request', compact('users', 'categories', 'query'));
}



public function frontend_refer_list()
    {
        $user = Auth::user();

        $referrals = User::with('packagebuys')
                        ->where('referred_by', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        $total_refer_income = $referrals->sum(function ($ref) {
            return $ref->packagebuys->sum('amount') * 0.10;
        });

        return view('frontend.frontendpages.reffer_list', compact('referrals', 'total_refer_income'));
    }

public function frontend_ads()
{
    if (!Auth::check()) {
        return redirect()->route('user.login')->withErrors('Please login to view ads.');
    }

    // Get ads configuration (first row or default empty object)
    $ads = Ad::first() ?? new Ad();

    // Get user's package
    $packageBuy = Packagebuy::where('user_id', Auth::id())->latest()->first();

    return view('frontend.frontendpages.ads', compact('ads', 'packageBuy'));
}

public function frontend_agent_list()
{
    $agents = User::where('role', 'agent')
                  ->where('status', 'approved')
                  ->get();

    return view('frontend.frontendpages.agent_list', compact('agents'));
}

public function frontend_widthraw()
{
    $withdraw_limit = \App\Models\Widthrawlimit::first();
    $withdraw_charge = \App\Models\Agentcommissonsetup::first();
    $payment_methods = \App\Models\Paymentmethod::all();

    return view('frontend.widthraw.index', [
        'payment_methods'    => $payment_methods,
        'widthraw_max_min'   => $withdraw_limit,    // min & max amount
        'widthraw_charge'    => $withdraw_charge->withdraw_total_commission ?? 0, // withdraw charge
    ]);
}

}
