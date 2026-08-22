# GlobalAdSLMT REST API Documentation

This is the official API documentation for the **GlobalAdSLMT** system. The backend is built using Laravel 11/10 and uses **Laravel Sanctum** for token-based authentication.

## Table of Contents
1. [Authentication & Recovery](#1-authentication--recovery)
2. [User Profile & Settings](#2-user-profile--settings)
3. [KYC Verification](#3-kyc-verification)
4. [Packages & Earning System](#4-packages--earning-system)
5. [Deposit System (Manual)](#5-deposit-system-manual)
6. [P2P & Agent Deposit/Withdrawal System](#6-p2p--agent-depositwithdrawal-system)
7. [Standard Withdrawal System](#7-standard-withdrawal-system)
8. [Unified Payment History API](#8-unified-payment-history-api)
9. [Friends & P2P Chat System](#9-friends--p2p-chat-system)
10. [User-to-Agent & User-to-Admin Chat Systems](#10-user-to-agent--user-to-admin-chat-systems)
11. [Agent Connection System](#11-agent-connection-system)
12. [Unified Notifications (Firebase & OneSignal)](#12-unified-notifications-firebase--onesignal)
13. [App Settings, Guidelines & Notice Boards](#13-app-settings-guidelines--notice-boards)
14. [Important Developer Warnings & Notes](#14-important-developer-warnings--notes)

---

## Base Configuration

- **Base URL:** `https://your-domain.com/api`
- **Headers Required (for authenticated endpoints):**
  ```http
  Authorization: Bearer <your_sanctum_token>
  Accept: application/json
  Content-Type: application/json (or multipart/form-data for file uploads)
  ```

---

## 1. Authentication & Recovery

### User Registration
Register a new user in the system. Optional referral code (`ref_code`) links user directly to referrer for commission processing.
- **Endpoint:** `POST /register`
- **Request Body:**
  ```json
  {
    "name": "John Doe",
    "email": "johndoe@example.com",
    "mobile": "01700000000",
    "password": "password123",
    "password_confirmation": "password123",
    "ref_code": "12345678"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "token": "1|abcdef123456...",
      "user": {
        "id": 12,
        "name": "John Doe",
        "email": "johndoe@example.com",
        "mobile": "01700000000",
        "ref_code": "87654321",
        "referred_by": 5,
        "created_at": "2026-06-10T14:10:00.000000Z"
      }
    },
    "message": "User registered successfully"
  }
  ```

### User Login
Authenticate user and retrieve token.
- **Endpoint:** `POST /login`
- **Request Body:**
  ```json
  {
    "email": "johndoe@example.com",
    "password": "password123"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "token": "2|ghijk7890...",
      "user": {
        "id": 12,
        "name": "John Doe",
        "email": "johndoe@example.com",
        "role": "user",
        "is_blocked": false
      }
    },
    "message": "User logged in successfully"
  }
  ```

### User Logout
Deletes current Sanctum access token.
- **Endpoint:** `POST /logout`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": [],
    "message": "User logged out successfully"
  }
  ```

### Request Password Reset
Requests reset code sent to email. Code is 8-10 digit numeric token.
- **Endpoint:** `POST /password/email`
- **Request Body:**
  ```json
  {
    "email": "johndoe@example.com"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Password reset code has been sent to your email!",
    "debug_token": "87635294" 
  }
  ```
  *(Note: `debug_token` is only included if `APP_DEBUG=true`)*

- **Response (400 Bad Request - Mail Not Configured):**
  ```json
  {
    "success": false,
    "message": "Forgot password is disabled because the administrator has not configured the email settings. Please contact support.",
    "error_code": "MAIL_NOT_CONFIGURED"
  }
  ```

### Verify & Reset Password
Completes recovery with token received.
- **Endpoint:** `POST /password/reset`
- **Request Body:**
  ```json
  {
    "email": "johndoe@example.com",
    "token": "87635294",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Password has been reset successfully!"
  }
  ```

---

## 2. User Profile & Settings

### Get Profile Details
- **Endpoint:** `GET /profile`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "id": 12,
      "name": "John Doe",
      "email": "johndoe@example.com",
      "photo": "https://domain.com/uploads/profile/profile_image.jpg",
      "photo_name": "profile_image.jpg",
      "role": "user",
      "created_at": "2026-06-10 14:10:00"
    },
    "message": "Profile retrieved successfully."
  }
  ```

### Update Profile
Supports partial updates. Uses multipart/form-data.
- **Endpoint:** `POST /profileupdate`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body (multipart/form-data):**
  - `name` (string, optional)
  - `email` (string, email, unique, optional)
  - `photo` (file, optional image, max 5MB)
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "id": 12,
      "name": "John Doe Updated",
      "email": "johndoe@example.com",
      "photo": "https://domain.com/uploads/profile/profile_17178000_60a8b9.jpg",
      "photo_name": "profile_17178000_60a8b9.jpg",
      "role": "user"
    },
    "message": "Profile updated successfully."
  }
  ```

### Delete Profile Photo
- **Endpoint:** `GET /profile/photo`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": [],
    "message": "Photo deleted successfully."
  }
  ```

### Change Password
Change account password inside system.
- **Endpoint:** `POST /chagepassword`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body:**
  ```json
  {
    "old_password": "currentpassword123",
    "new_password": "newpassword456",
    "confirm_password": "newpassword456"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": [],
    "message": "Password changed successfully."
  }
  ```

### Fetch Chat Profile of User
- **Endpoint:** `GET /chat-profile-user`
- **Headers:** `Authorization: Bearer <token>`
- **Query Parameters / Body:**
  - `user_id` (integer, required)
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "id": 15,
      "name": "Friend Name",
      "photo": "https://domain.com/uploads/profile/avatar.png",
      "photo_name": "avatar.png",
      "role": "user",
      "created_at": "2026-06-01 10:00:00"
    },
    "message": "User profile retrieved successfully"
  }
  ```

---

## 3. KYC Verification

### Submit KYC
- **Endpoint:** `POST /kycsubmit`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body (multipart/form-data):**
  - `document_type` (string, required, e.g., "NID", "Passport")
  - `document_first_part_photo` (file, required image, max 2MB)
  - `document_secound_part_photo` (file, required image, max 2MB)
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "kyc_id": 8,
      "status": "pending",
      "document_type": "NID",
      "first_photo_url": "https://domain.com/uploads/kyc/kyc_12_front_17178000.jpg",
      "second_photo_url": "https://domain.com/uploads/kyc/kyc_12_back_17178000.jpg",
      "submitted_at": "2026-06-10 14:15:00"
    },
    "message": "KYC submitted successfully"
  }
  ```

### Fetch KYC Status
- **Endpoint:** `GET /kycsubmit/kyc-status`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK - Approved/Pending):**
  ```json
  {
    "success": true,
    "data": {
      "kyc_id": 8,
      "document_type": "NID",
      "status": "pending",
      "status_message": "Your KYC is under review",
      "submitted_at": "2026-06-10 14:15:00",
      "first_photo_url": "https://domain.com/uploads/kyc/kyc_12_front_17178000.jpg",
      "second_photo_url": "https://domain.com/uploads/kyc/kyc_12_back_17178000.jpg"
    },
    "message": "KYC status fetched"
  }
  ```
- **Response (200 OK - Rejected):**
  ```json
  {
    "success": true,
    "data": {
      "kyc_id": 8,
      "document_type": "NID",
      "status": "rejected",
      "status_message": "Your KYC was rejected",
      "submitted_at": "2026-06-10 14:15:00",
      "can_resubmit": true,
      "rejection_reason": "Documents are blurry"
    },
    "message": "KYC status fetched"
  }
  ```

### Resubmit KYC
Allows resubmitting document if status is `rejected`. Automatically replaces old documents.
- **Endpoint:** `POST /kycsubmit/kyc-resubmit`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body (multipart/form-data):** Same as [Submit KYC](#submit-kyc).

---

## 4. Packages & Earning System

### Show All Packages
Lists all active packages. Sorted by price ASC.
- **Endpoint:** `GET /packageshow`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "package_name": "Silver Package",
        "validity": 30,
        "price": 20.00,
        "daily_income": 1.50,
        "daily_limit": 15,
        "photo": "https://domain.com/uploads/package/silver.png"
      }
    ]
  }
  ```

### Get User's Current Active Package
- **Endpoint:** `GET /user/current-package`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "id": 4,
      "user_id": 12,
      "package_id": 1,
      "package_name": "Silver Package",
      "amount": 20.00,
      "daily_income": 1.50,
      "daily_limit": 15,
      "validity": 30,
      "status": "approved",
      "created_at": "2026-06-08T12:00:00Z"
    }
  }
  ```

### Buy or Upgrade Package
Upgrading deducts only the difference (`new_package_price - old_package_price`) from balance. Referral commission is given to uplines on first purchase only.
- **Endpoint:** `POST /packagebuy/{package_id}`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Package purchased successfully!",
    "data": {
      "package_id": 2,
      "package_name": "Gold Package",
      "package_price": 50.00,
      "amount_paid": 30.00,
      "new_balance": 15.50,
      "daily_income": 3.50,
      "daily_limit": 30
    }
  }
  ```

### Fetch Daily Earning Data & Configs (Crucial Task Endpoint)
Returns today's ad stats, ad-watch timers, admob/start.io credentials, maintenance configurations and VPN logic.
- **Endpoint:** `GET /api/user-earning`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "status": true,
    "data": {
      "user_name": "John Doe",
      "ads_watched_today": 8,
      "ads_watched_in_current_cycle": 3,
      "current_cycle_number": 1,
      "last_claimed_cycle": 0,
      "daily_limit": 15,
      "is_break_active": false,
      "show_claim_button": false,
      "daily_limit_reached": false,
      "income_per_brack": "0.50",
      "next_reward": "0.50",
      "total_cycles": 3,
      "today_earning": "0.00",
      "total_earning": "24.50",
      "user_balance": "45.50",
      "admob_app_id": "ca-app-pub-3940256099942544~3347511713",
      "admob_status": true,
      "star_io_id": "207800000",
      "invalid_click_limit": 5,
      "invalid_deduct": "0.10",
      "task_break_time_minutes": 5,
      "button_timer_seconds": 30,
      "vpn_modes": "yes",
      "vpn_required_in_task_only": "yes",
      "allowed_country": "us,uk,ca",
      "registration_status": "open",
      "same_device_login": "no",
      "maintenance_mode": "no",
      "app_version": "1.0.4",
      "app_link": "https://play.google.com/store/apps/details?id=com.globaladslmt",
      "package_id": 1,
      "package_name": "Silver Package",
      "ad_brack": 5
    },
    "message": "Data loaded successfully"
  }
  ```

### Track Ad View
Increments ad view count. Starts break timer if ad count reaches cycle limit (`ad_brack`).
- **Endpoint:** `POST /track-ad-view`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "status": true,
    "data": {
      "ads_watched_today": 5,
      "ads_watched_in_current_cycle": 0,
      "ad_brack": 5,
      "cycle_completed": true,
      "start_break_timer": true
    },
    "message": "View tracked successfully"
  }
  ```

### Break Timer Complete
Cleans break status of today's record and determines if user can claim reward.
- **Endpoint:** `POST /break-complete`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "status": true,
    "data": {
      "show_claim_button": true,
      "ads_watched_today": 5,
      "ads_watched_in_current_cycle": 0,
      "current_cycle_number": 1,
      "last_claimed_cycle": 0,
      "daily_limit_reached": false
    },
    "message": "Break complete"
  }
  ```

### Claim Cycle Reward
Adds rewards to user balance and updates today's income.
- **Endpoint:** `POST /claim-reward`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "🎉 ৳0.50 Earned!",
    "data": {
      "earned": "0.50",
      "ads_watched_today": 5,
      "ads_watched_in_current_cycle": 0,
      "current_cycle_number": 1,
      "last_claimed_cycle": 1,
      "ad_brack": 5,
      "daily_limit": 15,
      "today_earning": "0.50",
      "total_earning": "25.00",
      "user_balance": "46.00",
      "has_more_ads": true,
      "daily_limit_reached": false,
      "remaining_cycles": 2
    }
  }
  ```

### Track Invalid Ad Click
- **Endpoint:** `POST /track-invalid-click`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Invalid click tracked"
  }
  ```

---

## 5. Deposit System (Manual)

### Submit Manual Deposit Request
Submit transaction proof directly for admin approval. Limits checked based on system configurations.
- **Endpoint:** `POST /deposite`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body (multipart/form-data):**
  - `amount` (numeric, required)
  - `transaction_id` (string, required)
  - `sender_account` (string, required)
  - `photo` (file, optional image, max 2MB)
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "id": 45,
      "amount": 25.00,
      "transaction_id": "TXN12345678",
      "sender_account": "01700000000",
      "status": "pending",
      "photo": "https://domain.com/uploads/deposits/45_image.jpg",
      "min_limit": 10.00,
      "max_limit": 1000.00
    },
    "message": "Deposit request submitted successfully and pending for approval."
  }
  ```

### Fetch All Deposits (Merged history from both standard & P2P tables)
- **Endpoint:** `GET /userDeposits`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 45,
        "amount": 25.00,
        "transaction_id": "TXN12345678",
        "sender_account": "01700000000",
        "status": "pending",
        "photo": "uploads/deposits/45_image.jpg",
        "created_at": "2026-06-10 14:20:00",
        "source": "deposites"
      },
      {
        "id": 12,
        "amount": 50.00,
        "transaction_id": "N/A",
        "sender_account": "N/A",
        "status": "approved",
        "photo": null,
        "created_at": "2026-06-08 10:00:00",
        "source": "userdepositerequests"
      }
    ],
    "summary": {
      "total_approved": 50.00,
      "total_pending": 25.00,
      "total_rejected": 0.00,
      "total_deposits": 2,
      "current_balance": 50.00
    }
  }
  ```

### Sync User Balance
Forces recalculated sum of all approved deposits across both tables to users balance.
- **Endpoint:** `POST /user/sync-balance`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "old_balance": 40.00,
      "new_balance": 50.00,
      "deposites_approved": 0,
      "requests_approved": 50.00
    },
    "message": "Balance synchronized successfully"
  }
  ```

---

## 6. P2P & Agent Deposit/Withdrawal System

This P2P trading system enables users to trade directly with approved agents. 
- **Important Rule:** `users.balance` is **never modified on deposits** (the balance is computed dynamically from approved deposits via `sync-balance` / `userDeposits`). 
- **Important Rule:** For **withdrawals**, balance is deducted instantly when the withdrawal is finalized.

### Get P2P Trade Posts
Lists all approved agent buy/sell offers with agent payment methods and statistics.
- **Endpoint:** `GET /buysellpost`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Posts loaded successfully",
    "posts": [
      {
        "id": 2,
        "post_type": "deposit",
        "trade_limit": 10.00,
        "trade_limit_two": 500.00,
        "available_balance": 1200.00,
        "rate_balance": 115.50,
        "duration": 30,
        "agent": {
          "id": 5,
          "name": "Agent Jerry",
          "email": "jerry@agent.com",
          "is_verified": true,
          "is_online": true,
          "payment_methods": [
            {
              "id": 1,
              "method_name": "bKash",
              "method_number": "01800000000"
            }
          ]
        },
        "limits": { "min": 10.00, "max": 500.00 },
        "amounts": { "hold": 1200.00, "currency": "BDT" }
      }
    ]
  }
  ```

