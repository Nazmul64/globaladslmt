<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AppsettingController;
use App\Http\Controllers\Backend\AdminagentcreateController;
use App\Http\Controllers\Backend\AdminApproveController;
use App\Http\Controllers\Backend\AdminautoController;
use App\Http\Controllers\Backend\AgentauthController;
use App\Http\Controllers\Backend\AgentController;
use App\Http\Controllers\Backend\NoticesController;
use App\Http\Controllers\Backend\PackageController;
use App\Http\Controllers\Backend\PaymentmethodController;
use App\Http\Controllers\Backend\ReffercommissionsetupController;
use App\Http\Controllers\Backend\WorkNoticesController;
use App\Http\Controllers\Frontend\FrontendAuthController;
use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\Frontend\DepositeController;
use App\Models\Appsetting;
use App\Models\Reffercommissionsetup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



Auth::routes();

// Frontend Pages Route Controller Start
 Route::get('frontend/options', [FrontendController::class, 'frontend_options'])->name('frontend.options');
 Route::get('frontend/adblance', [FrontendController::class, 'frontend_adblance'])->name('frontend.adblance');
 Route::get('frontend/deposite', [FrontendController::class, 'frontend_deposite'])->name('frontend.deposite');
 Route::post('frontend/deposite/store', [DepositeController::class, 'store_deposite'])->name('deposit.store');

// Frontend Pages Route Controller End


// Frontend Auth Controller Start
  Route::get('/', [FrontendAuthController::class, 'user_login'])->name('user.login');
  Route::post('user/login/submit', [FrontendAuthController::class, 'user_submit'])->name('user.submit');
  Route::get('user/register', [FrontendAuthController::class, 'user_register'])->name('user.register');
  Route::post('user/register/submit', [FrontendAuthController::class, 'user_register_submit'])->name('user.register.submit');
  Route::post('user/logout', [FrontendAuthController::class, 'user_logout'])->name('user.logout');
// Frontend Auth Controller End

// Frontend Route Controller Start
  Route::middleware(['user'])->group(function () {
    Route::get('frontend/dashboard', [FrontendController::class, 'frontend'])->name('frontend.index');
});
// Frontend Route Controller End


// Admin Login  Controller Start


Route::get('admin/login', [AdminautoController::class, 'admin_login'])->name('admin.login');
Route::post('admin/login/submit', [AdminautoController::class, 'admin_submit'])->name('admin.submit');
Route::post('admin/logout', [AdminautoController::class, 'admin_logout'])->name('admin.logout');
// Admin Login  Controller Start


// Admin Route Controller Start
Route::middleware(['is_admin'])->group(function () {
  Route::get('admin/dashboard', [AdminController::class, 'admin_dashboard'])->name('admin.dashboard');
  Route::resource('appsetting', AppsettingController::class);
  Route::get('agent/pending', [AdminApproveController::class,'pendingAgents'])->name('admin.agent.pending');
  Route::get('agent/approve/{id}', [AdminApproveController::class,'approveAgent'])->name('admin.agent.approve');
  Route::get('agent/reject/{id}', [AdminApproveController::class,'rejectAgent'])->name('admin.agent.reject');
  Route::get('agent/approved/list', [AdminApproveController::class,'agentapprovedlist'])->name('agentapprovedlist');
  Route::get('admin/agent/rejected', [AdminApproveController::class, 'agentrejectlist']) ->name('admin.agent.rejectlist');
  Route::resource('agentcreate', AdminagentcreateController::class);
  Route::resource('paymentmethod',PaymentmethodController::class);
  Route::resource('reffercommission',ReffercommissionsetupController::class);
  Route::resource('notice',NoticesController::class);
  Route::resource('worknotice',WorkNoticesController::class);
  Route::resource('package',PackageController::class);
});

// Admin Route Controller End



// Agent Login  Controller Start
Route::get('agent/login', [AgentauthController::class, 'agent_login'])->name('agent.login');
Route::get('agent/register', [AgentauthController::class, 'agent_register'])->name('agent.register');
Route::post('/register/submit', [AgentauthController::class, 'agent_register_submit'])->name('agent.register.submit');
Route::post('agent/login/submit', [AgentauthController::class, 'agent_submit'])->name('agent.submit');
Route::post('agent/logout', [AgentauthController::class, 'agent_logout'])->name('agent.logout');
// Agent Login  Controller End


// Agent Route Controller Start
Route::middleware(['agent'])->group(function () {
   Route::get('agent/dashboard', [AgentController::class, 'agent_dashboard'])->name('agent.dashboard');

});
// Agent Route Controller End




