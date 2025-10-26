<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agentbuysellpost;
use App\Models\Category;
use Illuminate\Http\Request;

class AgentbuysellPostCreateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Agentbuysellpost::latest()->get();
        return view('agent.agentbuysellpost.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('agent.agentbuysellpost.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'trade_limit' => 'required|integer|min:1',
            'trade_limit_two' => 'required|integer|gte:trade_limit',
            'available_balance' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'payment_name' => 'required|string|max:255',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $validated['photo'] = $this->uploadImage($request->file('photo'));
        $validated['agent_id'] = auth()->id();

        Agentbuysellpost::create($validated);

        return redirect()->route('agentbuysellpost.index')
            ->with('success', 'Buy/Sell post created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
public function edit($id)
{
    $agentBuySellPost = Agentbuysellpost::findOrFail($id); // Correct variable name
    $categories = Category::all();
    return view('agent.agentbuysellpost.edit', compact('agentBuySellPost', 'categories'));
}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Agentbuysellpost $agentbuysellpost)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'trade_limit' => 'required|integer|min:1',
            'trade_limit_two' => 'required|integer|gte:trade_limit',
            'available_balance' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'payment_name' => 'required|string|max:255',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        // Handle photo update
        if ($request->hasFile('photo')) {
            if ($agentbuysellpost->photo && file_exists(public_path($agentbuysellpost->photo))) {
                unlink(public_path($agentbuysellpost->photo));
            }
            $validated['photo'] = $this->uploadImage($request->file('photo'));
        }

        $agentbuysellpost->update($validated);

        return redirect()->route('agentbuysellpost.index')
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agentbuysellpost $agentbuysellpost)
    {
        if ($agentbuysellpost->photo && file_exists(public_path($agentbuysellpost->photo))) {
            unlink(public_path($agentbuysellpost->photo));
        }

        $agentbuysellpost->delete();

        return redirect()->route('agentbuysellpost.index')
            ->with('success', 'Post deleted successfully!');
    }

    /**
     * Upload image helper
     */
    private function uploadImage($image)
    {
        $imageName = uniqid('post_') . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('uploads/agentbuysellpost'), $imageName);
        return 'uploads/agentbuysellpost/' . $imageName;
    }
}
