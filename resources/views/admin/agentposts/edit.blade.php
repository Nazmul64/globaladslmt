@extends('admin.master')

@section('content')
<div class="container mt-4">

    <h4 class="mb-3">Edit Agent Buy/Sell Post</h4>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('agent.agentposts.update', $post->id) }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Agent --}}
        <div class="mb-3">
            <label class="form-label">Agent</label>
            <input type="text" class="form-control"
                   value="{{ $post->agent->name ?? 'N/A' }}" readonly>
        </div>

        {{-- Dollar / Category --}}
        <div class="mb-3">
            <label class="form-label">Dollar Sign</label>
            <input type="text" class="form-control"
                   value="{{ $post->dollarsign?->dollarsigned ?? 'N/A' }}" readonly>
        </div>

        {{-- Rate --}}
        <div class="mb-3">
            <label class="form-label">Rate</label>
            <input type="text" name="rate_balance" class="form-control"
                   value="{{ old('rate_balance', $post->rate_balance) }}">
        </div>

        {{-- Trade Limit --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Trade Limit From</label>
                <input type="text" name="trade_limit" class="form-control"
                       value="{{ old('trade_limit', $post->trade_limit) }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Trade Limit To</label>
                <input type="text" name="trade_limit_two" class="form-control"
                       value="{{ old('trade_limit_two', $post->trade_limit_two) }}">
            </div>
        </div>

        {{-- Payment --}}
        <div class="mb-3">
            <label class="form-label">Payment Method</label>
            <input type="text" name="payment_name" class="form-control"
                   value="{{ old('payment_name', $post->payment_name) }}">
        </div>

        {{-- Status --}}
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="1" {{ $post->status == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ $post->status == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        {{-- 🔥 PHOTO SECTION (FIXED) --}}
        <div class="mb-3">
            <label class="form-label">Current Photos</label><br>

            @if($post->photo)
                @php
                    $photos = json_decode($post->photo, true);
                    $photos = is_array($photos) ? $photos : [$post->photo];
                @endphp

                @foreach($photos as $photo)
                    @if(file_exists(public_path($photo)))
                        <img src="{{ asset($photo) }}"
                             width="70" height="70"
                             class="rounded border me-1 mb-1">
                    @endif
                @endforeach
            @else
                <span class="text-muted">No Image</span>
            @endif
        </div>

        {{-- Upload New Photo --}}
        <div class="mb-3">
            <label class="form-label">Upload New Photo</label>
            <input type="file" name="photo[]" class="form-control" multiple>
            <small class="text-muted">You can upload multiple images</small>
        </div>

        {{-- Buttons --}}
        <div class="mt-4">
            <button type="submit" class="btn btn-success">
                Update Post
            </button>

            <a href="{{ route('agent.agentposts') }}"
               class="btn btn-secondary">
                Back
            </a>
        </div>

    </form>
</div>
@endsection
