{{-- resources/views/admin/notification/send.blade.php --}}

@extends('admin.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-gradient-success d-flex justify-content-between align-items-center">
                    <h5 class="text-white mb-0">
                        <i class="fas fa-paper-plane me-2"></i>Send Push Notification (OneSignal & Firebase)
                    </h5>
                    <div>
                        <span class="badge bg-light text-dark me-2">
                            <i class="fas fa-bell text-warning"></i> OneSignal: {{ $onesignalUsers }} | <i class="fas fa-users text-primary"></i> Total: {{ $totalUsers }}
                        </span>
                        <a href="{{ route('history') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-history me-1"></i>History
                        </a>
                    </div>
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

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('send.store') }}" method="POST" id="notificationForm">
                        @csrf

                        <!-- Notification Platform -->
                        <div class="mb-4 p-3 bg-light rounded border">
                            <label class="form-label fw-bold">
                                <i class="fas fa-broadcast-tower text-primary me-1"></i>Notification Platform <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="notification_platform" id="platform_onesignal" value="onesignal" {{ old('notification_platform', 'onesignal') == 'onesignal' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="platform_onesignal">
                                        <i class="fas fa-bell text-danger me-1"></i> OneSignal (Instant Broadcast)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="notification_platform" id="platform_firebase" value="firebase" {{ old('notification_platform') == 'firebase' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="platform_firebase">
                                        <i class="fas fa-fire text-warning me-1"></i> Firebase FCM
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="notification_platform" id="platform_both" value="both" {{ old('notification_platform') == 'both' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="platform_both">
                                        <i class="fas fa-sync text-success me-1"></i> Both (OneSignal + Firebase)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- OneSignal Settings (Visible if OneSignal or Both selected) -->
                        <div id="onesignal_settings_div" class="row mb-3">
                            <div class="col-md-6 mb-2">
                                <label for="onesignal_app_id" class="form-label">
                                    OneSignal App ID
                                </label>
                                <input type="text" class="form-control font-monospace" id="onesignal_app_id" name="onesignal_app_id" value="{{ old('onesignal_app_id', $oneSignalAppId) }}" placeholder="19355887-8178-4d10-a8a3-3a6cc499968c">
                                <small class="text-muted">Default App ID configured in system</small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="onesignal_api_key" class="form-label">
                                    OneSignal REST API Key (Optional / from .env)
                                </label>
                                <input type="password" class="form-control font-monospace" id="onesignal_api_key" name="onesignal_api_key" value="{{ old('onesignal_api_key', $oneSignalApiKey) }}" placeholder="e.g. os_v2_app_... or key">
                                <small class="text-muted">OneSignal REST API Key for authorized pushes</small>
                            </div>
                        </div>

                        <!-- Firebase App Selection (Visible if Firebase or Both selected) -->
                        <div class="mb-3" id="firebase_app_div" style="display: none;">
                            <label for="firebase_app_id" class="form-label">
                                Select Firebase App <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="firebase_app_id" name="firebase_app_id">
                                <option value="">Choose a Firebase App...</option>
                                @foreach($apps as $app)
                                    <option value="{{ $app->id }}" {{ old('firebase_app_id') == $app->id ? 'selected' : '' }}>
                                        {{ $app->app_name }} ({{ $app->package_name }})
                                    </option>
                                @endforeach
                            </select>
                            @if($apps->isEmpty())
                                <small class="text-warning">
                                    <i class="fas fa-info-circle"></i> No Firebase app added yet. Use OneSignal for instant notification delivery.
                                </small>
                            @endif
                        </div>

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">
                                Notification Title <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="title"
                                   name="title"
                                   placeholder="e.g., Important Update & New Offer Available!"
                                   maxlength="255"
                                   value="{{ old('title') }}"
                                   required>
                            <small class="text-muted">Max 255 characters</small>
                        </div>

                        <!-- Message -->
                        <div class="mb-3">
                            <label for="message" class="form-label">
                                Notification Message <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control"
                                      id="message"
                                      name="message"
                                      rows="4"
                                      placeholder="Write your notification message here..."
                                      maxlength="1000"
                                      required>{{ old('message') }}</textarea>
                            <small class="text-muted">Max 1000 characters</small>
                        </div>

                        <!-- Image URL (Optional) -->
                        <div class="mb-3">
                            <label for="image_url" class="form-label">
                                Image URL (Optional)
                            </label>
                            <input type="url"
                                   class="form-control"
                                   id="image_url"
                                   name="image_url"
                                   placeholder="https://example.com/image.jpg"
                                   value="{{ old('image_url') }}">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Add an image URL to show banner image with notification
                            </small>
                        </div>

                        <!-- Action URL (Optional) -->
                        <div class="mb-3">
                            <label for="action_url" class="form-label">
                                Action URL (Optional)
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="action_url"
                                   name="action_url"
                                   placeholder="/notifications or https://globalmoney.ltd"
                                   value="{{ old('action_url', '/notifications') }}">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Screen or URL opened when user taps notification
                            </small>
                        </div>

                        <!-- Send To -->
                        <div class="mb-3">
                            <label class="form-label">
                                Send To <span class="text-danger">*</span>
                            </label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="send_to"
                                           id="send_to_all"
                                           value="all"
                                           {{ old('send_to', 'all') == 'all' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="send_to_all">
                                        <i class="fas fa-users text-primary"></i> All App Users (Broadcast)
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="send_to"
                                           id="send_to_specific"
                                           value="specific"
                                           {{ old('send_to') == 'specific' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="send_to_specific">
                                        <i class="fas fa-user-check text-warning"></i> Specific Users
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- User Selection (Hidden by default) -->
                        <div class="mb-3" id="user_selection_div" style="display: none;">
                            <label class="form-label">
                                Select Users <span class="text-danger">*</span>
                            </label>
                            <div id="users_list"
                                 class="border rounded p-3 bg-light"
                                 style="max-height: 400px; overflow-y: auto;">
                                <p class="text-muted text-center">
                                    <i class="fas fa-spinner fa-spin"></i> Loading users list...
                                </p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>Send Push Notification
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
                        <i class="fas fa-clock me-2"></i>Recent Notifications (Last 10)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Platform</th>
                                    <th>Title</th>
                                    <th>Message</th>
                                    <th>Send To</th>
                                    <th>Sent</th>
                                    <th>Failed</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sentNotifications as $notif)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $notif->sent_at ? $notif->sent_at->format('d M, Y') : ($notif->created_at ? $notif->created_at->format('d M, Y') : '') }}</strong>
                                        </div>
                                        <small class="text-muted">{{ $notif->sent_at ? $notif->sent_at->format('h:i A') : ($notif->created_at ? $notif->created_at->format('h:i A') : '') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $notif->notification_type == 'onesignal' ? 'bg-danger' : ($notif->notification_type == 'firebase' ? 'bg-warning text-dark' : 'bg-primary') }}">
                                            <i class="fas fa-bell"></i> {{ strtoupper($notif->notification_type ?? 'ONESIGNAL') }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ Str::limit($notif->title, 30) }}</strong>
                                    </td>
                                    <td>{{ Str::limit($notif->message, 40) }}</td>
                                    <td>
                                        <span class="badge {{ $notif->send_to == 'all' ? 'bg-primary' : 'bg-warning' }}">
                                            {{ ucfirst($notif->send_to ?? 'all') }}
                                            @if($notif->send_to == 'specific' && $notif->user_ids)
                                                ({{ is_array($notif->user_ids) ? count($notif->user_ids) : 1 }} users)
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> {{ $notif->total_sent }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($notif->total_failed > 0)
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times"></i> {{ $notif->total_failed }}
                                            </span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            Delivered
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
    const platformOnesignal = document.getElementById('platform_onesignal');
    const platformFirebase = document.getElementById('platform_firebase');
    const platformBoth = document.getElementById('platform_both');
    const onesignalSettingsDiv = document.getElementById('onesignal_settings_div');
    const firebaseAppDiv = document.getElementById('firebase_app_div');
    const firebaseAppSelect = document.getElementById('firebase_app_id');

    const sendToAll = document.getElementById('send_to_all');
    const sendToSpecific = document.getElementById('send_to_specific');
    const userSelectionDiv = document.getElementById('user_selection_div');
    const usersList = document.getElementById('users_list');
    const form = document.getElementById('notificationForm');

    function updatePlatformVisibility() {
        if (platformFirebase.checked) {
            onesignalSettingsDiv.style.display = 'none';
            firebaseAppDiv.style.display = 'block';
            firebaseAppSelect.required = true;
        } else if (platformBoth.checked) {
            onesignalSettingsDiv.style.display = 'flex';
            firebaseAppDiv.style.display = 'block';
            firebaseAppSelect.required = false;
        } else {
            // OneSignal
            onesignalSettingsDiv.style.display = 'flex';
            firebaseAppDiv.style.display = 'none';
            firebaseAppSelect.required = false;
        }
    }

    platformOnesignal.addEventListener('change', updatePlatformVisibility);
    platformFirebase.addEventListener('change', updatePlatformVisibility);
    platformBoth.addEventListener('change', updatePlatformVisibility);
    updatePlatformVisibility();

    // Toggle user selection visibility
    let usersLoaded = false;
    function toggleUserSelection() {
        if (sendToSpecific.checked) {
            userSelectionDiv.style.display = 'block';
            if (!usersLoaded) {
                loadAllUsers();
            }
        } else {
            userSelectionDiv.style.display = 'none';
        }
    }

    sendToAll.addEventListener('change', toggleUserSelection);
    sendToSpecific.addEventListener('change', toggleUserSelection);
    toggleUserSelection();

    // Load users via AJAX
    function loadAllUsers() {
        usersList.innerHTML = '<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading users...</p>';

        fetch('{{ route("users.index") }}')
            .then(response => response.text())
            .then(htmlText => {
                // Fallback direct AJAX fetch
                fetch('/api/user-search?q=')
                    .then(res => res.json())
                    .then(data => {
                        let users = data.users || data.data || [];
                        if (users.length > 0) {
                            renderUsersList(users);
                        } else {
                            usersList.innerHTML = '<p class="text-muted text-center">No specific users found. You can send to All Users.</p>';
                        }
                    })
                    .catch(() => {
                        usersList.innerHTML = '<p class="text-muted text-center">User list ready. You can send to all users or enter emails.</p>';
                    });
            })
            .catch(() => {
                usersList.innerHTML = '<p class="text-muted text-center">User selection ready.</p>';
            });
    }

    function renderUsersList(users) {
        let html = '<div class="row">';
        users.forEach(user => {
            html += `
                <div class="col-md-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input user-checkbox"
                               type="checkbox"
                               name="user_ids[]"
                               value="${user.id}"
                               id="user_${user.id}">
                        <label class="form-check-label" for="user_${user.id}">
                            <strong>${user.name || 'User'}</strong><br>
                            <small class="text-muted">${user.email || ''}</small>
                        </label>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        html += `
            <div class="mt-3 text-center">
                <button type="button" class="btn btn-sm btn-primary" onclick="selectAllUsers()">
                    <i class="fas fa-check-double"></i> Select All
                </button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="deselectAllUsers()">
                    <i class="fas fa-times"></i> Deselect All
                </button>
            </div>
        `;
        usersList.innerHTML = html;
        usersLoaded = true;
    }

    // Form validation
    form.addEventListener('submit', function(e) {
        if (platformFirebase.checked && !firebaseAppSelect.value) {
            e.preventDefault();
            alert('Please select a Firebase App or switch to OneSignal.');
            firebaseAppSelect.focus();
            return false;
        }

        if (sendToSpecific.checked) {
            const checkedUsers = document.querySelectorAll('.user-checkbox:checked');
            if (checkedUsers.length === 0) {
                e.preventDefault();
                alert('Please select at least one user or select "All Users".');
                return false;
            }
        }
    });
});

function selectAllUsers() {
    document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = true);
}

function deselectAllUsers() {
    document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
}
</script>
@endsection
