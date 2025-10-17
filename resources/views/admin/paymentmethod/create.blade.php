@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3>Add Payment Method</h3>

    <form action="{{ route('paymentmethod.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="method_name" class="form-label">Method Name</label>
            <input type="text" name="method_name" class="form-control" value="{{ old('method_name') }}">
            @error('method_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="photo" class="form-label">Photo (optional)</label>
            <input type="file" name="photo" class="form-control">
            @error('photo')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </select>
            @error('status')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-success">Add Method</button>
        <a href="{{ route('paymentmethod.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
