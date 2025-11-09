<?php

use App\Http\Controllers\Api\DepositeUserController;
use App\Http\Controllers\Api\KycsubmitforuserController;
use App\Http\Controllers\Api\PasswordchangeController;
use App\Http\Controllers\Api\PaymentmethodController;
use App\Http\Controllers\Api\ProfilechangeController;
use App\Http\Controllers\Api\RegisterController;
use Illuminate\Support\Facades\Route;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class,'login']);
Route::post('logout', [RegisterController::class,'logout']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('kycsubmit', [KycsubmitforuserController::class,'kycsubmit']);
    Route::post('chagepassword', [PasswordchangeController::class, 'chagepassword']);
    Route::get('profile', [ProfilechangeController::class, 'getProfile']);
    // Update profile (name, email, photo)
    Route::post('profileupdate', [ProfilechangeController::class, 'profileUpdate']);
    // Optional: Delete profile photo
    Route::delete('profile/photo', [ProfilechangeController::class, 'deletePhoto']);
    Route::get('paymentmethod', [PaymentmethodController::class, 'paymentmethod']);
    Route::post('deposite', [DepositeUserController::class, 'deposite']);
     Route::get('totaldeposite', [DepositeUserController::class, 'totaldeposite']);

    // All deposits list
    Route::get('userDeposits', [DepositeUserController::class, 'userDeposits']);

    // Optional: Get deposits by status (pending/approved/rejected)
    Route::get('userDeposits/{status}', [DepositeUserController::class, 'userDepositsByStatus']);

    // Optional: Get single deposit details
    Route::get('deposit/{id}', [DepositeUserController::class, 'getDepositById']);


});
  Route::get('paymentmethod', [PaymentmethodController::class, 'paymentmethod']);
  Route::get('kycsubmit/kyc-status', [KycsubmitforuserController::class, 'kycStatus']);
  Route::post('kycsubmit/kyc-resubmit', [KycsubmitforuserController::class, 'kycResubmit']);


