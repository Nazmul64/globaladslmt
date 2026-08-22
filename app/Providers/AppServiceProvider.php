<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('mailsettings')) {
                $mail = \App\Models\Mailsetting::first();
                if ($mail) {
                    $config = [
                        'mail.mailers.smtp.transport' => $mail->mail_mailer ?? 'smtp',
                        'mail.mailers.smtp.host' => $mail->mail_host,
                        'mail.mailers.smtp.port' => $mail->mail_port,
                        'mail.mailers.smtp.encryption' => strtolower($mail->mail_encryption) === 'ssl' ? 'ssl' : 'tls',
                        'mail.mailers.smtp.username' => $mail->mail_username,
                        'mail.from.address' => $mail->mail_from_address,
                        'mail.from.name' => $mail->mail_from_name,
                    ];
                    if ($mail->mail_password) {
                        $config['mail.mailers.smtp.password'] = $mail->mail_password;
                    }
                    config($config);
                }
            }
        } catch (\Exception $e) {
            // Silence database/connection errors before migrations run
        }
    }
}
