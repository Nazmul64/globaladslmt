<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Creaetpost;
use Illuminate\Http\Request;

class AdminUsersocialpostshow extends Controller
{
    public function usersocialpostshow()
    {
        $posts =Creaetpost::with('user')
            ->latest()
            ->get();

        return view('admin.usersocialpostshow.index', compact('posts'));
    }
    public function destroy($id)
{
    $post = Creaetpost::findOrFail($id);
    $post->delete(); // Soft delete

    return redirect()->back()->with('success', 'Post deleted successfully');
}
public function edit($id)
{
    $post = Creaetpost::findOrFail($id);
    return view('admin.usersocialpostshow.edit', compact('post'));
}

public function update(Request $request, $id)
{
    $post = Creaetpost::findOrFail($id);

    $request->validate([
        'content'   => 'nullable|string',
        'privacy'   => 'required|in:public,friends,only_me',
        'is_active' => 'required|boolean',
        'image'     => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,bmp,avif,jfif|max:10240',
    ], [
        'image.mimes' => 'Supported formats are JPG, JPEG, PNG, GIF, SVG, WEBP, BMP, AVIF, JFIF.',
        'image.max' => 'Image size cannot exceed 10MB.',
    ]);

    if ($request->hasFile('image')) {
        if ($post->image && file_exists(public_path('uploads/posts/'.$post->image))) {
            unlink(public_path('uploads/posts/'.$post->image));
        }

        $imageName = time().'.'.$request->image->extension();
        $request->image->move(public_path('uploads/posts'), $imageName);
        $post->image = $imageName;
    }

    $post->content   = $request->content;
    $post->privacy   = $request->privacy;
    $post->is_active = $request->is_active;
    $post->save();

    return redirect()->route('admin.usersocialposts')->with('success', 'Post updated successfully');
}
}
