@extends('admin.master')

@section('content')

<form action="{{ route('appsetting.update', $appsetting->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row gy-4">

        <!-- Left Column: Basic & Task Settings -->
        <div class="col-md-6">
             <a href="{{ route('appsetting.index') }}" class="btn btn-success mb-2">
            <i class="bi bi-arrow-left-circle"></i> Back to Index
        </a>
            <div class="card">
                <div class="card-header"><h6 class="card-title mb-0">App Settings</h6></div>

                <div class="card-body">
                    <div class="row gy-3">

                        <!-- Basic Settings -->
                        <div class="col-12">
                            <label class="form-label">Star App ID</label>
                            <input type="number" name="star_io_id" class="form-control" value="{{ old('star_io_id', $appsetting->star_io_id) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">App Theme</label>
                            <select name="app_theme" class="form-select">
                                @php
                                    $themes = ['Elegant','Classic','Simple','Elegant Pro'];
                                @endphp
                                @foreach($themes as $theme)
                                    <option value="{{ $theme }}" {{ old('app_theme', $appsetting->app_theme) == $theme ? 'selected' : '' }}>{{ $theme }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Home Icon Style</label>
                            <select name="home_icon_themes" class="form-select">
                                @php
                                    $icons = ['Gradient','Simple','Solid','Default'];
                                @endphp
                                @foreach($icons as $icon)
                                    <option value="{{ $icon }}" {{ old('home_icon_themes', $appsetting->home_icon_themes) == $icon ? 'selected' : '' }}>{{ $icon }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Currency Symbol</label>
                            <input type="text" name="currency_symbol" class="form-control" value="{{ old('currency_symbol', $appsetting->currency_symbol) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Status</label>
                            <select name="enabled" class="form-select">
                                <option value="enabled" {{ old('enabled', $appsetting->enabled) == 'enabled' ? 'selected' : '' }}>Enabled</option>
                                <option value="disabled" {{ old('enabled', $appsetting->enabled) == 'disabled' ? 'selected' : '' }}>Disabled</option>
                            </select>
                        </div>

                        <!-- Task Rewards -->
                        @for($i=1; $i<=5; $i++)
                        <div class="col-12">
                            <label class="form-label">Task Rewards Level {{ $i }}</label>
                            <input type="number" step="0.01" name="task_rewards_level_{{ $i }}" class="form-control" value="{{ old('task_rewards_level_'.$i, $appsetting->{'task_rewards_level_'.$i}) }}">
                        </div>
                        @endfor

                        <!-- Referral & Invalid Click -->
                        <div class="col-12">
                            <label class="form-label">Referral Commission</label>
                            <input type="number" name="refer_commission" class="form-control" value="{{ old('refer_commission', $appsetting->refer_commission) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Invalid Click Limit</label>
                            <input type="number" name="invalid_click_limit" class="form-control" value="{{ old('invalid_click_limit', $appsetting->invalid_click_limit) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Invalid Deduct</label>
                            <input type="number" name="invalid_deduct" class="form-control" value="{{ old('invalid_deduct', $appsetting->invalid_deduct) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">View Before Click Target</label>
                            <input type="number" name="view_before_click_view_target" class="form-control" value="{{ old('view_before_click_view_target', $appsetting->view_before_click_view_target) }}">
                        </div>

                        <!-- Task Limits -->
                        @for($i=1; $i<=5; $i++)
                        <div class="col-12">
                            <label class="form-label">Task Limit Level {{ $i }}</label>
                            <input type="number" name="task_limit_level_{{ $i }}" class="form-control" value="{{ old('task_limit_level_'.$i, $appsetting->{'task_limit_level_'.$i}) }}">
                        </div>
                        @endfor

                        <div class="col-12">
                            <label class="form-label">Task Break Time (Minutes)</label>
                            <input type="number" name="task_break_time_minutes" class="form-control" value="{{ old('task_break_time_minutes', $appsetting->task_break_time_minutes) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Button Timer (Seconds)</label>
                            <input type="number" name="button_timer_seconds" class="form-control" value="{{ old('button_timer_seconds', $appsetting->button_timer_seconds) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Statistics Point Rate</label>
                            <input type="number" name="statistics_point_rate" class="form-control" value="{{ old('statistics_point_rate', $appsetting->statistics_point_rate) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Paywell Point Rate</label>
                            <input type="number" name="paywell_point_rate" class="form-control" value="{{ old('paywell_point_rate', $appsetting->paywell_point_rate) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Fixed Withdraw</label>
                            <select name="fixed_withdraw" class="form-select">
                                <option value="yes" {{ old('fixed_withdraw', $appsetting->fixed_withdraw) == 'yes' ? 'selected' : '' }}>Yes</option>
                                <option value="no" {{ old('fixed_withdraw', $appsetting->fixed_withdraw) == 'no' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary mt-3">Update Settings</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: VPN & App Config -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h6 class="card-title mb-0">VPN & App Configuration</h6></div>
                <div class="card-body">
                    <div class="row gy-3">

                        <div class="col-12">
                            <label class="form-label">VPN Modes</label>
                            <select name="vpn_modes" class="form-select">
                                <option value="not_allowed" {{ old('vpn_modes', $appsetting->vpn_modes) == 'not_allowed' ? 'selected' : '' }}>Not Allowed</option>
                                <option value="required" {{ old('vpn_modes', $appsetting->vpn_modes) == 'required' ? 'selected' : '' }}>Required</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">VPN Required in Task Only</label>
                            <select name="vpn_required_in_task_only" class="form-select">
                                <option value="yes" {{ old('vpn_required_in_task_only', $appsetting->vpn_required_in_task_only) == 'yes' ? 'selected' : '' }}>Yes</option>
                                <option value="no" {{ old('vpn_required_in_task_only', $appsetting->vpn_required_in_task_only) == 'no' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Allowed Country</label>
                            <input type="text" name="allowed_country" class="form-control" value="{{ old('allowed_country', $appsetting->allowed_country) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Info API Key</label>
                            <input type="text" name="info_api_key" class="form-control" value="{{ old('info_api_key', $appsetting->info_api_key) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Telegram Channel Link</label>
                            <input type="text" name="telegram" class="form-control" value="{{ old('telegram', $appsetting->telegram) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">WhatsApp Link</label>
                            <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $appsetting->whatsapp) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Support Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $appsetting->email) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">How To Work Link</label>
                            <input type="text" name="how_to_work_link" class="form-control" value="{{ old('how_to_work_link', $appsetting->how_to_work_link) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Privacy Policy</label>
                            <input type="text" name="privacy_policy" class="form-control" value="{{ old('privacy_policy', $appsetting->privacy_policy) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Registration Status</label>
                            <select name="registration_status" class="form-select">
                                <option value="open" {{ old('registration_status', $appsetting->registration_status) == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="closed" {{ old('registration_status', $appsetting->registration_status) == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Same Device Login</label>
                            <select name="same_device_login" class="form-select">
                                <option value="yes" {{ old('same_device_login', $appsetting->same_device_login) == 'yes' ? 'selected' : '' }}>Yes</option>
                                <option value="no" {{ old('same_device_login', $appsetting->same_device_login) == 'no' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Maintenance Mode</label>
                            <select name="maintenance_mode" class="form-select">
                                <option value="yes" {{ old('maintenance_mode', $appsetting->maintenance_mode) == 'yes' ? 'selected' : '' }}>Yes</option>
                                <option value="no" {{ old('maintenance_mode', $appsetting->maintenance_mode) == 'no' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">App Version</label>
                            <input type="text" name="app_version" class="form-control" value="{{ old('app_version', $appsetting->app_version) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">App Link</label>
                            <input type="text" name="app_link" class="form-control" value="{{ old('app_link', $appsetting->app_link) }}">
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</form>
@endsection
