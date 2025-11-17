@extends('agent.master')

@section('content')
<div class="container mt-4" style="max-width: 700px;">
    <h4 class="mb-3 fw-bold">Add Payment Method</h4>

    <form action="{{ route('paymentsetup.store') }}" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
        @csrf

        <div class="mb-3">
            <label class="form-label">Method Name *</label>
            <input type="text" name="method_name" class="form-control" value="{{ old('method_name') }}" placeholder="Example: Bkash, Nagad, Rocket" required>
            @error('method_name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Method Number</label>
            <input type="text" name="method_number" class="form-control" value="{{ old('method_number') }}" placeholder="Example: 017xxxxxxxx">
            @error('method_number') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Upload Photo (Optional)</label>
            <input type="file" name="photo" class="form-control">
            @error('photo') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Status *</label>
            <select name="status" class="form-select" required>
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </select>
            @error('status') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button class="btn btn-primary" style="width: 100%;">Save Method</button>
    </form>
</div>
@endsection
