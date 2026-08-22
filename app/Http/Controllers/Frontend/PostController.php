<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Creaetpost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    /**
     * Display all posts with users
     */
    public function index()
    {
        try {
            $posts = Creaetpost::with(['user', 'comments.user', 'likes'])
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->paginate(5);

            return view('frontend.post.index', compact('posts'));
        } catch (\Exception $e) {
            Log::error('Error loading posts: ' . $e->getMessage());
            return view('frontend.post.index')->with('posts', collect([]));
        }
    }

    /**
     * Store a new post
     */
    public function store(Request $request)
    {
        try {
            // Validation
            $request->validate([
                'content' => 'nullable|string|max:5000',
                'image'   => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
                'video'   => 'nullable|mimes:mp4,mov,avi,wmv,flv,mkv|max:51200', // 50MB max
                'privacy' => 'required|in:public,friends,only_me',
            ]);

            // Check if content, image or video exists
            if (empty($request->content) && !$request->hasFile('image') && !$request->hasFile('video')) {
                return redirect()->route('posts.index')
                    ->with('error', 'Please write something or add an image/video!');
            }

            // Prepare post data
            $postData = [
                'user_id' => Auth::id(),
                'content' => $request->content,
                'privacy' => $request->privacy ?? 'public',
                'is_active' => true,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $uploadPath = public_path('uploads/posts');

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $image->move($uploadPath, $imageName);
                $postData['image'] = 'uploads/posts/' . $imageName;
            }

            // Handle video upload
            if ($request->hasFile('video')) {
                $video = $request->file('video');
                $videoName = time() . '_' . uniqid() . '.' . $video->getClientOriginalExtension();
                $uploadPath = public_path('uploads/posts/videos');

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $video->move($uploadPath, $videoName);
                $postData['video'] = 'uploads/posts/videos/' . $videoName;
            }

            // Create post
            Creaetpost::create($postData);

            return redirect()->route('posts.index')
                ->with('success', 'Post created successfully! 🎉');

        } catch (\Exception $e) {
            Log::error('Post creation failed: ' . $e->getMessage());
            return redirect()->route('posts.index')
                ->with('error', 'Failed to create post!');
        }
    }

    /**
     * Display single post
     */
    public function show($id)
    {
        try {
            $post = Creaetpost::with(['user', 'comments.user', 'likes'])
                ->findOrFail($id);

            return view('frontend.post.show', compact('post'));
        } catch (\Exception $e) {
            return redirect()->route('posts.index')
                ->with('error', 'Post not found!');
        }
    }

    /**
     * Edit post
     */
    public function edit(Request $request, $id)
    {
        try {
            $post = Creaetpost::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            return view('frontend.post.edit', compact('post'));

        } catch (\Exception $e) {
            Log::error('Post edit load failed: ' . $e->getMessage());
            return redirect()->route('posts.index')
                ->with('error', 'Failed to load post for editing!');
        }
    }

    /**
     * Update post
     */
    public function update(Request $request, $id)
    {
        try {
            // Find post and verify ownership
            $post = Creaetpost::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Validation
            $request->validate([
                'content' => 'nullable|string|max:5000',
                'image'   => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
                'video'   => 'nullable|mimes:mp4,mov,avi,wmv,flv,mkv|max:51200',
                'privacy' => 'nullable|in:public,friends,only_me',
            ]);

            // Update content and privacy
            $post->content = $request->content;

            if ($request->has('privacy')) {
                $post->privacy = $request->privacy;
            }

            // Handle image removal
            if ($request->has('remove_image') && $request->remove_image) {
                if ($post->image && file_exists(public_path($post->image))) {
                    unlink(public_path($post->image));
                }
                $post->image = null;
            }

            // Handle video removal
            if ($request->has('remove_video') && $request->remove_video) {
                if ($post->video && file_exists(public_path($post->video))) {
                    unlink(public_path($post->video));
                }
                $post->video = null;
            }

            // Handle new image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($post->image && file_exists(public_path($post->image))) {
                    unlink(public_path($post->image));
                }

                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $uploadPath = public_path('uploads/posts');

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $image->move($uploadPath, $imageName);
                $post->image = 'uploads/posts/' . $imageName;
            }

            // Handle new video upload
            if ($request->hasFile('video')) {
                // Delete old video
                if ($post->video && file_exists(public_path($post->video))) {
                    unlink(public_path($post->video));
                }

                $video = $request->file('video');
                $videoName = time() . '_' . uniqid() . '.' . $video->getClientOriginalExtension();
                $uploadPath = public_path('uploads/posts/videos');

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $video->move($uploadPath, $videoName);
                $post->video = 'uploads/posts/videos/' . $videoName;
            }

            // Check if post has content, image or video
            if (empty($post->content) && empty($post->image) && empty($post->video)) {
                return back()->with('error', 'Post must have content, image or video!');
            }

            $post->save();

            return redirect()->route('posts.index')
                ->with('success', 'Post updated successfully! ✅');

        } catch (\Exception $e) {
            Log::error('Post update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update post!');
        }
    }

    /**
     * Delete post
     */
    public function destroy($id)
    {
        try {
            $post = Creaetpost::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Delete image if exists
            if ($post->image && file_exists(public_path($post->image))) {
                unlink(public_path($post->image));
            }

            // Delete video if exists
            if ($post->video && file_exists(public_path($post->video))) {
                unlink(public_path($post->video));
            }

            $post->delete();

            return redirect()->route('posts.index')
                ->with('success', 'Post deleted successfully! 🗑️');

        } catch (\Exception $e) {
            Log::error('Post deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete post!');
        }
    }

    /**
     * Toggle like/unlike post
     */
    public function toggleLike(Request $request, $id)
    {
        try {
            $post = Creaetpost::findOrFail($id);
            $userId = Auth::id();

            $like = $post->likes()->where('user_id', $userId)->first();

            if ($like) {
                // Unlike
                $like->delete();
                $post->decrement('likes_count');
                $liked = false;
            } else {
                // Like
                $post->likes()->create(['user_id' => $userId]);
                $post->increment('likes_count');
                $liked = true;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'liked' => $liked,
                    'likes_count' => $post->fresh()->likes_count,
                ]);
            }

            return back();

        } catch (\Exception $e) {
            Log::error('Like toggle failed: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to toggle like'
                ], 500);
            }

            return back();
        }
    }

    /**
     * Add comment to post
     */
    public function addComment(Request $request, $id)
    {
        try {
            $post = Creaetpost::findOrFail($id);

            $request->validate([
                'comment' => 'required|string|max:1000',
            ]);

            $comment = $post->comments()->create([
                'user_id' => Auth::id(),
                'comment' => $request->comment,
            ]);

            $post->increment('comments_count');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'comment' => $comment->load('user'),
                    'comments_count' => $post->fresh()->comments_count,
                ]);
            }

            return back()->with('success', 'Comment added!');

        } catch (\Exception $e) {
            Log::error('Comment creation failed: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add comment'
                ], 500);
            }

            return back()->with('error', 'Failed to add comment!');
        }
    }

    /**
     * Delete comment
     */
    public function deleteComment(Request $request, $postId, $commentId)
    {
        try {
            $post = Creaetpost::findOrFail($postId);

            $comment = $post->comments()
                ->where('id', $commentId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $comment->delete();
            $post->decrement('comments_count');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'comments_count' => $post->fresh()->comments_count,
                ]);
            }

            return back()->with('success', 'Comment deleted!');

        } catch (\Exception $e) {
            Log::error('Comment deletion failed: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete comment'
                ], 500);
            }

            return back()->with('error', 'Failed to delete comment!');
        }
    }

    /**
     * Share post
     */
    public function share(Request $request, $id)
    {
        try {
            $post = Creaetpost::findOrFail($id);

            $request->validate([
                'share_content' => 'nullable|string|max:500',
            ]);

            $post->shares()->create([
                'user_id' => Auth::id(),
                'share_content' => $request->share_content,
            ]);

            $post->increment('shares_count');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'shares_count' => $post->fresh()->shares_count,
                ]);
            }

            return back()->with('success', 'Post shared! 🔄');

        } catch (\Exception $e) {
            Log::error('Post share failed: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to share post'
                ], 500);
            }

            return back()->with('error', 'Failed to share post!');
        }
    }

    /**
     * My posts
     */
    public function myPosts()
    {
        try {
            $posts = Creaetpost::with(['user', 'comments.user', 'likes'])
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view('frontend.post.my-posts', compact('posts'));
        } catch (\Exception $e) {
            Log::error('Error loading my posts: ' . $e->getMessage());
            return view('frontend.post.my-posts')->with('posts', collect([]));
        }
    }

    /**
     * Search posts
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q');

            $posts = Creaetpost::with(['user', 'comments.user', 'likes'])
                ->where('is_active', true)
                ->where(function($q) use ($query) {
                    $q->where('content', 'LIKE', "%{$query}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view('frontend.post.search', compact('posts', 'query'));
        } catch (\Exception $e) {
            Log::error('Search failed: ' . $e->getMessage());
            return view('frontend.post.search')->with([
                'posts' => collect([]),
                'query' => $request->input('q')
            ]);
        }
    }
}