### Create P2P Trade Request (Deposit or Withdrawal)
- **Endpoint:** `POST /user/deposit/request` (or `POST /user/withdraw/request`)
- **Headers:** `Authorization: Bearer <token>`
- **Request Body:**
  ```json
  {
    "type": "deposit", // Or "withdraw"
    "agent_id": 5,
    "post_id": 2,
    "amount": 100.00,
    "sender_account": "01700000000", // Required if type is 'withdraw'
    "transaction_id": "TXN_OPTIONAL"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Deposit request sent successfully",
    "request_id": 34,
    "amount": 100.00
  }
  ```

### Check Trade Status (Polling)
- **Endpoint:** `GET /user/deposit/status` (or `GET /user/withdraw/status`)
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "status": "agent_confirmed", // pending, agent_confirmed, user_submitted, completed, cancelled
    "deposit_id": 34,
    "amount": 100.00
  }
  ```

### Cancel Trade Request
Users can cancel requests only when in `pending` status.
- **Endpoint:** `POST /depositecancled` (or `POST /withdrawcancled`)
- **Headers:** `Authorization: Bearer <token>`
- **Request Body:**
  ```json
  {
    "deposit_id": 34 // Or "withdraw_id": 34
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Deposit cancelled successfully"
  }
  ```

### Submit Payment Proof (For Deposit Requests)
After the agent confirms, the user pays the agent and submits details.
- **Endpoint:** `POST /user/deposit/submit/{id}`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body (multipart/form-data):**
  - `transaction_id` (string, required)
  - `sender_account` (string, required)
  - `photo` (file, required image proof, max 5MB)
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Payment proof submitted successfully",
    "deposit_id": 34
  }
  ```

