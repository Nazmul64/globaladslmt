{{-- resources/views/admin/notification/users.blade.php --}}

@extends('admin.master')

@section('content')
<div class="container-fluid py-4">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h4 class="text-primary mb-0">{{ $totalUsers }}</h4>
                    <p class="text-muted mb-0">Total Users with FCM</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h4 class="text-success mb-0">{{ $activeTokens }}</h4>
                    <p class="text-muted mb-0">Active Tokens</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fab fa-android fa-2x text-success mb-2"></i>
                    <h4 class="text-success mb-0">{{ \App\Models\User::where('device_type', 'android')->whereNotNull('fcm_token')->count() }}</h4>
                    <p class="text-muted mb-0">Android Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fab fa-apple fa-2x text-info mb-2"></i>
                    <h4 class="text-info mb-0">{{ \App\Models\User::where('device_type', 'ios')->whereNotNull('fcm_token')->count() }}</h4>
                    <p class="text-muted mb-0">iOS Users</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-gradient-primary d-flex justify-content-between align-items-center">
                    <h5 class="text-white mb-0">
                        <i class="fas fa-users me-2"></i>Users with FCM Tokens
                    </h5>
                    <a href="{{ route('notification.send') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-paper-plane me-1"></i>Send Notification
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Firebase App</label>
                                <select name="firebase_app_id" class="form-select">
                                    <option value="">All Apps</option>
                                    @foreach($apps as $app)
                                        <option value="{{ $app->id }}" {{ request('firebase_app_id') == $app->id ? 'selected' : '' }}>
                                            {{ $app->app_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Device Type</label>
                                <select name="device_type" class="form-select">
                                    <option value="">All Devices</option>
                                    <option value="android" {{ request('device_type') == 'android' ? 'selected' : '' }}>Android</option>
                                    <option value="ios" {{ request('device_type') == 'ios' ? 'selected' : '' }}>iOS</option>
                                    <option value="web" {{ request('device_type') == 'web' ? 'selected' : '' }}>Web</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Name or email..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>

                    @if($users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>User Info</th>
                                        <th>Firebase App</th>
                                        <th>Device</th>
                                        <th>FCM Status</th>
                                        <th>Last Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr>
                                        <td>{{ $users->firstItem() + $loop->index }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if(isset($user->photo))
                                                <img src="{{ asset('uploads/users/' . $user->photo) }}"
                                                     class="rounded-circle me-2"
                                                     width="40" height="40"
                                                     alt="User"
                                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random'">
                                                @else
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random"
                                                     class="rounded-circle me-2"
                                                     width="40" height="40"
                                                     alt="User">
                                                @endif
                                                <div>
                                                    <strong>{{ $user->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($user->firebaseApp)
                                                <span class="badge bg-secondary">
                                                    {{ $user->firebaseApp->app_name }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->device_type)
                                                <span class="badge {{ $user->device_type == 'android' ? 'bg-success' : ($user->device_type == 'ios' ? 'bg-info' : 'bg-secondary') }}">
                                                    <i class="fab fa-{{ $user->device_type == 'android' ? 'android' : ($user->device_type == 'ios' ? 'apple' : 'chrome') }}"></i>
                                                    {{ ucfirst($user->device_type) }}
                                                </span>
                                            @else
                                                <span class="text-muted">Unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->fcm_token)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> Active
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle"></i> No Token
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->fcm_updated_at)
                                                <small class="text-muted">
                                                    {{ $user->fcm_updated_at->diffForHumans() }}
                                                </small>
                                            @else
                                                <span class="text-muted">Never</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button"
                                                        class="btn btn-sm btn-info"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewUserModal{{ $user->id }}"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if($user->fcm_token)
                                                <form action="{{ route('notification.users.clear-token', $user->id) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Clear FCM token for this user?')">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-warning" title="Clear FCM Token">
                                                        <i class="fas fa-eraser"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- View User Modal -->
                                    <div class="modal fade" id="viewUserModal{{ $user->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-gradient-info">
                                                    <h5 class="modal-title text-white">User Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Name:</strong>
                                                            <p>{{ $user->name }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Email:</strong>
                                                            <p>{{ $user->email }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Firebase App:</strong>
                                                            <p>{{ $user->firebaseApp->app_name ?? 'Not Assigned' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Device Type:</strong>
                                                            <p>
                                                                @if($user->device_type)
                                                                    <span class="badge {{ $user->device_type == 'android' ? 'bg-success' : ($user->device_type == 'ios' ? 'bg-info' : 'bg-secondary') }}">
                                                                        {{ ucfirst($user->device_type) }}
                                                                    </span>
                                                                @else
                                                                    Unknown
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>FCM Token Updated:</strong>
                                                            <p>
                                                                @if($user->fcm_updated_at)
                                                                    {{ $user->fcm_updated_at->format('d M, Y h:i A') }}
                                                                    <br>
                                                                    <small class="text-muted">({{ $user->fcm_updated_at->diffForHumans() }})</small>
                                                                @else
                                                                    Never
                                                                @endif
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Registered:</strong>
                                                            <p>{{ $user->created_at->format('d M, Y h:i A') }}</p>
                                                        </div>
                                                    </div>
                                                    @if($user->fcm_token)
                                                    <div class="mb-3">
                                                        <strong>FCM Token:</strong>
                                                        <p>
                                                            <code style="word-break: break-all; font-size: 11px;">
                                                                {{ Str::limit($user->fcm_token, 150) }}
                                                            </code>
                                                        </p>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $users->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No users found with FCM tokens</p>
                            @if(request()->has('firebase_app_id') || request()->has('search') || request()->has('device_type'))
                                <a href="{{ route('notification.users.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Clear Filters
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
