@extends('frontend.master')

@section('content')
<div class="container">

    {{-- Create Post Box --}}
    <div class="post-trigger-container">
        <div class="post-create-box">
            <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="post-trigger-user align-items-center gap-2 mb-2">
                    <img src="{{ auth()->user()->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=d63384&color=fff&size=50' }}" class="post-trigger-avatar" alt="{{ auth()->user()->name }}" style="width:45px;height:45px;border-radius:50%;object-fit:cover;">
                    <div class="d-inline-flex align-items-center gap-2 mb-1">
                        <strong style="font-size:1rem;">{{ auth()->user()->name }}</strong>
                        @if(auth()->user()->is_verified)
                            <span class="badge bg-success text-white rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                <i class="fas fa-check-circle me-1"></i> Verified
                            </span>
                        @else
                            <span class="badge bg-secondary text-white rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                <i class="fas fa-info-circle me-1"></i> Unverified
                            </span>
                        @endif
                    </div>
                </div>
                <textarea name="content" class="post-create-textarea form-control" rows="2" placeholder="What's on your mind, {{ auth()->user()->name }}?"></textarea>

                <div class="post-create-options" id="postCreateOptions" style="display: none;">
                    {{-- Image Upload Only --}}
                    <div class="mb-3 mt-2">
                        <label class="form-label">Add Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*" id="postImage">
                        <div id="imagePreview" class="mt-2"></div>
                    </div>

                    <select name="privacy" class="form-select mb-3">
                        <option value="public">Public</option>
                        <option value="friends">Friends</option>
                        <option value="only_me">Only Me</option>
                    </select>
                    <button type="submit" class="btn btn-primary w-100">Post</button>
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
                    <div class="post-user-info d-flex align-items-center gap-2">
                        <img src="{{ $post->user->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($post->user->name ?? 'User') . '&background=d63384&color=fff&size=50' }}" class="post-user-avatar" alt="{{ $post->user->name ?? 'User' }}" style="width:42px;height:42px;border-radius:50%;object-fit:cover;">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="post-user-name mb-0" style="font-weight:600;">{{ $post->user->name ?? 'User' }}</h6>
                                @if($post->user->is_verified ?? false)
                                    <span class="badge bg-success text-white rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="fas fa-check-circle me-1"></i> Verified
                                    </span>
                                @else
                                    <span class="badge bg-secondary text-white rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="fas fa-info-circle me-1"></i> Unverified
                                    </span>
                                @endif
                            </div>
                            <small class="post-time text-muted">{{ $post->created_at->diffForHumans() }}</small>
                        </div>
                    </div>

                  @if(auth()->id() === $post->user_id)
    <div class="dropdown text-end">
        <button class="btn btn-light btn-sm border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-ellipsis-h"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow">
            <li>
                <button class="dropdown-item" type="button" onclick="toggleEditPost({{ $post->id }})">
                    <i class="fas fa-edit me-2"></i> Edit
                </button>
            </li>

            <li>
                <form action="{{ route('posts.destroy', $post->id) }}" method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this post?')" style="margin:0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-trash me-2"></i> Delete
                    </button>
                </form>
            </li>
        </ul>
    </div>
@endif

                </div>

                {{-- Post Content (View Mode) --}}
                <div id="post-content-{{ $post->id }}">
                    @if($post->content)
                        <div class="post-content">{{ nl2br(e($post->content)) }}</div>
                    @endif

                    {{-- Post Image Only --}}
                    @if($post->image)
                        <div class="post-image">
                            <img src="{{ asset($post->image) }}" alt="Post Image" class="img-fluid">
                        </div>
                    @endif
                </div>

                {{-- Edit Post Form --}}
                <div class="post-edit-form" id="edit-form-{{ $post->id }}" style="display: none;">
                    <form action="{{ route('posts.update', $post->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <textarea name="content" class="form-control mb-3" rows="4">{{ $post->content }}</textarea>

                        {{-- Current Image --}}
                        @if($post->image)
                            <div class="current-media mb-3">
                                <label class="form-label">Current Image:</label>
                                <img src="{{ asset($post->image) }}" class="img-fluid" style="max-height:200px;border-radius:8px;">
                                <div class="form-check mt-2">
                                    <input type="checkbox" name="remove_image" value="1" class="form-check-input" id="removeImage{{ $post->id }}">
                                    <label class="form-check-label" for="removeImage{{ $post->id }}">Remove this image</label>
                                </div>
                            </div>
                        @endif

                        {{-- New Image Upload --}}
                        <div class="mb-3">
                            <label class="form-label">Upload new image (optional)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>

                        <select name="privacy" class="form-select mb-3">
                            <option value="public" {{ $post->privacy == 'public' ? 'selected' : '' }}>Public</option>
                            <option value="friends" {{ $post->privacy == 'friends' ? 'selected' : '' }}>Friends</option>
                            <option value="only_me" {{ $post->privacy == 'only_me' ? 'selected' : '' }}>Only Me</option>
                        </select>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">Update Post</button>
                            <button type="button" class="btn btn-secondary" onclick="toggleEditPost({{ $post->id }})">Cancel</button>
                        </div>
                    </form>
                </div>

                {{-- Post Stats --}}
                <div class="post-stats">
                    <span class="likes-count">{{ $post->likes_count ?? 0 }} Likes</span>
                    <span class="comments-count">{{ $post->comments_count ?? 0 }} Comments</span>
                </div>

                {{-- Post Actions --}}
                <div class="post-actions-bar">
                    <button class="action-btn like-btn {{ $post->isLikedByUser() ? 'active' : '' }}" data-post-id="{{ $post->id }}">
                        <i class="fas fa-thumbs-up"></i> <span class="like-text">Like</span>
                    </button>
                    <button class="action-btn comment-btn" onclick="toggleComments({{ $post->id }})">
                        <i class="fas fa-comment"></i> <span>Comment</span>
                    </button>
                </div>

                {{-- Comments Section --}}
                <div class="comments-section" id="comments-{{ $post->id }}" style="display: none;">
                    <div class="comment-form">
                        <form class="comment-submit-form" data-post-id="{{ $post->id }}">
                            @csrf
                            <input type="text" class="comment-input" placeholder="Write a comment..." required>
                            <button type="submit" class="comment-submit-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>

                    <div class="comments-list" id="comments-list-{{ $post->id }}">
                        @foreach($post->comments as $comment)
                            <div class="comment-item" id="comment-{{ $comment->id }}">
                                <img src="{{ $comment->user->profile_photo ? asset('storage/' . $comment->user->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name) }}"
                                     class="comment-avatar" alt="{{ $comment->user->name }}">
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



