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
                    <label for="banner_ad_1" class="form-label">Banner Ad Top</label>
                    <textarea name="banner_ad_1" id="banner_ad_1" class="form-control" rows="3">{{ old('banner_ad_1') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="banner_ad_2" class="form-label">Banner Ad Bottom</label>
                    <textarea name="banner_ad_2" id="banner_ad_2" class="form-control" rows="3">{{ old('banner_ad_2') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="interstitial" class="form-label">Interstitial Ad</label>
                    <textarea name="interstitial" id="interstitial" class="form-control" rows="3">{{ old('interstitial') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="rewarded_video" class="form-label">Rewarded Video Ad</label>
                    <textarea name="rewarded_video" id="rewarded_video" class="form-control" rows="3">{{ old('rewarded_video') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="native" class="form-label">Native Ad</label>
                    <textarea name="native" id="native" class="form-control" rows="3">{{ old('native') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="show_mrce_ads" class="form-label">Show MRCE Ads</label>
                    <select name="show_mrce_ads" id="show_mrce_ads" class="form-control" required>
                        <option value="">-- Select --</option>
                        <option value="enabled" {{ old('show_mrce_ads')=='enabled'?'selected':'' }}>Enabled</option>
                        <option value="disabled" {{ old('show_mrce_ads')=='disabled'?'selected':'' }}>Disabled</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="show_button_timer_ads" class="form-label">Show Button Timer Ads</label>
                    <select name="show_button_timer_ads" id="show_button_timer_ads" class="form-control" required>
                        <option value="">-- Select --</option>
                        <option value="enabled" {{ old('show_button_timer_ads')=='enabled'?'selected':'' }}>Enabled</option>
                        <option value="disabled" {{ old('show_button_timer_ads')=='disabled'?'selected':'' }}>Disabled</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="show_banner_ads" class="form-label">Show Banner Ads</label>
                    <select name="show_banner_ads" id="show_banner_ads" class="form-control" required>
                        <option value="">-- Select --</option>
                        <option value="enabled" {{ old('show_banner_ads')=='enabled'?'selected':'' }}>Enabled</option>
                        <option value="disabled" {{ old('show_banner_ads')=='disabled'?'selected':'' }}>Disabled</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success w-100">Create Ad</button>
            </form>
        </div>
    </div>
</div>
@endsection
