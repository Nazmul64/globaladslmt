@extends('admin.master')

@section('content')
<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Website Landing Page All Texts & Info</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Landing Page Settings</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-24" role="alert">
            <i class="ri-checkbox-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-24" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.landingsettings.update') }}" method="POST">
        @csrf

        <!-- Hero Section Settings -->
        <div class="card mb-24 shadow-sm">
            <div class="card-header bg-base py-16 px-24 border-bottom">
                <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                    <i class="ri-window-line text-primary"></i> 1. Top Hero Section Texts
                </h5>
                <small class="text-secondary-light">Manage the top main heading, badge, and intro text displayed on the website.</small>
            </div>
            <div class="card-body p-24">
                <div class="row gy-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Hero Top Badge Text</label>
                        <input type="text" name="hero_badge" class="form-control" value="{{ old('hero_badge', $settings->hero_badge) }}" placeholder="e.g. Verified & Secure Platform">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Hero Main Title (Heading)</label>
                        <input type="text" name="hero_title" class="form-control" value="{{ old('hero_title', $settings->hero_title) }}" placeholder="e.g. Next-Gen Micro-Earning & P2P Trading Ecosystem">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Hero Subtitle / Description Text</label>
                        <textarea name="hero_subtitle" rows="3" class="form-control" placeholder="Enter company introduction...">{{ old('hero_subtitle', $settings->hero_subtitle) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Google Play Store Showcase Card Settings -->
        <div class="card mb-24 shadow-sm">
            <div class="card-header bg-base py-16 px-24 border-bottom">
                <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                    <i class="ri-google-play-line text-success"></i> 2. Google Play Store Card & Download Link
                </h5>
                <small class="text-secondary-light">Customize the interactive Google Play Store showcase card shown beside the hero section.</small>
            </div>
            <div class="card-body p-24">
                <div class="row gy-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">App Title (Name)</label>
                        <input type="text" name="app_name" class="form-control" value="{{ old('app_name', $settings->app_name) }}" placeholder="e.g. Globalmoney ltd">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Publisher / Developer Name</label>
                        <input type="text" name="app_publisher" class="form-control" value="{{ old('app_publisher', $settings->app_publisher) }}" placeholder="e.g. BD IT POINT">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">App Meta Text</label>
                        <input type="text" name="app_meta" class="form-control" value="{{ old('app_meta', $settings->app_meta) }}" placeholder="e.g. Contains ads · In-app purchases">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Rating Score</label>
                        <input type="text" name="rating_score" class="form-control" value="{{ old('rating_score', $settings->rating_score) }}" placeholder="e.g. 4.8">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Reviews Count</label>
                        <input type="text" name="rating_count" class="form-control" value="{{ old('rating_count', $settings->rating_count) }}" placeholder="e.g. 1K+ reviews">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Total Downloads</label>
                        <input type="text" name="downloads_count" class="form-control" value="{{ old('downloads_count', $settings->downloads_count) }}" placeholder="e.g. 100+">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Content Rating Badge</label>
                        <input type="text" name="content_rating" class="form-control" value="{{ old('content_rating', $settings->content_rating) }}" placeholder="e.g. Rated for 3+">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Device Compatibility Notice</label>
                        <input type="text" name="device_compatibility" class="form-control" value="{{ old('device_compatibility', $settings->device_compatibility) }}" placeholder="e.g. This app is available for your Android devices">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Google Play Store Download URL</label>
                        <input type="url" name="play_store_url" class="form-control" value="{{ old('play_store_url', $settings->play_store_url) }}" placeholder="https://play.google.com/store/apps/details?id=...">
                    </div>
                </div>
            </div>
        </div>

        <!-- Platform Stats Counters -->
        <div class="card mb-24 shadow-sm">
            <div class="card-header bg-base py-16 px-24 border-bottom">
                <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                    <i class="ri-bar-chart-2-line text-warning"></i> 3. Platform Stats Counters
                </h5>
                <small class="text-secondary-light">The 3 trust counters displayed under the hero section.</small>
            </div>
            <div class="card-body p-24">
                <div class="row gy-3">
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light">
                            <h6 class="fw-bold mb-2">Counter #1</h6>
                            <label class="form-label text-sm">Value</label>
                            <input type="text" name="stat_1_value" class="form-control mb-2" value="{{ old('stat_1_value', $settings->stat_1_value) }}" placeholder="100%">
                            <label class="form-label text-sm">Label</label>
                            <input type="text" name="stat_1_label" class="form-control" value="{{ old('stat_1_label', $settings->stat_1_label) }}" placeholder="Secure Transactions">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light">
                            <h6 class="fw-bold mb-2">Counter #2</h6>
                            <label class="form-label text-sm">Value</label>
                            <input type="text" name="stat_2_value" class="form-control mb-2" value="{{ old('stat_2_value', $settings->stat_2_value) }}" placeholder="24/7">
                            <label class="form-label text-sm">Label</label>
                            <input type="text" name="stat_2_label" class="form-control" value="{{ old('stat_2_label', $settings->stat_2_label) }}" placeholder="Live Agent Support">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light">
                            <h6 class="fw-bold mb-2">Counter #3</h6>
                            <label class="form-label text-sm">Value</label>
                            <input type="text" name="stat_3_value" class="form-control mb-2" value="{{ old('stat_3_value', $settings->stat_3_value) }}" placeholder="4.8 ★">
                            <label class="form-label text-sm">Label</label>
                            <input type="text" name="stat_3_label" class="form-control" value="{{ old('stat_3_label', $settings->stat_3_label) }}" placeholder="User Rating">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features / Platform Highlights Section -->
        <div class="card mb-24 shadow-sm">
            <div class="card-header bg-base py-16 px-24 border-bottom">
                <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                    <i class="ri-shield-star-line text-info"></i> 4. Platform Highlights & Feature Cards
                </h5>
                <small class="text-secondary-light">Customize section titles and all 6 highlight cards.</small>
            </div>
            <div class="card-body p-24">
                <div class="row gy-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Section Badge</label>
                        <input type="text" name="features_badge" class="form-control" value="{{ old('features_badge', $settings->features_badge) }}" placeholder="Platform Highlights">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Section Title</label>
                        <input type="text" name="features_title" class="form-control" value="{{ old('features_title', $settings->features_title) }}" placeholder="Designed for Ease, Built for Security">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Section Subtitle</label>
                        <textarea name="features_subtitle" rows="2" class="form-control">{{ old('features_subtitle', $settings->features_subtitle) }}</textarea>
                    </div>
                </div>

                <div class="row gy-3">
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <h6 class="fw-bold text-primary mb-2">Card 1</h6>
                            <input type="text" name="feat_1_title" class="form-control mb-2" value="{{ old('feat_1_title', $settings->feat_1_title) }}" placeholder="P2P USDT Trading">
                            <textarea name="feat_1_desc" rows="2" class="form-control" placeholder="Description...">{{ old('feat_1_desc', $settings->feat_1_desc) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <h6 class="fw-bold text-primary mb-2">Card 2</h6>
                            <input type="text" name="feat_2_title" class="form-control mb-2" value="{{ old('feat_2_title', $settings->feat_2_title) }}" placeholder="Daily Micro Earning">
                            <textarea name="feat_2_desc" rows="2" class="form-control" placeholder="Description...">{{ old('feat_2_desc', $settings->feat_2_desc) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <h6 class="fw-bold text-primary mb-2">Card 3</h6>
                            <input type="text" name="feat_3_title" class="form-control mb-2" value="{{ old('feat_3_title', $settings->feat_3_title) }}" placeholder="KYC & Verified Badges">
                            <textarea name="feat_3_desc" rows="2" class="form-control" placeholder="Description...">{{ old('feat_3_desc', $settings->feat_3_desc) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <h6 class="fw-bold text-primary mb-2">Card 4</h6>
                            <input type="text" name="feat_4_title" class="form-control mb-2" value="{{ old('feat_4_title', $settings->feat_4_title) }}" placeholder="Social Feed & Agent Chat">
                            <textarea name="feat_4_desc" rows="2" class="form-control" placeholder="Description...">{{ old('feat_4_desc', $settings->feat_4_desc) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <h6 class="fw-bold text-primary mb-2">Card 5</h6>
                            <input type="text" name="feat_5_title" class="form-control mb-2" value="{{ old('feat_5_title', $settings->feat_5_title) }}" placeholder="Instant Withdrawals">
                            <textarea name="feat_5_desc" rows="2" class="form-control" placeholder="Description...">{{ old('feat_5_desc', $settings->feat_5_desc) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <h6 class="fw-bold text-primary mb-2">Card 6</h6>
                            <input type="text" name="feat_6_title" class="form-control mb-2" value="{{ old('feat_6_title', $settings->feat_6_title) }}" placeholder="Multi-Level Referrals">
                            <textarea name="feat_6_desc" rows="2" class="form-control" placeholder="Description...">{{ old('feat_6_desc', $settings->feat_6_desc) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom CTA Banner & Footer Info -->
        <div class="card mb-24 shadow-sm">
            <div class="card-header bg-base py-16 px-24 border-bottom">
                <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                    <i class="ri-layout-bottom-line text-purple"></i> 5. Bottom Download Banner & Footer Texts
                </h5>
                <small class="text-secondary-light">Update bottom call-to-action text and footer branding descriptions.</small>
            </div>
            <div class="card-body p-24">
                <div class="row gy-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">CTA Banner Title</label>
                        <input type="text" name="cta_title" class="form-control" value="{{ old('cta_title', $settings->cta_title) }}" placeholder="Start Earning Today with Global Money Ltd">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">CTA Banner Subtitle</label>
                        <textarea name="cta_subtitle" rows="2" class="form-control" placeholder="Install the official Android application from Google Play...">{{ old('cta_subtitle', $settings->cta_subtitle) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Footer About / Company Statement</label>
                        <textarea name="footer_about" rows="3" class="form-control">{{ old('footer_about', $settings->footer_about) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Footer Bottom Note / Tagline</label>
                        <input type="text" name="footer_copyright" class="form-control" value="{{ old('footer_copyright', $settings->footer_copyright) }}" placeholder="Designed for High Performance & User Security">
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-flex align-items-center justify-content-end gap-3 mb-40">
            <button type="submit" class="btn btn-primary px-32 py-12 radius-8 d-flex align-items-center gap-2">
                <i class="ri-save-line text-lg"></i> Save All Landing Page Settings
            </button>
        </div>
    </form>
</div>
@endsection
