@extends('agent.master')

@section('content')
<div class="container mt-4" style="max-width: 700px;">
    <h4 class="mb-3 fw-bold">Edit Buy/Sell Post</h4>

    <form action="{{ route('agentbuysellpost.update', $agentBuySellPost->id) }}" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
        @csrf
        @method('PUT')

        <!-- Category -->
        <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $agentBuySellPost->category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->category_name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Taka & Dollar Signed -->
        <div class="mb-3">
            <label for="dollarsigends_id" class="form-label">Taka & Dollar Signed</label>
            <select name="dollarsigends_id" id="dollarsigends_id" class="form-select @error('dollarsigends_id') is-invalid @enderror" required>
                @foreach($takaandDollarsigend as $item)
                    <option value="{{ $item->id }}" {{ $agentBuySellPost->dollarsigends_id == $item->id ? 'selected' : '' }}>
                        {{ $item->dollarsigned }} ({{ $item->takasigned }})
                    </option>
                @endforeach
            </select>
            @error('dollarsigends_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Photos -->
        <div class="mb-3">
            <label for="photo" class="form-label fw-semibold">Photos</label><br>
            @if($agentBuySellPost->photo)
                @php
                    $photos = is_array(json_decode($agentBuySellPost->photo)) ? json_decode($agentBuySellPost->photo) : [$agentBuySellPost->photo];
                @endphp
                @foreach($photos as $photo)
                    <img src="{{ asset($photo) }}" width="100" height="100" class="mb-2 me-2 rounded border">
                @endforeach
            @endif
            <input type="file" class="form-control @error('photo.*') is-invalid @enderror" name="photo[]" id="photo" multiple>
            <small class="text-muted">Leave empty if you don't want to change/add photos.</small>
            @error('photo.*')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Trade Limits -->
        <div class="mb-3">
            <label for="trade_limit" class="form-label fw-semibold">Trade Limit (Min)</label>
            <input type="number" class="form-control @error('trade_limit') is-invalid @enderror" name="trade_limit" id="trade_limit" value="{{ old('trade_limit', $agentBuySellPost->trade_limit) }}" required>
            @error('trade_limit')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="trade_limit_two" class="form-label fw-semibold">Trade Limit (Max)</label>
            <input type="number" class="form-control @error('trade_limit_two') is-invalid @enderror" name="trade_limit_two" id="trade_limit_two" value="{{ old('trade_limit_two', $agentBuySellPost->trade_limit_two) }}" required>
            @error('trade_limit_two')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Rate Balance -->
        <div class="mb-3">
            <label for="rate_balance" class="form-label fw-semibold">Rate Balance</label>
            <input type="number" step="0.01" class="form-control @error('rate_balance') is-invalid @enderror" name="rate_balance" id="rate_balance" value="{{ old('rate_balance', $agentBuySellPost->rate_balance) }}" required>
            @error('rate_balance')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Payment Name -->
        <div class="mb-3">
            <label for="payment_name" class="form-label fw-semibold">Payment Name</label>
            <input type="text" class="form-control @error('payment_name') is-invalid @enderror" name="payment_name" id="payment_name" value="{{ old('payment_name', $agentBuySellPost->payment_name) }}" required>
            @error('payment_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Status -->
        <div class="mb-3">
            <label for="status" class="form-label fw-semibold">Status</label>
            <select class="form-select @error('status') is-invalid @enderror" name="status" id="status" required>
                <option value="approved" {{ $agentBuySellPost->status === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="pending" {{ $agentBuySellPost->status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="rejected" {{ $agentBuySellPost->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            @error('status')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Form Buttons -->
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
