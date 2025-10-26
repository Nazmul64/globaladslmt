@extends('agent.master')

@section('content')
<div class="container mt-4" style="max-width: 650px;">
    <h4 class="mb-3 fw-bold">Create New Buy/Sell Post</h4>
    <form action="{{ route('agentbuysellpost.store') }}" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
        @csrf

        <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select name="category_id" id="category_id" class="form-select" required>
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="photo" class="form-label fw-semibold">Photo</label>
            <input type="file" class="form-control" name="photo" id="photo" required>
        </div>

        <div class="mb-3">
            <label for="trade_limit" class="form-label fw-semibold">Trade Limit (Min)</label>
            <input type="number" class="form-control" name="trade_limit" id="trade_limit" required>
        </div>

        <div class="mb-3">
            <label for="trade_limit_two" class="form-label fw-semibold">Trade Limit (Max)</label>
            <input type="number" class="form-control" name="trade_limit_two" id="trade_limit_two" required>
        </div>

        <div class="mb-3">
            <label for="available_balance" class="form-label fw-semibold">Available Balance</label>
            <input type="number" step="0.01" class="form-control" name="available_balance" id="available_balance" required>
        </div>

        <div class="mb-3">
            <label for="duration" class="form-label fw-semibold">Duration (minutes)</label>
            <input type="number" class="form-control" name="duration" id="duration" required>
        </div>

        <div class="mb-3">
            <label for="payment_name" class="form-label fw-semibold">Payment Name</label>
            <input type="text" class="form-control" name="payment_name" id="payment_name" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label fw-semibold">Status</label>
            <select class="form-select" name="status" id="status" required>
                <option value="approved" selected>Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('agentbuysellpost.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Create Post
            </button>
        </div>
    </form>
</div>
@endsection
