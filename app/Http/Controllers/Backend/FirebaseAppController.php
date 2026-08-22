<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FCMNotification;
use App\Models\Notification;

/**
 * Firebase Notification Service - Single Table System এর জন্য
 * Location: app/Services/FirebaseNotificationService.php
 */
class FirebaseNotificationService
{
    /**
     * Firebase Messaging instance তৈরি করুন
     */
    protected function getMessaging($packageName)
    {
        // Firebase App খুঁজুন
        $app = Notification::apps()
            ->where('package_name', $packageName)
            ->where('is_active', true)
            ->first();

        if (!$app) {
            throw new \Exception('Firebase App পাওয়া যায়নি বা নিষ্ক্রিয়');
        }

        $credentials = $app->firebase_credentials;

        if (!$credentials) {
            throw new \Exception('Firebase credentials পাওয়া যায়নি');
        }

        // Temporary file তৈরি করুন
        $tempFile = tempnam(sys_get_temp_dir(), 'firebase_');
        file_put_contents($tempFile, json_encode($credentials));

        try {
            $factory = (new Factory)->withServiceAccount($tempFile);
            $messaging = $factory->createMessaging();

            unlink($tempFile);

            return $messaging;
        } catch (\Exception $e) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            throw $e;
        }
    }

    /**
     * সকল ইউজারকে নোটিফিকেশন পাঠান
     */
    public function sendToAll($packageName, $title, $body, $data = [])
    {
        try {
            $messaging = $this->getMessaging($packageName);

            $message = CloudMessage::withTarget('topic', 'all_users')
                ->withNotification(FCMNotification::create($title, $body))
                ->withData(array_merge($data, [
                    'sent_at' => now()->toDateTimeString(),
                    'type' => 'admin_notification'
                ]));

            $messaging->send($message);

            // Total users count
            $totalUsers = Notification::users()
                ->where('package_name', $packageName)
                ->whereNotNull('fcm_token')
                ->count();

            return [
                'success' => true,
                'message' => "নোটিফিকেশন সফলভাবে পাঠানো হয়েছে ($totalUsers জন)",
                'total_sent' => $totalUsers
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'total_sent' => 0
            ];
        }
    }

    /**
     * নির্দিষ্ট ইউজারদের নোটিফিকেশন পাঠান
     */
    public function sendToSpecificUsers($packageName, $userEmails, $title, $body, $data = [])
    {
        try {
            $messaging = $this->getMessaging($packageName);

            // FCM tokens নিন
            $users = Notification::users()
                ->where('package_name', $packageName)
                ->whereIn('user_email', $userEmails)
                ->whereNotNull('fcm_token')
                ->get();

            if ($users->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'কোনো ভ্যালিড FCM token পাওয়া যায়নি',
                    'total_sent' => 0
                ];
            }

            $tokens = $users->pluck('fcm_token')->toArray();

            $message = CloudMessage::new()
                ->withNotification(FCMNotification::create($title, $body))
                ->withData(array_merge($data, [
                    'sent_at' => now()->toDateTimeString(),
                    'type' => 'admin_notification'
                ]));

            $result = $messaging->sendMulticast($message, $tokens);

            $successCount = $result->successes()->count();
            $failureCount = $result->failures()->count();

            return [
                'success' => true,
                'message' => "সফল: $successCount জন, ব্যর্থ: $failureCount জন",
                'total_sent' => $successCount,
                'total_failed' => $failureCount
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'total_sent' => 0,
                'total_failed' => 0
            ];
        }
    }

    /**
     * Single device এ পাঠান
     */
    public function sendToDevice($packageName, $deviceToken, $title, $body, $data = [])
    {
        try {
            $messaging = $this->getMessaging($packageName);

            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification(FCMNotification::create($title, $body))
                ->withData(array_merge($data, [
                    'sent_at' => now()->toDateTimeString(),
                    'type' => 'admin_notification'
                ]));

            $messaging->send($message);

            return [
                'success' => true,
                'message' => 'নোটিফিকেশন পাঠানো হয়েছে',
                'total_sent' => 1
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'total_sent' => 0
            ];
        }
    }

    /**
     * ইউজারদের Topic এ subscribe করান
     */
    public function subscribeToTopic($packageName, $tokens, $topic = 'all_users')
    {
        try {
            $messaging = $this->getMessaging($packageName);
            $messaging->subscribeToTopic($topic, $tokens);

            return ['success' => true, 'message' => 'Topic এ subscribe হয়েছে'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
