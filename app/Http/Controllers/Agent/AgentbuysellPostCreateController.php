<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agentbuysellpost;
use App\Models\Category;
use App\Models\TakaandDollarsigend;
use App\Models\AgentDeposite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentbuysellPostCreateController extends Controller
{
    /**
     * Display all posts
     */
    public function index()
    {
        $posts = Agentbuysellpost::with(['category', 'dollarsign'])
            ->where('agent_id', Auth::id())
            ->latest()
            ->get();

        return view('agent.agentbuysellpost.index', compact('posts'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $agent = Auth::user();

        // Total Deposited Amount
        $total_deposited = AgentDeposite::where('agent_id', $agent->id)->sum('amount');

        // Locked Amount (Admin lock করেছে - শুধু Display এর জন্য)
        $locked_amount = $agent->locked_amount ?? 0;

        // ✅ Main Balance = Total Deposited - Locked Amount
        // এটা দিয়েই পোস্ট এবং উইথড্র করা যাবে
        $main_balance = $total_deposited - $locked_amount;

        // ✅ Available for POST = শুধু Main Balance
        // Locked Amount শুধু Display করবে, ব্যবহার করা যাবে না
        $total_available_for_post = $main_balance;

        $categories = Category::all();
        $takaandDollarsigend = TakaandDollarsigend::all();

        return view('agent.agentbuysellpost.create', compact(
            'categories',
            'takaandDollarsigend',
            'main_balance',
            'locked_amount',
            'total_available_for_post'
        ));
    }

    /**
     * Store New Post with Balance Validation
     * ✅ Deposit Post = শুধু Main Balance
     * ✅ Withdraw Post = Main Balance + Locked Amount
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'        => 'required|exists:categories,id',
            'dollarsigends_id'   => 'required|exists:takaand_dollarsigends,id',
            'photo.*'            => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'trade_limit'        => 'required|integer|min:1',
            'trade_limit_two'    => 'required|integer|gte:trade_limit',
            'rate_balance'       => 'required|numeric|min:0',
            'payment_name'       => 'required|string|max:255',
            'status'             => 'required|in:pending,approved,rejected',
        ]);

        $agent = Auth::user();

        // ---- GET AGENT BALANCES ----
        $total_deposited = AgentDeposite::where('agent_id', $agent->id)->sum('amount');
        $locked_amount = $agent->locked_amount ?? 0;

        // ✅ Main Balance = Total Deposited - Locked Amount
        $main_balance = $total_deposited - $locked_amount;

        // ---- GET CATEGORY NAME ----
        $category = Category::find($validated['category_id']);
        $category_name = strtolower($category->category_name ?? '');

        // ✅ Deposit Post = শুধু Main Balance
        if ($category_name === 'deposit' || $category_name === 'deposite') {
            $available_for_post = $main_balance;

            if ($validated['trade_limit'] > $available_for_post || $validated['trade_limit_two'] > $available_for_post) {
                return back()->withInput()->with('error',
                    'Insufficient Main Balance for Deposit Post! Your Main Balance is $' .
                    number_format($main_balance, 2) .
                    '. Deposit posts require Main Balance only. Locked Amount ($' .
                    number_format($locked_amount, 2) . ') cannot be used for Deposit posts.'
                );
            }

            if ($main_balance <= 0) {
                return back()->withInput()->with('error',
                    'Cannot create Deposit Post! Your Main Balance is $0.00. Please deposit funds first.'
                );
            }
        }
        // ✅ Withdraw Post = Main Balance + Locked Amount
        else if ($category_name === 'withdraw' || $category_name === 'withdrawal') {
            $available_for_post = $main_balance + $locked_amount;

            if ($validated['trade_limit'] > $available_for_post || $validated['trade_limit_two'] > $available_for_post) {
                return back()->withInput()->with('error',
                    'Insufficient Balance for Withdraw Post! Your available balance is $' .
                    number_format($available_for_post, 2) .
                    ' (Main Balance: $' . number_format($main_balance, 2) .
                    ' + Locked Amount: $' . number_format($locked_amount, 2) . ')'
                );
            }

            if ($available_for_post <= 0) {
                return back()->withInput()->with('error',
                    'Cannot create Withdraw Post! Your total balance is $0.00.'
                );
            }
        }
        // ✅ Other Posts = শুধু Main Balance
        else {
            $available_for_post = $main_balance;

            if ($validated['trade_limit'] > $available_for_post || $validated['trade_limit_two'] > $available_for_post) {
                return back()->withInput()->with('error',
                    'Insufficient Main Balance! Available: $' . number_format($main_balance, 2)
                );
            }
        }

        $validated['agent_id'] = $agent->id;

        if ($request->hasFile('photo')) {
            $validated['photo'] = json_encode($this->uploadMultipleImages($request->file('photo')));
        }

        Agentbuysellpost::create($validated);

        return redirect()->route('agentbuysellpost.index')
            ->with('success', 'Buy/Sell post created successfully!');
    }

    /**
     * Edit Post
     */
    public function edit($id)
    {
        $agent = Auth::user();
        $agentBuySellPost = Agentbuysellpost::findOrFail($id);

        // Total Deposited Amount
        $total_deposited = AgentDeposite::where('agent_id', $agent->id)->sum('amount');

        // Locked Amount (শুধু Display এর জন্য)
        $locked_amount = $agent->locked_amount ?? 0;

        // ✅ Main Balance = Total Deposited - Locked Amount
        $main_balance = $total_deposited - $locked_amount;

        // ✅ Available for POST = শুধু Main Balance
        $total_available_for_post = $main_balance;

        $categories = Category::all();
        $takaandDollarsigend = TakaandDollarsigend::all();

        return view('agent.agentbuysellpost.edit', compact(
            'agentBuySellPost',
            'categories',
            'takaandDollarsigend',
            'main_balance',
            'locked_amount',
            'total_available_for_post'
        ));
    }

    /**
     * Update Post with Balance Validation
     * ✅ Deposit Post = শুধু Main Balance
     * ✅ Withdraw Post = Main Balance + Locked Amount
     */
    public function update(Request $request, Agentbuysellpost $agentbuysellpost)
    {
        $validated = $request->validate([
            'category_id'        => 'required|exists:categories,id',
            'dollarsigends_id'   => 'required|exists:takaand_dollarsigends,id',
            'photo.*'            => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'trade_limit'        => 'required|integer|min:1',
            'trade_limit_two'    => 'required|integer|gte:trade_limit',
            'rate_balance'       => 'required|numeric|min:0',
            'payment_name'       => 'required|string|max:255',
            'status'             => 'required|in:pending,approved,rejected',
        ]);

        $agent = Auth::user();

        // ---- GET AGENT BALANCES ----
        $total_deposited = AgentDeposite::where('agent_id', $agent->id)->sum('amount');
        $locked_amount = $agent->locked_amount ?? 0;

        // ✅ Main Balance = Total Deposited - Locked Amount
        $main_balance = $total_deposited - $locked_amount;

        // ---- GET CATEGORY NAME ----
        $category = Category::find($validated['category_id']);
        $category_name = strtolower($category->category_name ?? '');

        // ✅ Deposit Post = শুধু Main Balance
        if ($category_name === 'deposit' || $category_name === 'deposite') {
            $available_for_post = $main_balance;

            if ($validated['trade_limit'] > $available_for_post || $validated['trade_limit_two'] > $available_for_post) {
                return back()->withInput()->with('error',
                    'Insufficient Main Balance for Deposit Post! Your Main Balance is $' .
                    number_format($main_balance, 2) .
                    '. Deposit posts require Main Balance only. Locked Amount ($' .
                    number_format($locked_amount, 2) . ') cannot be used for Deposit posts.'
                );
            }
        }
        // ✅ Withdraw Post = Main Balance + Locked Amount
        else if ($category_name === 'withdraw' || $category_name === 'withdrawal') {
            $available_for_post = $main_balance + $locked_amount;

            if ($validated['trade_limit'] > $available_for_post || $validated['trade_limit_two'] > $available_for_post) {
                return back()->withInput()->with('error',
                    'Insufficient Balance for Withdraw Post! Your available balance is $' .
                    number_format($available_for_post, 2) .
                    ' (Main Balance: $' . number_format($main_balance, 2) .
                    ' + Locked Amount: $' . number_format($locked_amount, 2) . ')'
                );
            }
        }
        // ✅ Other Posts = শুধু Main Balance
        else {
            $available_for_post = $main_balance;

            if ($validated['trade_limit'] > $available_for_post || $validated['trade_limit_two'] > $available_for_post) {
                return back()->withInput()->with('error',
                    'Insufficient Main Balance! Available: $' . number_format($main_balance, 2)
                );
            }
        }

        if ($request->hasFile('photo')) {
            if ($agentbuysellpost->photo) {
                foreach (json_decode($agentbuysellpost->photo) as $oldPhoto) {
                    $path = public_path($oldPhoto);
                    if (file_exists($path)) unlink($path);
                }
            }
            $validated['photo'] = json_encode($this->uploadMultipleImages($request->file('photo')));
        }

        $agentbuysellpost->update($validated);

        return redirect()->route('agentbuysellpost.index')
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Delete Post
     */
    public function destroy(Agentbuysellpost $agentbuysellpost)
    {
        if ($agentbuysellpost->photo) {
            foreach (json_decode($agentbuysellpost->photo) as $photo) {
                $path = public_path($photo);
                if (file_exists($path)) unlink($path);
            }
        }
        $agentbuysellpost->delete();

        return redirect()->route('agentbuysellpost.index')
            ->with('success', 'Post deleted successfully!');
    }

    /**
     * Upload multiple images
     */
    private function uploadMultipleImages($images)
    {
        $uploaded = [];
        foreach ($images as $image) {
            $filename = uniqid('post_') . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/agentbuysellpost'), $filename);
            $uploaded[] = 'uploads/agentbuysellpost/' . $filename;
        }
        return $uploaded;
    }
}
