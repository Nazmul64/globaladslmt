# 🚀 RESTful API Specification & Integration Guide
**Version:** 2.4.0  
**Base URL:** `http://127.0.0.1:8000/api` (or live production domain `https://ukearn.com/api`)  
**Authorization Header:** `Bearer <JWT_OR_SANCTUM_TOKEN>`  
**Accept Header:** `application/json`  
**Content-Type:** `application/json` (or `multipart/form-data` for file uploads)

---

## 📌 1. KYC Verification & Friend Chat Status Update (CRITICAL)

### Problem Solved:
Previously, when user KYC was approved in the Admin Panel (`/kyc/kyclist`), the Friend Chat / User list in Flutter app was showing **"Unverified"** with red badge.
Now, the Backend User Model and all Chat & Friend APIs dynamically inspect:
1. `users.is_verified == 1`
2. `kycs` table where `user_id = auth->id` and `LOWER(status) = 'approved'`
3. `agentkycs` table where `user_id = auth->id` and `LOWER(status) = 'approved'`

### Standard User Object Attributes Returned Across ALL APIs:
All user objects in Chat, Friend List, Friend Requests, Search, and Profile endpoints now include:
```json
{
  "id": 1,
  "name": "Nazmul",
  "phone": "01706640864",
  "email": "nazmul@gmail.com",
  "photo_url": "https://ukearn.com/public/uploads/profile/1727157600.png",
  "is_verified": true,
  "kyc_approved": true,
  "kyc_status": "verified",
  "verification_status": "verified",
  "status": "verified"
}
```

> 📱 **Flutter / Mobile App Developer Note:**
> Use `user['is_verified'] == true` or `user['kyc_status'] == 'verified'` to render the **Blue Verified Badge** (Verified) instead of Unverified.

---

## 📌 2. 100% English Push Notifications & Dynamic App Logo Standard

### Push Notification Enhancements:
1. **Zero Bengali Text**: All system notifications, push notifications, friend requests, support messages, deposits, withdrawals, and task notifications are strictly in **English**.
2. **Customer Support Branding**: Admin support is officially branded as **"Customer Support"**.
3. **Dynamic Platform Logo**: Notifications now include the active platform logo (UK EARN / uploaded in Admin `logosetting`) in the FCM payload (`image`, `icon`, `logo_url`, `large_icon`, `imageUrl`).

### FCM Push Notification Payload Structure:
```json
{
  "title": "New Friend Request",
  "body": "Nazmul sent you a friend request",
  "logo_url": "https://ukearn.com/public/uploads/logo/1727158000.png",
  "imageUrl": "https://ukearn.com/public/uploads/logo/1727158000.png",
  "data": {
    "type": "friend_request",
    "title": "New Friend Request",
    "body": "Nazmul sent you a friend request",
    "image": "https://ukearn.com/public/uploads/logo/1727158000.png",
    "icon": "https://ukearn.com/public/uploads/logo/1727158000.png",
    "logo_url": "https://ukearn.com/public/uploads/logo/1727158000.png",
    "large_icon": "https://ukearn.com/public/uploads/logo/1727158000.png",
    "click_action": "FLUTTER_NOTIFICATION_CLICK"
  }
}
```

### Standard Notification Messages List:
| Event | Title | Body |
|---|---|---|
| **Friend Request Received** | `New Friend Request` | `{Sender Name} sent you a friend request` |
| **Friend Request Accepted** | `Friend Request Accepted` | `{User Name} accepted your friend request` |
| **Friend Chat Message** | `New Message` | `New message from {Sender Name}` |
| **Admin Support Reply** | `Customer Support` | `New message received from Customer Support` |
| **User Support to Admin** | `Customer Support` | `New Support Message from {User Name}` |
| **Deposit Submitted** | `Deposit Submitted` | `Your deposit request of ৳{Amount} has been submitted` |
| **Deposit Approved** | `Deposit Approved` | `Your deposit of ৳{Amount} has been approved` |
| **Withdrawal Submitted** | `Withdrawal Requested` | `Your withdrawal request of ৳{Amount} has been submitted` |
| **Withdrawal Approved** | `Withdrawal Approved` | `Your withdrawal of ৳{Amount} has been processed successfully` |

---

## 📌 3. API Endpoints Directory

### 🟢 A. Friend Chat & Messaging APIs

#### 1. Friend Chat User List (Contacts & Recent Chats)
- **Endpoint:** `GET /api/chat/frontend/list`
- **Headers:** `Authorization: Bearer <token>`
- **Response:**
```json
{
  "status": true,
  "data": [
    {
      "id": 2,
      "name": "Nazmul",
      "phone": "01706640864",
      "email": "nazmul@gmail.com",
      "photo_url": "https://ukearn.com/public/uploads/profile/avatar.png",
      "is_verified": true,
      "kyc_approved": true,
      "kyc_status": "verified",
      "verification_status": "verified",
      "status": "verified",
      "last_message": "Hi, how are you?",
      "last_message_time": "12:12 PM",
      "unread_count": 0
    }
  ]
}
```

