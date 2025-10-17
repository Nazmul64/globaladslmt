@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3>Edit Notice</h3>

    <form action="{{ route('worknotice.update', $notice->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $notice->title) }}">
            @error('title')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="5">{{ old('description', $notice->description) }}</textarea>
            @error('description')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <button type="submit" class="btn btn-success">Update Notice</button>
    </form>
</div>
@endsection
