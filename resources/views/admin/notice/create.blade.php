@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3>Add New Notice</h3>

    <form action="{{ route('notice.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}">
            @error('title')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="5">{{ old('description') }}</textarea>
            @error('description')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Create Notice</button>
    </form>
</div>
@endsection
