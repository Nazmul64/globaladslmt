<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController as BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class RegisterController extends BaseController
{
  public function register(Request $request)
{
    // Check email already exists
    if (User::where('email', $request->email)->exists()) {
        return $this->sendError('Email already registered. Please try another email.', [], 422);
    }

    $validator = Validator::make($request->all(), [
        'name'                       => 'required|string|max:255',
        'email'                      => 'required|email',
        'mobile'                     => 'required|string|max:100',
        'password'                   => 'required|min:6|confirmed',
        'password_confirmation'      => 'required',
        'ref_code'                   => 'nullable|string'
    ]);

    if ($validator->fails()) {
        return $this->sendError('Validation Error', $validator->errors(), 422);
    }

    $referredBy = null;

    if (!empty($request->ref_code)) {
        $referrer = User::where('ref_code', $request->ref_code)->first();
        $referredBy = $referrer ? $referrer->id : null;
    }

    do {
        $newRefCode = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    } while (User::where('ref_code', $newRefCode)->exists());

    $user = User::create([
        'name'        => $request->name,
        'email'       => $request->email,
        'mobile'      => $request->mobile,
        'ref_code'    => $newRefCode,
        'referred_by' => $referredBy,
        'password'    => Hash::make($request->password),
    ]);

    // Generate Token
        $success['token'] = $user->createToken('RestApi')->plainTextToken;
        $success['name']  = $user->name;

        return $this->sendResponse($success, 'User Registered Successfully');
}

}