### Complete Withdrawal (For Withdraw Requests)
Once agent confirms receipt/payment, user completes and system deducts balance.
- **Endpoint:** `POST /user/withdraw/submit/{id}`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Withdraw completed successfully",
    "new_balance": "15.50",
    "amount": 100.00,
    "net_amount": 95.00
  }
  ```

### Fetch P2P Trade History
- **Endpoint:** `GET /p2p/getHistory`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "History loaded successfully",
    "history": [
      {
        "id": 34,
        "type": "deposit",
        "amount": "100.00",
        "currency": "USDT",
        "status": "completed",
        "agent_name": "Agent Jerry",
        "transaction_id": "TXN98765432",
        "sender_account": "01700000000",
        "created_at": "2026-06-10 14:30:00"
      }
    ]
  }
  ```

---

## 7. Standard Withdrawal System

Standard withdrawals bypass agents and are processed directly by administrators. The balance is **deducted immediately** upon submission of the request.

### Show Withdrawal Config & Options
- **Endpoint:** `GET /userwidthrawshow`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "user": { "id": 12, "name": "John Doe", "balance": 45.50 },
      "payment_methods": [
        {
          "id": 1,
          "method_name": "bKash",
          "method_number": "01800000000",
          "photo": "https://domain.com/uploads/paymentmethod/bkash.png",
          "status": "active"
        }
      ],
      "withdraw_limit": {
        "min_withdraw_limit": 10.00,
        "max_withdraw_limit": 500.00
      }
    },
    "message": "User withdraw data fetched successfully."
  }
  ```

### Submit Standard Withdrawal Request
- **Endpoint:** `POST /userwidthrawstore`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body:**
  ```json
  {
    "payment_method_id": 1,
    "account_number": "01700000000",
    "wallet_address": "Optional Binance ID / TRC20",
    "amount": 20.00
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "withdraw": {
        "id": 5,
        "amount": 20.00,
        "status": "pending",
        "payment_method": "bKash"
      },
      "new_balance": 25.50
    },
    "message": "Withdraw request submitted successfully. Amount deducted from your balance."
  }
  ```

### Fetch Standard Withdrawal History (Paginated)
- **Endpoint:** `GET /withdraw-history`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "withdraw_history": [
        {
          "id": 5,
          "amount": 20.00,
          "commission": 0.00,
          "status": "pending",
          "payment_method": "bKash",
          "account_number": "01700000000",
          "wallet_address": null,
          "requested_at": "2026-06-10 14:40:00"
        }
      ]
    }
  }
  ```

