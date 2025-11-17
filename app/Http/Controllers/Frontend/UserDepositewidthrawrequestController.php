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
use Carbon\Carbon;

class UserDepositewidthrawrequestController extends Controller
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

            // Calculate total orders and agent stats
            foreach ($all_agentbuysellpost as $post) {
                $agentId = $post->user_id;

                // Count completed deposits
                $completedDeposits = Userdepositerequest::where('agent_id', $agentId)
                    ->where('status', 'completed')
                    ->count();

                // Count completed withdraws
                $completedWithdraws = UserWidhrawrequest::where('agent_id', $agentId)
                    ->where('status', 'completed')
                    ->count();

                // Total orders count (এজেন্ট কতটা অর্ডার করেছে)
                $totalOrders = $completedDeposits + $completedWithdraws;

                // Success rate calculation (98-100% range)
                $successRate = $totalOrders > 0 ? min(100, 98 + ($totalOrders % 3)) : 0;

                // Attach to post
                $post->total_orders = $totalOrders;
                $post->success_rate = number_format($successRate, 1);

                // Check if agent is online (last_active_at থেকে চেক করবে)
                $lastActive = $post->user->last_active_at;
                $post->is_online = $lastActive && Carbon::parse($lastActive)->diffInMinutes(now()) <= 5;
            }

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
     * User Sends Deposit/Withdraw Request
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

            $validator = Validator::make($request->all(), [
                'type' => 'required|in:deposit,withdraw',
                'agent_id' => 'required|exists:users,id',
                'post_id' => 'required|exists:agentbuysellposts,id',
                'amount' => 'required|numeric|min:0.01',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            if ($request->type === 'withdraw') {
                $user = User::lockForUpdate()->find(Auth::id());
                if ($user->balance < $request->amount) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient balance. Your balance: ' . number_format($user->balance, 2) . ' USDT'
                    ], 400);
                }
            }

            $post = Agentbuysellpost::find($request->post_id);
            if ($post) {
                if ($request->amount < $post->trade_limit || $request->amount > $post->trade_limit_two) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Amount must be between {$post->trade_limit} and {$post->trade_limit_two} USDT"
                    ], 400);
                }
            }

            if ($request->type === 'deposit') {
                $existingRequest = Userdepositerequest::where('user_id', Auth::id())
                    ->whereIn('status', ['pending', 'agent_confirmed', 'user_submitted'])
                    ->first();
            } else {
                $existingRequest = UserWidhrawrequest::where('user_id', Auth::id())
                    ->whereIn('status', ['pending', 'agent_confirmed', 'user_submitted'])
                    ->first();
            }

            if ($existingRequest) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending ' . $request->type . ' request. Please complete or cancel it first.'
                ], 400);
            }

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

            if ($request->type === 'deposit') {
                $data['type'] = 'deposit';
                $record = Userdepositerequest::create($data);
                $message = 'Deposit request sent successfully! Waiting for agent confirmation.';
            } else {
                $record = UserWidhrawrequest::create($data);
                $message = 'Withdraw request sent successfully! Waiting for agent confirmation.';
            }

            Log::info('Request Created Successfully', [
                'type' => $request->type,
                'id' => $record->id,
                'user_id' => Auth::id(),
                'agent_id' => $request->agent_id,
                'amount' => $request->amount
            ]);

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
            return response()->json(['success' => false, 'message' => 'Failed to send request: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check Deposit Status (Polling)
     */
    public function checkDepositStatus()
    {
        try {
            if (!Auth::check()) return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);

            $deposit = Userdepositerequest::where('user_id', Auth::id())
                ->where('status', 'agent_confirmed')
                ->latest()
                ->first();

            if ($deposit) {
                Log::info('Deposit Status - Agent Confirmed', [
                    'deposit_id' => $deposit->id,
                    'user_id' => Auth::id(),
                    'amount' => $deposit->amount
                ]);
                return response()->json(['status' => 'agent_confirmed', 'deposit_id' => $deposit->id, 'amount' => $deposit->amount]);
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
     * User Submits Deposit Details
     */
    public function userSubmitDeposit(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'transaction_id' => 'required|string|max:255',
                'sender_account' => 'required|string|max:255',
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $deposit = Userdepositerequest::lockForUpdate()->find($id);
            if (!$deposit || $deposit->user_id !== Auth::id() || $deposit->status !== 'agent_confirmed') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Invalid or unauthorized deposit request'], 400);
            }

            $photoPath = $deposit->photo;
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = 'deposit_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $uploadPath = public_path('uploads/deposit');
                if (!file_exists($uploadPath)) mkdir($uploadPath, 0755, true);
                if ($deposit->photo && file_exists(public_path($deposit->photo))) @unlink(public_path($deposit->photo));
                $file->move($uploadPath, $filename);
                $photoPath = 'uploads/deposit/' . $filename;
            }

            $deposit->transaction_id = $request->transaction_id;
            $deposit->sender_account = $request->sender_account;
            $deposit->photo = $photoPath;
            $deposit->status = 'user_submitted';
            $deposit->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Deposit details submitted successfully! Waiting for final approval.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Deposit Submit Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'deposit_id' => $id,
                'user_id' => Auth::id()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to submit deposit: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check Withdraw Status (Polling)
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
                return response()->json(['status' => 'agent_confirmed', 'withdraw_id' => $withdraw->id, 'amount' => $withdraw->amount]);
            }
            return response()->json(['status' => 'pending']);
        } catch (\Exception $e) {
            Log::error('Withdraw Status Check Error', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * User Releases Withdraw Funds
     */
    public function userSubmitWithdraw(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $withdraw = UserWidhrawrequest::lockForUpdate()->find($id);
            if (!$withdraw || $withdraw->user_id !== Auth::id() || $withdraw->status !== 'agent_confirmed') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Invalid or unauthorized withdraw request'], 400);
            }

            $user = User::lockForUpdate()->find($withdraw->user_id);
            if (!$user || $user->balance < $withdraw->amount) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Insufficient balance'], 400);
            }

            $agentDeposit = AgentDeposite::lockForUpdate()->firstOrCreate(['agent_id' => $withdraw->agent_id], ['amount' => 0]);

            $agentCommission = 0;
            $adminCommission = 0;
            $totalCommission = 0;

            $commissionSetup = Agentcommissonsetup::where('status', 1)->first();
            if ($commissionSetup) {
                $totalCommission = ($commissionSetup->commission_type === 'percent')
                    ? ($withdraw->amount * $commissionSetup->withdraw_total_commission) / 100
                    : $commissionSetup->withdraw_total_commission;

                $agentCommission = $totalCommission / 2;
                $adminCommission = $totalCommission / 2;
            }

            $netAmount = $withdraw->amount - $totalCommission;

            $user->balance -= $withdraw->amount;
            $user->save();

            $agentDeposit->amount += ($netAmount + $agentCommission);
            $agentDeposit->save();

            $withdraw->status = 'completed';
            $withdraw->agent_commission = $agentCommission;
            $withdraw->admin_commission = $adminCommission;
            $withdraw->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Withdraw released successfully!', 'new_balance' => number_format($user->balance, 2)]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdraw Release Error', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            return response()->json(['success' => false, 'message' => 'Failed to release withdraw: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Agent Accepts Withdraw Request
     */
    public function acceptWithdrawRequest($id)
    {
        DB::beginTransaction();
        try {
            $withdraw = UserWidhrawrequest::lockForUpdate()->find($id);
            if (!$withdraw || $withdraw->agent_id != Auth::id() || $withdraw->status !== 'pending') {
                DB::rollBack();
                return back()->with('error', 'Invalid or unauthorized request');
            }

            $withdraw->status = 'agent_confirmed';
            $withdraw->save();

            DB::commit();
            return back()->with('success', 'Withdraw request accepted. User can now release funds.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Accept Withdraw Error', ['error' => $e->getMessage(), 'agent_id' => Auth::id()]);
            return back()->with('error', 'Failed to accept request: ' . $e->getMessage());
        }
    }
}