#### 2. Get Messages Between Two Users
- **Endpoint:** `GET /api/chat/frontend/messages/{receiver_id}`
- **Headers:** `Authorization: Bearer <token>`
- **Response:**
```json
{
  "status": true,
  "data": [
    {
      "id": 105,
      "sender_id": 1,
      "receiver_id": 2,
      "message": "Hello!",
      "image_url": null,
      "created_at": "2026-09-24T06:12:00.000000Z",
      "is_me": true
    }
  ]
}
```

#### 3. Send Message to Friend
- **Endpoint:** `POST /api/chat/frontend/send`
- **Headers:** `Authorization: Bearer <token>`
- **Body (JSON or Form-Data):**
```json
{
  "receiver_id": 2,
  "message": "Hello there!",
  "image": "<optional image file>"
}
```
- **Response:**
```json
{
  "status": true,
  "message": "Message sent successfully",
  "data": {
    "id": 106,
    "sender_id": 1,
    "receiver_id": 2,
    "message": "Hello there!",
    "image_url": null,
    "created_at": "2026-09-24T06:15:00.000000Z"
  }
}
```

---

### 🟢 B. Friend Request System APIs

#### 1. Search Users / Add Friends
- **Endpoint:** `GET /api/friends/search?query=01706640864`
- **Headers:** `Authorization: Bearer <token>`
- **Response:**
```json
{
  "status": true,
  "data": [
    {
      "id": 2,
      "name": "Nazmul",
      "phone": "01706640864",
      "email": "nazmul@gmail.com",
      "photo_url": "https://ukearn.com/public/uploads/profile/avatar.png",
      "is_verified": true,
      "kyc_approved": true,
      "kyc_status": "verified",
      "verification_status": "verified",
      "status": "verified",
      "friendship_status": "not_friends"
    }
  ]
}
```

#### 2. Send Friend Request
- **Endpoint:** `POST /api/friend-request/send`
- **Headers:** `Authorization: Bearer <token>`
- **Body:**
```json
{
  "receiver_id": 2
}
```
- **Response:**
```json
{
  "status": true,
  "message": "Friend request sent successfully"
}
```

#### 3. Accept Friend Request
- **Endpoint:** `POST /api/friend-request/accept`
- **Headers:** `Authorization: Bearer <token>`
- **Body:**
```json
{
  "request_id": 15
}
```
- **Response:**
```json
{
  "status": true,
  "message": "Friend request accepted successfully"
}
```

#### 4. Get My Friends List
- **Endpoint:** `GET /api/friends`
- **Headers:** `Authorization: Bearer <token>`
- **Response:**
```json
{
  "status": true,
  "data": [
    {
      "id": 2,
      "name": "Nazmul",
      "phone": "01706640864",
      "email": "nazmul@gmail.com",
      "photo_url": "https://ukearn.com/public/uploads/profile/avatar.png",
      "is_verified": true,
      "kyc_approved": true,
      "kyc_status": "verified",
      "verification_status": "verified",
      "status": "verified"
    }
  ]
}
```

---

### 🟢 C. Customer Support Chat APIs (Admin <-> User)

#### 1. Get Support Chat Messages (User side)
- **Endpoint:** `GET /api/chat/admin/messages`
- **Headers:** `Authorization: Bearer <token>`
- **Response:**
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "sender_type": "user",
      "message": "Need help with deposit",
      "created_at": "2026-09-24T05:00:00.000000Z"
    },
    {
      "id": 2,
      "sender_type": "admin",
      "message": "Hello! How may Customer Support assist you today?",
      "created_at": "2026-09-24T05:01:00.000000Z"
    }
  ]
}
```

#### 2. Send Message to Customer Support
- **Endpoint:** `POST /api/chat/admin/send`
- **Headers:** `Authorization: Bearer <token>`
- **Body:**
```json
{
  "message": "I have completed KYC verification.",
  "image": "<optional image file>"
}
```
- **Response:**
```json
{
  "status": true,
  "message": "Message sent to Customer Support"
}
```

---

### 🟢 D. User Profile & KYC Verification Status API

#### 1. Get Chat Profile / My Profile
- **Endpoint:** `GET /api/chat-profile-user` or `GET /api/user/profile`
- **Headers:** `Authorization: Bearer <token>`
- **Response:**
```json
{
  "status": true,
  "data": {
    "id": 1,
    "name": "Nazmul",
    "phone": "01706640864",
    "email": "nazmul@gmail.com",
    "photo_url": "https://ukearn.com/public/uploads/profile/avatar.png",
    "is_verified": true,
    "kyc_approved": true,
    "kyc_status": "verified",
    "verification_status": "verified",
    "status": "verified",
    "balance": "550.00",
    "created_at": "2026-09-01T10:00:00.000000Z"
  }
}
```

---

## 📌 4. Key Summary Checklist for App Integration

| Feature | Details | Verification Key |
|---|---|---|
| **KYC Verification Badge** | Blue verified badge must show when `is_verified: true` or `kyc_status: "verified"` | `is_verified` (Boolean) & `kyc_status` (String) |
| **Notification Language** | 100% English across all events | No Bengali strings |
| **Customer Support** | Display title as "Customer Support" | Notification title & chat header |
| **Notification Logo** | Always show dynamic platform logo | Payload `image`, `icon`, `logo_url` |
| **Friend Chat Listing** | Displays live verification status of each contact | `is_verified: true` |

---
**Maintained by:** GlobalAds Dev Team  
**Last Updated:** September 24, 2026
