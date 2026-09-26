<?php

namespace App\Services;

use App\Models\FirebaseApp;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Throwable;

class PushNotificationService
{
    /**
     * Send a notification and record it in database (100% English & with Platform Logo)
     *
     * @param int|string $userId
     * @param string $title
     * @param string $body
     * @param string $type
     * @param array $payload
     * @return UserNotification|null
     */
    public static function send($userId, $title, $body, $type, $payload = [])
    {
        $notificationRecord = null;

        // Ensure title and body are 100% English and Customer Support branding
        $title = self::sanitizeToEnglish((string) $title);
        $body = self::sanitizeToEnglish((string) $body);
        $logoUrl = self::resolveLogoUrl();

        try {
            // 1. Save notification record in database
            $notificationRecord = UserNotification::create([
                'user_id' => $userId,
                'title'   => (string) $title,
                'body'    => (string) $body,
                'type'    => (string) $type,
                'payload' => array_merge($payload, ['logo_url' => $logoUrl]),
                'is_read' => false,
            ]);
        } catch (Throwable $e) {
            Log::error("UserNotification DB Save Error: " . $e->getMessage(), [
                'user_id' => $userId,
                'type'    => $type,
            ]);
        }

        // 2. Trigger Firebase Push Notification
        try {
            $user = User::find($userId);

            if ($user && !empty($user->fcm_token)) {
                $messaging = self::resolveMessagingInstance($user);

                if ($messaging) {
                    $stringPayload = [];
                    if (is_array($payload)) {
                        foreach ($payload as $key => $val) {
                            $stringPayload[(string)$key] = is_scalar($val) ? (string)$val : json_encode($val);
                        }
                    }

                    $messageData = array_merge([
                        'type'         => (string) $type,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'icon'         => (string) $logoUrl,
                        'logo_url'     => (string) $logoUrl,
                    ], $stringPayload);

                    $notificationBuilder = Notification::create((string)$title, (string)$body);
                    // Only attach withImageUrl if an explicit image is provided in payload (do not blow up huge app logo in drawer)
                    if (!empty($payload['image_url'])) {
                        $notificationBuilder = $notificationBuilder->withImageUrl($payload['image_url']);
                    }

                    $message = CloudMessage::withTarget('token', $user->fcm_token)
                        ->withNotification($notificationBuilder)
                        ->withData($messageData);

                    $messaging->send($message);

                    Log::info("FCM Sent successfully to user {$userId}", [
                        'type'     => $type,
                        'title'    => $title,
                    ]);
                }
            }
        } catch (Throwable $e) {
            Log::error("FCM Send Error: " . $e->getMessage(), [
                'user_id' => $userId,
                'type'    => $type,
            ]);
        }

        return $notificationRecord;
    }

    /**
     * Resolve Platform Branding Logo URL dynamically
     */
    public static function resolveLogoUrl(): string
    {
        try {
            $settingLogo = \App\Models\Settinglogo::first() ?? \App\Models\Logosetting::first();
            if ($settingLogo && !empty($settingLogo->photo)) {
                $photoPath = public_path('uploads/logo/' . $settingLogo->photo);
                if (file_exists($photoPath)) {
                    return asset('uploads/logo/' . $settingLogo->photo);
                }
            }

            if (file_exists(public_path('admin/assets/images/logo.png'))) {
                return asset('admin/assets/images/logo.png');
            }

            if (file_exists(public_path('logo.png'))) {
                return asset('logo.png');
            }
        } catch (Throwable $e) {
            // fallback
        }

        return asset('uploads/avator.jpg');
    }

