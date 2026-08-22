@extends('admin.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-gradient-primary d-flex justify-content-between align-items-center">
                    <h5 class="text-white mb-0">
                        <i class="fab fa-google me-2"></i>Firebase Apps Management
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
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($apps as $app)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $app->app_name }}</strong>
                                        </td>
                                        <td>
                                            <code>{{ $app->package_name }}</code>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $app->firebase_credentials['project_id'] ?? 'N/A' }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <i class="fas fa-users"></i> {{ $app->users_count }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($app->is_active)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> Active
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle"></i> Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $app->created_at->format('d M, Y') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button"
                                                        class="btn btn-sm btn-info"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewModal{{ $app->id }}"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm btn-warning"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editModal{{ $app->id }}"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('notification.firebase.toggle', $app->id) }}"
                                                      method="POST"
                                                      class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit"
                                                            class="btn btn-sm {{ $app->is_active ? 'btn-secondary' : 'btn-success' }}"
                                                            title="{{ $app->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="fas fa-power-off"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('notification.firebase.delete', $app->id) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure? This will delete all related data!')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- View Modal -->
                                    <div class="modal fade" id="viewModal{{ $app->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-gradient-info">
                                                    <h5 class="modal-title text-white">Firebase App Details</h5>
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
                                                            <p>{{ $app->firebase_credentials['project_id'] ?? 'N/A' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Total Users:</strong>
                                                            <p><span class="badge bg-info">{{ $app->users_count }} users</span></p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Status:</strong>
                                                            <p>
                                                                @if($app->is_active)
                                                                    <span class="badge bg-success">Active</span>
                                                                @else
                                                                    <span class="badge bg-danger">Inactive</span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Created At:</strong>
                                                            <p>{{ $app->created_at->format('d M, Y h:i A') }}</p>
                                                        </div>
                                                    </div>
                                                    @if($app->server_key)
                                                    <div class="mb-3">
                                                        <strong>Server Key:</strong>
                                                        <p><code style="word-break: break-all; font-size: 11px;">{{ Str::limit($app->server_key, 100) }}</code></p>
                                                    </div>
                                                    @endif
                                                    <div class="mb-3">
                                                        <strong>Firebase Credentials:</strong>
                                                        <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"><code>{{ json_encode($app->firebase_credentials, JSON_PRETTY_PRINT) }}</code></pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal{{ $app->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-gradient-warning">
                                                    <h5 class="modal-title text-white">Edit Firebase App</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('notification.firebase.edit') }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="app_id" value="{{ $app->id }}">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">App Name <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="app_name" value="{{ $app->app_name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Package Name <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="package_name" value="{{ $app->package_name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Firebase JSON (Optional)</label>
                                                            <input type="file" class="form-control" name="firebase_json" accept=".json">
                                                            <small class="text-muted">Leave empty to keep existing credentials</small>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Server Key (Optional)</label>
                                                            <input type="text" class="form-control" name="server_key" value="{{ $app->server_key }}">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-warning">Update App</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fab fa-google fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No Firebase apps found</p>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary">
                <h5 class="modal-title text-white">Add New Firebase App</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('notification.firebase.add') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">App Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="app_name" placeholder="e.g., My Android App" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Package Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="package_name" placeholder="e.g., com.example.app" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Firebase JSON File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="firebase_json" accept=".json" required>
                        <small class="text-muted">Upload your Firebase service account JSON file</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Server Key (Optional)</label>
                        <input type="text" class="form-control" name="server_key" placeholder="Firebase Server Key">
                        <small class="text-muted">Legacy FCM server key (optional)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add App</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
