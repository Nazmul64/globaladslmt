<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PasswordchangeController extends BaseController
{
    /**
     * Change user password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chagepassword(Request $request)
    {
        try {
            // Get authenticated user
            $user = Auth::user();

            if (!$user) {
                return $this->sendError('User not authenticated.', [], 401);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'old_password'     => 'required|string',
                'new_password'     => 'required|string|min:6|different:old_password',
                'confirm_password' => 'required|string|same:new_password',
            ], [
                'old_password.required'        => 'Current password is required.',
                'new_password.required'        => 'New password is required.',
                'new_password.min'             => 'New password must be at least 6 characters.',
                'new_password.different'       => 'New password must be different from current password.',
                'confirm_password.required'    => 'Please confirm your new password.',
                'confirm_password.same'        => 'Passwords do not match.',
            ]);

            // Check validation errors
            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            // Verify old password
            if (!Hash::check($request->old_password, $user->password)) {
                return $this->sendError('Current password is incorrect.', [], 401);
            }

            // Update password
            $user->password = Hash::make($request->new_password);
            $user->save();

            // Return success response
            return $this->sendResponse([], 'Password changed successfully.');

        } catch (\Exception $e) {
            return $this->sendError('Server Error', ['error' => $e->getMessage()], 500);
        }
    }
}
