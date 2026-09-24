# GlobalAds LMT - Final Check RESTful API Documentation

This document contains the complete and verified RESTful API endpoints for the GlobalAds platform, including updated App Settings, VPN & Location Controls, Daily Task & Reward Claim synchronization, Ad Network Timer configurations, Push Notification with Dynamic App Logo, and KYC verification status.

---

## 1. App Configuration & Control Settings

### 1.1 Get Global App Settings
- **Endpoint:** `GET /api/app-setting`
- **Authentication:** Optional (Public)
- **Description:** Returns all global settings including AdMob IDs, Star.io IDs, timer statuses, VPN modes, allowed countries, and maintenance mode.

#### Response Example:
```json
{
  "success": true,
  "status": true,
  "data": {
    "star_io_id": "209922521",
    "startapp_app_id": "209922521",
    "admob_app_id": "ca-app-pub-3940256099942544~3347511713",
    "admob_banner_id": "ca-app-pub-3940256099942544/6300978111",
    "admob_interstitial_id": "ca-app-pub-3940256099942544/1033173712",
    "admob_rewarded_interstitial_id": "ca-app-pub-3940256099942544/5354046379",
    "admob_rewarded_id": "ca-app-pub-3940256099942544/5224354917",
    "admob_native_id": "ca-app-pub-3940256099942544/2247696110",
    "admob_app_open_id": "ca-app-pub-3940256099942544/9257395921",
    "admob_status": true,
    "stario_timer_status": "yes",
    "admob_timer_status": "no",
    "task_break_time_minutes": 2,
    "button_timer_seconds": 30,
    "ad_timer_seconds": 15,
    "invalid_click_limit": 5,
    "invalid_deduct": 1.0,
    "view_before_click_view_target": 10,
    "vpn_modes": "not_allowed",
    "vpn_required_in_task_only": "yes",
    "allowed_country": "us,uk,au,bangladesh,india,pakistan,canada,australia",
    "registration_status": "open",
    "same_device_login": "yes",
    "maintenance_mode": "no",
    "app_version": "1.0.0",
    "app_link": "https://play.google.com/store/apps/details?id=com.globaladslmt.app"
  }
}
```

### 1.2 Get Latest Ads Configuration (For Flutter/Android)
- **Endpoint:** `GET /api/ads/latest`
- **Authentication:** Optional (Public)

---

## 2. Daily Tasks & Earnings Engine

### 2.1 Get User Earning & Task State
- **Endpoint:** `GET /api/user-earning`
- **Authentication:** Bearer Token (`auth:sanctum`)
- **Description:** Returns the user's active package, current ad progress, cycle count, break time remaining, and claim readiness.

#### Response Example:
```json
{
  "status": true,
  "data": {
    "user_name": "John Doe",
    "is_blocked": false,
    "is_withdraw_blocked": false,
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
    "user_balance": "0.00",
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
  },
  "message": "Data loaded successfully"
}
```

### 2.2 Track Ad View (Sequential & Debounced)
- **Endpoint:** `POST /api/track-ad-view`
- **Authentication:** Bearer Token (`auth:sanctum`)
- **Behavior:** 
  - Prevents skipped ads by strictly incrementing ad view count 1-by-1.
  - Rate-limited/debounced to eliminate accidental multi-incrementing.
  - Automatically respects `admob_timer_status`: If AdMob timer is disabled (`no`), break time is skipped so users can proceed seamlessly.

#### Response Example:
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

### 2.3 Break Complete (Timer Expired)
- **Endpoint:** `POST /api/break-complete`
- **Authentication:** Bearer Token (`auth:sanctum`)

### 2.4 Claim Reward (Balance Synchronization)
- **Endpoint:** `POST /api/claim-reward`
- **Authentication:** Bearer Token (`auth:sanctum`)
- **Behavior:**
  - Validates completion of target ads for the cycle.
  - Credit is added to user `balance` and daily `today_earning`.
  - Marks the cycle as claimed in DB (`last_claimed_cycle = current_cycle_number`).

#### Response Example:
```json
{
  "status": true,
  "success": true,
  "message": "🎉 $0.02 Earned!",
  "data": {
    "earned": "0.02",
    "reward_amount": 0.02,
    "user_balance": "0.02",
    "balance": 0.02,
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

## 3. KYC Verification Status

### 3.1 Get Profile with Verification Status
- **Endpoint:** `GET /api/profile`
- **Authentication:** Bearer Token (`auth:sanctum`)

#### Response Example:
```json
{
  "status": true,
  "message": "Profile fetched successfully",
  "data": {
    "id": 12,
    "name": "Alex Smith",
    "mobile": "+8801700000000",
    "email": "alex@example.com",
    "phone": "+8801700000000",
    "ref_code": "48291048",
    "referral_code": "48291048",
    "referral_link": "https://globaladslmt.com/register?ref=48291048",
    "is_blocked": false,
    "is_withdraw_blocked": false,
    "is_verified": true,
    "kyc_status": "verified",
    "verification_status": "verified",
    "profile_photo": "https://globaladslmt.com/uploads/profile/profile_12.jpg",
    "total_coins": 150.5,
    "balance": 150.5,
    "tasks_done": 25
  }
}
```

### 3.2 Check User Verification Status
- **Endpoint:** `GET /api/user/{user_id}/verify-status`
- **Authentication:** Optional (Public)

#### Response:
```json
{
  "status": true,
  "verified": true,
  "is_verified": true,
  "kyc_status": "verified",
  "message": "User is verified"
}
```

---

## 4. Push Notifications & App Logo

### 4.1 Push Notification Integration
- When sending push notifications via OneSignal or Firebase FCM:
  - The App Logo configured in Admin (`Settinglogo`) is automatically included in `large_icon`, `app_logo`, `icon_url`, and custom payload data.
  - Ensures Android & iOS show the official app logo on notifications instead of default blank squares.

### 4.2 Fetch App Logo
- **Endpoint:** `GET /api/logo`
- **Response:**
```json
{
  "status": true,
  "data": {
    "id": 1,
    "photo": "https://globaladslmt.com/uploads/logo/logo_main.png"
  },
  "message": "Logo fetched successfully"
}
```
