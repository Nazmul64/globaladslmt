<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Mailsetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ForgotPasswordsController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.exists' => 'This email is not registered.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if mail configuration is set by the admin or in config/env
        $mailSetting = Mailsetting::first();
        $mailHost = $mailSetting->mail_host ?? config('mail.mailers.smtp.host');
        $mailUsername = $mailSetting->mail_username ?? config('mail.mailers.smtp.username');
        $mailPassword = $mailSetting->mail_password ?? config('mail.mailers.smtp.password');

        if (empty($mailHost) || empty($mailUsername) || empty($mailPassword)) {
            return response()->json([
                'success' => false,
                'message' => 'Forgot password is disabled because the administrator has not configured the email settings. Please contact support.',
                'error_code' => 'MAIL_NOT_CONFIGURED'
            ], 400);
        }

        $email = $request->email;

        // Generate 8-10 digit numeric token
        $token = $this->generateNumericToken();

        // Delete old tokens for this email
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();

        // Store new token
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        Log::info("Reset token generated for {$email}: {$token}");

        // Send email
        try {
            Mail::send('emails.password_reset', [
                'token' => $token,
                'email' => $email
            ], function ($message) use ($email) {
                $message->to($email)
                        ->subject('Password Reset Code - ' . config('app.name'));
            });

            Log::info('Password reset email sent successfully to: ' . $email);

            return response()->json([
                'success' => true,
                'message' => 'Password reset code has been sent to your email!',
                'debug_token' => config('app.debug') ? $token : null, // Only in debug mode
            ], 200);

        } catch (\Exception $e) {
            Log::error('Email sending exception: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => true,
                'message' => 'Reset Code: ' . $token,
                'token' => $token,
                'note' => 'Email service error. Please use this token: ' . $token,
            ], 200);
        }
    }

    private function generateNumericToken()
    {
        return (string) rand(100000, 999999);
    }
}
