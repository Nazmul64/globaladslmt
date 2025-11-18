<?php

use App\Http\Controllers\Api\Agentfrientrequest;
use App\Http\Controllers\Api\AgentlistController;
use App\Http\Controllers\Api\ChatRequestController;
use App\Http\Controllers\Api\DepositeUserController;
use App\Http\Controllers\Api\KycsubmitforuserController;
use App\Http\Controllers\Api\PasswordchangeController;
use App\Http\Controllers\Api\PaymentmethodController;
use App\Http\Controllers\Api\ProfilechangeController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\Depositeinstrctionshow;
use App\Http\Controllers\Api\HowtoworkController;
use App\Http\Controllers\Api\P2PpostController;
use App\Http\Controllers\Api\P2PshowforuserController;
use App\Http\Controllers\Api\PackagesbuyuserController;
use App\Http\Controllers\Api\PackagesshowuserController;
use App\Http\Controllers\Api\PaymenthistoryiController;
use App\Http\Controllers\Api\UserbalanceshowController;
use App\Http\Controllers\Api\UserchatController;
use App\Http\Controllers\Api\UserDepositewidthrawrequestController;
use App\Http\Controllers\Api\UserforadminChatController;
use App\Http\Controllers\Api\UsertoagentChatController;
use App\Http\Controllers\Api\UserWidhrawrequestAgentController;
use App\Http\Controllers\Api\UserWidthrawController;
use App\Http\Controllers\Api\WroknoticesController;
use App\Models\Package;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class,'login']);
Route::post('logout', [RegisterController::class,'logout']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('kycsubmit', [KycsubmitforuserController::class,'kycsubmit']);
    Route::post('chagepassword', [PasswordchangeController::class, 'chagepassword']);
    Route::get('profile', [ProfilechangeController::class, 'getProfile']);
    // Update profile (name, email, photo)
    Route::post('profileupdate', [ProfilechangeController::class, 'profileUpdate']);
    // Optional: Delete profile photo
    Route::delete('profile/photo', [ProfilechangeController::class, 'deletePhoto']);
    Route::get('paymentmethod', [PaymentmethodController::class, 'paymentmethod']);
    Route::post('deposite', [DepositeUserController::class, 'deposite']);
    Route::get('totaldeposite', [DepositeUserController::class, 'totaldeposite']);
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
    Route::get('/user/balance', [PackagesbuyuserController::class, 'getUserBalance']);

    // Buy or update package
    Route::post('/packagebuy/{package_id}', [PackagesbuyuserController::class, 'packagebuy']);













     Route::post('agent-friend-request', [Agentfrientrequest::class, 'sendFriendRequest']);

    // Check friend request status with specific agent
    Route::get('check-friend-request/{agent_id}', [Agentfrientrequest::class, 'checkFriendRequestStatus']);

    // Accept friend request (for agents)
    Route::post('friend-request/accept/{request_id}', [Agentfrientrequest::class, 'acceptFriendRequest']);

    // Reject friend request (for agents)
    Route::post('friend-request/reject/{request_id}', [Agentfrientrequest::class, 'rejectFriendRequest']);

    // Cancel sent friend request
    Route::delete('friend-request/cancel/{request_id}', [Agentfrientrequest::class, 'cancelFriendRequest']);

    // Get all received friend requests (for agents)
    Route::get('friend-request/received', [Agentfrientrequest::class, 'getReceivedRequests']);

    // Get all sent friend requests
    Route::get('friend-request/sent', [Agentfrientrequest::class, 'getSentRequests']);

    // Get all connected agents/users
    Route::get('friend-request/connected', [Agentfrientrequest::class, 'getConnectedAgents']);




    Route::get('/usertoagentchat/agent', [UsertoagentChatController::class, 'getAgentInfo']);

    // Fetch messages
    Route::get('/usertoagentchat/fetch', [UsertoagentChatController::class, 'fetchMessages']);

    // Send message
    Route::post('/usertoagentchat/send', [UsertoagentChatController::class, 'sendMessage']);

    // Mark messages as read
    Route::post('/usertoagentchat/mark-read', [UsertoagentChatController::class, 'markAsRead']);

    // Delete message
    Route::delete('/usertoagentchat/message/{id}', [UsertoagentChatController::class, 'deleteMessage']);

    // Get unread count
    Route::get('/usertoagentchat/unread-count', [UsertoagentChatController::class, 'getUnreadCount']);
    Route::get('userwidthrawshow', [UserWidthrawController::class, 'userwidthrawshow']);
    Route::post('userwidthrawstore', [UserWidthrawController::class, 'userwidthrawstore']);





    // Paymenthistory Start
    Route::get('paymenthistory', [PaymenthistoryiController::class, 'paymenthistory']);

    // Paymenthistory End


    //How to work Start
     Route::get('howtowork', [HowtoworkController::class, 'howtowork']);
   //How to work Start

     Route::get('/user-search', [ChatRequestController::class, 'search']);

    // Friend Requests
    Route::post('/user/friend/request', [ChatRequestController::class, 'sendFriendRequest']);
    Route::post('/cancel/friend/request', [ChatRequestController::class, 'cancelFriendRequest']);

    // Received Requests
    Route::get('/user/friend/request/accept/view', [ChatRequestController::class, 'sendFriendRequestaccept']);
    Route::post('/user/friend/request/accept', [ChatRequestController::class, 'acceptRequest']);
    Route::post('/user/friend/request/reject', [ChatRequestController::class, 'rejectRequest']);

    // Friends List
    Route::get('/friends', [ChatRequestController::class, 'friends']);
    Route::get('/friends/count', [ChatRequestController::class, 'friendsCount']);
    Route::post('/unfriend', [ChatRequestController::class, 'unfriend']);




    Route::get('chat/frontend/list', [UserchatController::class, 'frontend_chat_list']);
    Route::post('chat/frontend/submit', [UserchatController::class, 'frontend_chat_submit']);
    Route::get('chat/frontend/messages', [UserchatController::class, 'frontend_chat_messages']);
    Route::get('chat/unread-counts', [UserchatController::class, 'getUnreadCounts']);

    // Optional bonus features
    Route::delete('chat/message/delete', [UserchatController::class, 'deleteMessage']);
    Route::post('chat/message/mark-read', [UserchatController::class, 'markAsRead']);
    Route::get('chat/last-messages', [UserchatController::class, 'getLastMessages']);
    // Balance show
    Route::get('userbalanceshow', [UserbalanceshowController::class, 'userbalanceshow']);
    // Balance show

  // Wrok Notices show
    Route::get('worknotices', [WroknoticesController::class, 'worknotices']);
  // Wrok Notices show












Route::get('user/withdraw/status', [UserWidhrawrequestAgentController::class, 'checkWithdrawStatus']);


Route::post('user/withdraw/submit/{id}', [UserWidhrawrequestAgentController::class, 'userSubmitWithdraw']);


Route::post('agent/withdraw/accept/{id}', [UserWidhrawrequestAgentController::class, 'acceptWithdrawRequest']);



 Route::get('buysellpost', [P2PpostController::class, 'buysellpost']);

    // Deposit routes
    Route::post('user/deposit/request', [UserDepositewidthrawrequestController::class, 'userwidhraw_request']);
    Route::get('user/deposit/status', [UserDepositewidthrawrequestController::class, 'checkDepositStatus']);
    Route::post('user/deposit/submit/{id}', [UserDepositewidthrawrequestController::class, 'userSubmitDeposit']);

    // Withdraw routes
    Route::post('user/withdraw/request', [UserDepositewidthrawrequestController::class, 'userwidhraw_request']);
    Route::get('user/withdraw/status', [UserDepositewidthrawrequestController::class, 'checkWithdrawStatus']);
    Route::post('user/withdraw/submit/{id}', [UserDepositewidthrawrequestController::class, 'userSubmitWithdraw']);

    // Agent routes
    Route::post('agent/withdraw/accept/{id}', [UserDepositewidthrawrequestController::class, 'acceptWithdrawRequest']);

});



  //How to work Start
 Route::get('howtowork', [HowtoworkController::class, 'howtowork']);
   //How to work Start


































Route::get('/packageshow', [PackagesshowuserController::class, 'packageshow']);
Route::get('test', [AgentlistController::class, 'test']);
Route::get('agentlist', [AgentlistController::class, 'agentlist']);
Route::get('agent/{id}', [AgentlistController::class, 'show']);





  Route::get('paymentmethod', [PaymentmethodController::class, 'paymentmethod']);
  Route::get('kycsubmit/kyc-status', [KycsubmitforuserController::class, 'kycStatus']);
  Route::post('kycsubmit/kyc-resubmit', [KycsubmitforuserController::class, 'kycResubmit']);
  Route::get('/deposit-instructions', [Depositeinstrctionshow::class, 'Depositeinstrctionshow']);
  Route::get('/deposit-instructions/videos', [Depositeinstrctionshow::class, 'Depositeinstrctionshowvideos']);


