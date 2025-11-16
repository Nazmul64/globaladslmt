<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agentbuysellpost;
use App\Models\Category;
use App\Models\TakaandDollarsigend;
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
     * Store a new post
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'dollarsigends_id' => 'required|exists:takaand_dollarsigends,id',
            'photo.*' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'trade_limit' => 'required|integer|min:1',
            'trade_limit_two' => 'required|integer|gte:trade_limit',
            'rate_balance' => 'required|numeric|min:0',
            'payment_name' => 'required|string|max:255',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $validated['agent_id'] = Auth::id();

        if ($request->hasFile('photo')) {
            $validated['photo'] = json_encode($this->uploadMultipleImages($request->file('photo')));
        }

        Agentbuysellpost::create($validated);

        return redirect()->route('agentbuysellpost.index')
            ->with('success', 'Buy/Sell post created successfully!');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $agentBuySellPost = Agentbuysellpost::findOrFail($id);
        $categories = Category::all();
        $takaandDollarsigend = TakaandDollarsigend::all();
        return view('agent.agentbuysellpost.edit', compact('agentBuySellPost', 'categories', 'takaandDollarsigend'));
    }

    /**
     * Update an existing post
     */
    public function update(Request $request, Agentbuysellpost $agentbuysellpost)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'dollarsigends_id' => 'required|exists:takaand_dollarsigends,id',
            'photo.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'trade_limit' => 'required|integer|min:1',
            'trade_limit_two' => 'required|integer|gte:trade_limit',
            'rate_balance' => 'required|numeric|min:0',
            'payment_name' => 'required|string|max:255',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        // Handle new uploaded photos
        if ($request->hasFile('photo')) {
            // Delete old photos
            if ($agentbuysellpost->photo) {
                foreach (json_decode($agentbuysellpost->photo) as $oldPhoto) {
                    $filePath = public_path($oldPhoto);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
            $validated['photo'] = json_encode($this->uploadMultipleImages($request->file('photo')));
        }

        $agentbuysellpost->update($validated);

        return redirect()->route('agentbuysellpost.index')
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Delete a post
     */
    public function destroy(Agentbuysellpost $agentbuysellpost)
    {
        if ($agentbuysellpost->photo) {
            foreach (json_decode($agentbuysellpost->photo) as $photo) {
                $filePath = public_path($photo);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        $agentbuysellpost->delete();

        return redirect()->route('agentbuysellpost.index')
            ->with('success', 'Post deleted successfully!');
    }

    /**
     * Upload multiple images helper
     */
    private function uploadMultipleImages($images)
    {
        $uploadedImages = [];
        foreach ($images as $image) {
            $filename = uniqid('post_') . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/agentbuysellpost'), $filename);
            $uploadedImages[] = 'uploads/agentbuysellpost/' . $filename;
        }
        return $uploadedImages;
    }
}
