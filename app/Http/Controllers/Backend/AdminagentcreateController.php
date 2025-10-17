<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\cr;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminagentcreateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $agents = User::where('role', 'agent')->get();
       return view('admin.agentlist.index', compact('agents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         $countries = ['United States','Canada','United Kingdom','Australia','Germany','France','India','Japan','China','Bangladesh'];
        return view('admin.agentlist.create',compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'country' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'country' => $request->country,
            'role' => 'agent',
            'status' => 'approved',
            'password' =>Hash::make($request->password),
        ]);

        return redirect()->route('agentcreate.index')->with('success', 'Agent created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(cr $cr)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
  public function edit($id)
    {
        $agent = User::findOrFail($id);
        return view('admin.agentlist.edit', compact('agent'));
    }

    /**
     * Update the specified resource in storage.
     */
 public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'country' => 'required|string|max:100',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $agent = User::findOrFail($id);
        $agent->name = $request->name;
        $agent->email = $request->email;
        $agent->country = $request->country;

        if($request->filled('password')){
            $agent->password = Hash::make($request->password);
        }

        $agent->save();

        return redirect()->route('agentcreate.index')->with('success', 'Agent updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
  public function destroy($id)
{
    $agent = User::findOrFail($id);

    // Optional: prevent admin from deleting themselves
    if(Auth::user()->id == $agent->id){
        return redirect()->back()->with('error', 'You cannot delete yourself!');
    }

    $agent->delete();

    return redirect()->route('agentcreate.index')->with('success', 'Agent deleted successfully!');
}

}
