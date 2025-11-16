<?php


use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminuserdepositeApprovedController;
use App\Http\Controllers\Admin\WidthrawlimitController;
use App\Http\Controllers\Api\AdsApiController;
use App\Http\Controllers\Agent\adminChatforAgentController;
use App\Http\Controllers\Agent\AgentbuysellPostCreateController;
use App\Http\Controllers\Agent\AgentchattouserChatController;
use App\Http\Controllers\Agent\AgentDepositeController;
use App\Http\Controllers\Agent\AgentPasswordchangeController;
use App\Http\Controllers\Agent\AgentProfileController;
use App\Http\Controllers\Agent\AgentracceptuserandDeposite;
use App\Http\Controllers\Agent\AgentrequestAcceptController;
use App\Http\Controllers\Agent\AgentWidhrawrequestacceptController;
use App\Http\Controllers\Agent\ChateforagentandadminController;
use App\Http\Controllers\AppsettingController;
use App\Http\Controllers\Backend\AdminagentcreateController;
use App\Http\Controllers\Backend\AdminagentDepositeController;
use App\Http\Controllers\Backend\AdminandchatuserController;
use App\Http\Controllers\Backend\AdminApproveController;
use App\Http\Controllers\Backend\AdminautoController;
use App\Http\Controllers\Backend\AdminBlockuserController;
use App\Http\Controllers\Backend\AdmindepositeApprovedController;
use App\Http\Controllers\Backend\AdminkeyapprovedController;
use App\Http\Controllers\Backend\AdminPackageuylistcheckController;
use App\Http\Controllers\Backend\AdsController;
use App\Http\Controllers\Backend\AgentauthController;
use App\Http\Controllers\Backend\AgentController;
use App\Http\Controllers\Backend\AgentkyapprovedcController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\DepositelimiteController;
use App\Http\Controllers\Backend\DepositewidhrawComissionagetController;
use App\Http\Controllers\Backend\DepositInstructionController;
use App\Http\Controllers\Backend\NoticesController;
use App\Http\Controllers\Backend\PackageController;
use App\Http\Controllers\Backend\PaymentmethodController;
use App\Http\Controllers\Backend\ReffercommissionsetupController;
use App\Http\Controllers\Backend\StepguideController;
use App\Http\Controllers\Backend\SupportController;
use App\Http\Controllers\Backend\TakaandDollarsigendController;
use App\Http\Controllers\Backend\WhychooseusControllerController;
use App\Http\Controllers\Backend\WorkNoticesController;
use App\Http\Controllers\Frontend\AgentkycController;
use App\Http\Controllers\Frontend\BuyandsellposController;
use App\Http\Controllers\Frontend\ChatRequestController;
use App\Http\Controllers\Frontend\FrontendAuthController;
use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\Frontend\DepositeController;
use App\Http\Controllers\Frontend\KeyController;
use App\Http\Controllers\Frontend\PackageBuyControllery;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\TaskController;
use App\Http\Controllers\Frontend\UserchatController;
use App\Http\Controllers\Frontend\UserDepositeController;
use App\Http\Controllers\Frontend\UserDepositewidthrawrequestController;
use App\Http\Controllers\Frontend\UserfriendrequestforAgentController;
use App\Http\Controllers\Frontend\UsertoadminchatController;
use App\Http\Controllers\Frontend\UsertoagentChatController;
use App\Http\Controllers\Frontend\UserWidhrawrequestAgentController;
use App\Http\Controllers\Frontend\UserWidthrawController;
use App\Models\Appsetting;
use App\Models\DepositInstruction;
use App\Models\Reffercommissionsetup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



Auth::routes();

// Frontend Pages Route Controller Start


Route::get('/ads', [AdsApiController::class, 'index']);       // All ads
Route::get('/ads/{id}', [AdsApiController::class, 'show']);  // Single ad
Route::get('/ads-settings', [AdsApiController::class, 'latest']); // Latest ad settings

// Frontend Pages Route Controller End


