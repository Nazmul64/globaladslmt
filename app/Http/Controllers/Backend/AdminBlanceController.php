<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminBlanceController extends Controller
{
    public function adminusercheck(){
        $users=User::where('role','user')->get();
       return view('admin.adminblanceadd.index',compact('users'));
    }

    public function adminBalanceEdit(Request $request, $id)
    {
        $blance_edit = User::findOrFail($id);
        return view('admin.adminblanceadd.edit', compact('blance_edit'));
    }

     public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $user = User::findOrFail($id);
        $oldBalance = (float)($user->balance ?? 0);
        $newBalance = (float)$request->amount;
        $diffAmount = $newBalance - $oldBalance;

        $user->balance = $newBalance;
        $user->save();

        // Record entry in deposits table for deposit history
        $depositAmount = $diffAmount > 0 ? $diffAmount : $newBalance;
        if ($depositAmount > 0) {
            \App\Models\Deposite::create([
                'user_id' => $user->id,
                'amount' => $depositAmount,
                'transaction_id' => 'MANUAL-' . rand(100000, 999999),
                'sender_account' => 'Manually add kora hoice',
                'status' => 'approved',
            ]);
        }

        return redirect()->back()->with('success', 'User balance updated successfully and manual deposit recorded!');
    }

  public function usrdelete($id)
{
    $user_delete = User::findOrFail($id);
    $user_delete->delete();

    return redirect()->back()->with('success', 'User deleted successfully!');
}

}
