<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AppsettingController;
use App\Models\Appsetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('admin/dashboard', [AdminController::class, 'admin_dashboard'])->name('admin.dashboard');
Route::resource('appsetting', AppsettingController::class);