    /**
     * Convert any legacy or Bengali notification text to clean 100% English
     */
    public static function sanitizeToEnglish(string $text): string
    {
        $translations = [
            'উত্তোলন স্থগিত (Withdrawal Blocked)' => 'Withdrawal Blocked',
            'উত্তোলন স্থগিত' => 'Withdrawal Blocked',
            'আপনার অ্যাকাউন্ট থেকে উইথড্র ও P2P USDT সেল সাময়িকভাবে বন্ধ আছে। বিস্তারিত জানতে সাপোর্টে যোগাযোগ করুন।' => 'Your account withdrawal and P2P USDT sell are temporarily disabled. Please contact support for details.',
            'আপনার অ্যাকাউন্ট থেকে উইথড্র ও P2P USDT সেল সাময়িকভাবে বন্ধ আছে।' => 'Your account withdrawal and P2P USDT sell are temporarily disabled.',
            'উইথড্র রিকোয়েস্ট জমা হয়েছে' => 'Withdrawal Request Submitted',
            'উইথড্র রিকোয়েস্ট জমা হয়েছে' => 'Withdrawal Request Submitted',
            'ডিপোজিট রিকোয়েস্ট জমা হয়েছে' => 'Deposit Request Submitted',
            'ডিপোজিট রিকোয়েস্ট জমা হয়েছে' => 'Deposit Request Submitted',
            'টাকার উইথড্র রিকোয়েস্ট সফলভাবে জমা হয়েছে।' => 'USDT withdrawal request has been submitted successfully.',
            'টাকার উইথড্র রিকোয়েস্ট সফলভাবে জমা হয়েছে।' => 'USDT withdrawal request has been submitted successfully.',
            'টাকার ডিপোজিট রিকোয়েস্ট সফলভাবে জমা হয়েছে।' => 'USDT deposit request has been submitted successfully.',
            'টাকার ডিপোজিট রিকোয়েস্ট সফলভাবে জমা হয়েছে।' => 'USDT deposit request has been submitted successfully.',
            'অ্যাডমিন সাপোর্ট' => 'Customer Support',
            'এডমিন সাপোর্ট'   => 'Customer Support',
            'অ্যাডমিন'        => 'Customer Support',
            'এডমিন'          => 'Customer Support',
            'Admin Support'  => 'Customer Support',
            'নতুন ফ্রেন্ড রিকোয়েস্ট' => 'New Friend Request',
            'নতুন ফ্রেন্ড রিকোয়েস্ট' => 'New Friend Request',
            'আপনাকে ফ্রেন্ড রিকোয়েস্ট পাঠিয়েছে' => 'sent you a friend request',
            'আপনাকে ফ্রেন্ড রিকোয়েস্ট পাঠিয়েছে' => 'sent you a friend request',
            'রিকোয়েস্ট গ্রহণ করা হয়েছে' => 'Friend Request Accepted',
            'রিকোয়েস্ট গ্রহণ করা হয়েছে' => 'Friend Request Accepted',
            'আপনার ফ্রেন্ড রিকোয়েস্ট এক্সেপ্ট করেছে' => 'accepted your friend request',
            'আপনার ফ্রেন্ড রিকোয়েস্ট গ্রহণ করেছেন' => 'accepted your friend request',
            'নতুন মেসেজ' => 'New Message',
            'নতুন পোস্ট' => 'New Post',
            'একটি নতুন পোস্ট করেছে' => 'shared a new post',
            'নতুন সাপোর্ট মেসেজ' => 'New Support Message',
            'অ্যাডমিন থেকে নতুন একটি মেসেজ বা ফাইল এসেছে' => 'New message received from Customer Support',
            'এডমিন থেকে নতুন একটি মেসেজ বা ফাইল এসেছে' => 'New message received from Customer Support',
            'নতুন ডিপোজিট রিকোয়েস্ট' => 'New Deposit Request',
            'ডিপোজিট রিকোয়েস্ট গৃহীত' => 'Deposit Request Accepted',
            'পেমেন্ট প্রুফ জমা হয়েছে' => 'Payment Proof Submitted',
            'পেমেন্ট প্রুফ জমা হয়েছে' => 'Payment Proof Submitted',
            'ইউজার ডিপোজিটের পেমেন্ট প্রুফ জমা দিয়েছেন' => 'User submitted deposit payment proof',
            'ডিপোজিট সফল' => 'Deposit Successful',
            'ডিপোজিট সফল হয়েছে' => 'Deposit Successful',
            'আপনার' => 'Your',
            'ডিপোজিট সফলভাবে সম্পন্ন হয়েছে' => 'deposit has been completed successfully',
            'নতুন উইথড্র রিকোয়েস্ট' => 'New Withdrawal Request',
            'উইথড্র রিকোয়েস্ট গৃহীত' => 'Withdrawal Request Accepted',
            'এজেন্ট আপনার' => 'Agent accepted your',
            'উইথড্র রিকোয়েস্ট গ্রহণ করেছে' => 'withdrawal request',
            'ডিপোজিট রিকোয়েস্ট গ্রহণ করেছে' => 'deposit request',
            'উইথড্র সম্পন্ন' => 'Withdrawal Completed',
            'উইথড্র সফলভাবে সম্পন্ন হয়েছে' => 'withdrawal completed successfully',
            'একজন ইউজার' => 'A user submitted',
            'ডিপোজিট রিকোয়েস্ট পাঠিয়েছে' => 'deposit request',
            'উইথড্র রিকোয়েস্ট পাঠিয়েছে' => 'withdrawal request',
        ];

        foreach ($translations as $bn => $en) {
            if (mb_strpos($text, $bn) !== false) {
                $text = str_replace($bn, $en, $text);
            }
        }

        return trim($text);
    }

    /**
     * Resolve Firebase Messaging instance safely
     */
    protected static function resolveMessagingInstance(?User $user = null)
    {
        // 1. Try Laravel container binding if kreait is configured
        try {
            if (app()->bound('firebase.messaging')) {
                return app('firebase.messaging');
            }
        } catch (Throwable $e) {
            // fallback
        }

        // 2. Try credentials from active FirebaseApp model in DB
        try {
            $firebaseApp = null;
            if ($user && $user->firebase_app_id) {
                $firebaseApp = FirebaseApp::where('id', $user->firebase_app_id)->where('is_active', true)->first();
            }
            if (!$firebaseApp) {
                $firebaseApp = FirebaseApp::where('is_active', true)->first();
            }

            if ($firebaseApp && !empty($firebaseApp->firebase_credentials)) {
                $credentials = is_array($firebaseApp->firebase_credentials)
                    ? $firebaseApp->firebase_credentials
                    : json_decode($firebaseApp->firebase_credentials, true);

                if (!empty($credentials)) {
                    $factory = (new Factory)->withServiceAccount($credentials);
                    return $factory->createMessaging();
                }
            }
        } catch (Throwable $e) {
            Log::error("FirebaseApp resolver error: " . $e->getMessage());
        }

        // 3. Try credentials file in storage or env
        try {
            $credentialsFile = config('firebase.projects.app.credentials');
            if ($credentialsFile && file_exists($credentialsFile)) {
                $factory = (new Factory)->withServiceAccount($credentialsFile);
                return $factory->createMessaging();
            }
        } catch (Throwable $e) {
            Log::error("Firebase credentials file resolver error: " . $e->getMessage());
        }

        return null;
    }
}
