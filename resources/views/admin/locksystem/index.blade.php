@extends('admin.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">🔒 Lock System Settings</h4>
                    <div>
                        <a href="{{ route('locksystem.manage-agents') }}" class="btn btn-info">
                            <i class="fas fa-users-cog"></i> Manage Agents
                        </a>
                        @if(!$lock)
                        <a href="{{ route('locksystem.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Set Lock Amount
                        </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if($lock)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="8%">ID</th>
                                    <th width="18%">Lock Amount</th>
                                    <th width="15%">Status</th>
                                    <th width="18%">Created At</th>
                                    <th width="18%">Last Updated</th>
                                    <th width="23%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $lock->id }}</td>
                                    <td>
                                        <strong class="text-primary fs-5">${{ number_format($lock->locked_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm {{ $lock->status ? 'btn-success' : 'btn-secondary' }} dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown">
                                                {{ $lock->status ? '✅ Active' : '⛔ Inactive' }}
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <form action="{{ route('locksystem.toggle-status', $lock->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="1">
                                                        <button type="submit" class="dropdown-item {{ $lock->status ? 'active' : '' }}">
                                                            <i class="fas fa-check-circle text-success"></i> Active
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('locksystem.toggle-status', $lock->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="0">
                                                        <button type="submit" class="dropdown-item {{ !$lock->status ? 'active' : '' }}">
                                                            <i class="fas fa-times-circle text-secondary"></i> Inactive
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>{{ $lock->created_at->format('M d, Y h:i A') }}</td>
                                    <td>{{ $lock->updated_at->format('M d, Y h:i A') }}</td>
                                    <td>
                                        <a href="{{ route('locksystem.edit', $lock->id) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>

                                        <form action="{{ route('locksystem.destroy', $lock->id) }}" method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('⚠️ Delete lock system?\n\nThis will unlock ALL agents!')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-{{ $lock->status ? 'info' : 'warning' }} mt-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Current Status: {{ $lock->status ? 'ACTIVE' : 'INACTIVE' }}</strong>
                        <ul class="mb-0 mt-2">
                            @if($lock->status)
                            <li>Global lock is <strong>ACTIVE</strong></li>
                            <li>Lock amount: <strong>${{ number_format($lock->locked_amount, 2) }}</strong></li>
                            <li>Applied to all agents (unless individually overridden)</li>
                            @else
                            <li>Global lock is <strong>INACTIVE</strong></li>
                            <li>Agents have full balance access (unless individually locked)</li>
                            @endif
                        </ul>
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>No lock configured.</strong> Click "Set Lock Amount" to start.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
