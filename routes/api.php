<?php

use App\Http\Controllers\Api\KycsubmitforuserController;
use App\Http\Controllers\Api\RegisterController;
use Illuminate\Support\Facades\Route;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class,'login']);
Route::post('logout', [RegisterController::class,'logout']);


// Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
//    Route::post('kycsubmit', [KycsubmitforuserController::class,'kycsubmit']);
//    Route::get('kyc/status', [KycsubmitforuserController::class, 'getKycStatus']);
//    Route::post('kyc/resubmit', [KycsubmitforuserController::class, 'resubmitKyc']);
// });
Route::middleware('auth:sanctum')->group(function () {
    Route::post('kycsubmit', [KycsubmitforuserController::class,'kycsubmit']);
    Route::get('kyc/status', [KycsubmitforuserController::class, 'getKycStatus']);
    Route::post('kyc/resubmit', [KycsubmitforuserController::class, 'resubmitKyc']);
});

