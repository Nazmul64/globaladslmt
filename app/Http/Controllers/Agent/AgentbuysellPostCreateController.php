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
        $posts = Agentbuysellpost::with(['category', 'dollarsign'])->latest()->get();
        return view('agent.agentbuysellpost.index', compact('posts'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $categories = Category::all();
        $takaandDollarsigend = TakaandDollarsigend::all();
        return view('agent.agentbuysellpost.create', compact('categories', 'takaandDollarsigend'));
    }

    /**
     * Store New Post with Balance Validation
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

        // ---- GET AGENT CURRENT BALANCE ----
        $totalBalance = AgentDeposite::where('agent_id', Auth::id())->sum('amount');

        // ---- CHECK IF TRADE LIMIT EXCEEDS BALANCE ----
        if ($validated['trade_limit'] > $totalBalance || $validated['trade_limit_two'] > $totalBalance) {
            return back()->withInput()->with('error', 'Insufficient balance! Please reduce trade limit according to your available balance.');
        }

        $validated['agent_id'] = Auth::id();

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
        $agentBuySellPost = Agentbuysellpost::findOrFail($id);
        $categories = Category::all();
        $takaandDollarsigend = TakaandDollarsigend::all();
        return view('agent.agentbuysellpost.edit', compact('agentBuySellPost', 'categories', 'takaandDollarsigend'));
    }

    /**
     * Update Post with Balance Validation
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

        // ---- GET AGENT CURRENT BALANCE ----
        $totalBalance = AgentDeposite::where('agent_id', Auth::id())->sum('amount');

        // ---- CHECK BALANCE ----
        if ($validated['trade_limit'] > $totalBalance || $validated['trade_limit_two'] > $totalBalance) {
            return back()->withInput()->with('error', 'Insufficient balance! Please adjust trade limit based on your available balance.');
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

    // Upload multiple images
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
