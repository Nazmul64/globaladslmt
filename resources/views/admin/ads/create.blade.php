@extends('admin.master')

@section('content')
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Create New Ad</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('ads.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="code" class="form-label">Ad Code</label>
                    <textarea name="code" id="code" class="form-control" rows="5" required>{{ old('code') }}</textarea>
                    @error('code')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="show_mrce_ads" class="form-label">Show MRCE Ads</label>
                    <select name="show_mrce_ads" id="show_mrce_ads" class="form-control" required>
                        <option value="">-- Select --</option>
                        <option value="enabled" {{ old('show_mrce_ads')=='enabled'?'selected':'' }}>Enabled</option>
                        <option value="disabled" {{ old('show_mrce_ads')=='disabled'?'selected':'' }}>Disabled</option>
                    </select>
                    @error('show_mrce_ads')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="show_button_timer_ads" class="form-label">Show Button Timer Ads</label>
                    <select name="show_button_timer_ads" id="show_button_timer_ads" class="form-control" required>
                        <option value="">-- Select --</option>
                        <option value="enabled" {{ old('show_button_timer_ads')=='enabled'?'selected':'' }}>Enabled</option>
                        <option value="disabled" {{ old('show_button_timer_ads')=='disabled'?'selected':'' }}>Disabled</option>
                    </select>
                    @error('show_button_timer_ads')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="show_banner_ads" class="form-label">Show Banner Ads</label>
                    <select name="show_banner_ads" id="show_banner_ads" class="form-control" required>
                        <option value="">-- Select --</option>
                        <option value="enabled" {{ old('show_banner_ads')=='enabled'?'selected':'' }}>Enabled</option>
                        <option value="disabled" {{ old('show_banner_ads')=='disabled'?'selected':'' }}>Disabled</option>
                    </select>
                    @error('show_banner_ads')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success w-100">Create Ad</button>
            </form>
        </div>
    </div>
</div>
@endsection
