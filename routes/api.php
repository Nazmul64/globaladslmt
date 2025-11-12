<?php

use App\Http\Controllers\Api\Agentfrientrequest;
use App\Http\Controllers\Api\AgentlistController;
use App\Http\Controllers\Api\DepositeUserController;
use App\Http\Controllers\Api\KycsubmitforuserController;
use App\Http\Controllers\Api\PasswordchangeController;
use App\Http\Controllers\Api\PaymentmethodController;
use App\Http\Controllers\Api\ProfilechangeController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\Depositeinstrctionshow;
use App\Http\Controllers\Api\P2PshowforuserController;
use App\Http\Controllers\Api\PackagesbuyuserController;
use App\Http\Controllers\Api\PackagesshowuserController;
use App\Http\Controllers\Api\UserforadminChatController;
use App\Http\Controllers\Api\UsertoagentChatController;
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
    Route::post('/packagebuy/{id}', [PackagesbuyuserController::class, 'packagebuy']);
    Route::get('buysellpost', [P2PshowforuserController::class, 'buysellpost']);
    Route::get('/usertoadminchat/fetch', [UserforadminChatController::class, 'fetchMessages']);
    Route::post('/usertoadminchat/send', [UserforadminChatController::class, 'sendMessage']);
    Route::post('/usertoadminchat/mark-read', [UserforadminChatController::class, 'markAsRead']);





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

    // User info route
    // Route::get('/user', function (Request $request) {
    //     return response()->json([
    //         'status' => true,
    //         'data' => $request->user(),
    //     ]);
    // });
Route::post('frontends/userto/agent/chat/submit', [UsertoagentChatController::class, 'frontend_chat_submit']);
Route::get('frontends/userto/message/chat/messages', [UsertoagentChatController::class, 'frontend_chat_messages']);
Route::get('frontends/userto/unread/agent/chat/unread-counts', [UsertoagentChatController::class, 'getUnreadCounts']);
Route::delete('frontends/userto/agent/chat/message/{message_id}', [UsertoagentChatController::class, 'deleteMessage']);
Route::get('frontends/user/toagent/chat', [UsertoagentChatController::class, 'frontend_user_toagent_chat']);
});












Route::get('/packageshow', [PackagesshowuserController::class, 'packageshow']);
Route::get('test', [AgentlistController::class, 'test']);
Route::get('agentlist', [AgentlistController::class, 'agentlist']);
Route::get('agent/{id}', [AgentlistController::class, 'show']);





  Route::get('paymentmethod', [PaymentmethodController::class, 'paymentmethod']);
  Route::get('kycsubmit/kyc-status', [KycsubmitforuserController::class, 'kycStatus']);
  Route::post('kycsubmit/kyc-resubmit', [KycsubmitforuserController::class, 'kycResubmit']);
  Route::get('/deposit-instructions', [Depositeinstrctionshow::class, 'Depositeinstrctionshow']);
  Route::get('/deposit-instructions/videos', [Depositeinstrctionshow::class, 'Depositeinstrctionshowvideos']);