---

## 8. Unified Payment History API

Retrieves comprehensive transaction history by merging standard deposits, P2P deposits, standard withdrawals, and P2P withdrawals.

> [!WARNING]
> This route is currently nested inside `/test-agents` closure in `routes/api.php` by mistake. It will only run as `/api/paymenthistory` if it is moved outside of the closure block.

- **Endpoint:** `GET /paymenthistory`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Payment history fetched successfully",
    "history": [
      {
        "type": "deposit", // deposit, deposit_request, withdraw, withdraw_request
        "id": "TXN12345678",
        "amount": "25.00",
        "status": "approved",
        "date": "2026-06-10",
        "formatted_date": "Jun 10, 2026",
        "time": "02:20 PM",
        "photo": "uploads/deposits/image.jpg"
      },
      {
        "type": "withdraw",
        "id": "#WD-5",
        "amount": "20.00",
        "status": "pending",
        "date": "2026-06-10",
        "formatted_date": "Jun 10, 2026",
        "time": "02:40 PM",
        "payment_method": "bKash",
        "account_number": "01700000000",
        "wallet_address": null
      }
    ],
    "total": 2
  }
  ```

---

## 9. Friends & P2P Chat System

This module allows regular users (`role: user`) to search, add friends, and chat with each other.

### Search Users
- **Endpoint:** `GET /user-search`
- **Headers:** `Authorization: Bearer <token>`
- **Query Parameters:** `q` (string search query)
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 18,
        "name": "Alex Mercer",
        "email": "alex@example.com",
        "photo": "https://domain.com/uploads/profile/alex.jpg",
        "friend_status": "none", // none, pending, accepted, rejected
        "request_sent_by_me": false,
        "can_send_request": true
      }
    ],
    "message": "1 user(s) found"
  }
  ```

