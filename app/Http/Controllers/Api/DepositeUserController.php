<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deposite;
use App\Models\Depositelimite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositeUserController extends Controller
{
public function deposite(Request $request)
{
    $user = Auth::user();

    // Get deposit limit
    $limits = Depositelimite::first();
    if (!$limits) {
        return response()->json([
            'success' => false,
            'message' => 'Deposit limit not set in the system.'
        ], 400);
    }

    $request->validate([
        'amount' => 'required|numeric|min:1',
        'transaction_id' => 'required|string|max:255',
        'sender_account' => 'required|string|max:255',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // ✅ Check min and max deposit limit
    if ($request->amount < $limits->min_deposit || $request->amount > $limits->max_deposit) {
        return response()->json([
            'success' => false,
            'message' => "Deposit amount must be between {$limits->min_deposit} and {$limits->max_deposit}."
        ], 422);
    }

    $deposit = new Deposite();
    $deposit->user_id = $user->id;
    $deposit->amount = $request->amount;
    $deposit->transaction_id = $request->transaction_id;
    $deposit->sender_account = $request->sender_account;
    $deposit->status = 'pending';

    // ✅ Photo Upload
    if ($request->hasFile('photo')) {
        $image = $request->file('photo');
        $filename = uniqid().'_'.time().'.'.$image->getClientOriginalExtension();
        $image->move(public_path('uploads/deposits'), $filename);
        $deposit->photo = $filename;
    }

    $deposit->save();

    return response()->json([
        'success' => true,
        'message' => 'Deposit request submitted successfully and pending for approval.',
        'data' => [
            'id' => $deposit->id,
            'amount' => $deposit->amount,
            'transaction_id' => $deposit->transaction_id,
            'sender_account' => $deposit->sender_account,
            'status' => $deposit->status,
            'photo' => $deposit->photo ? asset('uploads/deposits/'.$deposit->photo) : null,
            'min_limit' => $limits->min_deposit,
            'max_limit' => $limits->max_deposit,
        ]
    ]);
}


}
