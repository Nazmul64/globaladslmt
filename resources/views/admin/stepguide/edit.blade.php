@extends('admin.master')
@section('content')
<div class="container mt-4">
    <h3>Edit Stepguide</h3>

    <form action="{{ route('stepguide.update', $edit->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" name="title" id="title" value="{{ old('title', $edit->title) }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" name="description" id="description" required>{{ old('description', $edit->description) }}</textarea>
        </div>
        <div class="mb-3">
            <label for="icon" class="form-label">Icon (Font Awesome Class)</label>
            <input type="text" class="form-control" name="icon" id="icon" value="{{ old('icon', $edit->icon) }}">
            <small>Example: fa-solid fa-box</small>
        </div>
        <div class="mb-3">
            <label for="serial_number" class="form-label">Serial Number</label>
            <input type="number" class="form-control" name="serial_number" id="serial_number" value="{{ old('serial_number', $edit->serial_number) }}" required>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Update Stepguide</button>
    </form>
</div>
@endsection
