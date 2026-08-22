@extends('admin.master')

@section('content')
<div class="container mt-4">

    <h4 class="mb-3">User Social Posts</h4>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Content</th>
                <th>Image</th>
                <th>Privacy</th>
                <th>Status</th>
                <th>Likes</th>
                <th>Comments</th>
                <th>Shares</th>
                <th>Created</th>
                <th width="150">Action</th>
            </tr>
        </thead>

        <tbody>
        @forelse ($posts as $key => $post)
            <tr>
                <td>{{ $key + 1 }}</td>

                <td>
                    {{ $post->user->name ?? 'N/A' }}
                </td>

                <td style="max-width:220px;">
                    {{ \Illuminate\Support\Str::limit($post->content, 50) }}
                </td>

                <td>
                    @if($post->image && file_exists(public_path($post->image)))
                        <img src="{{ asset($post->image) }}" width="60" class="rounded">
                    @else
                        <span class="text-muted">No Image</span>
                    @endif
                </td>

                <td>
                    <span class="badge bg-info">
                        {{ ucfirst($post->privacy) }}
                    </span>
                </td>

                <td>
                    @if($post->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </td>

                <td>{{ $post->likes_count }}</td>
                <td>{{ $post->comments_count }}</td>
                <td>{{ $post->shares_count }}</td>

                <td>
                    {{ $post->created_at->format('d M Y') }}
                </td>

                <td>
                    {{-- Edit --}}
                    <a href="{{ route('admin.usersocialposts.edit', $post->id) }}"
                       class="btn btn-sm btn-primary mb-1 w-100">
                        Edit
                    </a>

                    {{-- Delete --}}
                    <form action="{{ route('admin.usersocialposts.delete', $post->id) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this post?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger w-100">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center text-muted">
                    No posts found
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
