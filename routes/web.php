<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AppsettingController;
use App\Http\Controllers\Backend\AdminautoController;
use App\Http\Controllers\Backend\AgentController;
use App\Models\Appsetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Admin Login  Controller Start


Route::get('admin/login', [AdminautoController::class, 'admin_login'])->name('admin.login');
Route::post('admin/login/submit', [AdminautoController::class, 'admin_submit'])->name('admin.submit');
Route::post('admin/logout', [AdminautoController::class, 'admin_logout'])->name('admin.logout');
// Admin Login  Controller Start


// Admin Route Controller Start
Route::middleware(['is_admin'])->group(function () {
 Route::get('admin/dashboard', [AdminController::class, 'admin_dashboard'])->name('admin.dashboard');
 Route::resource('appsetting', AppsettingController::class);

});

// Admin Route Controller End


Route::get('agent/dashboard', [AgentController::class, 'agent_dashboard'])->name('agent.dashboard');