### Send Friend Request
- **Endpoint:** `POST /user/friend/request`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body:**
  ```json
  {
    "receiver_id": 18
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "id": 99,
      "sender_id": 12,
      "receiver_id": 18,
      "status": "pending"
    },
    "message": "Friend request sent successfully"
  }
  ```

### Accept / Reject Friend Request
- **Endpoint:** `POST /user/friend/request/accept` (or `/user/friend/request/reject`)
- **Headers:** `Authorization: Bearer <token>`
- **Request Body:**
  ```json
  {
    "sender_id": 12
  }
  ```

### Fetch Friends List
- **Endpoint:** `GET /friends`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 18,
        "name": "Alex Mercer",
        "email": "alex@example.com",
        "photo": "https://domain.com/uploads/profile/alex.jpg",
        "friendship_since": "2026-06-10T14:45:00Z"
      }
    ]
  }
  ```

### Send Chat Message (With optional image)
Send text or photo to a friend.
- **Endpoint:** `POST /chat/frontend/submit`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body (multipart/form-data):**
  - `receiver_id` (integer, required)
  - `message` (string, required if image is missing)
  - `image` (file, optional image, max 5MB)
- **Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Message sent successfully",
    "data": {
      "id": 156,
      "message": "Hello friend!",
      "image": null,
      "is_sent": true,
      "created_at": "02:46 PM"
    }
  }
  ```

