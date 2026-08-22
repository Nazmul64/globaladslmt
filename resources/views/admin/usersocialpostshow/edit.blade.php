@extends('admin.master')

@section('content')
<div class="container mt-4">
    <h4>Edit Post</h4>

    <form action="{{ route('admin.usersocialposts.update', $post->id) }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Content</label>
            <textarea name="content" class="form-control" rows="4">{{ $post->content }}</textarea>
        </div>

        <div class="mb-3">
            <label>Privacy</label>
            <select name="privacy" class="form-control">
                <option value="public" {{ $post->privacy=='public'?'selected':'' }}>Public</option>
                <option value="friends" {{ $post->privacy=='friends'?'selected':'' }}>Friends</option>
                <option value="only_me" {{ $post->privacy=='only_me'?'selected':'' }}>Only Me</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="is_active" class="form-control">
                <option value="1" {{ $post->is_active ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !$post->is_active ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Image</label><br>
            @if($post->image)
                <img src="{{ asset($post->image) }}" width="100" class="mb-2">
            @endif
            <input type="file" name="image" class="form-control">
        </div>

        <button class="btn btn-success">Update Post</button>
        <a href="{{ route('admin.usersocialposts') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
