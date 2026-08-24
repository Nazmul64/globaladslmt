<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Deposite;
use App\Models\User;
use Illuminate\Http\Request;

class AdmindepositeEditController extends Controller
{
 public function adminuserdepositecheck()
    {
        $users = User::where('role', 'user')->get();
        return view('admin.depositeedit.edit', compact('users'));
    }
public function depositesupdate(Request $request, $id)
{
    $deposite = Deposite::findOrFail($id);

    $request->validate([
        'amount' => 'required|numeric|min:1',
    ]);

    $deposite->update([
        'amount' => $request->amount,
    ]);

    return redirect()->back()->with('success', 'Deposit amount updated successfully!');
}
    public function depositebalanceEdit($id)
    {
        $users = User::where('role', 'user')->get(); // সকল ইউজার
        $balance_edit = Deposite::where('user_id', $id)->first(); // user_id অনুযায়ী balance edit

        if(!$balance_edit){
            // যদি balance record না থাকে তাহলে নতুন create করতে পারো বা error show করো
            $balance_edit = new Deposite();
            $balance_edit->user_id = $id;
            $balance_edit->amount = 0;
        }

        return view('admin.depositeedit.depositeaddforadminedit', compact('balance_edit', 'users'));
    }

  public function depositebalanceUpdate(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount'  => 'required|numeric|min:0',
        ]);

        $balance = Deposite::updateOrCreate(
            ['user_id' => $request->user_id],
            ['amount' => $request->amount]
        );

        return back()->with('success', '💰 Balance updated successfully!');
    }
}
