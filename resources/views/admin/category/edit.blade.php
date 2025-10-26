@extends('admin.master')

@section('content')
<div class="container mt-4" style="max-width: 600px;">
    <h4 class="mb-3 fw-bold">Edit Category</h4>

    <form action="{{ route('category.update', $category->id) }}" method="POST" class="card p-4 shadow-sm">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="category_name" class="form-label fw-semibold">Category Name</label>
            <input type="text" class="form-control" name="category_name" id="category_name" value="{{ old('category_name', $category->category_name) }}" required>
            @error('category_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('category.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save"></i> Update Category
            </button>
        </div>
    </form>
</div>
@endsection
