<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Packagebuy;
use Illuminate\Http\Request;

class AdminPackageuylistcheckController extends Controller
{
    // 🔸 Admin Package Buy List
    public function admin_package_list()
    {
          $packageBuys =Packagebuy::with(['user', 'package'])->orderBy('id', 'desc')->get();
           return view('admin.packagebuylist.index',compact('packageBuys'));
    }
}
