@extends('admin.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-gradient-primary d-flex justify-content-between align-items-center">
                    <h5 class="text-white mb-0">
                        <i class="fas fa-users me-2"></i>App Users Management
                    </h5>
                </div>

                <div class="card-body">
                    <!-- Filter Section -->
                    <form method="GET" action="{{ route('usersIndex') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <select name="firebase_app_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Apps</option>
                                    @foreach($apps as $app)
                                        <option value="{{ $app->id }}" {{ request('firebase_app_id') == $app->id ? 'selected' : '' }}>
                                            {{ $app->app_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('usersIndex') }}" class="btn btn-secondary w-100">
                                    <i class="fas fa-redo"></i> Reset Filter
                                </a>
                            </div>
                        </div>
                    </form>

                    @if($users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>App</th>
                                        <th>User Name</th>
                                        <th>Email</th>
                                        <th>Device Type</th>
                                        <th>FCM Token</th>
                                        <th>Registered</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $user->firebaseApp->app_name ?? 'N/A' }}</strong>
                                        </td>
                                        <td>{{ $user->user_name }}</td>
                                        <td>{{ $user->user_email }}</td>
                                        <td>
                                            <span class="badge bg-{{ $user->device_type == 'android' ? 'success' : 'primary' }}">
                                                {{ ucfirst($user->device_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($user->fcm_token)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check"></i> Active
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times"></i> No Token
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $user->created_at->format('d M, Y h:i A') }}
                                            </small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">কোনো user পাওয়া যায়নি</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
