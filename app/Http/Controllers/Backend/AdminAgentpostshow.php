<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Agentbuysellpost;
use Illuminate\Http\Request;

class AdminAgentpostshow extends Controller
{
    // 🔹 Show all agent posts
    public function agentpostshow()
    {
        $posts = Agentbuysellpost::with([
            'agent',
            'category',
            'dollarsign',
            'agentPaymentMethods'
        ])->latest()->get();

        return view('admin.agentposts.index', compact('posts'));
    }

    // 🔹 Edit post
    public function edit($id)
    {
        $post = Agentbuysellpost::findOrFail($id);
        return view('admin.agentposts.edit', compact('post'));
    }

    // 🔹 Update post
    public function update(Request $request, $id)
    {
        $post = Agentbuysellpost::findOrFail($id);

        $request->validate([
            'trade_limit'       => 'required',
            'trade_limit_two'   => 'required',
            'rate_balance'      => 'required',
            'payment_name'      => 'required|string',
            'status'            => 'required|boolean',
            'photo'             => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,bmp,avif,jfif|max:10240',
        ], [
            'photo.mimes' => 'Supported formats are JPG, JPEG, PNG, GIF, SVG, WEBP, BMP, AVIF, JFIF.',
            'photo.max' => 'Photo size cannot exceed 10MB.',
        ]);

        if ($request->hasFile('photo')) {
            if ($post->photo && file_exists(public_path('uploads/agentbuysellpost/'.$post->photo))) {
                unlink(public_path('uploads/agentbuysellpost/'.$post->photo));
            }

            $imageName = time().'.'.$request->photo->extension();
            $request->photo->move(public_path('uploads/agentbuysellpost'), $imageName);
            $post->photo = $imageName;
        }

        $post->update($request->except('photo'));

        return redirect()->route('agent.agentposts')
            ->with('success', 'Agent post updated successfully');
    }

    // 🔹 Delete post
    public function destroy($id)
    {
        $post = Agentbuysellpost::findOrFail($id);
        $post->delete();

        return redirect()->back()
            ->with('success', 'Agent post deleted successfully');
    }
}