<script>
document.addEventListener('DOMContentLoaded', function () {
    // Show create post options on focus
    const textarea = document.querySelector('.post-create-textarea');
    if (textarea) {
        textarea.addEventListener('focus', function () {
            document.getElementById('postCreateOptions').style.display = 'block';
            this.rows = 4;
        });
    }

    // Image preview (create post)
    const imageInput = document.getElementById('postImage');
    if (imageInput) {
        imageInput.addEventListener('change', function (e) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function (ev) {
                    preview.innerHTML = `<img src="${ev.target.result}" alt="Preview">`;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }

    // Like functionality
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            if (this.classList.contains('loading')) return;
            const postId = this.dataset.postId;
            const likeText = this.querySelector('.like-text');
            const original = likeText.textContent;

            this.classList.add('loading');
            likeText.innerHTML = '<span class="spinner"></span>';

            try {
                const res = await fetch(`/posts/${postId}/like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.classList.toggle('active');
                    document.querySelector(`#post-${postId} .likes-count`).textContent = data.likes_count + ' Likes';
                }
            } catch (e) { console.error(e); }
            finally {
                this.classList.remove('loading');
                likeText.textContent = original;
            }
        });
    });

    // Comment submit
    document.querySelectorAll('.comment-submit-form').forEach(form => {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const postId = this.dataset.postId;
            const input = this.querySelector('.comment-input');
            const btn = this.querySelector('.comment-submit-btn');
            const comment = input.value.trim();
            if (!comment) return;

            input.disabled = btn.disabled = true;
            btn.classList.add('loading');

            try {
                const res = await fetch(`/posts/${postId}/comment`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ comment })
                });
                const data = await res.json();
                if (data.success) {
                    input.value = '';
                    const list = document.getElementById(`comments-list-${postId}`);
                    const div = document.createElement('div');
                    div.className = 'comment-item';
                    div.id = `comment-${data.comment.id}`;
                    div.innerHTML = `
                        <img src="${data.comment.user.profile_photo ? '/storage/' + data.comment.user.profile_photo : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(data.comment.user.name)}" class="comment-avatar" alt="${data.comment.user.name}">
                        <div class="comment-content">
                            <div class="comment-header"><strong>${data.comment.user.name}</strong>
                                <button class="delete-comment-btn" onclick="deleteComment(${postId}, ${data.comment.id})"><i class="fas fa-times"></i></button>
                            </div>
                            <p>${data.comment.comment}</p>
                            <small>Just now</small>
                        </div>`;
                    list.insertBefore(div, list.firstChild);

                    document.querySelector(`#post-${postId} .comments-count`).textContent = data.comments_count + ' Comments';
                }
            } catch (e) {
                console.error(e);
                alert('Failed to post comment.');
            } finally {
                input.disabled = btn.disabled = false;
                btn.classList.remove('loading');
            }
        });
    });
});

// Toggle functions
function toggleEditPost(id) {
    const content = document.getElementById('post-content-' + id);
    const form = document.getElementById('edit-form-' + id);
    content.style.display = content.style.display === 'none' ? 'block' : 'none';
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
function toggleComments(id) {
    const sec = document.getElementById('comments-' + id);
    sec.style.display = sec.style.display === 'none' ? 'block' : 'none';
}
function toggleSharePost(id) {
    const sec = document.getElementById('share-form-' + id);
    sec.style.display = sec.style.display === 'none' ? 'block' : 'none';
}
async function deleteComment(postId, commentId) {
    if (!confirm('Delete this comment?')) return;
    try {
        const res = await fetch(`/posts/${postId}/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('comment-' + commentId).remove();
            document.querySelector(`#post-${postId} .comments-count`).textContent = data.comments_count + ' Comments';
        }
    } catch (e) {
        console.error(e);
        alert('Failed to delete comment.');
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

@endsection
