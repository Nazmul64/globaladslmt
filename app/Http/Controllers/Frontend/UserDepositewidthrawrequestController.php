<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Userdepositerequest;
use App\Models\UserWidhrawrequest;
use App\Models\Agentbuysellpost;
use App\Models\Category;
use App\Models\Depositelimite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserDepositewidthrawrequestController extends Controller
{
    // 1. Deposit / Withdraw Request
    public function userwidhraw_request(Request $request)
    {
        $request->validate([
            'type' => 'required|in:deposit,withdraw',
            'agent_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
        ]);

        // Check deposit limit
        $limit = Depositelimite::latest()->first();
        if($limit){
            if($request->amount < $limit->min_deposit || $request->amount > $limit->max_deposit){
                return redirect()->back()->with('error', "Amount must be between {$limit->min_deposit} and {$limit->max_deposit}");
            }
        }

        $data = [
            'user_id' => Auth::id(),
            'agent_id' => $request->agent_id,
            'amount' => $request->amount,
            'status' => 'pending',
        ];

        if($request->type === 'deposit'){
            Userdepositerequest::create($data);
        } else {
            UserWidhrawrequest::create($data);
        }

        return redirect()->back()->with('success', ucfirst($request->type).' request sent successfully!');
    }

    // 2. Check latest Deposit status
    public function checkDepositStatus()
    {
        $deposit = Userdepositerequest::where('user_id', Auth::id())->latest()->first();
        if($deposit && $deposit->status === 'agent_confirmed'){
            return response()->json(['status'=>'agent_confirmed','deposit_id'=>$deposit->id]);
        }
        return response()->json(['status'=>'pending']);
    }

    // 3. Check latest Withdraw status
    public function checkWithdrawStatus()
    {
        $withdraw = UserWidhrawrequest::where('user_id', Auth::id())->latest()->first();
        if($withdraw && $withdraw->status === 'agent_confirmed'){
            return response()->json(['status'=>'agent_confirmed','withdraw_id'=>$withdraw->id]);
        }
        return response()->json(['status'=>'pending']);
    }

    // 4. User submits Deposit details
    public function userSubmitDeposit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string|max:255',
            'sender_account' => 'required|string|max:255',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if($validator->fails()){
            return response()->json(['success'=>false,'message'=>$validator->errors()->first()]);
        }

        $deposit = Userdepositerequest::findOrFail($id);
        if($deposit->status !== 'agent_confirmed'){
            return response()->json(['success'=>false,'message'=>'Deposit not confirmed by agent']);
        }

        $deposit->transaction_id = $request->transaction_id;
        $deposit->sender_account = $request->sender_account;

        if($request->hasFile('photo')){
            $file = $request->file('photo');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/deposits'), $filename);
            $deposit->photo = $filename;
        }

        $deposit->status = 'user_submitted';
        $deposit->save();

        return response()->json(['success'=>true]);
    }

    // 5. User submits Withdraw details
    public function userSubmitWithdraw(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string|max:255',
            'receiver_account' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if($validator->fails()){
            return response()->json(['success'=>false,'message'=>$validator->errors()->first()]);
        }

        $withdraw = UserWidhrawrequest::findOrFail($id);
        if($withdraw->status !== 'agent_confirmed'){
            return response()->json(['success'=>false,'message'=>'Withdraw not confirmed by agent']);
        }

        $withdraw->transaction_id = $request->transaction_id;
        $withdraw->receiver_account = $request->receiver_account;

        if($request->hasFile('photo')){
            $file = $request->file('photo');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/withdraws'), $filename);
            $withdraw->photo = $filename;
        }

        $withdraw->status = 'user_submitted';
        $withdraw->save();

        return response()->json(['success'=>true]);
    }

    // 6. Buy/Sell page
    public function buysellpost()
    {
        $categories = Category::all();
        $all_agentbuysellpost = Agentbuysellpost::with('category','user')
            ->where('status','approved')
            ->get();

        return view('frontend.buyandsellpost.index', compact('categories','all_agentbuysellpost'));
    }
}
