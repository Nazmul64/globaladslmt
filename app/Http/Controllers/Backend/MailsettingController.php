<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Mailsetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailsettingController extends Controller
{
    /**
     * Display a listing of mail settings.
     */
    public function index()
    {
        $mailsetting = Mailsetting::first() ?? new Mailsetting([
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => '587',
            'mail_encryption' => 'SSL',
        ]);

        return view('admin.mailsetting.index', compact('mailsetting'));
    }

    /**
     * Store/Update mail configuration.
     */
    public function update(Request $request)
    {
        $request->validate([
            'mail_mailer' => 'nullable|string|max:50',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|string|max:10',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|max:50',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        $data = $request->only([
            'mail_mailer',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name',
        ]);

        if ($request->filled('mail_password')) {
            $data['mail_password'] = $request->mail_password;
        }

        $mailsetting = Mailsetting::first();

        if ($mailsetting) {
            $mailsetting->update($data);
        } else {
            if (!$request->filled('mail_password')) {
                $data['mail_password'] = null;
            }
            Mailsetting::create($data);
        }

        return redirect()
            ->route('mailsetting.index')
            ->with('success', 'Mail configuration saved successfully!');
    }

    /**
     * Send test mail to recipient.
     */
    public function sendTestMail(Request $request)
    {
        $request->validate([
            'recipient_email' => 'required|email',
            'message' => 'required|string',
        ]);

        $mail = Mailsetting::first();

        if (!$mail) {
            return back()->with('error', 'Please configure and save SMTP mail settings first.');
        }

        try {
            // Apply SMTP settings dynamically
            config([
                'mail.mailers.smtp.transport' => $mail->mail_mailer ?? 'smtp',
                'mail.mailers.smtp.host' => $mail->mail_host,
                'mail.mailers.smtp.port' => $mail->mail_port,
                'mail.mailers.smtp.encryption' => strtolower($mail->mail_encryption) === 'ssl' ? 'ssl' : 'tls',
                'mail.mailers.smtp.username' => $mail->mail_username,
                'mail.mailers.smtp.password' => $mail->mail_password,
                'mail.from.address' => $mail->mail_from_address,
                'mail.from.name' => $mail->mail_from_name,
            ]);

            // Reset the mailer to ensure the config changes are applied
            Mail::purge();

            // Send raw test email
            Mail::raw($request->message, function ($msg) use ($request, $mail) {
                $msg->to($request->recipient_email)
                    ->subject('Test Email from ' . ($mail->mail_from_name ?? config('app.name')));
            });

            return back()->with('success', 'Test email sent successfully!');
        } catch (\Exception $e) {
            Log::error('Test mail sending failed: ' . $e->getMessage());
            return back()->with('error', 'Mail Send Failed: ' . $e->getMessage());
        }
    }
}
