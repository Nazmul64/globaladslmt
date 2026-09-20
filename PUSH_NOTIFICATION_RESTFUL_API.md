# 🔔 Flutter & RESTful API Push Notification Integration Documentation

> **Document Version:** 1.0.0  
> **Backend Framework:** Laravel 11/12 (Sanctum Authenticated)  
> **Mobile Framework:** Flutter (Android & iOS)  
> **Push Service:** Firebase Cloud Messaging (FCM HTTP v1 / Legacy fallback)  

---

## 📋 সূচিপত্র (Table of Contents)
1. [সিস্টেম ওভারভিউ (System Overview)](#1-সিস্টেম-ওভারভিউ-system-overview)
2. [ফ্লাটার প্রোজেক্ট সেটআপ (Flutter Project Setup)](#2-ফ্লাটার-প্রোজেক্ট-সেটআপ-flutter-project-setup)
3. [অ্যান্ড্রয়েড কনফিগারেশন (Android Configuration)](#3-অ্যান্ড্রয়েড-কনফিগারেশন-android-configuration)
4. [FCM টোকেন সিঙ্ক ও লাইফসাইকেল (FCM Token Sync & Lifecycle)](#4-fcm-টোকেন-সিঙ্ক-ও-লাইফসাইকেল-fcm-token-sync--lifecycle)
5. [নোটিফিকেশন হ্যান্ডলিং ও রাউটিং লজিক (Notification Handling & Navigation)](#5-নোটিফিকেশন-হ্যান্ডলিং-ও-রাউটিং-লজিক-notification-handling--navigation)
6. [রেডিমেড ডার্ট সার্ভিস ক্লাস (Ready-to-use Dart Service Classes)](#6-রেডিমেড-ডার্ট-সার্ভিস-ক্লাস-ready-to-use-dart-service-classes)
7. [RESTful API এন্ডপয়েন্ট রেফারেন্স (RESTful API Endpoint Reference)](#7-restful-api-এন্ডপয়েন্ট-রেফারেন্স-restful-api-endpoint-reference)
8. [ইভেন্ট টাইপ ও পে-লোড ডেটা স্ট্রাকচার (Payload Data Contracts)](#8-ইভেন্ট-টাইপ-ও-পে-লোড-ডেটা-স্ট্রাকচার-payload-data-contracts)

---

## 1. সিস্টেম ওভারভিউ (System Overview)

এই সিস্টেমে ব্যাকএন্ড (Laravel) এবং মোবাইল অ্যাপ (Flutter)-এর মধ্যে পুশ নোটিফিকেশন সমন্বয়ের জন্য দুটি অংশ রয়েছে:
1. **FCM Push Notifications:** ব্যবহারকারী অ্যাপের বাইরে থাকা অবস্থায় (Background / Terminated) অথবা অ্যাপে থাকা অবস্থায় (Foreground) তাৎক্ষণিক পুশ অ্যালার্ট ও রিডাইরেকশন।
2. **In-App Notification History Database:** প্রতিটি নোটিফিকেশন সার্ভারের `user_notifications` টেবিলে সংরক্ষিত থাকে, যা অ্যাপের নোটিফিকেশন বেল আইকন বা ইনবক্স স্ক্রিনে প্রদর্শিত হয়।

---

## 2. ফ্লাটার প্রোজেক্ট সেটআপ (Flutter Project Setup)

### ক. Firebase কনসোল ফাইল যোগ করা:
1. Firebase Console (`console.firebase.google.com`) থেকে প্রোজেক্টের `google-services.json` ডাউনলোড করুন।
2. ফাইলটি আপনার Flutter প্রোজেক্টের `android/app/google-services.json` ফোল্ডারে রাখুন।

### খ. `pubspec.yaml` ডিপেন্ডেন্সি যোগ:
```yaml
dependencies:
  flutter:
    sdk: flutter

  # Firebase Core & Messaging
  firebase_core: ^3.10.0
  firebase_messaging: ^15.2.0

  # Local Notification (Foreground Banner এর জন্য)
  flutter_local_notifications: ^18.0.1

  # HTTP Client & Storage
  http: ^1.2.0
  shared_preferences: ^2.3.0
```

---

## 3. অ্যান্ড্রয়েড কনফিগারেশন (Android Configuration)

### ক. `android/build.gradle` (Project Level):
```groovy
buildscript {
    dependencies {
        // Google Services plugin
        classpath 'com.google.gms:google-services:4.4.2'
    }
}
```

### খ. `android/app/build.gradle` (App Level):
```groovy
apply plugin: 'com.android.application'
apply plugin: 'com.google.gms.google-services' // ফাইলের একদম নিচে যোগ করুন

android {
    defaultConfig {
        minSdkVersion 21
        targetSdkVersion 34
        multiDexEnabled true
    }
}
```

### গ. `android/app/src/main/AndroidManifest.xml`:
`<manifest>` ট্যাগের মধ্যে পারমিশনগুলো নিশ্চিত করুন:
```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android">

    <!-- Notification Permissions -->
    <uses-permission android:name="android.permission.INTERNET"/>
    <uses-permission android:name="android.permission.RECEIVE_BOOT_COMPLETED"/>
    <uses-permission android:name="android.permission.VIBRATE" />
    <uses-permission android:name="android.permission.POST_NOTIFICATIONS"/>

    <application
        android:label="Global Money"
        android:name="${applicationName}"
        android:icon="@mipmap/ic_launcher">

        <!-- Default Notification Channel -->
        <meta-data
            android:name="com.google.firebase.messaging.default_notification_channel_id"
            android:value="high_importance_channel" />

        <!-- Notification Click Action Filter -->
        <intent-filter>
            <action android:name="FLUTTER_NOTIFICATION_CLICK" />
            <category android:name="android.intent.category.DEFAULT" />
        </intent-filter>

    </application>
</manifest>
```

---

## 4. FCM টোকেন সিঙ্ক ও লাইফসাইকেল (FCM Token Sync & Lifecycle)

ইউজার অ্যাপে লগইন করার সাথে সাথে ডিভাইসের FCM টোকেনটি সার্ভারে আপডেট করতে হবে:

```dart
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class TokenSyncService {
  static const String baseUrl = 'https://your-domain.com/api';

  static Future<void> syncFCMToken(String authToken) async {
    try {
      // ১. পারমিশন চাওয়া (বিশেষ করে Android 13+ এবং iOS এর জন্য)
      NotificationSettings settings = await FirebaseMessaging.instance.requestPermission(
        alert: true,
        badge: true,
        sound: true,
      );

      if (settings.authorizationStatus == AuthorizationStatus.authorized) {
        // ২. বর্তমান ডিভাইসের FCM টোকেন নেওয়া
        String? fcmToken = await FirebaseMessaging.instance.getToken();
        if (fcmToken != null) {
          await _sendTokenToServer(authToken, fcmToken);
        }

        // ৩. টোকেন স্বয়ংক্রিয়ভাবে রিফ্রেশ হলে নতুন টোকেন সার্ভারে পাঠানো
        FirebaseMessaging.instance.onTokenRefresh.listen((newToken) async {
          await _sendTokenToServer(authToken, newToken);
        });
      }
    } catch (e) {
      print("Error syncing FCM token: $e");
    }
  }

  static Future<void> _sendTokenToServer(String authToken, String fcmToken) async {
    final url = Uri.parse('$baseUrl/update-fcm-token');
    await http.post(
      url,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $authToken',
      },
      body: jsonEncode({
        'fcm_token': fcmToken,
        'device_type': 'android', // অথবা 'ios'
      }),
    );
  }
}
```

---

## 5. নোটিফিকেশন হ্যান্ডলিং ও রাউটিং লজিক (Notification Handling & Navigation)

নোটিফিকেশন আসার পর তিনটি সম্ভাব্য অবস্থা হতে পারে:
1. **Foreground:** অ্যাপ চালু এবং স্ক্রিনে দৃশ্যমান। (এখানে `flutter_local_notifications` দিয়ে ড্রপডাউন ব্যানার দেখাতে হবে)।
2. **Background:** অ্যাপ মিনিমাইজ করা আছে। সিস্টেমে ট্রের নোটিফিকেশনে ট্যাপ করলে অ্যাপ ওপেন হয়ে নির্দিষ্ট পেজে নিয়ে যাবে।
3. **Terminated:** অ্যাপ সম্পূর্ণ বন্ধ (Killed) ছিল। নোটিফিকেশনে ট্যাপ করে অ্যাপ ওপেন হলে সংশ্লিষ্ট স্ক্রিনে নিয়ে যাবে।

```dart
void handleNotificationClick(BuildContext context, Map<String, dynamic> data) {
  final String type = data['type'] ?? '';

  switch (type) {
    case 'friend_request':
      // ফ্রেন্ড রিকোয়েস্ট লিস্ট পেজ
      Navigator.pushNamed(context, '/friend-requests');
      break;

    case 'friend_accepted':
      // ফ্রেন্ড লিস্ট বা প্রোফাইল পেজ
      Navigator.pushNamed(context, '/friends-list');
      break;

    case 'new_post':
      // নির্দিষ্ট পোস্টের বিস্তারিত ভিউ
      Navigator.pushNamed(
        context, 
        '/post-details', 
        arguments: {'post_id': data['post_id']}
      );
      break;

    case 'chat_message':
      // ইউজার-টু-ইউজার চ্যাট স্ক্রিন
      Navigator.pushNamed(
        context, 
        '/chat-screen', 
        arguments: {
          'chat_id': data['chat_id'],
          'sender_id': data['sender_id']
        }
      );
      break;

    case 'admin_message':
      // অ্যাডমিন সাপোর্ট চ্যাট স্ক্রিন
      Navigator.pushNamed(context, '/admin-support-chat');
      break;

    case 'p2p_order':
      // P2P অর্ডার ডিটেইলস স্ক্রিন
      Navigator.pushNamed(
        context, 
        '/p2p-order-details', 
        arguments: {'order_id': data['order_id']}
      );
      break;

    case 'deposit':
    case 'withdraw':
      // ওয়ালেট ট্রানজেকশন হিস্ট্রি স্ক্রিন
      Navigator.pushNamed(context, '/wallet-history');
      break;

    default:
      // ডিফল্ট নোটিফিকেশন সেন্টার
      Navigator.pushNamed(context, '/notifications');
      break;
  }
}
```

---

## 6. রেডিমেড ডার্ট সার্ভিস ক্লাস (Ready-to-use Dart Service Classes)

এই সার্ভিস ফাইলটি কপি করে আপনার Flutter প্রোজেক্টের `lib/services/push_notification_service.dart`-এ রাখুন।

```dart
import 'dart:convert';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

// গ্লোবাল নেভিগেশন কি
final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

// ব্যাকগ্রাউন্ড মেসেজ হ্যান্ডলার
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  print("Background FCM message received: ${message.messageId}");
}

class PushNotificationService {
  static final FirebaseMessaging _firebaseMessaging = FirebaseMessaging.instance;
  static final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();

  static const AndroidNotificationChannel _channel = AndroidNotificationChannel(
    'high_importance_channel',
    'High Importance Notifications',
    description: 'This channel is used for important push notifications.',
    importance: Importance.high,
    playSound: true,
  );

  /// অ্যাপ শুরু হওয়ার সময় কল করুন (main.dart-এ)
  static Future<void> initialize() async {
    // ১. ব্যাকগ্রাউন্ড হ্যান্ডলার রেজিস্টার
    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);

    // ২. লোকাল নোটিফিকেশন ইনিশিয়ালাইজ
    const AndroidInitializationSettings androidSettings =
        AndroidInitializationSettings('@mipmap/ic_launcher');
    const DarwinInitializationSettings iosSettings =
        DarwinInitializationSettings();

    const InitializationSettings initSettings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );

    await _localNotifications.initialize(
      initSettings,
      onDidReceiveNotificationResponse: (NotificationResponse response) {
        if (response.payload != null && response.payload!.isNotEmpty) {
          final Map<String, dynamic> data = jsonDecode(response.payload!);
          _navigateByPayload(data);
        }
      },
    );

    // ৩. নোটিফিকেশন চ্যানেল তৈরি
    await _localNotifications
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(_channel);

    // ৪. ফোরগ্রাউন্ড প্রেজেন্টেশন অপশন
    await FirebaseMessaging.instance.setForegroundNotificationPresentationOptions(
      alert: true,
      badge: true,
      sound: true,
    );

    // ৫. লিসেনার সেটআপ
    _setupListeners();
  }

  static void _setupListeners() {
    // ফোরগ্রাউন্ডে নোটিফিকেশন এলে
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      RemoteNotification? notification = message.notification;
      AndroidNotification? android = message.notification?.android;

      if (notification != null && android != null) {
        _localNotifications.show(
          notification.hashCode,
          notification.title,
          notification.body,
          NotificationDetails(
            android: AndroidNotificationDetails(
              _channel.id,
              _channel.name,
              channelDescription: _channel.description,
              importance: Importance.high,
              priority: Priority.high,
              icon: '@mipmap/ic_launcher',
              playSound: true,
            ),
          ),
          payload: jsonEncode(message.data),
        );
      }
    });

    // ব্যাকগ্রাউন্ডে নোটিফিকেশনে ক্লিক করে অ্যাপ খুললে
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      _navigateByPayload(message.data);
    });

    // টার্মিনেটেড অবস্থায় নোটিফিকেশনে ক্লিক করে অ্যাপ লঞ্চ হলে
    FirebaseMessaging.instance.getInitialMessage().then((RemoteMessage? message) {
      if (message != null) {
        Future.delayed(const Duration(milliseconds: 500), () {
          _navigateByPayload(message.data);
        });
      }
    });
  }

  /// পে-লোড অনুযায়ী পেজ নেভিগেশন
  static void _navigateByPayload(Map<String, dynamic> data) {
    if (navigatorKey.currentState == null) return;
    final context = navigatorKey.currentState!.context;

    final String type = data['type']?.toString() ?? '';

    switch (type) {
      case 'friend_request':
        Navigator.pushNamed(context, '/friend-requests');
        break;
      case 'friend_accepted':
        Navigator.pushNamed(context, '/friends-list');
        break;
      case 'new_post':
        Navigator.pushNamed(context, '/post-details', arguments: data['post_id']);
        break;
      case 'chat_message':
        Navigator.pushNamed(context, '/chat-screen', arguments: {
          'chat_id': data['chat_id'],
          'sender_id': data['sender_id'],
        });
        break;
      case 'admin_message':
        Navigator.pushNamed(context, '/admin-support-chat');
        break;
      case 'p2p_order':
        Navigator.pushNamed(context, '/p2p-order-details', arguments: data['order_id']);
        break;
      case 'deposit':
      case 'withdraw':
        Navigator.pushNamed(context, '/wallet-history');
        break;
      default:
        Navigator.pushNamed(context, '/notifications');
        break;
    }
  }
}
```

---

## 7. RESTful API এন্ডপয়েন্ট রেফারেন্স (RESTful API Endpoint Reference)

### 📌 Base URL: `https://your-domain.com/api`

---

### ক. FCM টোকেন আপডেট (Update FCM Token)
ইউজার লগইন করার পর কল করতে হবে।
- **URL:** `POST /update-fcm-token`
- **Headers:** 
  ```http
  Authorization: Bearer <user_sanctum_token>
  Content-Type: application/json
  Accept: application/json
  ```
- **Request Body:**
  ```json
  {
    "fcm_token": "fGb7yKx9...EXAMPLE_TOKEN_STRING...",
    "device_type": "android"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "FCM Token Updated"
  }
  ```

---

### খ. নোটিফিকেশন হিস্ট্রি লিস্ট (Get Notifications List)
অ্যাপের নোটিফিকেশন পেজে পেজিনেটেড তালিকা দেখানোর জন্য।
- **URL:** `GET /notifications?page=1&per_page=20`
- **Headers:** 
  ```http
  Authorization: Bearer <user_sanctum_token>
  Accept: application/json
  ```
- **Response (200 OK):**
  ```json
  {
    "status": true,
    "data": {
      "current_page": 1,
      "data": [
        {
          "id": 105,
          "user_id": 12,
          "title": "নতুন ফ্রেন্ড রিকোয়েস্ট",
          "body": "Rahim আপনাকে ফ্রেন্ড রিকোয়েস্ট পাঠিয়েছে",
          "type": "friend_request",
          "payload": {
            "sender_id": 5,
            "request_id": 42
          },
          "is_read": false,
          "created_at": "2026-09-20T17:15:00.000000Z",
          "updated_at": "2026-09-20T17:15:00.000000Z"
        }
      ],
      "first_page_url": "https://your-domain.com/api/notifications?page=1",
      "from": 1,
      "last_page": 1,
      "last_page_url": "https://your-domain.com/api/notifications?page=1",
      "next_page_url": null,
      "path": "https://your-domain.com/api/notifications",
      "per_page": 20,
      "prev_page_url": null,
      "to": 1,
      "total": 1
    }
  }
  ```

---

### গ. নির্দিষ্ট নোটিফিকেশন রিড মার্ক করা (Mark Single Notification as Read)
ব্যবহারকারী যখন নির্দিষ্ট একটি নোটিফিকেশনে ট্যাপ করবে।
- **URL:** `POST /notifications/{id}/mark-as-read`
- **Headers:** `Authorization: Bearer <user_sanctum_token>`
- **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Marked as read"
  }
  ```

---

### ঘ. সব নোটিফিকেশন রিড মার্ক করা (Mark All as Read)
"Mark all as read" বাটনে চাপলে।
- **URL:** `POST /notifications/mark-all-read`
- **Headers:** `Authorization: Bearer <user_sanctum_token>`
- **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "All notifications marked as read"
  }
  ```

---

### ঙ. আনরিড নোটিফিকেশন সংখ্যা (Unread Count Badge)
অ্যাপের বেল আইকনে লাল ব্যাজ কাউন্ট দেখানোর জন্য।
- **URL:** `GET /notifications/unread-count`
- **Headers:** `Authorization: Bearer <user_sanctum_token>`
- **Response (200 OK):**
  ```json
  {
    "status": true,
    "unread_count": 4
  }
  ```

---

## 8. ইভেন্ট টাইপ ও পে-লোড ডেটা স্ট্রাকচার (Payload Data Contracts)

Firebase Message-এর `data` অবজেক্টে নিচের ফিল্ডগুলো পাঠানো হয়:

| Type (`data.type`) | Trigger Event | Sent `data` Fields | Recommended App Route |
| :--- | :--- | :--- | :--- |
| `friend_request` | অন্য ইউজার ফ্রেন্ড রিকোয়েস্ট পাঠালে | `{"type": "friend_request", "sender_id": "5", "request_id": "12"}` | `/friend-requests` |
| `friend_accepted` | ফ্রেন্ড রিকোয়েস্ট এক্সেপ্ট হলে | `{"type": "friend_accepted", "friend_id": "12", "request_id": "12"}` | `/friends-list` |
| `new_post` | কোনো ফ্রেন্ড নতুন পোস্ট দিলে | `{"type": "new_post", "post_id": "45", "user_id": "5"}` | `/post-details` |
| `chat_message` | ফ্রেন্ড বা এজেন্টের মেসেজ | `{"type": "chat_message", "chat_id": "89", "sender_id": "5"}` | `/chat-screen` |
| `admin_message` | সাপোর্ট টিম থেকে রিপ্লাই | `{"type": "admin_message", "chat_id": "12", "sender_id": "1"}` | `/admin-support-chat` |
| `p2p_order` | P2P বাই/সেল বা অর্ডার আপডেট | `{"type": "p2p_order", "order_id": "33", "amount": "50"}` | `/p2p-order-details` |
| `deposit` | ডিপোজিট রিকোয়েস্ট জমা বা অ্যাপ্রুভ | `{"type": "deposit", "order_id": "10", "amount": "100"}` | `/wallet-history` |
| `withdraw` | উইথড্র রিকোয়েস্ট জমা বা অ্যাপ্রুভ | `{"type": "withdraw", "order_id": "15", "amount": "50"}` | `/wallet-history` |

---

> 💡 **নোট:** এই ডকুমেন্টে উল্লেখিত সকল API এন্ডপয়েন্ট ও পুশ নোটিফিকেশন লজিক লারাভেল সার্ভারে অলরেডি অ্যাক্টিভ এবং টেস্টেড করা আছে।
