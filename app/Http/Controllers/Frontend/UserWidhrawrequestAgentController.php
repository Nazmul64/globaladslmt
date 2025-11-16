<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Userdepositerequest;
use App\Models\UserWidhrawrequest;
use App\Models\Agentbuysellpost;
use App\Models\Category;
use App\Models\User;
use App\Models\AgentDeposite;
use App\Models\Agentcommissonsetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserWidhrawrequestAgentController extends Controller
{
    /**
     * Display Buy/Sell Posts Page
     */
    public function buysellpost()
    {
        try {
            $categories = Category::all();
            $all_agentbuysellpost = Agentbuysellpost::with(['category', 'user', 'dollarsign', 'agentamounts'])
                ->where('status', 'approved')
                ->latest()
                ->get();

            return view('frontend.buyandsellpost.index', compact('categories', 'all_agentbuysellpost'));
        } catch (\Exception $e) {
            Log::error('Buy/Sell Post Page Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return back()->with('error', 'Failed to load posts. Please try again.');
        }
    }

    /**
     * User Sends Deposit/Withdraw Request (Step 1)
     * Route: POST user/deposit/request OR POST user/withdraw/request
     */
    public function userwidhraw_request(Request $request)
    {
        DB::beginTransaction();
        try {
            Log::info('Request Started', [
                'type' => $request->type,
                'user_id' => Auth::id(),
                'data' => $request->except('_token')
            ]);

            // Validate request
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:deposit,withdraw',
                'agent_id' => 'required|exists:users,id',
                'post_id' => 'required|exists:agentbuysellposts,id',
                'amount' => 'required|numeric|min:0.01',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Check withdraw balance
            if ($request->type === 'withdraw') {
                $user = User::lockForUpdate()->find(Auth::id());
                if (!$user) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found'
                    ], 404);
                }
                if ($user->balance < $request->amount) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient balance. Your balance: ' . number_format($user->balance, 2) . ' USDT'
                    ], 400);
                }
            }

            // Check trade limits
            $post = Agentbuysellpost::find($request->post_id);
            if (!$post) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found'
                ], 404);
            }
            if ($request->amount < $post->trade_limit || $request->amount > $post->trade_limit_two) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Amount must be between {$post->trade_limit} and {$post->trade_limit_two} USDT"
                ], 400);
            }

            // Check for existing pending requests
            $existingRequest = $request->type === 'deposit'
                ? Userdepositerequest::where('user_id', Auth::id())->whereIn('status', ['pending', 'agent_confirmed', 'user_submitted'])->first()
                : UserWidhrawrequest::where('user_id', Auth::id())->whereIn('status', ['pending', 'agent_confirmed'])->first();

            if ($existingRequest) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending ' . $request->type . ' request. Please complete or cancel it first.'
                ], 400);
            }

            // Prepare request data
            $data = [
                'user_id' => Auth::id(),
                'agent_id' => $request->agent_id,
                'amount' => $request->amount,
                'status' => 'pending',
                'transaction_id' => null,
                'sender_account' => null,
                'photo' => null,
                'agent_commission' => 0,
                'admin_commission' => 0,
            ];

            // Create deposit or withdraw request
            if ($request->type === 'deposit') {
                $data['type'] = 'deposit';
                $record = Userdepositerequest::create($data);
                $message = 'Deposit request sent successfully! Waiting for agent confirmation.';
                Log::info('Deposit Request Created', [
                    'deposit_id' => $record->id,
                    'user_id' => Auth::id(),
                    'agent_id' => $request->agent_id,
                    'amount' => $request->amount
                ]);
            } else {
                $record = UserWidhrawrequest::create($data);
                $message = 'Withdraw request sent successfully! Waiting for agent confirmation.';
                Log::info('Withdraw Request Created', [
                    'withdraw_id' => $record->id,
                    'user_id' => Auth::id(),
                    'agent_id' => $request->agent_id,
                    'amount' => $request->amount
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => $message,
                'request_id' => $record->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Request Creation Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'type' => $request->type ?? 'unknown',
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check Deposit Status (Polling - Step 2)
     */
    public function checkDepositStatus()
    {
        try {
            if (!Auth::check()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            $deposit = Userdepositerequest::where('user_id', Auth::id())
                ->where('status', 'agent_confirmed')
                ->latest()
                ->first();

            if ($deposit) {
                Log::info('Deposit Status Check - Agent Confirmed', [
                    'deposit_id' => $deposit->id,
                    'user_id' => Auth::id(),
                    'amount' => $deposit->amount
                ]);
                return response()->json([
                    'status' => 'agent_confirmed',
                    'deposit_id' => $deposit->id,
                    'amount' => $deposit->amount
                ]);
            }

            return response()->json(['status' => 'pending']);

        } catch (\Exception $e) {
            Log::error('Deposit Status Check Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id()
            ]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * User Submits Deposit Details (Step 3)
     */
    public function userSubmitDeposit(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            Log::info('Deposit Submit Started', [
                'deposit_id' => $id,
                'user_id' => Auth::id(),
                'has_photo' => $request->hasFile('photo'),
                'request_data' => $request->except('photo')
            ]);

            $validator = Validator::make($request->all(), [
                'transaction_id' => 'required|string|max:255',
                'sender_account' => 'required|string|max:255',
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                Log::error('Deposit Validation Failed', [
                    'errors' => $validator->errors()->toArray(),
                    'deposit_id' => $id
                ]);
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $deposit = Userdepositerequest::lockForUpdate()->find($id);
            if (!$deposit || $deposit->user_id !== Auth::id() || $deposit->status !== 'agent_confirmed') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Unauthorized or invalid deposit status'], 403);
            }

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = 'deposit_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $uploadPath = public_path('uploads/deposit');
                if (!file_exists($uploadPath)) mkdir($uploadPath, 0755, true);
                if ($deposit->photo && file_exists(public_path($deposit->photo))) @unlink(public_path($deposit->photo));
                $file->move($uploadPath, $filename);
                $deposit->photo = 'uploads/deposit/' . $filename;
            }

            $deposit->transaction_id = $request->transaction_id;
            $deposit->sender_account = $request->sender_account;
            $deposit->status = 'user_submitted';
            $deposit->save();

            Log::info('Deposit Details Submitted Successfully', [
                'deposit_id' => $deposit->id,
                'user_id' => Auth::id()
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Deposit details submitted successfully! Waiting for final approval.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Deposit Submit Error', [
                'error' => $e->getMessage(),
                'deposit_id' => $id,
                'user_id' => Auth::id()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to submit deposit: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check Withdraw Status (Polling - Step 2)
     */
    public function checkWithdrawStatus()
    {
        try {
            if (!Auth::check()) return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);

            $withdraw = UserWidhrawrequest::where('user_id', Auth::id())
                ->where('status', 'agent_confirmed')
                ->latest()
                ->first();

            if ($withdraw) {
                Log::info('Withdraw Status Check - Agent Confirmed', [
                    'withdraw_id' => $withdraw->id,
                    'user_id' => Auth::id(),
                    'amount' => $withdraw->amount
                ]);
                return response()->json([
                    'status' => 'agent_confirmed',
                    'withdraw_id' => $withdraw->id,
                    'amount' => $withdraw->amount
                ]);
            }

            return response()->json(['status' => 'pending']);

        } catch (\Exception $e) {
            Log::error('Withdraw Status Check Error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * User Releases Withdraw Funds (Step 3)
     */
    public function userSubmitWithdraw(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            Log::info('Withdraw Release Started', [
                'withdraw_id' => $id,
                'user_id' => Auth::id()
            ]);

            $withdraw = UserWidhrawrequest::lockForUpdate()->find($id);
            if (!$withdraw || $withdraw->user_id !== Auth::id() || $withdraw->status !== 'agent_confirmed') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Unauthorized or invalid withdraw status'], 403);
            }

            $user = User::lockForUpdate()->find($withdraw->user_id);
            if (!$user || $user->balance < $withdraw->amount) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Insufficient balance'], 400);
            }

            $agentDeposit = AgentDeposite::lockForUpdate()->firstOrCreate(
                ['agent_id' => $withdraw->agent_id],
                ['amount' => 0]
            );

            $agentCommission = $adminCommission = $totalCommission = 0;
            $commissionSetup = Agentcommissonsetup::where('status', 1)->first();
            if ($commissionSetup) {
                $totalCommission = $commissionSetup->commission_type === 'percent'
                    ? ($withdraw->amount * $commissionSetup->withdraw_total_commission) / 100
                    : $commissionSetup->withdraw_total_commission;
                $agentCommission = $adminCommission = $totalCommission / 2;
            }

            $netAmount = $withdraw->amount - $totalCommission;

            $previousUserBalance = $user->balance;
            $user->balance -= $withdraw->amount;
            $user->save();

            $previousAgentAmount = $agentDeposit->amount;
            $agentDeposit->amount += ($netAmount + $agentCommission);
            $agentDeposit->save();

            $withdraw->status = 'completed';
            $withdraw->agent_commission = $agentCommission;
            $withdraw->admin_commission = $adminCommission;
            $withdraw->save();

            Log::info('Withdraw Completed Successfully', [
                'withdraw_id' => $withdraw->id,
                'user_previous_balance' => $previousUserBalance,
                'user_new_balance' => $user->balance,
                'agent_previous_amount' => $previousAgentAmount,
                'agent_new_amount' => $agentDeposit->amount,
                'agent_commission' => $agentCommission,
                'admin_commission' => $adminCommission
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Withdraw released successfully!', 'new_balance' => number_format($user->balance, 2)]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdraw Release Error', [
                'error' => $e->getMessage(),
                'withdraw_id' => $id,
                'user_id' => Auth::id()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to release withdraw: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Agent Accepts Withdraw Request (Backend)
     */
    public function acceptWithdrawRequest($id)
    {
        DB::beginTransaction();
        try {
            $withdraw = UserWidhrawrequest::lockForUpdate()->find($id);
            if (!$withdraw || $withdraw->agent_id != Auth::id() || $withdraw->status !== 'pending') {
                DB::rollBack();
                return back()->with('error', 'Unauthorized or invalid withdraw request');
            }

            $withdraw->status = 'agent_confirmed';
            $withdraw->save();

            Log::info('Withdraw Request Accepted by Agent', [
                'withdraw_id' => $id,
                'agent_id' => Auth::id(),
                'user_id' => $withdraw->user_id,
                'amount' => $withdraw->amount
            ]);

            DB::commit();
            return back()->with('success', 'Withdraw request accepted successfully. User can now release funds.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Accept Withdraw Error', [
                'error' => $e->getMessage(),
                'withdraw_id' => $id,
                'agent_id' => Auth::id()
            ]);
            return back()->with('error', 'Failed to accept request: ' . $e->getMessage());
        }
    }
}