### Fetch Messages
Retrieves messages with a friend and automatically marks received messages as read.
- **Endpoint:** `GET /chat/frontend/messages`
- **Headers:** `Authorization: Bearer <token>`
- **Query Parameters:** `user_id` (integer, friend's user id)
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 156,
        "message": "Hello friend!",
        "image": null,
        "is_sent": true,
        "is_read": true,
        "created_at": "02:46 PM",
        "date": "Jun 10, 2026"
      }
    ]
  }
  ```

---

## 10. User-to-Agent & User-to-Admin Chat Systems

These systems support two communication channels: one for chatting with agents/moderators and one directly with the primary system administrator.

### Usertoagent: Get Agent List
- **Endpoint:** `GET /usertoagentchat/agents`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Agents loaded",
    "data": {
      "agents": [
        {
          "id": 5,
          "name": "Agent Jerry",
          "email": "jerry@agent.com",
          "avatar": "https://domain.com/uploads/agent/jerry.png",
          "status": "available",
          "unread_count": 0
        }
      ]
    }
  }
  ```

### Usertoagent: Send Message to Agent
- **Endpoint:** `POST /usertoagentchat/send`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body (multipart/form-data):**
  - `agent_id` (integer, required)
  - `message` (string, optional)
  - `image` (file, optional image, max 5MB)
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Message sent successfully",
    "data": {
      "id": 89,
      "sender_id": 12,
      "receiver_id": 5,
      "sender_type": "user",
      "message": "Support request",
      "message_type": "text",
      "image_url": null,
      "is_read": false,
      "created_at": "2026-06-10T14:48:00Z"
    }
  }
  ```

### Usertoadmin: Send Message to Admin
Sends message directly to system Administrator (defaults to User ID 1).
- **Endpoint:** `POST /usertoadminchat/send`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body (multipart/form-data):**
  - `message` (string, optional)
  - `image` (file, optional, max 2MB)
  - `message_type` (string, optional, 'text' or 'image')
- **Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "মেসেজ সফলভাবে পাঠানো হয়েছে",
    "data": {
      "id": 105,
      "sender_id": 12,
      "sender_type": "user",
      "receiver_id": 1,
      "message": "Hello Admin",
      "message_type": "text",
      "image_url": null,
      "is_read": false,
      "created_at": "2026-06-10T14:49:00Z"
    }
  }
  ```

