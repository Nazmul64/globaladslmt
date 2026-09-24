@extends('admin.master')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-pencil-square me-2"></i>Edit App Settings #{{ $appsetting->id }}
        </h4>
        <a href="{{ route('appsetting.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Whoops!</strong> There were some problems with your input.
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('appsetting.update', $appsetting->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- LEFT COLUMN: Basic Settings --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-sliders me-2"></i>Basic App Settings & Timers
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            {{-- Star.io / StartApp App ID --}}
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark">
                                    <i class="bi bi-star-fill text-warning me-1"></i>Star.io (StartApp) App ID
                                </label>
                                <input type="text"
                                       class="form-control form-control-lg border-warning @error('star_io_id') is-invalid @enderror"
                                       name="star_io_id"
                                       value="{{ old('star_io_id', $appsetting->star_io_id) }}"
                                       placeholder="e.g. 209922521">
                                <small class="text-muted">Enter your Star.io (StartApp) App ID here to serve StartApp ads dynamically in the mobile app.</small>
                                @error('star_io_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Invalid Click Limit --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-x-octagon text-danger me-1"></i>Invalid Click Limit
                                </label>
                                <input type="number"
                                       step="0.01"
                                       class="form-control @error('invalid_click_limit') is-invalid @enderror"
                                       name="invalid_click_limit"
                                       value="{{ old('invalid_click_limit', $appsetting->invalid_click_limit) }}"
                                       placeholder="e.g., 5">
                                @error('invalid_click_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Invalid Deduct --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-dash-circle text-warning me-1"></i>Invalid Deduct
                                </label>
                                <input type="number"
                                       step="0.01"
                                       class="form-control @error('invalid_deduct') is-invalid @enderror"
                                       name="invalid_deduct"
                                       value="{{ old('invalid_deduct', $appsetting->invalid_deduct) }}"
                                       placeholder="e.g., 1">
                                @error('invalid_deduct')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- View Before Click Target --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-eye text-info me-1"></i>View Before Click Target
                                </label>
                                <input type="number"
                                       step="0.01"
                                       class="form-control @error('view_before_click_view_target') is-invalid @enderror"
                                       name="view_before_click_view_target"
                                       value="{{ old('view_before_click_view_target', $appsetting->view_before_click_view_target) }}"
                                       placeholder="e.g., 10">
                                @error('view_before_click_view_target')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12"><hr></div>

                            {{-- Task Break Time --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-clock text-primary me-1"></i>Task Break (Min)
                                </label>
                                <input type="number"
                                       step="0.01"
                                       class="form-control @error('task_break_time_minutes') is-invalid @enderror"
                                       name="task_break_time_minutes"
                                       value="{{ old('task_break_time_minutes', $appsetting->task_break_time_minutes) }}"
                                       placeholder="5">
                                @error('task_break_time_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Button Timer --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-stopwatch text-success me-1"></i>Button Timer (Sec)
                                </label>
                                <input type="number"
                                       step="0.01"
                                       class="form-control @error('button_timer_seconds') is-invalid @enderror"
                                       name="button_timer_seconds"
                                       value="{{ old('button_timer_seconds', $appsetting->button_timer_seconds) }}"
                                       placeholder="30">
                                @error('button_timer_seconds')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Ad Timer --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-play-circle text-info me-1"></i>Ad Timer (Sec)
                                </label>
                                <input type="number"
                                       step="0.01"
                                       class="form-control @error('ad_timer_seconds') is-invalid @enderror"
                                       name="ad_timer_seconds"
                                       value="{{ old('ad_timer_seconds', $appsetting->ad_timer_seconds) }}"
                                       placeholder="15">
                                @error('ad_timer_seconds')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Custom Toggle Switch Styles --}}
                            <style>
                                .custom-toggle-switch {
                                    width: 3.2rem !important;
                                    height: 1.7rem !important;
                                    cursor: pointer;
                                }
                                .custom-toggle-switch:checked {
                                    background-color: #198754 !important;
                                    border-color: #198754 !important;
                                }
                            </style>

                            {{-- Ad Timer Network Controls --}}
                            <div class="col-12 mt-3">
                                <div class="p-3 bg-light rounded border">
                                    <label class="form-label fw-bold text-dark d-block mb-3">
                                        <i class="bi bi-toggle-on text-primary me-1"></i>Ad Timer Controls (Enable / Disable Timer)
                                    </label>
                                    <div class="row g-3">
                                        {{-- Star.io Ad Timer Switch --}}
                                        <div class="col-md-6">
                                            <div class="p-3 bg-white rounded border shadow-sm h-100">
                                                <label class="form-label fw-semibold text-muted small d-block mb-2">
                                                    <i class="bi bi-star-fill text-warning me-1"></i>Star.io Ad Timer
                                                </label>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <input type="hidden" name="stario_timer_status" value="no">
                                                    <div class="form-check form-switch form-switch-lg mb-0 ps-0 d-flex align-items-center">
                                                        <input class="form-check-input ms-0 me-3 custom-toggle-switch" 
                                                               type="checkbox" 
                                                               role="switch" 
                                                               name="stario_timer_status" 
                                                               id="stario_timer_status_switch" 
                                                               value="yes" 
                                                               {{ old('stario_timer_status', $appsetting->stario_timer_status ?? 'yes') == 'yes' ? 'checked' : '' }}
                                                               onchange="updateTimerSwitchBadge('stario', this.checked)">
                                                    </div>
                                                    <span id="stario_timer_badge" class="badge px-3 py-2 fs-6 {{ old('stario_timer_status', $appsetting->stario_timer_status ?? 'yes') == 'yes' ? 'bg-success' : 'bg-danger' }}">
                                                        <i class="bi {{ old('stario_timer_status', $appsetting->stario_timer_status ?? 'yes') == 'yes' ? 'bi-check-circle' : 'bi-x-circle' }} me-1"></i>
                                                        {{ old('stario_timer_status', $appsetting->stario_timer_status ?? 'yes') == 'yes' ? 'Yes (Active)' : 'No (Disabled)' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Google AdMob Ad Timer Switch --}}
                                        <div class="col-md-6">
                                            <div class="p-3 bg-white rounded border shadow-sm h-100">
                                                <label class="form-label fw-semibold text-muted small d-block mb-2">
                                                    <i class="bi bi-google text-primary me-1"></i>Google AdMob Ad Timer
                                                </label>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <input type="hidden" name="admob_timer_status" value="no">
                                                    <div class="form-check form-switch form-switch-lg mb-0 ps-0 d-flex align-items-center">
                                                        <input class="form-check-input ms-0 me-3 custom-toggle-switch" 
                                                               type="checkbox" 
                                                               role="switch" 
                                                               name="admob_timer_status" 
                                                               id="admob_timer_status_switch" 
                                                               value="yes" 
                                                               {{ old('admob_timer_status', $appsetting->admob_timer_status ?? 'yes') == 'yes' ? 'checked' : '' }}
                                                               onchange="updateTimerSwitchBadge('admob', this.checked)">
                                                    </div>
                                                    <span id="admob_timer_badge" class="badge px-3 py-2 fs-6 {{ old('admob_timer_status', $appsetting->admob_timer_status ?? 'yes') == 'yes' ? 'bg-success' : 'bg-danger' }}">
                                                        <i class="bi {{ old('admob_timer_status', $appsetting->admob_timer_status ?? 'yes') == 'yes' ? 'bi-check-circle' : 'bi-x-circle' }} me-1"></i>
                                                        {{ old('admob_timer_status', $appsetting->admob_timer_status ?? 'yes') == 'yes' ? 'Yes (Active)' : 'No (Disabled)' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <script>
                                function updateTimerSwitchBadge(type, isChecked) {
                                    const badge = document.getElementById(type + '_timer_badge');
                                    if (isChecked) {
                                        badge.className = 'badge px-3 py-2 fs-6 bg-success';
                                        badge.innerHTML = '<i class="bi bi-check-circle me-1"></i> Yes (Active)';
                                    } else {
                                        badge.className = 'badge px-3 py-2 fs-6 bg-danger';
                                        badge.innerHTML = '<i class="bi bi-x-circle me-1"></i> No (Disabled)';
                                    }
                                }
                            </script>

                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: VPN & App Control --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-shield-check me-2"></i>VPN, App Control & Configuration
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            {{-- VPN Modes --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-shield-lock text-primary me-1"></i>VPN Modes
                                </label>
                                <select name="vpn_modes" class="form-select @error('vpn_modes') is-invalid @enderror">
                                    <option value="not_allowed" {{ old('vpn_modes', $appsetting->vpn_modes) == 'not_allowed' ? 'selected' : '' }}>Not Allowed</option>
                                    <option value="allowed" {{ old('vpn_modes', $appsetting->vpn_modes) == 'allowed' ? 'selected' : '' }}>Allowed</option>
                                    <option value="required" {{ old('vpn_modes', $appsetting->vpn_modes) == 'required' ? 'selected' : '' }}>Required</option>
                                </select>
                                @error('vpn_modes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- VPN Required Task Only --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-check-circle text-success me-1"></i>VPN in Task Only
                                </label>
                                <select name="vpn_required_in_task_only" class="form-select @error('vpn_required_in_task_only') is-invalid @enderror">
                                    <option value="no" {{ old('vpn_required_in_task_only', $appsetting->vpn_required_in_task_only) == 'no' ? 'selected' : '' }}>No</option>
                                    <option value="yes" {{ old('vpn_required_in_task_only', $appsetting->vpn_required_in_task_only) == 'yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                                @error('vpn_required_in_task_only')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Allowed Country --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-globe text-info me-1"></i>Allowed Countries
                                </label>
                                <input type="text"
                                       class="form-control @error('allowed_country') is-invalid @enderror"
                                       name="allowed_country"
                                       value="{{ old('allowed_country', $appsetting->allowed_country) }}"
                                       placeholder="us,uk,au,bangladesh,india">
                                <small class="form-text text-muted">Comma-separated country codes</small>
                                @error('allowed_country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12"><hr></div>

                            {{-- Registration Status --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-door-open text-success me-1"></i>Registration
                                </label>
                                <select name="registration_status" class="form-select @error('registration_status') is-invalid @enderror">
                                    <option value="open" {{ old('registration_status', $appsetting->registration_status) == 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="closed" {{ old('registration_status', $appsetting->registration_status) == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                                @error('registration_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Same Device Login --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-phone text-warning me-1"></i>Same Device Login
                                </label>
                                <select name="same_device_login" class="form-select @error('same_device_login') is-invalid @enderror">
                                    <option value="no" {{ old('same_device_login', $appsetting->same_device_login) == 'no' ? 'selected' : '' }}>No</option>
                                    <option value="yes" {{ old('same_device_login', $appsetting->same_device_login) == 'yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                                @error('same_device_login')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Maintenance Mode --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-tools text-danger me-1"></i>Maintenance Mode
                                </label>
                                <select name="maintenance_mode" class="form-select @error('maintenance_mode') is-invalid @enderror">
                                    <option value="no" {{ old('maintenance_mode', $appsetting->maintenance_mode) == 'no' ? 'selected' : '' }}>No</option>
                                    <option value="yes" {{ old('maintenance_mode', $appsetting->maintenance_mode) == 'yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                                @error('maintenance_mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12"><hr></div>

                            {{-- App Version --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-app-indicator text-primary me-1"></i>App Version
                                </label>
                                <input type="text"
                                       class="form-control @error('app_version') is-invalid @enderror"
                                       name="app_version"
                                       value="{{ old('app_version', $appsetting->app_version) }}"
                                       placeholder="1.0.0">
                                @error('app_version')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- App Link --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-link-45deg text-info me-1"></i>App Link
                                </label>
                                <input type="url"
                                       class="form-control @error('app_link') is-invalid @enderror"
                                       name="app_link"
                                       value="{{ old('app_link', $appsetting->app_link) }}"
                                       placeholder="https://play.google.com/...">
                                @error('app_link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- FULL WIDTH: Google AdMob Settings --}}
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-google me-2"></i>Google AdMob Configuration
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            {{-- AdMob App ID --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">AdMob App ID</label>
                                <input type="text"
                                       class="form-control @error('admob_app_id') is-invalid @enderror"
                                       name="admob_app_id"
                                       value="{{ old('admob_app_id', $appsetting->admob_app_id) }}"
                                       placeholder="ca-app-pub-XXXXXXXXXXXXXXXX~YYYYYYYYYY">
                                @error('admob_app_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- AdMob Banner ID --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">AdMob Banner ID</label>
                                <input type="text"
                                       class="form-control @error('admob_banner_id') is-invalid @enderror"
                                       name="admob_banner_id"
                                       value="{{ old('admob_banner_id', $appsetting->admob_banner_id) }}"
                                       placeholder="ca-app-pub-XXXXXXXXXXXXXXXX/YYYYYYYYYY">
                                @error('admob_banner_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- AdMob Interstitial ID --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">AdMob Interstitial ID</label>
                                <input type="text"
                                       class="form-control @error('admob_interstitial_id') is-invalid @enderror"
                                       name="admob_interstitial_id"
                                       value="{{ old('admob_interstitial_id', $appsetting->admob_interstitial_id) }}"
                                       placeholder="ca-app-pub-XXXXXXXXXXXXXXXX/YYYYYYYYYY">
                                @error('admob_interstitial_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- AdMob Rewarded Interstitial ID --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">AdMob Rewarded Interstitial ID</label>
                                <input type="text"
                                       class="form-control @error('admob_rewarded_interstitial_id') is-invalid @enderror"
                                       name="admob_rewarded_interstitial_id"
                                       value="{{ old('admob_rewarded_interstitial_id', $appsetting->admob_rewarded_interstitial_id) }}"
                                       placeholder="ca-app-pub-XXXXXXXXXXXXXXXX/YYYYYYYYYY">
                                @error('admob_rewarded_interstitial_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- AdMob Rewarded ID --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">AdMob Rewarded ID</label>
                                <input type="text"
                                       class="form-control @error('admob_rewarded_id') is-invalid @enderror"
                                       name="admob_rewarded_id"
                                       value="{{ old('admob_rewarded_id', $appsetting->admob_rewarded_id) }}"
                                       placeholder="ca-app-pub-XXXXXXXXXXXXXXXX/YYYYYYYYYY">
                                @error('admob_rewarded_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- AdMob Native ID --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">AdMob Native ID</label>
                                <input type="text"
                                       class="form-control @error('admob_native_id') is-invalid @enderror"
                                       name="admob_native_id"
                                       value="{{ old('admob_native_id', $appsetting->admob_native_id) }}"
                                       placeholder="ca-app-pub-XXXXXXXXXXXXXXXX/YYYYYYYYYY">
                                @error('admob_native_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- AdMob App Open ID --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">AdMob App Open ID</label>
                                <input type="text"
                                       class="form-control @error('admob_app_open_id') is-invalid @enderror"
                                       name="admob_app_open_id"
                                       value="{{ old('admob_app_open_id', $appsetting->admob_app_open_id) }}"
                                       placeholder="ca-app-pub-XXXXXXXXXXXXXXXX/YYYYYYYYYY">
                                @error('admob_app_open_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- AdMob Status --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">AdMob Status</label>
                                <select name="admob_status" class="form-select @error('admob_status') is-invalid @enderror">
                                    <option value="0" {{ old('admob_status', $appsetting->admob_status) == 0 ? 'selected' : '' }}>Disabled</option>
                                    <option value="1" {{ old('admob_status', $appsetting->admob_status) == 1 ? 'selected' : '' }}>Enabled</option>
                                </select>
                                @error('admob_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="col-12">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save me-2"></i>Update App Settings
                    </button>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection
