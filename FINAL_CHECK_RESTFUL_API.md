# GlobalAds LMT - Final Check RESTful API Documentation

This document contains complete, verified documentation for all core features, RESTful endpoints, and backend logic in the GlobalAds platform.

---

## 1. KYC & User Verification System (ID Verified / Text)

### 1.1 Overview
When a user submits KYC and the Admin clicks **Approve**, all user profiles and verification endpoints immediately reflect:
- `is_verified`: `true` (Boolean)
- `kyc_approved`: `true` (Boolean)
- `kyc_status`: `"verified"` (String)
- `verification_status`: `"verified"` (String)

If not yet approved, it returns `is_verified: false`, `kyc_status: "unverified"` or `"pending"`.

### 1.2 Endpoints

#### `GET /api/profile`
- **Headers:** `Authorization: Bearer {token}`
- **Response:**
```json
{
  "status": true,
  "message": "Profile fetched successfully",
  "data": {
    "id": 10,
    "name": "Alex",
    "mobile": "01700000000",
    "email": "user@example.com",
    "ref_code": "83920194",
    "referral_link": "https://globaladslmt.com/register?ref=83920194",
    "is_blocked": false,
    "is_verified": true,
    "kyc_status": "verified",
    "verification_status": "verified",
    "profile_photo": "https://globaladslmt.com/uploads/profile/profile_10.jpg",
    "total_coins": 25.50,
    "balance": 25.50,
    "tasks_done": 40
  }
}
```

#### `GET /api/userbalanceshow` and `GET /api/userbalanceshows`
- **Headers:** `Authorization: Bearer {token}`
- **Response:**
```json
{
  "success": true,
  "status": true,
  "data": {
    "user": {
      "id": 10,
      "name": "Alex",
      "email": "user@example.com",
      "is_verified": true,
      "kyc_approved": true,
      "kyc_status": "verified",
      "verification_status": "verified"
    },
    "balance": 25.50,
    "user_balance": 25.50,
    "kyc_approved": true,
    "is_verified": true,
    "kyc_status": "verified",
    "verification_status": "verified",
    "ref_code": "83920194",
    "profile_photo": "https://globaladslmt.com/uploads/profile/profile_10.jpg"
  }
}
```

---

## 2. Invalid Click Protection & Account Auto-Block

### 2.1 Overview & Logic
- **Invalid Click Limit (`invalid_click_limit`)**: Set in Admin Panel (e.g., `5`).
- **Invalid Deduct (`invalid_deduct`)**: Amount deducted from user balance per invalid click (e.g., `1.00`).
- **Auto-Block**: When a user reaches the invalid click limit (e.g., 5 invalid clicks):
  1. The user's account is immediately set to `is_blocked = true`.
  2. The user is blocked from viewing ads, claiming rewards, and accessing app functions.
  3. A 403 error is returned instructing the user to contact Admin Support.
- **Admin Unblock**: When Admin unblocks the user from Admin Panel, the invalid click counter is automatically reset.

### 2.2 Endpoint: Track Invalid Click
- **Endpoint:** `POST /api/track-invalid-click`
- **Headers:** `Authorization: Bearer {token}`

#### Response (Warning - Before Limit Reached):
```json
{
  "status": true,
  "is_blocked": false,
  "invalid_clicks": 2,
  "limit": 5,
  "deducted": 1.0,
  "balance": 24.50,
  "message": "ইনভ্যালিড ক্লিক সনাক্ত হয়েছে (2/5)। সতর্ক থাকুন, লিমিট পার হলে একাউন্ট ব্লক হবে।"
}
```

#### Response (Limit Reached - Auto Blocked):
```json
{
  "status": false,
  "is_blocked": true,
  "invalid_clicks": 5,
  "limit": 5,
  "deducted": 1.0,
  "balance": 21.50,
  "message": "আপনার একাউন্টে সর্বোচ্চ ইনভ্যালিড ক্লিক হওয়ায় একাউন্ট ব্লক করা হয়েছে। অনুগ্রহ করে এডমিনের সাথে সাপোর্টে যোগাযোগ করুন।"
}
```

---

## 3. Daily Tasks, Sequential Ad Tracking & Claim Reward

