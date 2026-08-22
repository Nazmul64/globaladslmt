@extends('admin.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-gradient-info d-flex justify-content-between align-items-center">
                    <h5 class="text-white mb-0">
                        <i class="fas fa-history me-2"></i>Notification History
                    </h5>
                    <a href="{{ route('index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>Send New
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filter Section -->
                    <form method="GET" action="{{ route('admin.history') }}" class="mb-4">
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
                                <a href="{{ route('admin.history') }}" class="btn btn-secondary w-100">
                                    <i class="fas fa-redo"></i> Reset Filter
                                </a>
                            </div>
                        </div>
                    </form>

                    @if($notifications->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Date & Time</th>
                                        <th>App Name</th>
                                        <th>Title</th>
                                        <th>Message</th>
                                        <th>Send To</th>
                                        <th>Sent</th>
                                        <th>Failed</th>
                                        <th>Success Rate</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notifications as $notification)
                                    <tr>
                                        <td>{{ $notifications->firstItem() + $loop->index }}</td>
                                        <td>
                                            <div>
                                                <strong>{{ $notification->sent_at->format('d M, Y') }}</strong>
                                            </div>
                                            <small class="text-muted">{{ $notification->sent_at->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            @php
                                                $app = Notification_log::getAppByPackage($notification->package_name);
                                            @endphp
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-gradient-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                                    <i class="fas fa-mobile-alt text-white" style="font-size: 12px;"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $app->app_name ?? 'Deleted App' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $notification->package_name }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>{{ Str::limit($notification->title, 40) }}</strong>
                                        </td>
                                        <td>
                                            {{ Str::limit($notification->message, 50) }}
                                        </td>
                                        <td>
                                            <span class="badge {{ $notification->send_to == 'all' ? 'bg-primary' : 'bg-warning' }}">
                                                {{ ucfirst($notification->send_to) }}
                                                @if($notification->send_to == 'specific' && isset($notification->extra_data['user_emails']))
                                                    ({{ count($notification->extra_data['user_emails']) }} users)
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> {{ $notification->total_sent }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($notification->total_failed > 0)
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times"></i> {{ $notification->total_failed }}
                                                </span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $total = $notification->total_sent + $notification->total_failed;
                                                $rate = $total > 0 ? round(($notification->total_sent / $total) * 100, 2) : 0;
                                            @endphp
                                            <div class="progress" style="height: 20px; min-width: 80px;">
                                                <div class="progress-bar {{ $rate >= 80 ? 'bg-success' : ($rate >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                     role="progressbar"
                                                     style="width: {{ $rate }}%">
                                                    {{ $rate }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-sm btn-info"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewModal{{ $notification->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- View Modal -->
                                    <div class="modal fade" id="viewModal{{ $notification->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-gradient-info">
                                                    <h5 class="modal-title text-white">Notification Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>App Name:</strong>
                                                            <p>{{ $app->app_name ?? 'N/A' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Package Name:</strong>
                                                            <p><code>{{ $notification->package_name }}</code></p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Sent At:</strong>
                                                            <p>{{ $notification->sent_at->format('d M, Y h:i A') }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Send To:</strong>
                                                            <p>
                                                                <span class="badge {{ $notification->send_to == 'all' ? 'bg-primary' : 'bg-warning' }}">
                                                                    {{ ucfirst($notification->send_to) }}
                                                                </span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Title:</strong>
                                                        <p class="alert alert-secondary">{{ $notification->title }}</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Message:</strong>
                                                        <p class="alert alert-secondary">{{ $notification->message }}</p>
                                                    </div>
                                                    @if($notification->image_url)
                                                        <div class="mb-3">
                                                            <strong>Image URL:</strong>
                                                            <p><a href="{{ $notification->image_url }}" target="_blank">{{ $notification->image_url }}</a></p>
                                                        </div>
                                                    @endif
                                                    @if($notification->action_url)
                                                        <div class="mb-3">
                                                            <strong>Action URL:</strong>
                                                            <p><code>{{ $notification->action_url }}</code></p>
                                                        </div>
                                                    @endif
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="card text-center">
                                                                <div class="card-body">
                                                                    <h4 class="text-success">{{ $notification->total_sent }}</h4>
                                                                    <p class="mb-0">Total Sent</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card text-center">
                                                                <div class="card-body">
                                                                    <h4 class="text-danger">{{ $notification->total_failed }}</h4>
                                                                    <p class="mb-0">Failed</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
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
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">কোনো notification history পাওয়া যায়নি</p>
                            <a href="{{ route('index') }}" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>প্রথম Notification পাঠান
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
