# Global Money Ltd - RESTful API & Flutter App Developer Guide
**Document Version:** 2.0.0  
**Base URL:** `http://your-domain.com/api` (Local: `http://127.0.0.1:8000/api`)  
**Auth Header:** `Authorization: Bearer <token>`  
**Accept Header:** `Accept: application/json`

---

## 📋 Table of Contents
1. [Overview & Recent Fixes](#1-overview--recent-fixes)
2. [User Verification Badge in Social Feed & Posts](#2-user-verification-badge-in-social-feed--posts)
3. [User Referral Code & Referral Tracking](#3-user-referral-code--referral-tracking)
4. [Push Notifications (Firebase FCM) & Icon Fix](#4-push-notifications-firebase-fcm--icon-fix)
5. [English Notifications & Withdrawal Handling](#5-english-notifications--withdrawal-handling)
6. [Complete API Endpoints Reference](#6-complete-api-endpoints-reference)
   - [Authentication & Profile](#authentication--profile)
   - [Social Feed & Posts](#social-feed--posts)
   - [Earning & Tasks](#earning--tasks)
   - [Withdrawals & Deposits](#withdrawals--deposits)
   - [Friend Requests & Live Chat](#friend-requests--live-chat)

---

## 1. Overview & Recent Fixes

| Issue Addressed | Resolution in Backend | Flutter App Action |
| :--- | :--- | :--- |
| **Website Landing Page** | Root URL (`/`) now displays the full Company Landing Page & Google Play Store App Showcase (`https://play.google.com/store/apps/details?id=com.globalmoneyltd.globalmoneyltd`). Sign In, Sign Up, and Web Dashboard buttons permanently removed. | Web users see company info and direct download CTA. All landing page texts, badges, app metadata, reviews, stats & CTA banner are 100% dynamically manageable from Admin Panel (`/admin/landing-settings`). |
| **Admin Landing Settings** | Added `Website Landing Page -> Landing Page All Texts & Info` in Admin Sidebar to edit all Hero, App Card, Stats, Highlights & Footer texts anytime with default seeders. | Admin can change any text/number/link on the live website without editing code. |
| **Social Feed Verified Badge** | `user.is_verified`, `user.kyc_status`, `post.is_verified` returned in all feed responses (`/api/posts`, `/api/posts/{id}`, `/api/posts/my-posts`). | Render blue/green verified checkmark badge `✓ Verified` beside any author whose `is_verified == true`. |
| **Referral Code in API** | `ref_code`, `refer_code`, and `referral_code` are now automatically appended in all User serialization (`/api/login`, `/api/profile`, `/api/userbalanceshow`). | Read `user['referral_code']` or `user['ref_code']` directly from profile response. |
| **Push Notification Icon vs Huge Image** | Removed default full-bleed logo expansion in FCM message drawers. FCM now delivers clean notifications with app icon. | Standard Android notification drawer display. |
| **English Notifications** | All system push notifications and withdrawal/deposit alerts are now 100% standardized in English. | Displays clean English messages in the notification tray. |

---

## 2. User Verification Badge in Social Feed & Posts

When fetching posts for the Social Feed (`GET /api/posts`), each post object includes complete author and comments author data with verification status.

### Endpoint: `GET /api/posts`
**Headers:** `Authorization: Bearer <token>`  
**Query Params:** `page=1&per_page=10`

#### Example Response:
```json
{
  "success": true,
  "message": "Posts retrieved successfully",
  "data": {
    "posts": [
      {
        "id": 14,
        "user_id": 2,
        "content": "Lovely morning!",
        "image": "uploads/posts/photo_123.jpg",
        "image_url": "http://127.0.0.1:8000/uploads/posts/photo_123.jpg",
        "likes_count": 5,
        "comments_count": 2,
        "shares_count": 0,
        "is_liked": true,
        "is_verified": true,
        "author_is_verified": true,
        "created_at": "2026-09-26T10:15:00.000000Z",
        "time_ago": "9 minutes ago",
        "user": {
          "id": 2,
          "name": "Hafsa Chowdhury",
          "email": "hafsa@example.com",
          "photo": "uploads/profile/hafsa.jpg",
          "photo_url": "http://127.0.0.1:8000/uploads/profile/hafsa.jpg",
          "role": "user",
          "is_verified": true,
          "kyc_approved": true,
          "kyc_status": "verified",
          "verification_status": "verified",
          "referral_code": "84729104"
        },
        "comments": [
          {
            "id": 101,
            "post_id": 14,
            "user_id": 3,
            "comment": "Nice post!",
            "created_at": "2026-09-26T10:20:00.000000Z",
            "user": {
              "id": 3,
              "name": "JAHIDUL ISLAM",
              "is_verified": true,
              "photo_url": "http://127.0.0.1:8000/uploads/profile/jahid.jpg"
            }
          }
        ]
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 10,
      "total": 1
    }
  }
}
```

### Flutter Implementation Snippet:
```dart
Widget buildAuthorName(Post post) {
  return Row(
    children: [
      Text(
        post.user.name,
        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
      ),
      if (post.user.isVerified || post.isVerified) ...[
        SizedBox(width: 6),
        Container(
          padding: EdgeInsets.symmetric(horizontal: 6, vertical: 2),
          decoration: BoxDecoration(
            color: Colors.blue.withOpacity(0.12),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Row(
            children: [
              Icon(Icons.verified, size: 14, color: Colors.blue),
              SizedBox(width: 3),
              Text(
                "Verified",
                style: TextStyle(fontSize: 11, color: Colors.blue, fontWeight: FontWeight.w600),
              ),
            ],
          ),
        ),
      ],
    ],
  );
}
```

---

## 3. User Referral Code & Referral Tracking

The User model provides `ref_code`, `refer_code`, `referral_code`, and `referral_link` in all responses.

### Key Endpoints:
- `GET /api/profile` (Authentication required)
- `GET /api/totalreffer` (Authentication required)
- `GET /api/referral-stats` (Authentication required)

#### `GET /api/totalreffer` Response Example:
```json
{
  "status": true,
  "message": "Referrals fetched successfully",
  "referral_code": "84729104",
  "ref_code": "84729104",
  "referral_link": "http://127.0.0.1:8000/register?ref=84729104",
  "total_referrals": 12,
  "active_users": 8,
  "total_earnings": 45.50,
  "referral_users": [
    {
      "id": 15,
      "name": "Alex Smith",
      "email": "alex@example.com",
      "phone": "01700000000",
      "status": "active",
      "is_verified": true,
      "created_at": "2026-09-20T12:00:00.000Z"
    }
  ]
}
```

---

## 4. Push Notifications (Firebase FCM) & Icon Fix

### Register FCM Token:
When the user logs in on Android/iOS, send the device FCM token:

**Endpoint:** `POST /api/update-fcm-token` (or `POST /api/user/update-fcm-token`)  
**Body:**
```json
{
  "email": "user@example.com",
  "fcm_token": "eK...your_firebase_fcm_token...",
  "device_type": "android"
}
```

### Notification Payload Delivered to App:
The backend does **NOT** set `image_url` on normal notifications, ensuring the drawer shows a standard clean card with the app icon on the top-left corner:
```json
{
  "notification": {
    "title": "New message from JAHID",
    "body": "Hello there!"
  },
  "data": {
    "type": "chat_message",
    "chat_id": "89",
    "sender_id": "3",
    "click_action": "FLUTTER_NOTIFICATION_CLICK"
  }
}
```

---

## 5. English Notifications & Withdrawal Handling

When a user's withdrawal is blocked by administration (`is_blocked == true`):
- `GET /api/userwidthrawshow` returns `'is_withdraw_blocked': true` and `'is_blocked': true`.
- `POST /api/userwidthrawstore` returns status `403` with message:  
  `"Your withdrawal has been blocked by administration. Please contact support."`
- P2P USDT sell requests (`POST /api/user/withdraw/request`) return status `403` with message:  
  `"Your withdrawal is currently blocked by administration. You cannot place P2P USDT sell orders."`

### Flutter Dialog Handler:
```dart
if (response.statusCode == 403 || data['is_withdraw_blocked'] == true) {
  showDialog(
    context: context,
    builder: (ctx) => AlertDialog(
      title: Row(
        children: [
          Icon(Icons.block, color: Colors.red),
          SizedBox(width: 8),
          Text("Withdrawal Blocked"),
        ],
      ),
      content: Text(data['message'] ?? "Your withdrawal and P2P USDT sell are temporarily disabled. Please contact support."),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(ctx),
          child: Text("OK"),
        ),
      ],
    ),
  );
}
```

---

## 6. Complete API Endpoints Reference

### Authentication & Profile
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/register` | Register new user account (`name`, `email`, `mobile`, `password`, `password_confirmation`, `ref_code`) |
| `POST` | `/api/login` | Log in user with email/mobile and password (`email`, `password`, `device_id`) |
| `POST` | `/api/logout` | Revoke user Sanctum token |
| `GET` | `/api/profile` | Get full user profile with verification status & balance |
| `POST` | `/api/profileupdate` | Update user name, email, or photo |
| `POST` | `/api/kycsubmit` | Submit National ID / Passport for KYC verification |
| `GET` | `/api/kycsubmit/kyc-status` | Check KYC approval status |

### Social Feed & Posts
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/posts` | Paginated social feed with author verification flags |
| `POST` | `/api/posts` | Create new post (`content`, `image`, `video`, `privacy`) |
| `GET` | `/api/posts/{id}` | Get single post details with comments |
| `POST/PUT` | `/api/posts/{id}` | Update existing post |
| `DELETE` | `/api/posts/{id}` | Delete own post |
| `POST` | `/api/posts/{id}/like` | Toggle like/unlike |
| `GET` | `/api/posts/{id}/comments` | Get comments list |
| `POST` | `/api/posts/{id}/comments` | Add comment to post |
| `POST` | `/api/posts/{id}/share` | Share post |

### Earning & Tasks
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/user-earning` | Task timer, cycle stats, AdMob/Star.io configuration |
| `POST` | `/api/track-ad-view` | Track completed ad impression |
| `POST` | `/api/break-complete` | Finish break timer interval |
| `POST` | `/api/claim-reward` | Claim cycle task USDT earning reward |
| `POST` | `/api/track-invalid-click`| Log invalid ad click and deduct fine |

### Withdrawals & Deposits
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/userwidthrawshow` | Available balance, payment methods & withdrawal limits |
| `POST` | `/api/userwidthrawstore`| Submit manual withdrawal request |
| `GET` | `/api/withdraw-history` | User withdrawal transaction history |
| `POST` | `/api/deposite` | Direct admin deposit request |
| `GET` | `/api/userDeposits` | All deposit records & statuses |
| `POST` | `/api/user/deposit/request` | P2P deposit request to agent |
| `POST` | `/api/user/withdraw/request`| P2P withdraw request to agent |

### Friend Requests & Live Chat
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/friends` | Get accepted friends list with verification status |
| `POST` | `/api/user/friend/request` | Send friend request |
| `GET` | `/api/user/friend/request/accept/view` | View received pending friend requests |
| `POST` | `/api/user/friend/request/accept` | Accept received friend request |
| `POST` | `/api/user/friend/request/reject` | Reject received friend request |
| `GET` | `/api/chat/frontend/list` | Chat contacts list |
| `GET` | `/api/chat/frontend/messages?user_id={id}` | Fetch chat conversation messages |
| `POST` | `/api/chat/frontend/submit` | Send chat text or image message |
| `GET` | `/api/usertoagentchat/agents` | All active support agents list |
| `GET` | `/api/usertoagentchat/fetch?agent_id={id}` | Agent chat conversation messages |
| `POST` | `/api/usertoagentchat/send` | Send message to support agent |

---

## 7. Mobile App Setup & Store Information
- **App Name:** Globalmoney ltd
- **Publisher / Developer:** 
- **Google Play Link:** `https://play.google.com/store/apps/details?id=com.globalmoneyltd.globalmoneyltd`
- **Package ID:** `com.globalmoneyltd.globalmoneyltd`
