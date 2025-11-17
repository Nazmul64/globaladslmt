@extends('agent.master')

@section('content')
<div class="container mt-4" style="max-width: 700px;">
    <h4 class="mb-3 fw-bold">Create New Buy/Sell Post</h4>

    @php
        use App\Models\AgentDeposite;
        $total_amount = AgentDeposite::where('agent_id', auth()->id())->sum('amount');
    @endphp

    <!-- Show Balance -->
    <p class="mb-3 fw-bold">Available Balance: ${{ number_format($total_amount, 2) }}</p>

    <!-- Show Validation Alert -->
    @if(session('error'))
        <div class="alert alert-danger fw-semibold">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('agentbuysellpost.store') }}" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
        @csrf

        <!-- Category -->
        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->category_name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Taka & Dollar Signed -->
        <div class="mb-3">
            <label class="form-label">Taka & Dollar Signed</label>
            <select name="dollarsigends_id" class="form-select @error('dollarsigends_id') is-invalid @enderror" required>
                <option value="">Select Dollar Signed</option>
                @foreach($takaandDollarsigend as $item)
                    <option value="{{ $item->id }}" {{ old('dollarsigends_id') == $item->id ? 'selected' : '' }}>
                        {{ $item->dollarsigned }} ({{ $item->takasigned }})
                    </option>
                @endforeach
            </select>
            @error('dollarsigends_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Photo -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Photos</label>
            <input type="file" name="photo[]" multiple class="form-control @error('photo.*') is-invalid @enderror">
            <small class="text-muted">You can upload multiple images.</small>
            @error('photo.*')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Trade Limits -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Trade Limit (Min)</label>
            <input type="number" name="trade_limit" class="form-control @error('trade_limit') is-invalid @enderror" value="{{ old('trade_limit') }}" required>
            @error('trade_limit')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Trade Limit (Max)</label>
            <input type="number" name="trade_limit_two" class="form-control @error('trade_limit_two') is-invalid @enderror" value="{{ old('trade_limit_two') }}" required>
            @error('trade_limit_two')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Balance Rate -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Balance Rate</label>
            <input type="number" step="0.01" name="rate_balance" class="form-control @error('rate_balance') is-invalid @enderror" value="{{ old('rate_balance') }}" required>
            @error('rate_balance')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Payment Name -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Payment Name</label>
            <input type="text" name="payment_name" class="form-control @error('payment_name') is-invalid @enderror" value="{{ old('payment_name') }}" required>
            @error('payment_name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Status -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            @error('status')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Actions -->
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
