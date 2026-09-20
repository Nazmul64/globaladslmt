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
     * Send a notification and record it in database
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

        try {
            // 1. Save notification record in database
            $notificationRecord = UserNotification::create([
                'user_id' => $userId,
                'title'   => (string) $title,
                'body'    => (string) $body,
                'type'    => (string) $type,
                'payload' => $payload,
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
                    ], $stringPayload);

                    $message = CloudMessage::withTarget('token', $user->fcm_token)
                        ->withNotification(Notification::create((string)$title, (string)$body))
                        ->withData($messageData);

                    $messaging->send($message);

                    Log::info("FCM Sent successfully to user {$userId}", [
                        'type'  => $type,
                        'title' => $title,
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
