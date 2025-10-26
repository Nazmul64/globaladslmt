@extends('agent.master')

@section('content')
<div class="container mt-4" style="max-width: 650px;">
    <h4 class="mb-3 fw-bold">Edit Buy/Sell Post</h4>
    <form action="{{ route('agentbuysellpost.update', $agentBuySellPost->id) }}" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select name="category_id" id="category_id" class="form-select" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $agentBuySellPost->category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->category_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="photo" class="form-label">Photo</label><br>
            @if($agentBuySellPost->photo)
                <img src="{{ asset($agentBuySellPost->photo) }}" width="100" class="mb-2 rounded border">
            @endif
            <input type="file" class="form-control" name="photo" id="photo">
            <small class="text-muted">Leave empty if you don't want to change the photo.</small>
        </div>

        <div class="mb-3">
            <label for="trade_limit" class="form-label">Trade Limit (Min)</label>
            <input type="number" class="form-control" name="trade_limit" value="{{ old('trade_limit', $agentBuySellPost->trade_limit) }}" required>
        </div>

        <div class="mb-3">
            <label for="trade_limit_two" class="form-label">Trade Limit (Max)</label>
            <input type="number" class="form-control" name="trade_limit_two" value="{{ old('trade_limit_two', $agentBuySellPost->trade_limit_two) }}" required>
        </div>

        <div class="mb-3">
            <label for="available_balance" class="form-label">Available Balance</label>
            <input type="number" step="0.01" class="form-control" name="available_balance" value="{{ old('available_balance', $agentBuySellPost->available_balance) }}" required>
        </div>

        <div class="mb-3">
            <label for="duration" class="form-label">Duration (minutes)</label>
            <input type="number" class="form-control" name="duration" value="{{ old('duration', $agentBuySellPost->duration) }}" required>
        </div>

        <div class="mb-3">
            <label for="payment_name" class="form-label">Payment Name</label>
            <input type="text" class="form-control" name="payment_name" value="{{ old('payment_name', $agentBuySellPost->payment_name) }}" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label fw-semibold">Status</label>
            <select class="form-select" name="status" id="status" required>
                <option value="approved" {{ $agentBuySellPost->status === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="pending" {{ $agentBuySellPost->status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="rejected" {{ $agentBuySellPost->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('agentbuysellpost.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save"></i> Update Post
            </button>
        </div>
    </form>
</div>
@endsection
