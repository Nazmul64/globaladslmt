<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Category;
use App\Models\AgentDeposite;
use App\Models\Agentbuysellpost;
use App\Models\Userdepositerequest;
use App\Models\UserWidhrawrequest;
use App\Models\Agentcommissonsetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserWidhrawrequestAgentController extends Controller
{
    // SHOW BUY/SELL POSTS
    public function buysellpost()
    {
        try {
            $categories = Category::all();
            $posts = Agentbuysellpost::with(['category', 'user', 'dollarsign', 'agentamounts'])
                ->where('status', 'approved')
                ->latest()
                ->get();

            return view('frontend.buyandsellpost.index', compact('categories', 'posts'));

        } catch (\Exception $e) {
            Log::error("Buy/Sell Page Error: ".$e->getMessage());
            return back()->with("error", "Failed to load.");
        }
    }

    // USER DEPOSIT / WITHDRAW REQUEST
    public function userwidhraw_request(Request $request)
    {
        DB::beginTransaction();
        try {
            $rules = [
                'type' => 'required|in:deposit,withdraw',
                'agent_id' => 'required|exists:users,id',
                'post_id' => 'required|exists:agentbuysellposts,id',
                'amount' => 'required|numeric|min:0.01',
            ];

            if ($request->type === 'withdraw') {
                $rules['payment_method_id'] = 'nullable|exists:payment_methods,id';
                $rules['transaction_id'] = 'nullable|string|max:255';
                $rules['sender_account'] = 'nullable|string|max:255';
            }

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $user = User::lockForUpdate()->find(Auth::id());

            // Withdraw balance check
            if ($request->type === "withdraw" && $user->balance < $request->amount) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Insufficient balance. Your balance: ' . $user->balance . ' USDT'], 400);
            }

            // Trade limit check
            $post = Agentbuysellpost::find($request->post_id);
            if ($request->amount < $post->trade_limit || $request->amount > $post->trade_limit_two) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => "Amount must be between {$post->trade_limit} and {$post->trade_limit_two} USDT"], 400);
            }

            // Duplicate request check
            $pending = $request->type === "deposit"
                ? Userdepositerequest::where('user_id', $user->id)
                    ->whereIn('status', ['pending','agent_confirmed','user_submitted'])
                    ->first()
                : UserWidhrawrequest::where('user_id', $user->id)
                    ->whereIn('status', ['pending','agent_confirmed'])
                    ->first();

            if ($pending) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => "You already have a pending {$request->type} request."], 400);
            }

            // Prepare data array
            $data = [
                'user_id' => $user->id,
                'agent_id' => $request->agent_id,
                'amount' => $request->amount,
                'status' => 'pending',
                'agent_commission' => 0,
                'admin_commission' => 0,
            ];

            // Save optional fields
            if ($request->filled('transaction_id')) $data['transaction_id'] = trim($request->transaction_id);
            if ($request->filled('sender_account')) $data['sender_account'] = trim($request->sender_account);
            if ($request->type === 'withdraw' && $request->filled('payment_method_id')) $data['payment_method_id'] = $request->payment_method_id;

            if ($request->type === "deposit") {
                $data['type'] = "deposit";
                $data['photo'] = null;
                $record = Userdepositerequest::create($data);
                Log::info("Deposit Request Created", (array)$record->toArray());
            } else {
                $data['photo'] = null;
                $record = UserWidhrawrequest::create($data);
                Log::info("Withdraw Request Created", (array)$record->toArray());
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => ucfirst($request->type) . ' request sent successfully.', 'request_id' => $record->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Create Request Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to create request: ' . $e->getMessage()], 500);
        }
    }

    // SUBMIT DEPOSIT DETAILS
    public function userSubmitDeposit(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'transaction_id' => 'required|string|max:255',
                'sender_account' => 'required|string|max:255',
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
                'payment_method' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $deposit = Userdepositerequest::lockForUpdate()->find($id);
            if (!$deposit || $deposit->user_id != Auth::id() || $deposit->status !== 'agent_confirmed') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Invalid deposit request or status'], 400);
            }

            $path = 'uploads/deposit/';
            if (!file_exists(public_path($path))) mkdir(public_path($path), 0755, true);

            $fileName = "deposit_" . time() . "_" . uniqid() . "." . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move(public_path($path), $fileName);

            $deposit->photo = $path . $fileName;
            $deposit->transaction_id = trim($request->transaction_id);
            $deposit->sender_account = trim($request->sender_account);
            if ($request->filled('payment_method')) $deposit->payment_method = trim($request->payment_method);
            $deposit->status = "user_submitted";
            $deposit->save();

            Log::info("Deposit Submitted", (array)$deposit->toArray());

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Deposit details submitted successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Deposit Submit Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to submit deposit: ' . $e->getMessage()], 500);
        }
    }

    // WITHDRAW RELEASE BY USER
    public function userSubmitWithdraw(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $withdraw = UserWidhrawrequest::lockForUpdate()->find($id);
            if (!$withdraw || $withdraw->user_id != Auth::id() || $withdraw->status !== "agent_confirmed") {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Invalid withdraw request or status'], 400);
            }

            $user = User::lockForUpdate()->find($withdraw->user_id);
            if ($user->balance < $withdraw->amount) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Insufficient balance. Your balance: ' . $user->balance . ' USDT'], 400);
            }

            $commission = Agentcommissonsetup::where('status', 1)->first();
            $agentCommission = $adminCommission = 0;
            if ($commission) {
                $totalCommission = $commission->commission_type === 'percent'
                    ? ($withdraw->amount * $commission->withdraw_total_commission) / 100
                    : $commission->withdraw_total_commission;
                $agentCommission = $adminCommission = $totalCommission / 2;
            }

            $netPay = $withdraw->amount - ($agentCommission + $adminCommission);

            $user->balance -= $withdraw->amount;
            $user->save();

            $agentDepo = AgentDeposite::lockForUpdate()->firstOrCreate(['agent_id' => $withdraw->agent_id], ['amount' => 0]);
            $agentDepo->amount += ($netPay + $agentCommission);
            $agentDepo->save();

            if ($request->filled('transaction_id')) $withdraw->transaction_id = trim($request->transaction_id);
            if ($request->filled('sender_account')) $withdraw->sender_account = trim($request->sender_account);

            $withdraw->agent_commission = $agentCommission;
            $withdraw->admin_commission = $adminCommission;
            $withdraw->status = "completed";
            $withdraw->save();

            Log::info("Withdraw Completed", (array)$withdraw->toArray());

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Withdraw completed successfully!', 'new_balance' => number_format($user->balance, 2)]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Withdraw Release Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to complete withdraw: ' . $e->getMessage()], 500);
        }
    }

    // CHECK DEPOSIT STATUS
    public function checkDepositStatus()
    {
        try {
            $deposit = Userdepositerequest::where('user_id', Auth::id())
                ->whereIn('status', ['agent_confirmed', 'user_submitted'])
                ->latest()
                ->first();

            if ($deposit && $deposit->status === 'agent_confirmed') {
                return response()->json(['status' => 'agent_confirmed', 'deposit_id' => $deposit->id]);
            }

            return response()->json(['status' => 'no_update']);
        } catch (\Exception $e) {
            Log::error("Check Deposit Status Error: " . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    // CHECK WITHDRAW STATUS
    public function checkWithdrawStatus()
    {
        try {
            $withdraw = UserWidhrawrequest::where('user_id', Auth::id())
                ->where('status', 'agent_confirmed')
                ->latest()
                ->first();

            if ($withdraw) {
                return response()->json(['status' => 'agent_confirmed', 'withdraw_id' => $withdraw->id]);
            }

            return response()->json(['status' => 'no_update']);
        } catch (\Exception $e) {
            Log::error("Check Withdraw Status Error: " . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
}