// Frontend Auth Controller Start
  Route::get('/', [FrontendAuthController::class, 'user_login'])->name('user.login');
  Route::post('user/login/submit', [FrontendAuthController::class, 'user_submit'])->name('user.submit');
  Route::get('user/register', [FrontendAuthController::class, 'user_register'])->name('user.register');
  Route::post('user/register/submit', [FrontendAuthController::class, 'user_register_submit'])->name('user.register.submit');
  Route::post('user/logout', [FrontendAuthController::class, 'user_logout'])->name('user.logout');
// Frontend Auth Controller End

// Frontend Route Controller Start
  Route::middleware(['user'])->group(function () {


    // Ad View Page - Shows advertisement
    Route::get('/task/ad-views', [TaskController::class, 'ads_views'])->name('task.ad.views');

    // Complete Task - After ad view
    Route::post('/task/complete', [TaskController::class, 'complete'])->name('task.complete');

    // Bonus Reward - From rewarded video
    Route::post('/task/bonus-reward', [TaskController::class, 'bonusReward'])->name('task.bonus.reward');





   Route::get('dashboard', [FrontendController::class, 'frontend'])->name('frontend.index');
   Route::get('frontend/options', [FrontendController::class, 'frontend_options'])->name('frontend.options');
   Route::get('frontend/ads/view', [FrontendController::class, 'ads_view'])->name('task.ad.view');
   Route::get('frontend/widthraw', [FrontendController::class, 'frontend_widthraw'])->name('frontend.widthraw');
   Route::get('frontend/adblance', [FrontendController::class, 'frontend_adblance'])->name('frontend.adblance');
   Route::get('frontend/total_deposite', [FrontendController::class, 'total_deposite'])->name('total.deposite');
   Route::get('frontend/deposite', [FrontendController::class, 'frontend_deposite'])->name('frontend.deposite');
   Route::get('frontend/refer/list', [FrontendController::class, 'frontend_refer_list'])->name('frontend.refer.list');
   Route::get('frontend/support', [FrontendController::class, 'frontend_support'])->name('frontend.support');
   Route::get('frontend/payment/history', [FrontendController::class, 'frontend_payment_history'])->name('frontend.payment.history');
   Route::post('frontend/deposite/store', [DepositeController::class, 'store_deposite'])->name('deposit.store');
   Route::get('frontend/packages', [FrontendController::class, 'frontend_packages'])->name('frontend.packages');
   Route::post('/package/buy/{package_id}', [PackageBuyControllery::class, 'frontend_packages_buy'])->name('frontend.package.buy');
   Route::get('frontend/stepguide', [FrontendController::class, 'frontend_stepguide'])->name('frontend.stepguide');
   Route::get('frontend/profile', [FrontendController::class, 'frontend_profile'])->name('frontend.profile');
   Route::post('frontend/profile', [ProfileController::class, 'frontend_profile_update'])->name('frontend.profile.update');
   Route::get('frontend/main/profile', [ProfileController::class, 'frontend_main_profile'])->name('frontend.profile.main');
   Route::get('frontend/password/change', [ProfileController::class, 'frontend_password_change'])->name('frontend.password.change');
   Route::post('frontend/password/submit', [ProfileController::class, 'frontend_password_submit'])->name('frontend.password.submit');
   Route::get('frontend/user/agentlist/show', [FrontendController::class, 'frontend_agent_list'])->name('frontend.agentlist.show');
   /* User Kyc Route Start*/
   Route::get('frontend/key', [KeyController::class, 'frontend_key'])->name('frontend.key');
   Route::post('frontend/key', [KeyController::class, 'frontend_key_submit'])->name('frontend.key.submit');
   Route::get('frontend/ads', [FrontendController::class, 'frontend_ads'])->name('frontend.ads');
/* User  Kyc Route End*/
/*Friend Request User Route Start*/
   Route::get('/friends', [ChatRequestController::class, 'index'])->name('frontend.friends');
   Route::get('/user-search', [ChatRequestController::class, 'search'])->name('user.search');
   Route::post('/user/friend/request', [ChatRequestController::class, 'sendFriendRequest'])->name('user.friend.request');
   Route::get('/user/friend/request/accept/view', [ChatRequestController::class, 'sendFriendRequestaccept'])->name('user.accept.view');
   Route::post('/user/friend/request/accept', [ChatRequestController::class, 'acceptRequest'])->name('user.friend.request.accept');
   Route::post('/user/friend/request/reject', [ChatRequestController::class, 'rejectRequest'])->name('user.friend.request.reject');
   Route::post('/cancel/friend/request', [ChatRequestController::class, 'cancelFriendRequest'])->name('user.friend.request.cancel');
/*Friend Request User Route End*/
/* User Chat Route Start*/
Route::get('chat/frontend/list', [UserchatController::class, 'frontend_chat_list'])->name('frontend.user.chat.list');
Route::post('chat/frontend/submit', [UserchatController::class, 'frontend_chat_submit'])->name('frontend.user.chat.submit');
Route::get('chat/frontend/messages', [UserchatController::class, 'frontend_chat_messages'])->name('frontend.user.chat.messages');
Route::get('/chat/unread-counts', [UserchatController::class, 'getUnreadCounts'])->name('frontend.user.chat.unread');
/* User Chat Route Start*/
/* User To agent chat Route Start*/
Route::get('frontends/user/toagent/chat', [UsertoagentChatController::class, 'frontend_user_toagent_chat'])->name('frontend.user.toagent.chat');
Route::post('frontends/userto/agent/chat/submit', [UsertoagentChatController::class, 'frontend_chat_submit'])->name('agentuser.chat.agents.submit');
Route::get('frontends/userto/message/chat/messages', [UsertoagentChatController::class, 'frontend_chat_messages'])->name('agentsuser.toagent.userto.chat.messages');
Route::get('frontends/userto/unread/agent/chat/unread-counts', [UsertoagentChatController::class, 'getUnreadCounts'])->name('user.chat.agent.unread');
Route::delete('frontends/userto/agent/chat/message/{message_id}', [UsertoagentChatController::class, 'deleteMessage'])->name('user.chat.agent.delete.message');
/* User To agent chat Route end*/

/* User To agent chat Route Start*/
Route::post('user/agent/request/request', [UserfriendrequestforAgentController::class, 'agentsendFriendRequest'])->name('agentss.user.friend.request');
Route::post('/user/agent/friend/request/accept', [UserfriendrequestforAgentController::class, 'agentacceptRequest'])->name('agentss.user.friend.request.accept');
Route::post('/user/agent/friend/request/reject', [UserfriendrequestforAgentController::class, 'agentrejectRequest'])->name('agentss.user.friend.request.reject');
Route::post('user/agent/cancel/friend/request', [UserfriendrequestforAgentController::class, 'agentcancelFriendRequest'])->name('agentss.user.friend.request.cancel');
  /* User To agent chat Route end*/
Route::get('/usertoadminchat/fetch', [UsertoadminchatController::class, 'fetchMessages'])->name('usertoadminchat.fetch');
Route::post('/usertoadminchat/send', [UsertoadminchatController::class, 'sendMessage'])->name('usertoadminchat.send');
Route::post('/usertoadminchat/mark-read', [UsertoadminchatController::class, 'markRead'])->name('usertoadminchat.markread');
Route::get('/usertoadminchat/unread-count', [UsertoadminchatController::class, 'unreadCount'])->name('usertoadminchat.unreadcount');
Route::get('frontend/buysellpost', [BuyandsellposController::class, 'buysellpost'])->name('buy.sellpost');


// Deposite Routes
// Route::post('user/deposit/request', [UserDepositewidthrawrequestController::class, 'userwidhraw_request'])->name('user.deposit.request');
// Route::get('user/deposit/status', [UserDepositewidthrawrequestController::class, 'checkDepositStatus'])->name('user.deposit.status');
// Route::post('user/deposit/submit/{id}', [UserDepositewidthrawrequestController::class, 'userSubmitDeposit'])->name('user.deposit.submit');



// Route::post('user/withdraw/request', [UserDepositewidthrawrequestController::class, 'userwidhraw_request'])->name('user.withdraw.request');


Route::get('user/withdraw/status', [UserWidhrawrequestAgentController::class, 'checkWithdrawStatus'])->name('user.withdraw.status');


Route::post('user/withdraw/submit/{id}', [UserWidhrawrequestAgentController::class, 'userSubmitWithdraw'])->name('user.withdraw.submit');


Route::post('agent/withdraw/accept/{id}', [UserWidhrawrequestAgentController::class, 'acceptWithdrawRequest'])->name('agent.withdraw.accept');


Route::post('/user/deposite/manual', [UserWidthrawController::class, 'user_deposite_manual'])->name('user.withdraw.store');
    Route::get('buysellpost', [UserDepositewidthrawrequestController::class, 'buysellpost'])
        ->name('buysellpost');

    // ========== DEPOSIT ROUTES ==========

    // Step 1: User sends deposit request
    Route::post('user/deposit/request', [UserDepositewidthrawrequestController::class, 'userwidhraw_request'])
        ->name('user.deposit.request');

    // Step 2: Check deposit status (polling)
    Route::get('user/deposit/status', [UserDepositewidthrawrequestController::class, 'checkDepositStatus'])
        ->name('user.deposit.status');

    // Step 3: User submits payment details
    Route::post('user/deposit/submit/{id}', [UserDepositewidthrawrequestController::class, 'userSubmitDeposit'])
        ->name('user.deposit.submit');

    // ========== WITHDRAW ROUTES ==========

    // Step 1: User sends withdraw request
    Route::post('user/withdraw/request', [UserDepositewidthrawrequestController::class, 'userwidhraw_request'])
        ->name('user.withdraw.request');

    // Step 2: Check withdraw status (polling)
    Route::get('user/withdraw/status', [UserDepositewidthrawrequestController::class, 'checkWithdrawStatus'])
        ->name('user.withdraw.status');

    // Step 3: User releases withdraw funds
    Route::post('user/withdraw/submit/{id}', [UserDepositewidthrawrequestController::class, 'userSubmitWithdraw'])
        ->name('user.withdraw.submit');

    // ========== AGENT ROUTES ==========

    // Agent accepts withdraw request
    Route::post('agent/withdraw/accept/{id}', [UserDepositewidthrawrequestController::class, 'acceptWithdrawRequest'])
        ->name('agent.withdraw.accept');








});
// Frontend Route Controller End

