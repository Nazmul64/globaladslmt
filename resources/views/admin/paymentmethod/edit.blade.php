@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3>Edit Payment Method</h3>

    <form action="{{ route('paymentmethod.update', $paymentmethod->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="method_name" class="form-label">Method Name</label>
            <input type="text" name="method_name" class="form-control" value="{{ old('method_name', $paymentmethod->method_name) }}">
            @error('method_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label for="method_number" class="form-label">Method Number</label>
            <input type="text" name="method_number" class="form-control" value="{{ old('method_number', $paymentmethod->method_number) }}">
            @error('method_number')
                <span class="text-danger">{{ $message }}</span>
            @enderror

        <div class="mb-3">
            <label for="photo" class="form-label">Photo (optional)</label>
            <input type="file" name="photo" class="form-control">
            @if($paymentmethod->photo)
                <img src="{{ asset('uploads/paymentmethod/'.$paymentmethod->photo) }}" alt="Photo" width="50" class="mt-2">
            @endif
            @error('photo')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" {{ $paymentmethod->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $paymentmethod->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-success">Update Method</button>
        <a href="{{ route('paymentmethod.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
