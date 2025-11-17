@extends('agent.master')

@section('content')
<div class="container mt-4" style="max-width: 700px;">
    <h4 class="mb-3 fw-bold">Edit Payment Method</h4>

    <form action="{{ route('paymentsetup.update', $paymentsetup->id) }}" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Method Name *</label>
            <input type="text" name="method_name" class="form-control"
                value="{{ old('method_name', $paymentsetup->method_name) }}" required>
            @error('method_name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Method Number</label>
            <input type="text" name="method_number" class="form-control"
                value="{{ old('method_number', $paymentsetup->method_number) }}">
            @error('method_number') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label d-block">Existing Photo</label>
            @if($paymentsetup->photo)
                <img src="{{ asset('uploads/agentpaymentsetup/'.$paymentsetup->photo) }}" width="80" class="rounded border">
            @else
                <span class="text-muted">No photo available</span>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label">Change Photo (Optional)</label>
            <input type="file" name="photo" class="form-control">
            @error('photo') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Status *</label>
            <select name="status" class="form-select" required>
                <option value="active" {{ $paymentsetup->status=='active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $paymentsetup->status=='inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button class="btn btn-success" style="width: 100%;">Update Method</button>
    </form>
</div>
@endsection
