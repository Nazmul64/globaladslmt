<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function frontend(){
          return view('frontend.index');
    }
    public function frontend_options(){
          return view('frontend.pages.frontendpages.options');
    }
}