### Usertoadmin: Fetch Messages from Admin
Fetches messages between current user and admin.
- **Endpoint:** `GET /usertoadminchat/fetch`
- **Headers:** `Authorization: Bearer <token>`
- **Query Parameters:**
  - `page` (integer, optional, default: 1)
  - `per_page` (integer, optional, default: 20)
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "মেসেজ সফলভাবে লোড হয়েছে",
    "data": {
      "messages": [
        {
          "id": 105,
          "sender_id": 12,
          "sender_type": "user",
          "receiver_id": 1,
          "message": "Hello Admin",
          "message_type": "text",
          "image_url": null,
          "is_read": true,
          "created_at": "2026-06-10T14:49:00Z"
        }
      ],
      "pagination": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 20,
        "total": 1
      }
    }
  }
  ```

---

## 11. Agent Connection System

Users must connect with agents through a specific connection request flow before performing trades.

- **Send Connection Request:** `POST /agent-friend-request` (Body: `{ "receiver_id": <agent_id> }`)
- **Check Status:** `GET /check-friend-request/{agent_id}`
- **Accept (Agent only):** `POST /friend-request/accept/{request_id}`
- **Reject (Agent only):** `POST /friend-request/reject/{request_id}`
- **Cancel Request:** `DELETE /friend-request/cancel/{request_id}`
- **List Received (Agent only):** `GET /friend-request/received`
- **List Sent:** `GET /friend-request/sent`
- **List Connected:** `GET /friend-request/connected`

---

## 12. Unified Notifications (Firebase & OneSignal)

The application supports unified push notification setup.

### Update FCM Token (Firebase)
Call this on app startup or login.
- **Endpoint:** `POST /update-fcm-token`
- **Request Body:**
  ```json
  {
    "email": "johndoe@example.com",
    "fcm_token": "fcm_token_string_here...",
    "device_type": "android" // android, ios, web
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "FCM token updated successfully"
  }
  ```

### Update OneSignal Player ID
- **Endpoint:** `POST /update-onesignal-player`
- **Request Body:**
  ```json
  {
    "email": "johndoe@example.com",
    "player_id": "onesignal_player_uuid_here...",
    "device_type": "android"
  }
  ```

---

## 13. App Settings, Guidelines & Notice Boards

### Fetch Site Logo
- **Endpoint:** `GET /logosetting`
- **Response (200 OK):**
  ```json
  {
    "status": true,
    "data": {
      "id": 1,
      "photo": "https://domain.com/uploads/logo/logo.png"
    },
    "message": "Logo fetched successfully"
  }
  ```

### Fetch Mail Configuration
Fetches the active system email configuration (excluding password for security reasons). Useful for checking if the mail settings are properly configured.
- **Endpoint:** `GET /mailsetting`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "mail_mailer": "smtp",
      "mail_host": "smtp.mailtrap.io",
      "mail_port": "2525",
      "mail_username": "username_here",
      "mail_encryption": "tls",
      "mail_from_address": "hello@example.com",
      "mail_from_name": "GlobalAdSLMT"
    }
  }
  ```
