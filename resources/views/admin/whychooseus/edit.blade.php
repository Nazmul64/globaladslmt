@extends('admin.master')
@section('content')
<div class="container mt-4">
    <h2>Edit Item</h2>

    <form action="{{ route('whychooseu.update', $whychooseus->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="{{ $whychooseus->title }}">
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="4">{{ $whychooseus->description }}</textarea>
        </div>

        <div class="mb-3">
            <label>Icon (Font Awesome class)</label>
            <input type="text" name="icon" class="form-control" value="{{ $whychooseus->icon }}">
        </div>

        <button class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
