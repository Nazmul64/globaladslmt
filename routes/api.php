<?php

use App\Http\Controllers\Api\Agentfrientrequest;
use App\Http\Controllers\Api\AgentlistController;
use App\Http\Controllers\Api\AgentProfileController;
use App\Http\Controllers\Api\AgentVerifitController;
use App\Http\Controllers\Api\ChatRequestController;
use App\Http\Controllers\Api\ChatVerifitController;
use App\Http\Controllers\Api\DepositeUserController;
use App\Http\Controllers\Api\KycsubmitforuserController;
use App\Http\Controllers\Api\PasswordchangeController;
use App\Http\Controllers\Api\PaymentmethodController;
use App\Http\Controllers\Api\ProfilechangeController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\Depositeinstrctionshow;
use App\Http\Controllers\Api\DepositeInstructionsController;
use App\Http\Controllers\Api\ForgotPasswordsController;
use App\Http\Controllers\Api\HowtoworkController;
use App\Http\Controllers\Api\P2PpostController;
use App\Http\Controllers\Api\P2PshowforuserController;
use App\Http\Controllers\Api\PackagesbuyuserController;
use App\Http\Controllers\Api\PackagesshowuserController;
use App\Http\Controllers\Api\PaymenthistoryiController;
use App\Http\Controllers\Api\PostapiController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RefferController;
use App\Http\Controllers\Api\ResetPasswordsController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\ThemechangeController;
use App\Http\Controllers\Backend\ServerSettingController;
use App\Http\Controllers\Api\UserbalanceshowController;
use App\Http\Controllers\Api\UserchatController;
use App\Http\Controllers\Api\UserDepositewidthrawrequestController;
use App\Http\Controllers\Api\FirebaseNotificationController;
use App\Http\Controllers\Api\UserearningController;
use App\Http\Controllers\Api\UserforadminChatController;
use App\Http\Controllers\Api\UsertoagentChatController;
use App\Http\Controllers\Api\UserWidhrawrequestAgentController;
use App\Http\Controllers\Api\UserWidthrawController;
use App\Http\Controllers\Api\WidthrawInstructionController;
use App\Http\Controllers\Api\WroknoticesController;
use App\Http\Controllers\Api\TotalwidthrawhistoryController;
use App\Http\Controllers\Api\UserBalanceshow;
use App\Http\Controllers\Api\UserphotoController;
use App\Http\Controllers\Api\OneSignalNotificationController;
use App\Http\Controllers\Api\SettinglogoController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NewSettingsApiController;