### 3.1 Overview of Timer Rules & Ad Networks
- **`ad_timer_seconds` (e.g., 15s)**: Duration the user watches each ad.
- **`button_timer_seconds` (e.g., 30s)**: Cooldown countdown on the ad button.
- **`task_break_time_minutes` (e.g., 2 min)**: Break period after completing an ad cycle (e.g. 10 ads).
- **Google Ads vs Start.io Timer Rules**:
  - **`stario_timer_status: "yes"`**: Start.io ads use the configured timers & break time.
  - **`admob_timer_status: "no"`**: Google AdMob ads do NOT enforce break time timer. Break time is skipped so users can claim or proceed without unwanted delay.

### 3.2 Sequential Ad Counting (No Skipping)
- Ad tracking is debounced and rate-limited.
- Each call to `POST /api/track-ad-view` increments the count strictly 1-by-1 (e.g., 1 -> 2 -> 3 -> 4 -> 5).
- When the target ad count is reached, the Claim button appears.

### 3.3 Endpoints

#### `GET /api/user-earning`
- **Headers:** `Authorization: Bearer {token}`
- **Response:**
```json
{
  "status": true,
  "data": {
    "user_name": "Alex",
    "ads_watched_today": 10,
    "ads_watched_in_current_cycle": 0,
    "current_cycle_number": 1,
    "last_claimed_cycle": 0,
    "daily_limit": 100,
    "is_break_active": false,
    "break_remaining_seconds": 0,
    "show_claim_button": true,
    "daily_limit_reached": false,
    "income_per_brack": "0.02",
    "next_reward": "0.02",
    "total_cycles": 10,
    "today_earning": "0.00",
    "total_earning": "0.00",
    "user_balance": "25.00",
    "stario_timer_status": "yes",
    "admob_timer_status": "no",
    "task_break_time_minutes": 2,
    "button_timer_seconds": 30,
    "ad_timer_seconds": 15,
    "package_id": 2,
    "package_name": "Silver Membership",
    "package_price": "25.00",
    "ad_brack": 10,
    "daily_income": "0.42"
  }
}
```

#### `POST /api/track-ad-view`
- **Headers:** `Authorization: Bearer {token}`
- **Response:**
```json
{
  "status": true,
  "data": {
    "ads_watched_today": 10,
    "ads_watched_in_current_cycle": 0,
    "ad_brack": 10,
    "cycle_completed": true,
    "start_break_timer": false
  },
  "message": "View tracked successfully"
}
```

#### `POST /api/claim-reward`
- **Headers:** `Authorization: Bearer {token}`
- **Response:**
```json
{
  "status": true,
  "success": true,
  "message": "🎉 $0.02 Earned!",
  "data": {
    "earned": "0.02",
    "reward_amount": 0.02,
    "user_balance": "25.02",
    "balance": 25.02,
    "ads_watched_today": 10,
    "ads_watched_in_current_cycle": 0,
    "current_cycle_number": 1,
    "last_claimed_cycle": 1,
    "ad_brack": 10,
    "daily_limit": 100,
    "today_earning": "0.02",
    "total_earning": "0.02",
    "has_more_ads": true,
    "daily_limit_reached": false,
    "remaining_cycles": 9
  }
}
```

---

## 4. App Controls & Security Settings

### 4.1 Global Settings (`GET /api/app-setting`)
- **`vpn_modes`**: `"not_allowed"`, `"allowed"`, `"required"`.
- **`vpn_required_in_task_only`**: `"yes"` or `"no"`.
- **`allowed_country`**: Comma-separated country codes/names (`"us,uk,au,bangladesh,india,pakistan,canada,australia"`).
- **`same_device_login`**: `"yes"` or `"no"`. Enforces single device login matching the registered `device_id`.
- **`registration_status`**: `"open"` or `"closed"`. If `"closed"`, `POST /api/register` returns 403 Forbidden.
- **`maintenance_mode`**: `"yes"` or `"no"`. If `"yes"`, returns 503 Service Unavailable.

---

## 5. Push Notifications & App Logo

- When sending notifications from Admin via OneSignal or Firebase FCM:
  - The App Logo from `Settinglogo` is automatically attached to `large_icon`, `app_logo`, and payload data.
  - Ensures Android / iOS notifications display the platform icon instead of default white placeholder boxes.

---

## 6. Community Posts & Image Download

### 6.1 Post Image Download
- **Endpoint:** `GET /api/posts/{id}/download`
- **Authentication:** Public / Token
- **Description:** Allows users to download attached post images directly.
- **`Creaetpost` Object Attributes:**
  - `image_url`: Full URL to view image.
  - `download_url`: Endpoint URL to trigger direct image download.
