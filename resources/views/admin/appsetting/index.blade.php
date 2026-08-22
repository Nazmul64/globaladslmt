@extends('admin.master')

@section('content')
<div class="container-fluid py-4">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-gear-fill me-2"></i>App Settings Management
        </h4>
        <a href="{{ route('appsetting.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i> Add New Setting
        </a>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" style="width: 50px;">ID</th>
                            <th>Basic Info</th>
                            <th>Timers & Limits</th>
                            <th>VPN Settings</th>
                            <th>App Control</th>
                            <th>AdMob Status</th>
                            <th class="text-center" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appsettings as $setting)
                            <tr>
                                {{-- ID --}}
                                <td class="text-center fw-bold">{{ $setting->id }}</td>

                                {{-- Basic Info --}}
                                <td>
                                    <div class="mb-1">
                                        <strong>Star.io (StartApp) App ID:</strong>
                                        <span class="badge bg-warning text-dark fw-bold">{{ $setting->star_io_id ?? 'N/A' }}</span>
                                    </div>
                                    <div class="mb-1">
                                        <strong>App Version:</strong>
                                        <span class="text-muted">{{ $setting->app_version ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <strong>App Link:</strong>
                                        @if($setting->app_link)
                                            <a href="{{ $setting->app_link }}" target="_blank" class="text-decoration-none">
                                                <i class="bi bi-link-45deg"></i> View
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Timers & Limits --}}
                                <td>
                                    <div class="mb-1">
                                        <i class="bi bi-clock text-primary"></i>
                                        <strong>Break Time:</strong> {{ $setting->task_break_time_minutes ?? 'N/A' }} min
                                    </div>
                                    <div class="mb-1">
                                        <i class="bi bi-stopwatch text-success"></i>
                                        <strong>Button Timer:</strong> {{ $setting->button_timer_seconds ?? 'N/A' }} sec
                                    </div>
                                    <div class="mb-1">
                                        <i class="bi bi-play-circle text-info"></i>
                                        <strong>Ad Timer:</strong> {{ $setting->ad_timer_seconds ?? 'N/A' }} sec
                                    </div>
                                    <div class="mb-1">
                                        <i class="bi bi-x-circle text-danger"></i>
                                        <strong>Invalid Limit:</strong> {{ $setting->invalid_click_limit ?? 'N/A' }}
                                    </div>
                                    <div>
                                        <i class="bi bi-dash-circle text-warning"></i>
                                        <strong>Invalid Deduct:</strong> {{ $setting->invalid_deduct ?? 'N/A' }}
                                    </div>
                                </td>

                                {{-- VPN Settings --}}
                                <td>
                                    <div class="mb-1">
                                        <strong>Mode:</strong>
                                        @if($setting->vpn_modes == 'required')
                                            <span class="badge bg-success">Required</span>
                                        @else
                                            <span class="badge bg-danger">Not Allowed</span>
                                        @endif
                                    </div>
                                    <div class="mb-1">
                                        <strong>Task Only:</strong>
                                        <span class="badge {{ $setting->vpn_required_in_task_only == 'yes' ? 'bg-info' : 'bg-secondary' }}">
                                            {{ ucfirst($setting->vpn_required_in_task_only ?? 'N/A') }}
                                        </span>
                                    </div>
                                    <div>
                                        <strong>Countries:</strong>
                                        <small class="text-muted d-block">{{ $setting->allowed_country ?? 'N/A' }}</small>
                                    </div>
                                </td>

                                {{-- App Control --}}
                                <td>
                                    <div class="mb-1">
                                        <strong>Registration:</strong>
                                        <span class="badge {{ $setting->registration_status == 'open' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($setting->registration_status ?? 'N/A') }}
                                        </span>
                                    </div>
                                    <div class="mb-1">
                                        <strong>Same Device:</strong>
                                        <span class="badge {{ $setting->same_device_login == 'yes' ? 'bg-warning' : 'bg-secondary' }}">
                                            {{ ucfirst($setting->same_device_login ?? 'N/A') }}
                                        </span>
                                    </div>
                                    <div>
                                        <strong>Maintenance:</strong>
                                        <span class="badge {{ $setting->maintenance_mode == 'yes' ? 'bg-danger' : 'bg-success' }}">
                                            {{ $setting->maintenance_mode == 'yes' ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- AdMob Status --}}
                                <td class="text-center">
                                    @if($setting->admob_status)
                                        <span class="badge bg-success fs-6">
                                            <i class="bi bi-check-circle me-1"></i>Enabled
                                        </span>
                                    @else
                                        <span class="badge bg-secondary fs-6">
                                            <i class="bi bi-x-circle me-1"></i>Disabled
                                        </span>
                                    @endif
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-outline-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#admobModal{{ $setting->id }}">
                                            <i class="bi bi-eye"></i> View Details
                                        </button>
                                    </div>
                                </td>

                                {{-- Actions --}}
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('appsetting.edit', $setting->id) }}"
                                           class="btn btn-sm btn-primary"
                                           title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('appsetting.destroy', $setting->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this setting?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- AdMob Details Modal --}}
                            <div class="modal fade" id="admobModal{{ $setting->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-dark text-white">
                                            <h5 class="modal-title">
                                                <i class="bi bi-google me-2"></i>AdMob Configuration Details
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th style="width: 40%;">App ID</th>
                                                    <td>{{ $setting->admob_app_id ?? 'Not Set' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Banner ID</th>
                                                    <td>{{ $setting->admob_banner_id ?? 'Not Set' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Interstitial ID</th>
                                                    <td>{{ $setting->admob_interstitial_id ?? 'Not Set' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Rewarded Interstitial ID</th>
                                                    <td>{{ $setting->admob_rewarded_interstitial_id ?? 'Not Set' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Rewarded ID</th>
                                                    <td>{{ $setting->admob_rewarded_id ?? 'Not Set' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Native ID</th>
                                                    <td>{{ $setting->admob_native_id ?? 'Not Set' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>App Open ID</th>
                                                    <td>{{ $setting->admob_app_open_id ?? 'Not Set' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Status</th>
                                                    <td>
                                                        <span class="badge {{ $setting->admob_status ? 'bg-success' : 'bg-secondary' }}">
                                                            {{ $setting->admob_status ? 'Enabled' : 'Disabled' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                    <p class="text-muted mb-0">No app settings found. Create your first setting!</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
