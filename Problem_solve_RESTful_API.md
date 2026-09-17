# Problem Solve RESTful API & Flutter Implementation Guide

This document provides the complete technical specification, REST API contracts, and Flutter implementation instructions for all 5 requested features and fixes.

---

## 📑 Table of Contents
1. [Referral System & Refer Code Sharing](#1-referral-system--refer-code-sharing)
2. [Home Dashboard & Bottom Navigation Button Modifications](#2-home-dashboard--bottom-navigation-button-modifications)
3. [Withdrawal Block & P2P USDT Sell Order Restriction](#3-withdrawal-block--p2p-usdt-sell-order-restriction)
4. [Ad Timer & Break Count Background Handling (Seamless Resume/Auto-Finish)](#4-ad-timer--break-count-background-handling)
5. [Single Membership Constraint, Current Membership View & Buy Animation](#5-single-membership-constraint-current-membership-view--buy-animation)

---

## 1. Referral System & Refer Code Sharing

### 🎯 Problem & Solution
- **Problem**: When user clicks the "Refer" card, their own referral code was missing, making it impossible to copy and share with friends.
- **Backend Fix Applied**: Updated `RefferController::totalreffer` and `RefferController::referralStats` to ensure an 8-digit unique `ref_code` is always assigned to the user, and returned in the API responses along with the full `referral_link`.

### 🔌 API Specifications

#### `GET /api/totalreffer`
- **Headers**: `Authorization: Bearer <SANCTUM_TOKEN>`, `Accept: application/json`
- **Response Format**:
```json
{
  "status": true,
  "message": "Referrals fetched successfully",
  "referral_code": "79325743",
  "ref_code": "79325743",
  "referral_link": "https://yourdomain.com/register?ref=79325743",
  "total_referrals": 5,
  "active_users": 3,
  "total_earnings": 15.50,
  "referral_users": [
    {
      "id": 12,
      "name": "Rahim Khan",
      "email": "rahim@example.com",
      "phone": "017XXXXXXXX",
      "status": "active",
      "created_at": "2026-09-10T12:00:00.000Z",
      "earning": 0,
      "profile_photo": "https://yourdomain.com/uploads/profile/photo.jpg",
      "is_verified": true
    }
  ]
}
```

#### `GET /api/referral-stats`
- **Response Format**:
```json
{
  "status": true,
  "message": "Statistics fetched successfully",
  "data": {
    "direct_referrals": 5,
    "active_referrals": 3,
    "this_month_referrals": 2,
    "total_earnings": 15.50,
    "referral_code": "79325743",
    "ref_code": "79325743",
    "referral_link": "https://yourdomain.com/register?ref=79325743"
  }
}
```

### 📱 Flutter UI Implementation
1. In the **Refer / Referral Page**:
   - Display the **Referral Code** prominently in a styled card (e.g. `79325743`).
   - Add a **Copy Code Button** (`Clipboard.setData(ClipboardData(text: refCode))`) with a SnackBar: *"Referral code copied!"*.
   - Add a **Share Link Button** using `share_plus`:
   ```dart
   Share.share('Join me on GlobalAds and earn daily! Use my referral code: $refCode or register here: $referralLink');
   ```

---

## 2. Home Dashboard & Bottom Navigation Button Modifications

### 🎯 Requirements
1. **Total Deposit Card -> "Add Balance"**:
   - Change the button/card text on the Home Dashboard from `Total Deposit` to **`Add Balance`** (অ্যাড ব্যালেন্স).
   - On tap, navigate directly to the Deposit / Payment methods page (`/deposit` or `AddBalanceScreen`).

2. **Bottom Navigation "Withdraw" Icon -> "Deposit History"**:
   - Change the label and icon from `Withdraw` to **`Deposit History`** (ডিপোজিট হিস্টোরি).
   - On tap, navigate to the **Deposit History Screen** showing all previous deposit submissions and approvals.

### 🔌 Related APIs for Deposit & History

#### `GET /api/userDeposits`
- **Headers**: `Authorization: Bearer <SANCTUM_TOKEN>`
- **Response**: List of all deposit requests submitted by the user.

#### `GET /api/totaldeposite`
- **Response**: Total calculated deposit amount and active deposit balance.

---

## 3. Withdrawal Block & P2P USDT Sell Order Restriction

### 🎯 Problem & Solution
- **Problem**: When admin blocks a user's withdrawal, the user could still withdraw or place P2P USDT sell orders.
- **Backend Fix Applied**:
  - `UserWidthrawController::userwidthrawstore` (Direct withdraw) rejects blocked users with `403 Forbidden`.
  - `UserDepositewidthrawrequestController::userwidhraw_request` (P2P sell order) rejects `type === 'withdraw'` for blocked users with `403 Forbidden`.
  - `UserWidhrawrequestAgentController::userwidhraw_request` rejects `type === 'withdraw'` with `403 Forbidden`.
  - `is_withdraw_blocked: true/false` is returned in `/api/profile`, `/api/userwidthrawshow`, and `/api/user-earning`.

### 🔌 Direct Withdrawal API: `POST /api/userwidthrawstore`
- **Request Body**:
```json
{
  "payment_method_id": 1,
  "account_number": "017XXXXXXXX",
  "wallet_address": "0x123...",
  "amount": 20.00
}
```
- **Error Response When Blocked (HTTP 403)**:
```json
{
  "success": false,
  "message": "Your withdrawal has been blocked by administration. Please contact support."
}
```

### 🔌 P2P USDT Sell Order API: `POST /api/user/withdraw/request`
- **Request Body**:
```json
{
  "type": "withdraw",
  "agent_id": 4,
  "post_id": 10,
  "amount": 50,
  "sender_account": "Nagad: 017XXXXXXXX"
}
```
- **Error Response When Blocked (HTTP 403)**:
```json
{
  "success": false,
  "status": false,
  "message": "Your withdrawal is currently blocked by administration. You cannot place P2P USDT sell orders."
}
```

### 📱 Flutter App Handling
Before opening the Withdraw or P2P USDT Sell dialog:
```dart
if (userProfile.isWithdrawBlocked == true || userProfile.isBlocked == true) {
  showDialog(
    context: context,
    builder: (ctx) => AlertDialog(
      title: Text("উত্তোলন স্থগিত (Withdrawal Blocked)"),
      content: Text("আপনার অ্যাকাউন্ট থেকে উইথড্র ও P2P USDT সেল সাময়িকভাবে বন্ধ আছে। বিস্তারিত জানতে সাপোর্টে যোগাযোগ করুন।"),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(ctx),
          child: Text("ঠিক আছে"),
        ),
      ],
    ),
  );
  return;
}
```

---

## 4. Ad Timer & Break Count Background Handling

### 🎯 Problem & Solution
- **Problem**: When user starts watching an ad or enters break time and exits the app, the in-memory timer stopped or reset, causing lost time or breaking the task completion flow.
- **Backend Fix Applied**:
  - `UserearningController::userEarning` calculates real elapsed seconds based on `last_break_started` timestamp.
  - If the break elapsed while the app was closed (`elapsed >= break_total_seconds`), backend **automatically marks the break as complete**, clears `last_break_started`, and sets `show_claim_button: true`.
  - If still within break time, it returns exact `break_remaining_seconds`.
  - `claimReward` automatically validates elapsed time and allows claiming if break finished.

### 🔌 API: `GET /api/user-earning`
- **Response Format**:
```json
{
  "status": true,
  "data": {
    "user_name": "Jahidul Islam",
    "is_blocked": false,
    "is_withdraw_blocked": false,
    "ads_watched_today": 5,
    "ads_watched_in_current_cycle": 0,
    "current_cycle_number": 1,
    "last_claimed_cycle": 0,
    "daily_limit": 100,
    "is_break_active": false,
    "break_remaining_seconds": 0,
    "server_time": "2026-09-17T16:50:00.000Z",
    "last_break_started": null,
    "show_claim_button": true,
    "daily_limit_reached": false,
    "income_per_brack": "0.42",
    "next_reward": "0.42",
    "total_cycles": 20,
    "today_earning": "0.00",
    "total_earning": "12.50",
    "user_balance": "0.02",
    "button_timer_seconds": 30,
    "task_break_time_minutes": 1,
    "package_id": 2,
    "package_name": "Silver Membership",
    "package_price": "25.00",
    "ad_brack": 5,
    "daily_income": "0.42"
  }
}
```

### 📱 Flutter Implementation for Timers (Ad Button & Break)
Use **System Timestamps** rather than relying on pure `Timer.periodic`:

```dart
class TaskTimerManager {
  static const String KEY_AD_START = "ad_timer_start_ms";
  
  // 1. When user clicks "Watch Ad"
  static Future<void> startAdButtonTimer(int durationSeconds) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(KEY_AD_START, DateTime.now().millisecondsSinceEpoch);
    await prefs.setInt("ad_duration_seconds", durationSeconds);
  }

  // 2. Compute remaining seconds on App Resume (LifecycleState.resumed) or UI rebuild
  static Future<int> getRemainingAdSeconds() async {
    final prefs = await SharedPreferences.getInstance();
    final startMs = prefs.getInt(KEY_AD_START);
    final duration = prefs.getInt("ad_duration_seconds") ?? 30;
    
    if (startMs == null) return 0;
    
    final elapsedSeconds = ((DateTime.now().millisecondsSinceEpoch - startMs) / 1000).floor();
    final remaining = duration - elapsedSeconds;
    
    if (remaining <= 0) {
      await prefs.remove(KEY_AD_START);
      return 0; // Completed!
    }
    return remaining;
  }
}
```

---

## 5. Single Membership Constraint, Current Membership View & Buy Animation

### 🎯 Problem & Solution
- **Problem**: Users could buy multiple memberships or upgrade endlessly. User requested that once a membership is bought, the account is restricted to that single membership (no other membership can be purchased). Also, on successful purchase, an engaging animation with membership logo must be shown.
- **Backend Fix Applied**:
  - `PackagesbuyuserController::packagebuy` checks if the user has an active approved package. If active, rejects purchase with `400 Bad Request`: *"You already have an active membership! An account cannot purchase multiple memberships."*.
  - `PackagesbuyuserController::getCurrentPackage` and `packagebuy` return full `photo_url` for displaying package logos.
  - `PackagesshowuserController::packageshow` returns `has_active_package` and `active_package_id`.

### 🔌 Buy Membership API: `POST /api/packagebuy/{package_id}`
- **Headers**: `Authorization: Bearer <SANCTUM_TOKEN>`

#### Success Response (HTTP 200):
```json
{
  "success": true,
  "message": "Membership purchased successfully!",
  "data": {
    "package_id": 2,
    "package_name": "Silver Membership",
    "package_price": 25.0,
    "amount_paid": 25.0,
    "new_balance": 5.0,
    "daily_income": 0.42,
    "daily_limit": 100,
    "validity": 365,
    "photo": "https://yourdomain.com/uploads/package/silver.png",
    "photo_url": "https://yourdomain.com/uploads/package/silver.png"
  }
}
```

#### Rejection When User Already Has an Active Package (HTTP 400):
```json
{
  "success": false,
  "has_active_membership": true,
  "current_package": {
    "id": 14,
    "package_id": 2,
    "package_name": "Silver Membership",
    "photo": "https://yourdomain.com/uploads/package/silver.png",
    "photo_url": "https://yourdomain.com/uploads/package/silver.png",
    "amount": 25.0
  },
  "message": "You already have an active membership! An account cannot purchase multiple memberships."
}
```

### 🔌 Get Current Active Package: `GET /api/user/current-package`
- **Response**:
```json
{
  "success": true,
  "has_active_package": true,
  "data": {
    "id": 14,
    "user_id": 1,
    "package_id": 2,
    "package_name": "Silver Membership",
    "amount": 25.0,
    "daily_income": 0.42,
    "daily_limit": 100,
    "validity": 365,
    "photo": "https://yourdomain.com/uploads/package/silver.png",
    "photo_url": "https://yourdomain.com/uploads/package/silver.png",
    "status": "approved",
    "created_at": "2026-09-15T10:00:00.000Z",
    "updated_at": "2026-09-15T10:00:00.000Z"
  }
}
```

### 📱 Flutter UI & Success Animation Dialog Specification

1. **Membership Screen UI**:
   - At the top of the Membership screen, check if `has_active_package == true`.
   - If true:
     - Display the **Active Membership Card** at the top with a green verified checkmark icon, package name, daily income, and validity.
     - For all package cards in the list:
       - If `package.id == active_package_id`: Show a green badge **"Current Package (Active)"**.
       - For other packages: Disable the button or show a grey button **"Unavailable"** (যেহেতু একটি অ্যাকাউন্টে একটি মেম্বারশিপ প্রযোজ্য).

2. **Celebration Animation on Buy Success**:
   - Use `confetti` package or Lottie animation alongside a stylish custom dialog:
   ```dart
   void showMembershipSuccessDialog(BuildContext context, Map<String, dynamic> packageData) {
     showGeneralDialog(
       context: context,
       barrierDismissible: false,
       pageBuilder: (ctx, anim1, anim2) {
         return Center(
           child: Container(
             margin: EdgeInsets.all(24),
             padding: EdgeInsets.all(24),
             decoration: BoxDecoration(
               color: Color(0xFF1E1E2D),
               borderRadius: BorderRadius.circular(24),
               boxShadow: [
                 BoxShadow(color: Colors.pink.withOpacity(0.3), blurRadius: 20, spreadRadius: 5)
               ],
             ),
             child: Column(
               mainAxisSize: MainAxisSize.min,
               children: [
                 // Confetti / Animation Top
                 Icon(Icons.stars_rounded, color: Colors.amber, size: 64),
                 SizedBox(height: 12),
                 Text(
                   "Congratulations!",
                   style: TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold),
                 ),
                 SizedBox(height: 8),
                 Text(
                   "Membership Buy Successful",
                   style: TextStyle(color: Colors.greenAccent, fontSize: 16),
                 ),
                 SizedBox(height: 16),
                 // Membership Logo
                 if (packageData['photo_url'] != null)
                   ClipRRect(
                     borderRadius: BorderRadius.circular(16),
                     child: Image.network(
                       packageData['photo_url'],
                       height: 100,
                       fit: BoxFit.contain,
                     ),
                   ),
                 SizedBox(height: 12),
                 Text(
                   packageData['package_name'] ?? 'Membership',
                   style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                 ),
                 SizedBox(height: 6),
                 Text(
                   "Daily Income: \$${packageData['daily_income']} | Daily Ads: ${packageData['daily_limit']}",
                   style: TextStyle(color: Colors.grey[400], fontSize: 13),
                 ),
                 SizedBox(height: 20),
                 ElevatedButton(
                   style: ElevatedButton.styleFrom(
                     backgroundColor: Color(0xFFFF2E63),
                     shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                     padding: EdgeInsets.symmetric(horizontal: 32, vertical: 12),
                   ),
                   onPressed: () {
                     Navigator.pop(ctx);
                     // Refresh page data
                   },
                   child: Text("Start Earning Now", style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                 )
               ],
             ),
           ),
         );
       },
     );
   }
   ```

---
