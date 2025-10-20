<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Deposite;
use App\Models\Package;
use App\Models\Paymentmethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontendController extends Controller
{
  public function frontend() {
    return view('frontend.index');
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

       public function frontend_payment_history()
{
    $user = Auth::user();
    $deposits = Deposite::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    return view('frontend.frontendpages.payment_history', compact('deposits', 'user'));
}


public function frontend_refer_list()
{
    $user = Auth::user();

    // Fetch referred users with their approved package buys
    $referrals = \App\Models\User::where('referred_by', $user->id)
        ->with(['packagebuys' => function ($q) {
            $q->where('status', 'approved');
        }])
        ->orderBy('created_at', 'desc')
        ->get();

    // Calculate total referral income dynamically
    // Here we assume 10% commission, adjust as per your Reffercommissionsetup
    $total_refer_income = $referrals->sum(function($ref) {
        return $ref->packagebuys->sum('amount') * 0.10;
    });

    return view('frontend.frontendpages.reffer_list', compact('user', 'referrals', 'total_refer_income'));
}






}
