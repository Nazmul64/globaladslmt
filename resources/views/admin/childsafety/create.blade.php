@extends('admin.master')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Create Privacy Policy</h2>
        <a href="{{ route('Childsafety.index') }}" class="btn btn-secondary">
            &larr; Back to List
        </a>
    </div>


    <div class="card">
        <div class="card-body">
            <form action="{{ route('Childsafety.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}" placeholder="Enter policy title" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                    <textarea name="description" rows="8"
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Enter policy description" required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">
                    Save Child Safety Standard
                </button>
                <a href="{{ route('Childsafety.index') }}" class="btn btn-outline-secondary ms-2">
                    Cancel
                </a>
            </form>
        </div>
    </div>

</div>
@endsection
