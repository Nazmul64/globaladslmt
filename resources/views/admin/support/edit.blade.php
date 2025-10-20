@extends('admin.master')
@section('content')
<div class="container my-4">
    <h4>Edit Support</h4>
    <a href="{{ route('support.index') }}" class="btn btn-secondary mb-3">Back to List</a>

    <form action="{{ route('support.update', $support->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $support->name) }}" required>
        </div>

        <div class="mb-3">
            <label for="url_link" class="form-label">URL Link</label>
            <input type="url" name="url_link" class="form-control" id="url_link" value="{{ old('url_link', $support->url_link) }}" required>
        </div>

        <div class="mb-3">
            <label for="icon" class="form-label">Icon (Font Awesome class)</label>
            <input type="text" name="icon" class="form-control" id="icon" value="{{ old('icon', $support->icon) }}" required>
        </div>

        <button type="submit" class="btn btn-success">Update Support</button>
    </form>
</div>
@endsection