- **Response (404 Not Found):**
  ```json
  {
    "success": false,
    "message": "Mail configuration not found"
  }
  ```

### Fetch Google Ads Approval
Retrieves the Google Ads approval status text/configuration set by the administrator.
- **Endpoint:** `GET /googleadsapproval`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "approval_text": "This app is approved for Google Ads..."
    }
  }
  ```
- **Response (404 Not Found):**
  ```json
  {
    "success": false,
    "message": "Google Ads Approval configuration not found"
  }
  ```

### Fetch Notice Boards
Fetches regular announcements and package-watch specific guides.
- **Endpoint:** `GET /worknotices`
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "has_active_package": true,
      "work_notices": [
        { "id": 1, "title": "Watch Notice", "content": "Keep VPN active..." }
      ],
      "notices": [
        { "id": 2, "notice_text": "System update scheduled tonight." }
      ]
    }
  }
  ```

### Fetch "How to Work" Guideline
- **Endpoint:** `GET /howtowork`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "stepguides": [
      {
        "id": 1,
        "title": "Create Account",
        "description": "Register and verify NID",
        "icon": "user_plus",
        "serial_number": 1
      }
    ],
    "whychooseus": [
      {
        "id": 1,
        "title": "Instant Withdrawals",
        "description": "Fast approval via P2P agents",
        "icon": "bolt"
      }
    ]
  }
  ```

### Fetch Deposit Guidelines (Text Mode)
- **Endpoint:** `GET /depositeinstructions`
- **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Withdraw instructions fetched successfully",
    "data": [
      {
        "id": 1,
        "instruction_text": "First copy the bkash number..."
      }
    ]
  }
  ```

### Fetch Deposit Guidelines (Video & Payment Methods)
- **Endpoint:** `GET /deposit-instructions/videos`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Deposit instructions & payment methods retrieved successfully",
    "data": {
      "deposit_instructions": [
        {
          "id": 1,
          "deposite_instructions_title": "How to Deposit via bKash",
          "deposite_instructions_description": "Watch the video to learn...",
          "video_url": "https://youtube.com/watch?v=..."
        }
      ],
      "payment_methods": [
        {
          "id": 1,
          "method_name": "bKash",
          "photo": "https://domain.com/uploads/paymentmethod/bkash.png"
        }
      ]
    }
  }
  ```

### Fetch Withdrawal Guidelines
- **Endpoint:** `GET /widthrawinstructionss`
- **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Withdraw instructions fetched successfully",
    "data": [
      {
        "id": 1,
        "instruction_text": "Withdraw limit is between $10 and $500..."
      }
    ]
  }
  ```

---

## 14. Important Developer Warnings & Notes

> [!WARNING]
> **API Syntax Error in `routes/api.php`:**
> There is a syntax/logical block error at lines 122-134 where routes like `paymenthistory`, `howtowork`, `userbalanceshow` are accidentally nested inside the closure of the `/test-agents` route. 
> To ensure correct execution of `paymenthistory`, it must be registered explicitly outside of the closure.

> [!IMPORTANT]
> **Database Table Naming Mismatch:**
> In `TotalwidthrawhistoryController.php`, the controller uses the correct table spelling `user_widthraws` to fetch transactions but the debug/metadata methods checks table presence on `user_widthdraws` (with a 'd'). Be careful when updating database migrations.

> [!CAUTION]
> **Broken /deposit/balance Endpoint:**
> The route `/api/deposit/balance` calls `PackagesshowuserController@getBalance`. However, `PackagesshowuserController` has no `getBalance` method in its class implementation. This endpoint will return a **500 Server Error** if requested. Developers should avoid using it until a controller method is written.

> [!TIP]
> **VPN Logic in Earning Flow:**
> Make sure to read the `allowed_country` and `vpn_modes` values returned by the `GET /user-earning` API. If `vpn_modes` is `"yes"` and `vpn_required_in_task_only` is `"yes"`, enforce VPN check on the client-side app before initiating `/track-ad-view` requests.
