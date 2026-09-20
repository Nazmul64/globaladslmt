<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Creaetpost;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\PostShare;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Helper method to add is_liked to post
     */
    private function addIsLikedToPost($post)
    {
        $userId = Auth::id();
        if ($post->relationLoaded('likes')) {
            $post->is_liked = $post->likes->where('user_id', $userId)->isNotEmpty();
        } else {
            $post->is_liked = $post->likes()->where('user_id', $userId)->exists();
        }
        return $post;
    }

    /**
     * Helper method to add is_liked to posts collection
     */
    private function addIsLikedToPosts($posts)
    {
        $userId = Auth::id();

        foreach ($posts as $post) {
            if ($post->relationLoaded('likes')) {
                $post->is_liked = $post->likes->where('user_id', $userId)->isNotEmpty();
            } else {
                $post->is_liked = $post->likes()->where('user_id', $userId)->exists();
            }
        }

        return $posts;
    }

    /**
     * GET ALL POSTS
     */
    public function index(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);

            $posts = Creaetpost::with(['user:id,name,email,photo,role', 'comments.user:id,name,email,photo,role', 'likes:id,post_id,user_id'])
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Add is_liked to each post
            $posts->getCollection()->transform(function ($post) {
                return $this->addIsLikedToPost($post);
            });

            return response()->json([
                'success' => true,
                'message' => 'Posts retrieved successfully',
                'data' => [
                    'posts' => $posts->items(),
                    'pagination' => [
                        'current_page' => $posts->currentPage(),
                        'last_page' => $posts->lastPage(),
                        'per_page' => $posts->perPage(),
                        'total' => $posts->total(),
                        'from' => $posts->firstItem(),
                        'to' => $posts->lastItem(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error loading posts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new post
     * POST /api/posts
     */
    public function store(Request $request)
    {
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'content' => 'nullable|string|max:5000',
                'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
                'video' => 'nullable|mimes:mp4,mov,avi,wmv,flv,mkv|max:51200',
                'privacy' => 'required|in:public,friends,only_me',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if content, image or video exists
            if (empty($request->content) && !$request->hasFile('image') && !$request->hasFile('video')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please write something or add an image/video!'
                ], 400);
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
            $post = Creaetpost::create($postData);
            $post->load(['user', 'comments.user', 'likes']);

            // Send notification to user's friends
            try {
                $user = Auth::user();
                if ($user && !empty($user->friends)) {
                    foreach ($user->friends as $friend) {
                        PushNotificationService::send(
                            $friend->id,
                            "নতুন পোস্ট",
                            "{$user->name} একটি নতুন পোস্ট করেছে",
                            "new_post",
                            ['post_id' => $post->id, 'user_id' => $user->id]
                        );
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Post notification error: ' . $e->getMessage());
            }

            // Add is_liked
            $post = $this->addIsLikedToPost($post);

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully! 🎉',
                'data' => [
                    'post' => $post
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Post creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create post!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display single post
     * GET /api/posts/{id}
     */
    public function show($id)
    {
        try {
            $post = Creaetpost::with(['user', 'comments.user', 'likes'])
                ->findOrFail($id);

            // Add is_liked
            $post = $this->addIsLikedToPost($post);

            return response()->json([
                'success' => true,
                'message' => 'Post retrieved successfully',
                'data' => [
                    'post' => $post
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Post not found: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Post not found!',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update post
     * POST /api/posts/{id}
     * (Use POST with _method=PUT for file uploads)
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info('📝 Post Update Request:', [
                'post_id' => $id,
                'user_id' => Auth::id(),
                'content' => $request->content,
                'privacy' => $request->privacy,
                'has_image' => $request->hasFile('image'),
                'has_video' => $request->hasFile('video'),
                'remove_image' => $request->input('remove_image'),
                'remove_video' => $request->input('remove_video'),
            ]);

            // Find post and verify ownership
            $post = Creaetpost::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$post) {
                Log::error('Post not found or unauthorized', [
                    'post_id' => $id,
                    'user_id' => Auth::id()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Post not found or you are not authorized to edit this post!'
                ], 404);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'content' => 'nullable|string|max:5000',
                'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
                'video' => 'nullable|mimes:mp4,mov,avi,wmv,flv,mkv|max:51200',
                'privacy' => 'nullable|in:public,friends,only_me',
                'remove_image' => 'nullable',
                'remove_video' => 'nullable',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update content
            if ($request->has('content')) {
                $post->content = $request->content;
                Log::info('Content updated');
            }

            // Update privacy
            if ($request->has('privacy')) {
                $post->privacy = $request->privacy;
                Log::info('Privacy updated to: ' . $request->privacy);
            }

            // Handle image removal
            if ($request->input('remove_image') == '1' || $request->input('remove_image') === true) {
                if ($post->image && file_exists(public_path($post->image))) {
                    unlink(public_path($post->image));
                    Log::info('Old image deleted');
                }
                $post->image = null;
                Log::info('Image removed');
            }

            // Handle video removal
            if ($request->input('remove_video') == '1' || $request->input('remove_video') === true) {
                if ($post->video && file_exists(public_path($post->video))) {
                    unlink(public_path($post->video));
                    Log::info('Old video deleted');
                }
                $post->video = null;
                Log::info('Video removed');
            }

            // Handle new image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($post->image && file_exists(public_path($post->image))) {
                    unlink(public_path($post->image));
                    Log::info('Old image deleted before new upload');
                }

                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $uploadPath = public_path('uploads/posts');

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $image->move($uploadPath, $imageName);
                $post->image = 'uploads/posts/' . $imageName;
                Log::info('New image uploaded: ' . $post->image);
            }

            // Handle new video upload
            if ($request->hasFile('video')) {
                // Delete old video
                if ($post->video && file_exists(public_path($post->video))) {
                    unlink(public_path($post->video));
                    Log::info('Old video deleted before new upload');
                }

                $video = $request->file('video');
                $videoName = time() . '_' . uniqid() . '.' . $video->getClientOriginalExtension();
                $uploadPath = public_path('uploads/posts/videos');

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $video->move($uploadPath, $videoName);
                $post->video = 'uploads/posts/videos/' . $videoName;
                Log::info('New video uploaded: ' . $post->video);
            }

            // Check if post has content, image or video
            if (empty($post->content) && empty($post->image) && empty($post->video)) {
                Log::error('Post validation failed: No content');
                return response()->json([
                    'success' => false,
                    'message' => 'Post must have content, image or video!'
                ], 400);
            }

            $post->save();
            $post->load(['user', 'comments.user', 'likes']);

            // Add is_liked
            $post = $this->addIsLikedToPost($post);

            Log::info('✅ Post updated successfully', ['post_id' => $post->id]);

            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully! ✅',
                'data' => [
                    'post' => $post
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Post update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update post!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete post
     * DELETE /api/posts/{id}
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

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully! 🗑️'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Post deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete post!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle like/unlike post
     * POST /api/posts/{id}/like
     */
    public function toggleLike($id)
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
                $message = 'Post unliked';
            } else {
                // Like
                $post->likes()->create(['user_id' => $userId]);
                $post->increment('likes_count');
                $liked = true;
                $message = 'Post liked';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'liked' => $liked,
                    'likes_count' => $post->fresh()->likes_count
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Like toggle failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle like',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add comment to post
     * POST /api/posts/{id}/comments
     */
    public function addComment(Request $request, $id)
    {
        try {
            $post = Creaetpost::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'comment' => 'required|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $comment = $post->comments()->create([
                'user_id' => Auth::id(),
                'comment' => $request->comment,
            ]);

            $post->increment('comments_count');
            $comment->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully!',
                'data' => [
                    'comment' => $comment,
                    'comments_count' => $post->fresh()->comments_count
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Comment creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all comments for a post
     * GET /api/posts/{id}/comments
     */
    public function getComments(Request $request, $id)
    {
        try {
            $post = Creaetpost::findOrFail($id);

            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 20);

            $comments = $post->comments()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Comments retrieved successfully',
                'data' => [
                    'comments' => $comments->items(),
                    'pagination' => [
                        'current_page' => $comments->currentPage(),
                        'last_page' => $comments->lastPage(),
                        'per_page' => $comments->perPage(),
                        'total' => $comments->total(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get comments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get comments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete comment
     * DELETE /api/posts/{postId}/comments/{commentId}
     */
    public function deleteComment($postId, $commentId)
    {
        try {
            $post = Creaetpost::findOrFail($postId);

            $comment = $post->comments()
                ->where('id', $commentId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $comment->delete();
            $post->decrement('comments_count');

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully!',
                'data' => [
                    'comments_count' => $post->fresh()->comments_count
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Comment deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Share post
     * POST /api/posts/{id}/share
     */
    public function share(Request $request, $id)
    {
        try {
            $post = Creaetpost::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'share_content' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $share = $post->shares()->create([
                'user_id' => Auth::id(),
                'share_content' => $request->share_content,
            ]);

            $post->increment('shares_count');

            return response()->json([
                'success' => true,
                'message' => 'Post shared successfully! 🔄',
                'data' => [
                    'share' => $share,
                    'shares_count' => $post->fresh()->shares_count
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Post share failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to share post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * My posts
     * GET /api/posts/my-posts
     */
    public function myPosts(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);

            $posts = Creaetpost::with(['user:id,name,email,photo,role', 'comments.user:id,name,email,photo,role', 'likes:id,post_id,user_id'])
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Add is_liked to each post
            $posts->getCollection()->transform(function ($post) {
                return $this->addIsLikedToPost($post);
            });

            return response()->json([
                'success' => true,
                'message' => 'My posts retrieved successfully',
                'data' => [
                    'posts' => $posts->items(),
                    'pagination' => [
                        'current_page' => $posts->currentPage(),
                        'last_page' => $posts->lastPage(),
                        'per_page' => $posts->perPage(),
                        'total' => $posts->total(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error loading my posts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load my posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search posts
     * GET /api/posts/search?q=query
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q');
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);

            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query is required'
                ], 400);
            }

            $posts = Creaetpost::with(['user:id,name,email,photo,role', 'comments.user:id,name,email,photo,role', 'likes:id,post_id,user_id'])
                ->where('is_active', true)
                ->where(function($q) use ($query) {
                    $q->where('content', 'LIKE', "%{$query}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Add is_liked to each post
            $posts->getCollection()->transform(function ($post) {
                return $this->addIsLikedToPost($post);
            });

            return response()->json([
                'success' => true,
                'message' => 'Search results retrieved successfully',
                'data' => [
                    'query' => $query,
                    'posts' => $posts->items(),
                    'pagination' => [
                        'current_page' => $posts->currentPage(),
                        'last_page' => $posts->lastPage(),
                        'per_page' => $posts->perPage(),
                        'total' => $posts->total(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Search failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get post likes
     * GET /api/posts/{id}/likes
     */
    public function getLikes(Request $request, $id)
    {
        try {
            $post = Creaetpost::findOrFail($id);

            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 20);

            $likes = $post->likes()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Likes retrieved successfully',
                'data' => [
                    'likes' => $likes->items(),
                    'pagination' => [
                        'current_page' => $likes->currentPage(),
                        'last_page' => $likes->lastPage(),
                        'per_page' => $likes->perPage(),
                        'total' => $likes->total(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get likes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get likes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
