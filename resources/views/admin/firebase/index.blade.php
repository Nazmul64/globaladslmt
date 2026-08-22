{{-- resources/views/admin/firebase/index.blade.php --}}

@extends('admin.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-gradient-primary d-flex justify-content-between align-items-center">
                    <h5 class="text-white mb-0">
                        <i class="fas fa-fire me-2"></i>Firebase Apps Management
                    </h5>
                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addAppModal">
                        <i class="fas fa-plus me-1"></i>Add New App
                    </button>
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

                    @if($apps->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>App Name</th>
                                        <th>Package Name</th>
                                        <th>Project ID</th>
                                        <th>Total Users</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($apps as $app)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-gradient-info rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                    <i class="fas fa-mobile-alt text-white"></i>
                                                </div>
                                                <strong>{{ $app->app_name }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <code class="text-sm">{{ $app->package_name }}</code>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $app->project_id ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $app->users_count ?? 0 }} Users
                                            </span>
                                        </td>
                                        <td>
                                            <form action="{{ route('firebase.toggle', $app->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $app->is_active ? 'btn-success' : 'btn-secondary' }}">
                                                    <i class="fas fa-{{ $app->is_active ? 'check' : 'times' }}"></i>
                                                    {{ $app->is_active ? 'Active' : 'Inactive' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $app->created_at->format('d M, Y') }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button type="button"
                                                        class="btn btn-sm btn-info"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewAppModal{{ $app->id }}"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm btn-warning"
                                                        onclick="editApp({{ json_encode([
                                                            'id' => $app->id,
                                                            'app_name' => $app->app_name,
                                                            'package_name' => $app->package_name,
                                                            'server_key' => $app->server_key
                                                        ]) }})"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="confirmDelete({{ $app->id }})"
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>

                                            <form id="delete-form-{{ $app->id }}"
                                                  action="{{ route('firebase.delete', $app->id) }}"
                                                  method="POST"
                                                  class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- View Modal -->
                                    <div class="modal fade" id="viewAppModal{{ $app->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-gradient-info">
                                                    <h5 class="modal-title text-white">App Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>App Name:</strong>
                                                            <p>{{ $app->app_name }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Package Name:</strong>
                                                            <p><code>{{ $app->package_name }}</code></p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Project ID:</strong>
                                                            <p>{{ $app->project_id ?? 'N/A' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Status:</strong>
                                                            <p>
                                                                <span class="badge {{ $app->is_active ? 'bg-success' : 'bg-danger' }}">
                                                                    {{ $app->is_active ? 'Active' : 'Inactive' }}
                                                                </span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Total Users:</strong>
                                                            <p>{{ $app->users_count ?? 0 }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Created:</strong>
                                                            <p>{{ $app->created_at->format('d M, Y h:i A') }}</p>
                                                        </div>
                                                    </div>
                                                    @if($app->server_key)
                                                    <div class="mb-3">
                                                        <strong>Server Key:</strong>
                                                        <p><code>{{ Str::limit($app->server_key, 50) }}...</code></p>
                                                    </div>
                                                    @endif
                                                    <div class="mb-3">
                                                        <strong>Firebase Credentials:</strong>
                                                        <pre class="bg-dark text-white p-3 rounded" style="max-height: 300px; overflow-y: auto;">{{ json_encode($app->firebase_credentials, JSON_PRETTY_PRINT) }}</pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No Firebase Apps found</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppModal">
                                <i class="fas fa-plus me-1"></i>Add Your First App
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add App Modal -->
<div class="modal fade" id="addAppModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary">
                <h5 class="modal-title text-white">Add New Firebase App</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('firebase.add') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="app_name" class="form-label">App Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="app_name" name="app_name" required value="{{ old('app_name') }}" placeholder="e.g., My Awesome App">
                        <small class="text-muted">Enter a friendly name for your app</small>
                    </div>
                    <div class="mb-3">
                        <label for="package_name" class="form-label">Package Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="package_name" name="package_name" placeholder="com.myapp.example" required value="{{ old('package_name') }}">
                        <small class="text-muted">Must be unique for each app</small>
                    </div>
                    <div class="mb-3">
                        <label for="firebase_json" class="form-label">Firebase JSON File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="firebase_json" name="firebase_json" accept=".json" required>
                        <small class="text-muted">Download service account JSON file from Firebase Console → Project Settings → Service Accounts</small>
                    </div>
                    <div class="mb-3">
                        <label for="server_key" class="form-label">FCM Server Key (Optional)</label>
                        <textarea class="form-control" id="server_key" name="server_key" rows="2">{{ old('server_key') }}</textarea>
                        <small class="text-muted">Legacy server key (optional)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save App
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit App Modal -->
<div class="modal fade" id="editAppModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-warning">
                <h5 class="modal-title text-white">Edit Firebase App</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAppForm" action="{{ route('firebase.edit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="edit_app_id" name="app_id">
                    <div class="mb-3">
                        <label for="edit_app_name" class="form-label">App Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_app_name" name="app_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_package_name" class="form-label">Package Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_package_name" name="package_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_firebase_json" class="form-label">Firebase JSON File (Upload new file to replace)</label>
                        <input type="file" class="form-control" id="edit_firebase_json" name="firebase_json" accept=".json">
                        <small class="text-muted">Leave empty to keep existing credentials</small>
                    </div>
                    <div class="mb-3">
                        <label for="edit_server_key" class="form-label">FCM Server Key (Optional)</label>
                        <textarea class="form-control" id="edit_server_key" name="server_key" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-white">
                        <i class="fas fa-save me-1"></i>Update App
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this app? This will also delete all users and notifications related to this app.')) {
        document.getElementById('delete-form-' + id).submit();
    }
}

function editApp(appData) {
    document.getElementById('edit_app_id').value = appData.id;
    document.getElementById('edit_app_name').value = appData.app_name;
    document.getElementById('edit_package_name').value = appData.package_name;
    document.getElementById('edit_server_key').value = appData.server_key || '';

    const modal = new bootstrap.Modal(document.getElementById('editAppModal'));
    modal.show();
}
</script>
@endsection
