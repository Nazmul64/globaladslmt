<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Paymentmethod;
use Illuminate\Http\Request;

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


}