use App\Models\Package;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class,'login']);
Route::get('server-mode', [ServerSettingController::class, 'getServerMode']);
Route::get('serversetting', [ServerSettingController::class, 'getServerMode']);
Route::post('logout', [RegisterController::class,'logout']);
Route::post('password/email', [ForgotPasswordsController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [ResetPasswordsController::class, 'reset']);
    Route::middleware('auth:sanctum')->get('profile', [ProfilechangeController::class, 'getProfile']);
    // Update profile (name, email, photo)
    Route::middleware('auth:sanctum')->post('profileupdate', [ProfilechangeController::class, 'profileUpdate']);
    // Optional: Delete profile photo
    Route::middleware('auth:sanctum')->get('profile/photo', [ProfilechangeController::class, 'deletePhoto']);
    Route::middleware('auth:sanctum')->group(function () {
    Route::post('kycsubmit', [KycsubmitforuserController::class,'kycsubmit']);
    Route::post('chagepassword', [PasswordchangeController::class, 'chagepassword']);
    Route::get('paymentmethod', [PaymentmethodController::class, 'paymentmethod']);
    Route::post('deposite', [DepositeUserController::class, 'deposite']);
    Route::get('totaldeposite', [DepositeUserController::class, 'totaldeposite']);
    Route::post('/user/sync-balance', [DepositeUserController::class, 'syncUserBalance']);
    // All deposits list
    Route::get('userDeposits', [DepositeUserController::class, 'userDeposits']);
    // Optional: Get deposits by status (pending/approved/rejected)
    Route::get('userDeposits/{status}', [DepositeUserController::class, 'userDepositsByStatus']);
    // Optional: Get single deposit details
    Route::get('deposit/{id}', [DepositeUserController::class, 'getDepositById']);
    Route::get('/deposit/balance', [PackagesshowuserController::class, 'getBalance']);
    Route::get('buysellpost', [P2PpostController::class, 'buysellpost']);
    Route::get('/usertoadminchat/fetch', [UserforadminChatController::class, 'fetchMessages']);
    Route::post('/usertoadminchat/send', [UserforadminChatController::class, 'sendMessage']);
    Route::post('/usertoadminchat/mark-read', [UserforadminChatController::class, 'markAsRead']);
    // Get user's current active package
    Route::get('/user/current-package', [PackagesbuyuserController::class, 'getCurrentPackage']);
    // Get user's balance info
    Route::post('/packagebuy/{package_id}', [PackagesbuyuserController::class, 'packagebuy']);
    Route::post('agent-friend-request', [Agentfrientrequest::class, 'sendFriendRequest']);
    // Check friend request status with specific agent
    Route::get('check-friend-request/{agent_id}', [Agentfrientrequest::class, 'checkFriendRequestStatus']);
    // Accept friend request (for agents)
    Route::post('friend-request/accept/{request_id}', [Agentfrientrequest::class, 'acceptFriendRequest']);
    // Reject friend request (for agents)
    Route::post('friend-request/reject/{request_id}', [Agentfrientrequest::class, 'rejectFriendRequest']);
    // Cancl sent friend request
    Route::delete('friend-request/cancel/{request_id}', [Agentfrientrequest::class, 'cancelFriendRequest']);
    // Get all received friend requests (for agents)
    Route::get('friend-request/received', [Agentfrientrequest::class, 'getReceivedRequests']);
    // Get all sent friend requests
    Route::get('friend-request/sent', [Agentfrientrequest::class, 'getSentRequests']);
    // Get all connected agents/users
    Route::get('friend-request/connected', [Agentfrientrequest::class, 'getConnectedAgents']);


// ✅ GET - Agents list (all agents)
    Route::get('/usertoagentchat/agents', [UsertoagentChatController::class, 'getAgentsList']);
    // ✅ GET - Fetch messages for specific agent
    Route::get('/usertoagentchat/fetch', [UsertoagentChatController::class, 'fetchMessages']);
    // ✅ POST - Send message (text or image)
    Route::post('/usertoagentchat/send', [UsertoagentChatController::class, 'sendMessage']);
    // ✅ POST - Mark messages as read
    Route::post('/usertoagentchat/mark-read', [UsertoagentChatController::class, 'markAsRead']);
    // ✅ GET - Unread message count
    Route::get('/usertoagentchat/unread-count', [UsertoagentChatController::class, 'getUnreadCount']);
    // ✅ DELETE - Delete own message
    Route::delete('/usertoagentchat/message/{id}', [UsertoagentChatController::class, 'deleteMessage']);
    // ✅ GET - Single agent info (backward compatibility)
    Route::get('/usertoagentchat/agent', [UsertoagentChatController::class, 'getAgentInfo']);


});

// Test route (optional - remove in production)
   Route::get('/test-agents', function() {
    $agents = \App\Models\User::where('role', 'agent')->get();
    return response()->json([
        'success' => true,
        'count' => $agents->count(),
        'agents' => $agents
    ]);
   Route::get('paymenthistory', [PaymenthistoryiController::class, 'paymenthistory']);
   Route::get('howtowork', [HowtoworkController::class, 'howtowork']);
   Route::get('userbalanceshow', [UserbalanceshowController::class, 'userbalanceshow']);
   Route::get('buysellpost', [P2PpostController::class, 'buysellpost']);
   Route::get('profile', [ProfileController::class, 'profile']);
});

Route::get('paymentmethod', [PaymentmethodController::class,'paymentmethod']);
    Route::get('support', [SupportController::class, 'support']);
    Route::get('themechange', [ThemechangeController::class, 'themechange']);
    //How to work Start
    Route::get('howtowork', [HowtoworkController::class, 'howtowork']);
    Route::get('/packageshow', [PackagesshowuserController::class, 'packageshow']);
    Route::get('test', [AgentlistController::class, 'test']);
    Route::get('agentlist', [AgentlistController::class, 'agentlist']);
    Route::get('agent/{id}', [AgentlistController::class, 'show']);
  Route::get('/deposit-instructions', [Depositeinstrctionshow::class, 'Depositeinstrctionshow']);
  Route::get('/deposit-instructions/videos', [Depositeinstrctionshow::class, 'Depositeinstrctionshowvideos']);
  Route::get('widthrawinstructionss', [WidthrawInstructionController::class, 'widthrawinstructionss']);
  Route::get('depositeinstructions', [DepositeInstructionsController::class, 'depositeinstructions']);
Route::get('user/{user_id}/verify-status', [ChatVerifitController::class, 'userVerifyStatus']);
Route::middleware('auth:sanctum')->get('userphotoshow', [UserphotoController::class, 'userphotoshow']);
      // Get Agent Photo (Dynamic)
Route::get('photo', [AgentProfileController::class, 'userphotoshow']);
    // Alternative route name for backward compatibility
Route::get('userphotoshow', [AgentProfileController::class, 'userphotoshow']);
Route::middleware('auth:sanctum')->get('/userbalanceshow', [UserbalanceshowController::class, 'userbalanceshow']);
Route::get('agent/{user_id}/verify-status', [AgentVerifitController::class, 'userVerifyStatus'])->where('user_id', '[0-9]+') ->name('agent.verify.status');
Route::middleware('auth:sanctum')->get('totalreffer', [RefferController::class, 'totalreffer']);
Route::middleware('auth:sanctum')->get('referral-stats', [RefferController::class, 'referralStats']);
Route::middleware('auth:sanctum')->get('/totalwidthrawhistory', [TotalwidthrawhistoryController::class, 'totalWidthrawHistory']);
Route::middleware('auth:sanctum')->get('/user-search', [ChatRequestController::class, 'search']);
    // Friend Request Management
Route::middleware('auth:sanctum')->post('/user/friend/request', [ChatRequestController::class, 'sendFriendRequest']);
Route::middleware('auth:sanctum')->post('/cancel/friend/request', [ChatRequestController::class, 'cancelFriendRequest']);
    // View Requests
Route::middleware('auth:sanctum')->get('/user/friend/request/accept/view', [ChatRequestController::class, 'receivedRequests']);
Route::middleware('auth:sanctum')->get('/user/friend/request/sent', [ChatRequestController::class, 'sentRequests']); // NEW
    // Accept/Reject
Route::middleware('auth:sanctum')->post('/user/friend/request/accept', [ChatRequestController::class, 'acceptRequest']);
Route::middleware('auth:sanctum')->post('/user/friend/request/reject', [ChatRequestController::class, 'rejectRequest']);
    // Friends List
Route::middleware('auth:sanctum')->get('/friends', [ChatRequestController::class, 'friends']);
Route::middleware('auth:sanctum')->get('/friends/count', [ChatRequestController::class, 'friendsCount']);
Route::middleware('auth:sanctum')->post('/unfriend', [ChatRequestController::class, 'unfriend']);



  // User Search
Route::middleware('auth:sanctum')->get('chat/frontend/list', [UserchatController::class, 'frontend_chat_list']);
Route::middleware('auth:sanctum')->post('chat/frontend/submit', [UserchatController::class, 'frontend_chat_submit']);
Route::middleware('auth:sanctum')->get('chat/frontend/messages', [UserchatController::class, 'frontend_chat_messages']);
Route::middleware('auth:sanctum')->get('chat/unread-counts', [UserchatController::class, 'getUnreadCounts']);
    // Optional bonus features
Route::middleware('auth:sanctum')->delete('chat/message/delete', [UserchatController::class, 'deleteMessage']);
Route::middleware('auth:sanctum')->post('chat/message/mark-read', [UserchatController::class, 'markAsRead']);
Route::middleware('auth:sanctum')->get('chat/last-messages', [UserchatController::class, 'getLastMessages']);




Route::middleware('auth:sanctum')->get('/withdrawals/list', [TotalwidthrawhistoryController::class, 'getWithdrawalList']);
    // Get single withdrawal details
    // URL: GET /api/withdrawals/details/{id}
Route::middleware('auth:sanctum')->get('/withdrawals/details/{id}', [TotalwidthrawhistoryController::class, 'getWithdrawalDetails']);

    // ⚠️ Debug endpoints - Production এ comment out করে দিও!
Route::middleware('auth:sanctum')->get('/debug/withdrawal', [TotalwidthrawhistoryController::class, 'debugInfo']);
Route::middleware('auth:sanctum')->get('/debug/table-structure', [TotalwidthrawhistoryController::class, 'checkTableStructure']);
Route::middleware('auth:sanctum')->get('userbalanceshows', [UserBalanceshow::class, 'userbalanceshows']);
Route::middleware('auth:sanctum')->get('withdraw-history', [UserWidthrawController::class, 'withdrawHistory']);
Route::middleware('auth:sanctum')->get('userwidthrawshow', [UserWidthrawController::class, 'userwidthrawshow']);
Route::middleware('auth:sanctum')->post('userwidthrawstore', [UserWidthrawController::class, 'userwidthrawstore']);
Route::middleware('auth:sanctum')->get('p2p/getHistory', [P2PpostController::class, 'getHistory']);






Route::middleware('auth:sanctum')->get('/posts', [PostController::class, 'index']);
Route::middleware('auth:sanctum')->post('/posts', [PostController::class, 'store']);
Route::middleware('auth:sanctum')->get('/posts/my-posts', [PostController::class, 'myPosts']);
Route::middleware('auth:sanctum')->get('/posts/search', [PostController::class, 'search']);
Route::middleware('auth:sanctum')->get('/posts/{id}', [PostController::class, 'show']);
// Route::middleware('auth:sanctum')->post('/posts/{id}', [PostController::class, 'update']); // ✅ শুধু এটা রাখুন
Route::middleware('auth:sanctum')->match(['POST', 'PUT'], '/posts/{id}', [PostController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/posts/{id}', [PostController::class, 'destroy']);
    // Likes
Route::middleware('auth:sanctum')->post('/posts/{id}/like', [PostController::class, 'toggleLike']);
Route::middleware('auth:sanctum')->get('/posts/{id}/likes', [PostController::class, 'getLikes']);
    // Comments
Route::middleware('auth:sanctum')->get('/posts/{id}/comments', [PostController::class, 'getComments']);
Route::middleware('auth:sanctum')->post('/posts/{id}/comments', [PostController::class, 'addComment']);
Route::middleware('auth:sanctum')->delete('/posts/{postId}/comments/{commentId}', [PostController::class, 'deleteComment']);
    // Share
Route::middleware('auth:sanctum')->post('/posts/{id}/share', [PostController::class, 'share']);


Route::middleware('auth:sanctum')->get('kycsubmit/kyc-status', [KycsubmitforuserController::class, 'kycStatus']);
Route::middleware('auth:sanctum')->post('kycsubmit/kyc-resubmit', [KycsubmitforuserController::class, 'kycResubmit']);
Route::middleware('auth:sanctum')->get('/user-earning', [UserearningController::class, 'userEarning']);
Route::middleware('auth:sanctum')->post('/track-ad-view', [UserearningController::class, 'trackAdView']);
Route::middleware('auth:sanctum')->post('/track-invalid-click', [UserearningController::class, 'trackInvalidClick']); // 🔥 NEW
Route::middleware('auth:sanctum')->post('/break-complete', [UserearningController::class, 'breakComplete']);
Route::middleware('auth:sanctum')->post('/claim-reward', [UserearningController::class, 'claimReward']);



  // Wrok Notices show
Route::middleware('auth:sanctum')->get('worknotices', [WroknoticesController::class, 'worknotices']);
Route::middleware('auth:sanctum')->get('/user/balance', [PackagesbuyuserController::class, 'getUserBalance']);
    // Buy or update package

     // Deposit routes
Route::middleware('auth:sanctum')->post('user/deposit/request', [UserDepositewidthrawrequestController::class, 'userwidhraw_request']);
Route::middleware('auth:sanctum')->get('user/deposit/status', [UserDepositewidthrawrequestController::class, 'checkDepositStatus']);
Route::middleware('auth:sanctum')->post('user/deposit/submit/{id}', [UserDepositewidthrawrequestController::class, 'userSubmitDeposit']);
    // Withdraw routes
Route::middleware('auth:sanctum')->post('user/withdraw/request', [UserDepositewidthrawrequestController::class, 'userwidhraw_request']);
Route::middleware('auth:sanctum')->get('user/withdraw/status', [UserDepositewidthrawrequestController::class, 'checkWithdrawStatus']);
Route::middleware('auth:sanctum')->post('user/withdraw/submit/{id}', [UserDepositewidthrawrequestController::class, 'userSubmitWithdraw']);
Route::middleware('auth:sanctum')->post('depositecancled', [UserDepositewidthrawrequestController::class, 'depositecancled']);
Route::middleware('auth:sanctum')->get('user/deposit/status', [UserDepositewidthrawrequestController::class, 'depositStatus']);
Route::middleware('auth:sanctum')->post('agent/withdraw/accept/{id}', [UserDepositewidthrawrequestController::class, 'acceptWithdrawRequest']);




Route::middleware('auth:sanctum')->get('user/withdraw/status', [UserWidhrawrequestAgentController::class, 'checkWithdrawStatus']);
Route::middleware('auth:sanctum')->post('user/withdraw/submit/{id}', [UserWidhrawrequestAgentController::class, 'userSubmitWithdraw']);
Route::middleware('auth:sanctum')->post('agent/withdraw/accept/{id}', [UserWidhrawrequestAgentController::class, 'acceptWithdrawRequest']);
Route::middleware('auth:sanctum')->get('user/withdraw/status', [UserWidhrawrequestAgentController::class, 'withdrawStatus']);
Route::middleware('auth:sanctum')->post('withdrawcancled', [UserWidhrawrequestAgentController::class, 'withdrawcancled']);


Route::middleware('auth:sanctum')->get('/chat-profile-user', [ProfilechangeController::class, 'chatProfileUser']);





// ✅ Route 1: Update FCM Token (called from Flutter app on login/startup)
Route::post('/update-fcm-token', [FirebaseNotificationController::class, 'updateFcmToken']);

// ✅ Route 2: Get Notifications (fetch notification history from app)
Route::post('/get-notifications', [FirebaseNotificationController::class, 'getNotifications']);

// ✅ Route 3: Test Notification Send (optional - for testing)
Route::post('/test-notification', [FirebaseNotificationController::class, 'testNotification']);






Route::post('/update-onesignal-player-id', [OneSignalNotificationController::class, 'updateOneSignalPlayerId']);
Route::get('/get-notifications', [OneSignalNotificationController::class, 'getNotifications']);
Route::post('/send-notification-all', [OneSignalNotificationController::class, 'sendToAllUsers']);
Route::post('/send-notification-specific', [OneSignalNotificationController::class, 'sendToSpecificUsers']);
Route::get('/user-stats', [OneSignalNotificationController::class, 'getUserStats']);







// ✅ Update FCM Token (from Flutter app on login)
Route::post('/update-fcm-token', [NotificationController::class, 'updateFcmToken']);

// ✅ Send Firebase Notification (from admin panel)
Route::post('/send-firebase-notification', [NotificationController::class, 'sendFirebaseNotification']);

// ✅ Test Firebase Notification
Route::post('/test-firebase-notification', [NotificationController::class, 'testFirebaseNotification']);

// ====================================================================
// 🔔 ONESIGNAL NOTIFICATION ROUTES
// ====================================================================

// ✅ Update OneSignal Player ID (from Flutter app on login)
Route::post('/update-onesignal-player', [NotificationController::class, 'updateOneSignalPlayer']);

// ✅ Send OneSignal Notification (from admin panel)
Route::post('/send-onesignal-notification', [NotificationController::class, 'sendOneSignalNotification']);

// ✅ Test OneSignal Notification
Route::post('/test-onesignal-notification', [NotificationController::class, 'testOneSignalNotification']);

// ====================================================================
// 📥 COMMON NOTIFICATION ROUTES (Works for both platforms)
// ====================================================================

// ✅ Get Notifications List
Route::post('/get-notifications', [NotificationController::class, 'getNotifications']);

// ✅ Mark Single Notification as Read
Route::post('/mark-notification-read', [NotificationController::class, 'markNotificationRead']);

// ✅ Delete Single Notification
Route::post('/delete-notification', [NotificationController::class, 'deleteNotification']);

// ✅ Get Unread Notification Count
Route::post('/get-notification-count', [NotificationController::class, 'getNotificationCount']);

// ✅ Mark All Notifications as Read
Route::post('/mark-all-notifications-read', [NotificationController::class, 'markAllNotificationsRead']);

// ====================================================================
// 🚀 BULK NOTIFICATION ROUTES
// ====================================================================

// ✅ Send to All Users (select platform: firebase, onesignal, or both)
Route::post('/send-notification-to-all', [NotificationController::class, 'sendNotificationToAll']);

// ✅ Send to Specific User (select platform)
Route::post('/send-notification-to-user', [NotificationController::class, 'sendNotificationToUser']);

   Route::get('/logosetting', [SettinglogoController::class, 'index']);
   Route::get('/mailsetting', [NewSettingsApiController::class, 'getMailSetting']);
   Route::get('/googleadsapproval', [NewSettingsApiController::class, 'getGoogleAdsApproval']);
   Route::get('/app-settings', [NewSettingsApiController::class, 'getAppSetting']);
   Route::get('/appsetting', [NewSettingsApiController::class, 'getAppSetting']);

   // 📢 Ads API Endpoints
   Route::get('/ads', [\App\Http\Controllers\Api\AdsApiController::class, 'index']);
   Route::get('/ads/{id}', [\App\Http\Controllers\Api\AdsApiController::class, 'show']);
   Route::get('/ads-settings', [\App\Http\Controllers\Api\AdsApiController::class, 'latest']);
   Route::get('/ad-settings', [\App\Http\Controllers\Api\AdsApiController::class, 'latest']);

    Route::get('/privacy-policy', function () {
        $latestPrivacy = \App\Models\Privacy::latest()->first();
        $allPolicies = \App\Models\Privacy::latest()->get();
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $latestPrivacy->id ?? null,
                'title' => $latestPrivacy->title ?? 'Privacy Policy',
                'description' => $latestPrivacy->description ?? 'No privacy policy available.',
                'content' => $latestPrivacy->description ?? 'No privacy policy available.',
                'updated_at' => $latestPrivacy && $latestPrivacy->updated_at ? $latestPrivacy->updated_at->format('F d, Y') : null,
            ],
            'policies' => $allPolicies->map(function ($p) {
                return [
                    'id' => $p->id,
                    'title' => $p->title,
                    'description' => $p->description,
                    'updated_at' => $p->updated_at ? $p->updated_at->format('F d, Y') : null,
                ];
            }),
        ]);
    });
   Route::get('/home-cards', [\App\Http\Controllers\Api\HomeCardSettingApiController::class, 'getHomeCards']);
   Route::get('/home-card-settings', [\App\Http\Controllers\Api\HomeCardSettingApiController::class, 'getHomeCards']);
