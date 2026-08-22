@extends('admin.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-gradient-success d-flex justify-content-between align-items-center">
                    <h5 class="text-white mb-0">
                        <i class="fas fa-paper-plane me-2"></i>Send Push Notification
                    </h5>
                    <a href="{{ route('admin.history') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-history me-1"></i>History
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

                    <form action="{{ route('send') }}" method="POST" id="notificationForm">
                        @csrf

                        <!-- App Selection -->
                        <div class="mb-3">
                            <label for="firebase_app_id" class="form-label">Select App <span class="text-danger">*</span></label>
                            <select class="form-select" id="firebase_app_id" name="firebase_app_id" required>
                                <option value="">-- Select App --</option>
                                @foreach($apps as $app)
                                    <option value="{{ $app->id }}">{{ $app->app_name }} ({{ $app->package_name }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Notification Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Enter notification title" required>
                        </div>

                        <!-- Message -->
                        <div class="mb-3">
                            <label for="message" class="form-label">Notification Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="4" placeholder="Enter notification message" required></textarea>
                        </div>

                        <!-- Image URL (Optional) -->
                        <div class="mb-3">
                            <label for="image_url" class="form-label">Image URL (Optional)</label>
                            <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
                            <small class="text-muted">Leave empty if no image needed</small>
                        </div>

                        <!-- Action URL (Optional) -->
                        <div class="mb-3">
                            <label for="action_url" class="form-label">Action URL (Optional)</label>
                            <input type="text" class="form-control" id="action_url" name="action_url" placeholder="https://example.com or app://screen">
                            <small class="text-muted">User will be redirected when tapping notification</small>
                        </div>

                        <!-- Send To -->
                        <div class="mb-3">
                            <label class="form-label">Send To <span class="text-danger">*</span></label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="send_to" id="send_to_all" value="all" checked>
                                    <label class="form-check-label" for="send_to_all">
                                        All Users
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="send_to" id="send_to_specific" value="specific">
                                    <label class="form-check-label" for="send_to_specific">
                                        Specific Users
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- User Selection (Hidden by default) -->
                        <div class="mb-3" id="user_selection_div" style="display: none;">
                            <label class="form-label">Select Users <span class="text-danger">*</span></label>
                            <div id="users_list" class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                <p class="text-muted text-center">Please select an app first</p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="fas fa-paper-plane me-1"></i>Send Notification
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Notifications -->
    @if($sentNotifications && $sentNotifications->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-gradient-info">
                    <h5 class="text-white mb-0">
                        <i class="fas fa-clock me-2"></i>Recent Notifications
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>App</th>
                                    <th>Title</th>
                                    <th>Sent</th>
                                    <th>Failed</th>
                                    <th>Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sentNotifications as $notif)
                                <tr>
                                    <td>
                                        <small>{{ $notif->sent_at->format('d M, h:i A') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $notif->firebaseApp->app_name ?? 'N/A' }}</strong>
                                    </td>
                                    <td>{{ Str::limit($notif->title, 30) }}</td>
                                    <td>
                                        <span class="badge bg-success">{{ $notif->total_sent }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ $notif->total_failed }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $total = $notif->total_sent + $notif->total_failed;
                                            $rate = $total > 0 ? round(($notif->total_sent / $total) * 100, 1) : 0;
                                        @endphp
                                        <span class="badge {{ $rate >= 80 ? 'bg-success' : 'bg-warning' }}">
                                            {{ $rate }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const appSelect = document.getElementById('firebase_app_id');
    const sendToAll = document.getElementById('send_to_all');
    const sendToSpecific = document.getElementById('send_to_specific');
    const userSelectionDiv = document.getElementById('user_selection_div');
    const usersList = document.getElementById('users_list');
    const form = document.getElementById('notificationForm');

    // Toggle user selection visibility
    function toggleUserSelection() {
        if (sendToSpecific.checked) {
            userSelectionDiv.style.display = 'block';
            loadUsers();
        } else {
            userSelectionDiv.style.display = 'none';
        }
    }

    sendToAll.addEventListener('change', toggleUserSelection);
    sendToSpecific.addEventListener('change', toggleUserSelection);
    appSelect.addEventListener('change', function() {
        if (sendToSpecific.checked) {
            loadUsers();
        }
    });

    // Load users via AJAX
    function loadUsers() {
        const appId = appSelect.value;

        if (!appId) {
            usersList.innerHTML = '<p class="text-muted text-center">Please select an app first</p>';
            return;
        }

        usersList.innerHTML = '<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading users...</p>';

        fetch(`/users/by-app/${appId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.users.length > 0) {
                    let html = '<div class="row">';
                    data.users.forEach(user => {
                        html += `
                            <div class="col-md-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input user-checkbox" type="checkbox" name="user_ids[]" value="${user.id}" id="user_${user.id}">
                                    <label class="form-check-label" for="user_${user.id}">
                                        <strong>${user.user_name}</strong><br>
                                        <small class="text-muted">${user.user_email}</small>
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    html += `
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-primary" onclick="selectAllUsers()">Select All</button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="deselectAllUsers()">Deselect All</button>
                        </div>
                    `;
                    usersList.innerHTML = html;
                } else {
                    usersList.innerHTML = '<p class="text-danger text-center">No users found for this app</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                usersList.innerHTML = '<p class="text-danger text-center">Error loading users</p>';
            });
    }

    // Form validation
    form.addEventListener('submit', function(e) {
        if (sendToSpecific.checked) {
            const checkedUsers = document.querySelectorAll('.user-checkbox:checked');
            if (checkedUsers.length === 0) {
                e.preventDefault();
                alert('Please select at least one user');
                return false;
            }
        }
    });
});

// Select/Deselect All Users
function selectAllUsers() {
    document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = true);
}

function deselectAllUsers() {
    document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
}
</script>
@endsection
