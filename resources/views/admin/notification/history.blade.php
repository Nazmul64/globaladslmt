{{-- resources/views/admin/notification/history.blade.php --}}

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
                    <a href="{{ route('notification.send') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>Send New
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
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
                            <div class="col-md-3">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
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
                                        <th>App</th>
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
                                            <span class="badge bg-secondary">
                                                {{ $notification->firebaseApp->app_name ?? 'N/A' }}
                                            </span>
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
                                                @if($notification->send_to == 'specific' && $notification->user_ids)
                                                    ({{ count($notification->user_ids) }} users)
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
                                                $rate = $notification->success_rate;
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
                                                <i class="fas fa-eye"></i> View
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
                                                            <strong>Firebase App:</strong>
                                                            <p>{{ $notification->firebaseApp->app_name ?? 'N/A' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Sent At:</strong>
                                                            <p>{{ $notification->sent_at->format('d M, Y h:i A') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Send To:</strong>
                                                            <p>
                                                                <span class="badge {{ $notification->send_to == 'all' ? 'bg-primary' : 'bg-warning' }}">
                                                                    {{ ucfirst($notification->send_to) }}
                                                                    @if($notification->send_to == 'specific' && $notification->user_ids)
                                                                        ({{ count($notification->user_ids) }} users)
                                                                    @endif
                                                                </span>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Success Rate:</strong>
                                                            <p>
                                                                <span class="badge {{ $notification->success_rate >= 80 ? 'bg-success' : ($notification->success_rate >= 50 ? 'bg-warning' : 'bg-danger') }}">
                                                                    {{ $notification->success_rate }}%
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
                                                            <img src="{{ $notification->image_url }}" class="img-fluid rounded" style="max-width: 300px;" alt="Notification Image" onerror="this.style.display='none'">
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
                            {{ $notifications->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No notification history found</p>
                            @if(request()->has('firebase_app_id') || request()->has('date_from') || request()->has('date_to'))
                                <a href="{{ route('notification.history') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Clear Filters
                                </a>
                            @else
                                <a href="{{ route('notification.send') }}" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i>Send First Notification
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Summary -->
    @if($notifications->count() > 0)
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="text-primary">{{ $notifications->total() }}</h5>
                    <p class="mb-0">Total Notifications</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="text-success">{{ $notifications->sum('total_sent') }}</h5>
                    <p class="mb-0">Total Sent</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="text-danger">{{ $notifications->sum('total_failed') }}</h5>
                    <p class="mb-0">Total Failed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    @php
                        $totalSent = $notifications->sum('total_sent');
                        $totalFailed = $notifications->sum('total_failed');
                        $avgRate = ($totalSent + $totalFailed) > 0 ? round(($totalSent / ($totalSent + $totalFailed)) * 100, 2) : 0;
                    @endphp
                    <h5 class="text-info">{{ $avgRate }}%</h5>
                    <p class="mb-0">Avg Success Rate</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
