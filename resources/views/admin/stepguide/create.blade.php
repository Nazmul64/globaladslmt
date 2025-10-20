@extends('admin.master')
@section('content')
<div class="container mt-4">
    <h3>Add New Stepguide</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('stepguide.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" name="title" id="title" value="{{ old('title') }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" name="description" id="description" required>{{ old('description') }}</textarea>
        </div>
        <div class="mb-3">
            <label for="icon" class="form-label">Icon (Font Awesome Class)</label>
            <input type="text" class="form-control" name="icon" id="icon" value="{{ old('icon') }}">
            <small>Example: fa-solid fa-box</small>
        </div>
        <div class="mb-3">
            <label for="serial_number" class="form-label">Serial Number</label>
            <input type="number" class="form-control" name="serial_number" id="serial_number" value="{{ old('serial_number') }}" required>
        </div>
        <button type="submit" class="btn btn-success"><i class="fa-solid fa-plus"></i> Create Stepguide</button>
    </form>
</div>
@endsection