// Admin Login  Controller Start
Route::get('admin/login', [AdminautoController::class, 'admin_login'])->name('admin.login');
Route::post('admin/login/submit', [AdminautoController::class, 'admin_submit'])->name('admin.submit');
Route::post('admin/logout', [AdminautoController::class, 'admin_logout'])->name('admin.logout');
// Admin Login  Controller Start

// Admin Route Controller Start
Route::middleware(['is_admin'])->group(function () {
  Route::get('admin/dashboard', [AdminController::class, 'admin_dashboard'])->name('admin.dashboard');
  Route::resource('appsetting', AppsettingController::class);
  Route::get('agent/pending', [AdminApproveController::class,'pendingAgents'])->name('admin.agent.pending');
  Route::get('agent/approve/{id}', [AdminApproveController::class,'approveAgent'])->name('admin.agent.approve');
  Route::get('agent/reject/{id}', [AdminApproveController::class,'rejectAgent'])->name('admin.agent.reject');
  Route::get('agent/approved/list', [AdminApproveController::class,'agentapprovedlist'])->name('agentapprovedlist');
  Route::get('admin/agent/rejected', [AdminApproveController::class, 'agentrejectlist']) ->name('admin.agent.rejectlist');
  Route::resource('agentcreate', AdminagentcreateController::class);
  Route::resource('paymentmethod',PaymentmethodController::class);
  Route::resource('reffercommission',ReffercommissionsetupController::class);
  Route::resource('notice',NoticesController::class);
  Route::resource('worknotice',WorkNoticesController::class);
  Route::resource('package',PackageController::class);
  Route::resource('support',SupportController::class);
  Route::resource('stepguide',StepguideController::class);
  Route::resource('whychooseu',WhychooseusControllerController::class);
  Route::resource('dollarsiged',TakaandDollarsigendController::class);




//   Route::get('/pending', [AdmindepositeApprovedController::class, 'admin_deposite_pending'])->name('admin.deposite.pending');
//   Route::get('/approve/{id}', [AdmindepositeApprovedController::class, 'admin_deposite_approve'])->name('admin.deposite.approve');
//   Route::get('/reject/{id}', [AdmindepositeApprovedController::class, 'admin_deposite_reject'])->name('admin.deposite.reject');
//   Route::get('/approved/list', [AdmindepositeApprovedController::class, 'admin_deposite_approved_list'])->name('admin.deposite.approved.list');
//   Route::get('/reject/list', [AdmindepositeApprovedController::class, 'admin_deposite_reject_list'])->name('admin.deposite.reject.list');
Route::get('/pending', [AdmindepositeApprovedController::class, 'admin_deposite_pending'])->name('admin.deposite.pending');
Route::post('/approve/{id}', [AdmindepositeApprovedController::class, 'admin_deposite_approve'])->name('admin.deposite.approve');
Route::post('/reject/{id}', [AdmindepositeApprovedController::class, 'admin_deposite_reject'])->name('admin.deposite.reject');
Route::get('/approved/list', [AdmindepositeApprovedController::class, 'admin_deposite_approved_list'])->name('admin.deposite.approved.list');
Route::get('/reject/list', [AdmindepositeApprovedController::class, 'admin_deposite_reject_list'])->name('admin.deposite.reject.list');





  Route::get('/admin/package/list', [AdminPackageuylistcheckController::class, 'admin_package_list'])->name('admin.buy.package.list');
  Route::get('kyc/kyclist', [AdminkeyapprovedController::class,'kyclist'])->name('kyc.list');
  Route::post('kyc/approve/{id}', [AdminkeyapprovedController::class,'approvedkey'])->name('admin.kyc.approve');
  Route::post('kyc/reject/{id}', [AdminkeyapprovedController::class,'rejectapprovedkey'])->name('admin.kyc.reject');
  Route::get('admin/kyc/approved/list', [KeyController::class, 'frontend_kyc_approved'])->name('frontend.kyc.approved.list');
  Route::get('admin/kyc/reject/list', [KeyController::class, 'frontend_kyc_reject_list'])->name('frontend.kyc.reject.list');
  Route::resource('ads',AdsController::class);
  Route::post('admin/user/block/unblock', [AdminBlockuserController::class, 'admin_block_user'])->name('admin.block.user');
Route::get('admin/to/user/tochat/list', [AdminandchatuserController::class, 'adminuserchat'])->name('admin.userchat');
Route::get('admin/to/chat/fetch/{user_id}', [AdminandchatuserController::class, 'fetchMessages'])->name('admin.chat.fetch');
Route::post('admin/to/chat/send', [AdminandchatuserController::class, 'sendMessage'])->name('admin.chat.send');
Route::get('admin/to/user/unread', [AdminandchatuserController::class, 'unreadCount'])->name('admin.user.unread');
Route::post('admin/to/chat/mark-read/{user_id}', [AdminandchatuserController::class, 'markRead'])->name('admin.chat.markread');
// Show agent list
Route::get('admin/agent/chat', [ChateforagentandadminController::class, 'agent_for_chat_admin'])->name('admin.agent.chat');
// Fetch messages
Route::get('admin/agent/chat/fetch/{user_id}', [ChateforagentandadminController::class, 'fetchMessages'])->name('admin.agent.chat.fetch');
// Send message
Route::post('admin/agent/chat/send', [ChateforagentandadminController::class, 'sendMessage'])->name('admin.agent.chat.send');
// Mark as read
Route::post('admin/agent/chat/mark-read/{user_id}', [ChateforagentandadminController::class, 'markRead'])->name('admin.agent.markread');
// Get unread count
Route::get('admin/agent/unread-count/{agent}', [ChateforagentandadminController::class, 'unreadCount']);
Route::resource('depositelimit',DepositelimiteController::class);
Route::get('admin/agent/deposite', [AdminagentDepositeController::class, 'admin_agemt_deposite_pending'])->name('admin.agent.deposite.pending');
Route::post('/agent-deposite/approve/{id}', [AdminagentDepositeController::class, 'approve'])->name('admin.agentdeposit.approve');
Route::post('/agent-deposite/reject/{id}', [AdminagentDepositeController::class, 'reject'])->name('admin.agentdeposit.reject');
Route::get('admin/agent/deposite/approve/list', [AdminagentDepositeController::class, 'admin_agemt_deposite_approved_list'])->name('admin.agent.deposite.approved.list');
Route::get('admin/agent/deposite/reject/list', [AdminagentDepositeController::class, 'admin_agemt_deposite_reject_list'])->name('admin.agent.deposite.reject.list');
Route::resource('category',CategoryController::class);
Route::resource('agentcommission',DepositewidhrawComissionagetController::class);
Route::resource('widthrawlimit',WidthrawlimitController::class);
Route::get('admin/user/widtharw/approved/index', [AdminuserdepositeApprovedController::class, 'admin_widthraw_approvedindex'])->name('admin.widtharw.approved.index');
Route::put('/admin/withdraw/approve/{id}', [AdminuserdepositeApprovedController::class, 'approveWithdraw'])->name('admin.widthraw.approve');
Route::put('/admin/withdraw/reject/{id}', [AdminuserdepositeApprovedController::class, 'rejectWithdraw'])->name('admin.widthraw.reject');
Route::get('/admin/withdraw/approved/list', [AdminuserdepositeApprovedController::class, 'approved_list'])->name('approved.list');
Route::get('/admin/withdraw/rejected/list', [AdminuserdepositeApprovedController::class, 'rejected_list'])->name('rejected.list');




Route::resource('depositeinstruction',DepositInstructionController::class);
});
// Admin Route Controller End









