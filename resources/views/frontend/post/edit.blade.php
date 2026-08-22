@extends('frontend.master')

@section('content')
<div class="container">
    {{-- Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Create Post Box --}}
    <div class="post-trigger-container">
        <div class="post-create-box">
            <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" id="createPostForm">
                @csrf
                <div class="post-trigger-user">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" class="post-trigger-avatar" alt="{{ auth()->user()->name }}">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=667eea&color=fff&size=50" class="post-trigger-avatar" alt="{{ auth()->user()->name }}">
                    @endif
                    <textarea name="content" class="post-create-textarea" rows="2" placeholder="What's on your mind, {{ auth()->user()->name }}?" id="createPostTextarea"></textarea>
                </div>

                <div class="post-create-options" id="postCreateOptions" style="display: none;">
                    <div class="mb-3">
                        <input type="file" name="image" class="form-control" accept="image/*" id="postImage">
                        <div id="imagePreview" class="mt-2"></div>
                    </div>
                    <select name="privacy" class="form-select mb-3">
                        <option value="public">🌍 Public</option>
                        <option value="friends">👥 Friends</option>
                        <option value="only_me">🔒 Only Me</option>
                    </select>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">Post</button>
                        <button type="button" class="btn btn-secondary" onclick="cancelCreatePost()">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Posts List --}}
    <div class="posts-list mt-4">
        @forelse($posts as $post)
            <div class="post-card mb-3" id="post-{{ $post->id }}">
                {{-- Post Header --}}
                <div class="post-header">
                    <div class="post-user-info">
                        @if($post->user->profile_photo)
                            <img src="{{ asset('storage/' . $post->user->profile_photo) }}" class="post-user-avatar" alt="{{ $post->user->name }}">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($post->user->name) }}&background=667eea&color=fff&size=50" class="post-user-avatar" alt="{{ $post->user->name }}">
                        @endif
                        <div>
                            <h6 class="post-user-name">{{ $post->user->name }}</h6>
                            <small class="post-time">{{ $post->created_at->diffForHumans() }}</small>
                        </div>
                    </div>

                    @if(auth()->id() === $post->user_id)
                        <div class="dropdown">
                            <button class="post-menu-btn" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="#" onclick="toggleEditPost({{ $post->id }}); return false;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    <form action="{{ route('posts.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Delete this post?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endif
                </div>

                {{-- Post Content (Display Mode) --}}
                <div id="post-content-{{ $post->id }}">
                    @if($post->content)
                        <div class="post-content">{{ $post->content }}</div>
                    @endif

                    {{-- Post Image --}}
                    @if($post->image)
                        <div class="post-image">
                            <img src="{{ asset($post->image) }}" alt="Post Image">
                        </div>
                    @endif
                </div>

                {{-- Edit Post Form (Hidden by default) --}}
                <div class="post-edit-form" id="edit-form-{{ $post->id }}" style="display: none;">
                    <form action="{{ route('posts.update', $post->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Post Content</label>
                            <textarea name="content" class="form-control" rows="4" placeholder="What's on your mind?">{{ $post->content }}</textarea>
                        </div>

                        @if($post->image)
                            <div class="mb-3">
                                <label class="form-label">Current Image</label>
                                <div class="current-image-wrapper">
                                    <img src="{{ asset($post->image) }}" class="img-fluid" style="max-height: 200px; border-radius: 8px;">
                                </div>
                                <div class="form-check mt-2">
                                    <input type="checkbox" name="remove_image" value="1" class="form-check-input" id="removeImage{{ $post->id }}">
                                    <label class="form-check-label" for="removeImage{{ $post->id }}">
                                        <i class="fas fa-trash text-danger"></i> Remove this image
                                    </label>
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">{{ $post->image ? 'Replace with new image' : 'Add an image' }}</label>
                            <input type="file" name="image" class="form-control" accept="image/*" id="editImage{{ $post->id }}" onchange="previewEditImage({{ $post->id }}, this)">
                            <div id="editImagePreview{{ $post->id }}" class="mt-2"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Privacy</label>
                            <select name="privacy" class="form-select">
                                <option value="public" {{ $post->privacy == 'public' ? 'selected' : '' }}>🌍 Public</option>
                                <option value="friends" {{ $post->privacy == 'friends' ? 'selected' : '' }}>👥 Friends</option>
                                <option value="only_me" {{ $post->privacy == 'only_me' ? 'selected' : '' }}>🔒 Only Me</option>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-check"></i> Update Post
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleEditPost({{ $post->id }})">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Post Stats --}}
                <div class="post-stats">
                    <span class="likes-count">{{ $post->likes_count }} Likes</span>
                    <span class="comments-count">{{ $post->comments_count }} Comments</span>
                </div>

                {{-- Post Actions --}}
                <div class="post-actions-bar">
                    <button class="action-btn like-btn {{ $post->isLikedByUser() ? 'active' : '' }}" data-post-id="{{ $post->id }}">
                        <i class="fas fa-thumbs-up"></i> <span class="like-text">Like</span>
                    </button>
                    <button class="action-btn comment-btn" onclick="toggleComments({{ $post->id }})">
                        <i class="fas fa-comment"></i> Comment
                    </button>
                    <button class="action-btn share-btn" onclick="toggleSharePost({{ $post->id }})">
                        <i class="fas fa-share"></i> Share
                    </button>
                </div>

                {{-- Share Post Form (Hidden by default) --}}
                <div class="post-share-form" id="share-form-{{ $post->id }}" style="display: none;">
                    <form action="{{ route('posts.share', $post->id) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small">Say something about this post...</label>
                            <textarea name="share_content" class="form-control" rows="3" placeholder="Add your thoughts..."></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-share"></i> Share Now
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleSharePost({{ $post->id }})">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Comments Section --}}
                <div class="comments-section" id="comments-{{ $post->id }}" style="display: none;">
                    {{-- Comment Form --}}
                    <div class="comment-form">
                        <form class="comment-submit-form" data-post-id="{{ $post->id }}">
                            @csrf
                            <input type="text" class="comment-input" placeholder="Write a comment..." required>
                            <button type="submit" class="comment-submit-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>

                    {{-- Comments List --}}
                    <div class="comments-list" id="comments-list-{{ $post->id }}">
                        @foreach($post->comments as $comment)
                            <div class="comment-item" id="comment-{{ $comment->id }}">
                                <img src="{{ $comment->user->profile_photo ? asset('storage/' . $comment->user->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name) }}" class="comment-avatar" alt="{{ $comment->user->name }}">
                                <div class="comment-content">
                                    <div class="comment-header">
                                        <strong>{{ $comment->user->name }}</strong>
                                        @if(auth()->id() === $comment->user_id)
                                            <button class="delete-comment-btn" onclick="deleteComment({{ $post->id }}, {{ $comment->id }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                    <p>{{ $comment->comment }}</p>
                                    <small>{{ $comment->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                <p class="text-muted">No posts yet. Create your first post!</p>
            </div>
        @endforelse

        @if($posts->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>

<style>
.post-trigger-container, .posts-list {
    max-width: 600px;
    margin: 20px auto;
}

.post-create-box {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 15px;
}

.post-trigger-user {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.post-trigger-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.post-create-textarea {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid #e4e6eb;
    background: #f0f2f5;
    border-radius: 12px;
    outline: none;
    resize: none;
    font-family: inherit;
    font-size: 15px;
    transition: all 0.2s;
}

.post-create-textarea:focus {
    background: white;
    border-color: #1877f2;
    box-shadow: 0 0 0 2px rgba(24, 119, 242, 0.1);
}

.post-create-options {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e4e6eb;
}

#imagePreview img {
    max-width: 100%;
    border-radius: 8px;
    margin-top: 10px;
}

.post-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.post-header {
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.post-user-info {
    display: flex;
    gap: 10px;
    align-items: center;
}

.post-user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.post-user-name {
    margin: 0;
    font-weight: 600;
    font-size: 15px;
}

.post-time {
    color: #65676b;
    font-size: 13px;
}

.post-menu-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: background 0.2s;
}

.post-menu-btn:hover {
    background: #f0f2f5;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 8px;
}

.dropdown-item {
    padding: 10px 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.post-content {
    padding: 0 15px 15px;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.post-image {
    width: 100%;
}

.post-image img {
    width: 100%;
    height: auto;
    display: block;
}

.post-edit-form {
    padding: 20px;
    background: #f7f8fa;
    border-top: 1px solid #e4e6eb;
}

.post-edit-form .form-label {
    font-weight: 600;
    font-size: 14px;
    color: #050505;
    margin-bottom: 8px;
}

.current-image-wrapper {
    position: relative;
    display: inline-block;
}

#editImagePreview img {
    max-width: 100%;
    border-radius: 8px;
    margin-top: 10px;
}

.post-share-form {
    padding: 15px;
    background: #f7f8fa;
    border-top: 1px solid #e4e6eb;
}

.post-stats {
    padding: 10px 15px;
    display: flex;
    gap: 20px;
    color: #65676b;
    font-size: 14px;
    border-top: 1px solid #e4e6eb;
}

.post-actions-bar {
    padding: 8px 15px;
    display: flex;
    gap: 10px;
    border-top: 1px solid #e4e6eb;
}

.action-btn {
    flex: 1;
    padding: 8px;
    background: transparent;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
    position: relative;
}

.action-btn:hover {
    background: #f0f2f5;
}

.action-btn.active {
    color: #1877f2;
}

.action-btn.loading {
    pointer-events: none;
    opacity: 0.6;
}

.action-btn .spinner {
    display: inline-block;
    width: 12px;
    height: 12px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #1877f2;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
    margin-left: 5px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.comments-section {
    padding: 15px;
    background: #f7f8fa;
    border-top: 1px solid #e4e6eb;
}

.comment-form {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
    position: relative;
}

.comment-input {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid #ccd0d5;
    border-radius: 20px;
    outline: none;
    font-size: 14px;
}

.comment-input:focus {
    border-color: #1877f2;
}

.comment-input:disabled {
    background: #f0f2f5;
    cursor: not-allowed;
}

.comment-submit-btn {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #1877f2;
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.comment-submit-btn:hover:not(:disabled) {
    background: #166fe5;
    transform: scale(1.05);
}

.comment-submit-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.comment-submit-btn.loading {
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

.comment-item {
    display: flex;
    gap: 10px;
    margin-bottom: 12px;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.comment-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.comment-content {
    flex: 1;
    background: white;
    padding: 10px 12px;
    border-radius: 12px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.comment-header strong {
    font-size: 14px;
    font-weight: 600;
}

.delete-comment-btn {
    background: transparent;
    border: none;
    color: #65676b;
    cursor: pointer;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
}

.delete-comment-btn:hover {
    background: #f0f2f5;
    color: #e4294f;
}

.comment-content p {
    margin: 0 0 5px 0;
    font-size: 14px;
    line-height: 1.4;
}

.comment-content small {
    color: #65676b;
    font-size: 12px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .post-trigger-container, .posts-list {
        margin-left: 5px;
        margin-right: 5px;
    }

    .action-btn span {
        display: none;
    }

    .post-edit-form {
        padding: 15px;
    }
}
</style>

<script>
// Show/hide create post options when textarea is focused
document.getElementById('createPostTextarea').addEventListener('focus', function() {
    document.getElementById('postCreateOptions').style.display = 'block';
    this.rows = 4;
});

// Cancel create post
function cancelCreatePost() {
    const form = document.getElementById('createPostForm');
    const textarea = document.getElementById('createPostTextarea');
    const options = document.getElementById('postCreateOptions');
    const preview = document.getElementById('imagePreview');

    form.reset();
    textarea.rows = 2;
    options.style.display = 'none';
    preview.innerHTML = '';
}

// Image preview for create post
document.getElementById('postImage').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';

    if(e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});

// Preview edit image
function previewEditImage(postId, input) {
    const preview = document.getElementById('editImagePreview' + postId);
    preview.innerHTML = '';

    if(input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="New Image Preview">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Toggle edit post form
function toggleEditPost(postId) {
    const content = document.getElementById('post-content-' + postId);
    const form = document.getElementById('edit-form-' + postId);

    if(form.style.display === 'none') {
        content.style.display = 'none';
        form.style.display = 'block';
        // Scroll to form
        form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        content.style.display = 'block';
        form.style.display = 'none';
    }
}

// Toggle share post form
function toggleSharePost(postId) {
    const form = document.getElementById('share-form-' + postId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

// Toggle comments section
function toggleComments(postId) {
    const commentsSection = document.getElementById('comments-' + postId);
    commentsSection.style.display = commentsSection.style.display === 'none' ? 'block' : 'none';
}

// Like post with loading state
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (this.classList.contains('loading')) return;

        const postId = this.dataset.postId;
        const likeText = this.querySelector('.like-text');
        const originalText = likeText.textContent;

        // Show loading
        this.classList.add('loading');
        likeText.innerHTML = '<span class="spinner"></span>';

        try {
            const response = await fetch(`/posts/${postId}/like`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if(data.success) {
                this.classList.toggle('active');
                const likesCount = document.querySelector(`#post-${postId} .likes-count`);
                likesCount.textContent = data.likes_count + ' Likes';
            }
        } catch(error) {
            console.error('Error:', error);
        } finally {
            // Remove loading
            this.classList.remove('loading');
            likeText.textContent = originalText;
        }
    });
});

// Submit comment with loading state
document.querySelectorAll('.comment-submit-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const postId = this.dataset.postId;
        const input = this.querySelector('.comment-input');
        const submitBtn = this.querySelector('.comment-submit-btn');
        const comment = input.value.trim();

        if(!comment) return;

        // Show loading state
        input.disabled = true;
        submitBtn.disabled = true;
        submitBtn.classList.add('loading');

        try {
            const response = await fetch(`/posts/${postId}/comment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ comment })
            });

            const data = await response.json();

            if(data.success) {
                // Clear input
                input.value = '';

                // Add new comment to list
                const commentsList = document.getElementById(`comments-list-${postId}`);
                const newComment = document.createElement('div');
                newComment.className = 'comment-item';
                newComment.id = `comment-${data.comment.id}`;
                newComment.innerHTML = `
                    <img src="${data.comment.user.profile_photo ? '/storage/' + data.comment.user.profile_photo : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(data.comment.user.name)}" class="comment-avatar" alt="${data.comment.user.name}">
                    <div class="comment-content">
                        <div class="comment-header">
                            <strong>${data.comment.user.name}</strong>
                            <button class="delete-comment-btn" onclick="deleteComment(${postId}, ${data.comment.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <p>${data.comment.comment}</p>
                        <small>Just now</small>
                    </div>
                `;
                commentsList.insertBefore(newComment, commentsList.firstChild);

                // Update comments count
                const commentsCount = document.querySelector(`#post-${postId} .comments-count`);
                commentsCount.textContent = data.comments_count + ' Comments';
            }
        } catch(error) {
            console.error('Error:', error);
            alert('Failed to post comment. Please try again.');
        } finally {
            // Remove loading state
            input.disabled = false;
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
        }
    });
});

// Delete comment
async function deleteComment(postId, commentId) {
    if(!confirm('Delete this comment?')) return;

    const commentElement = document.getElementById(`comment-${commentId}`);

    // Add deleting animation
    commentElement.style.opacity = '0.5';

    try {
        const response = await fetch(`/posts/${postId}/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if(data.success) {
            // Remove comment with animation
            commentElement.style.transform = 'translateX(-100%)';
            setTimeout(() => {
                commentElement.remove();
            }, 300);

            // Update count
            const commentsCount = document.querySelector(`#post-${postId} .comments-count`);
            commentsCount.textContent = data.comments_count + ' Comments';
        }
    } catch(error) {
        console.error('Error:', error);
        commentElement.style.opacity = '1';
        alert('Failed to delete comment.');
    }
}

// Auto-hide alerts
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>

@endsection
