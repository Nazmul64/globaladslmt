<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Deposite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositeController extends Controller
{
    /**
     * Store a new deposit request
     */
   public function store_deposite(Request $request)
{

    $request->validate([
        'amount' => 'required|numeric|min:1',
        'transaction_id' => 'required|string|max:255',
        'sender_account' => 'required|string|max:255',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);


    $deposit = new Deposite([
        'user_id' => Auth::id(),
        'amount' => $request->amount,
        'transaction_id' => $request->transaction_id,
        'sender_account' => $request->sender_account,
        'status' => 'pending',
    ]);

    if ($request->hasFile('photo')) {
        $file = $request->file('photo');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/deposits'), $filename);
        $deposit->photo = $filename;
    }

    $deposit->save();

    return redirect()->back()->with('success', 'Deposit request submitted successfully!');
}

}