// Agent Login  Controller Start
Route::get('agent/login', [AgentauthController::class, 'agent_login'])->name('agent.login');
Route::get('agent/register', [AgentauthController::class, 'agent_register'])->name('agent.register');
Route::post('/register/submit', [AgentauthController::class, 'agent_register_submit'])->name('agent.register.submit');
Route::post('agent/login/submit', [AgentauthController::class, 'agent_submit'])->name('agent.submit');
Route::post('agent/logout', [AgentauthController::class, 'agent_logout'])->name('agent.logout');
// Agent Login  Controller End
// Agent Route Controller Start
Route::middleware(['agent'])->group(function () {
   Route::get('agent/dashboard', [AgentController::class, 'agent_dashboard'])->name('agent.dashboard');
   Route::get('agent/profile', [AgentProfileController::class, 'agent_profile'])->name('agent.profile');
   Route::post('agent/profile', [AgentProfileController::class, 'agent_profile_update'])->name('agent.profile.update');
   Route::get('agent/password/change', [AgentPasswordchangeController::class, 'agent_password_change'])->name('agent.password.change');
   Route::post('agent/password/submit', [AgentPasswordchangeController::class, 'agent_password_submit'])->name('agent.password.submit');
   Route::get('agent/kyc', [AgentkycController::class, 'agent_key'])->name('agent.key');
   Route::post('agent/kyc/submit', [AgentkycController::class, 'agent_key_submit'])->name('agent.key.submit');
   Route::get('agent/kyc/kyclist', [AgentkyapprovedcController::class,'agentkyclist'])->name('agent.kyc.list');
   Route::post('agent/kyc/approve/{id}', [AgentkyapprovedcController::class,'agentapprovedkey'])->name('agent.kyc.approve');
   Route::post('agent/kyc/reject/{id}', [AgentkyapprovedcController::class,'agentrejectapprovedkey'])->name('agent.kyc.reject');
   Route::get('agentss/kyc/approve/list', [AgentkyapprovedcController::class,'agentapprovedkeylist'])->name('agent.approved.kyc.list');
   Route::get('agentsss/kyc/reject/list', [AgentkyapprovedcController::class,'agentrejectapprovedkeylist'])->name('agent.kyc.reject.list');

   Route::get('agent/user/toagent/chat', [AgentchattouserChatController::class, 'agent_user_toagent_chat'])->name('agent.user.toagent.chat');
   Route::post('agent/userto/agent/chat/submit', [AgentchattouserChatController::class, 'agent_chat_submit'])->name('agent.chat.agents.submit');
   Route::get('agent/userto/message/chat/messages', [AgentchattouserChatController::class, 'agent_chat_messages'])->name('agent.toagent.userto.chat.messages');
   Route::get('agent/userto/unread/agent/chat/unread-counts', [AgentchattouserChatController::class, 'agent_chat_messagesge_tUnreadCounts'])->name('agent.chat.agent.unread');
// Agent chat page
 // Chat page
    Route::get('agent/user/toagent/chat', [AgentchattouserChatController::class, 'index'])->name('agent.user.toagent.chat');
    Route::post('agent/userto/agent/chat/submit', [AgentchattouserChatController::class, 'sendMessage'])->name('agent.chat.send');
    Route::get('agent/userto/message/chat/messages', [AgentchattouserChatController::class, 'messages'])->name('agent.chat.messages');
    Route::get('agent/userto/unread/agent/chat/unread-counts', [AgentchattouserChatController::class, 'unreadCounts'])->name('agent.chat.unread');
    Route::get('/agent/friend/request/accept/view',[AgentrequestAcceptController::class, 'agentacceptRequestview'])->name('agent.friend.request.accept.view');
    Route::post('/agent/friend/request/accept',[AgentrequestAcceptController::class, 'agentacceptRequest'])->name('agent.friend.request.accept');
    Route::post('/agent/friend/request/reject', [AgentrequestAcceptController::class, 'agentrejectRequest'])->name('agent.friend.request.reject');
    Route::post('/agent/friend/request/cancel',[AgentrequestAcceptController::class, 'agentcancelFriendRequest'])->name('agent.friend.request.cancel');
    Route::get('/chat-for-admin', [adminChatforAgentController::class, 'index'])->name('agentforchat.index');
    Route::get('/chat/fetch', [adminChatforAgentController::class, 'fetchMessages'])->name('agentchatadmin.fetch');
    Route::post('/chat/send', [adminChatforAgentController::class, 'sendMessage'])->name('agentchatforagent.send');
    Route::post('/chat/mark-read', [adminChatforAgentController::class, 'markRead'])->name('agentadmin.markread');
 // Chat page Ende
  // Agent Deposite Start
   Route::get('Agent/deposite', [AgentDepositeController::class, 'agentdeposite'])->name('agent.deposite');
   Route::post('agent/deposite/store', [AgentDepositeController::class, 'store'])->name('agent.deposite.store');
   Route::get('agent/deposite/approve/list', [AgentDepositeController::class, 'agent_deposite_approved_list'])->name('agent.deposite.approved.list');
   Route::get('agent/deposite/reject/list', [AgentDepositeController::class, 'agent_deposite_reject_list'])->name('agent.deposite.reject.list');
  // Agent Deposite End

  Route::resource('agentbuysellpost', AgentbuysellPostCreateController::class);



Route::get('/agent/deposit-requests', [AgentracceptuserandDeposite::class, 'agentDepositRequests'])->name('agent.deposit.requests');
Route::post('/agent/deposit/accept/{id}', [AgentracceptuserandDeposite::class, 'acceptDepositRequest'])->name('agent.deposit.accept');
Route::post('/agent/deposit/final-confirm/{id}', [AgentracceptuserandDeposite::class, 'finalDepositConfirm'])->name('agent.deposit.final');
Route::post('/agent/deposit/orderrelche/{id}', [AgentracceptuserandDeposite::class, 'finalDepositorderrelche'])->name('agent.deposit.orderrelche');


Route::get('/agent/withdraw-requests', [AgentWidhrawrequestacceptController::class, 'agentwidhrawRequests'])->name('agent.withdraw.requests');
Route::post('/agent/withdraw/accept/{id}', [AgentWidhrawrequestacceptController::class, 'acceptagentwidhrawRequest'])->name('agent.withdraw.accept');
Route::post('/agent/withdraw/release/{id}', [AgentWidhrawrequestacceptController::class, 'releaseWithdraw'])->name('agent.withdraw.release');




});



// Agent Route Controller End




