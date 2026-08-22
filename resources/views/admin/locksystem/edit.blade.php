@extends('admin.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 col-lg-6 mx-auto">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="fas fa-edit"></i> Edit Lock System</h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong><i class="fas fa-exclamation-circle"></i> Error!</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <div class="alert alert-info">
                        <strong>Current Settings:</strong><br>
                        Amount: <strong>${{ number_format($lock->locked_amount, 2) }}</strong><br>
                        Status: <strong>{{ $lock->status ? 'Active' : 'Inactive' }}</strong><br>
                        <small>Last updated: {{ $lock->updated_at->diffForHumans() }}</small>
                    </div>

                    <form action="{{ route('locksystem.update', $lock->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="locked_amount" class="form-label fw-bold">
                                Lock Amount ($) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-warning text-dark">
                                    <i class="fas fa-dollar-sign"></i>
                                </span>
                                <input type="number" name="locked_amount" id="locked_amount"
                                       class="form-control @error('locked_amount') is-invalid @enderror"
                                       step="0.01" min="0" value="{{ old('locked_amount', $lock->locked_amount) }}" required>
                                @error('locked_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="status" class="form-label fw-bold">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-select form-select-lg @error('status') is-invalid @enderror" required>
                                <option value="1" {{ old('status', $lock->status) == '1' ? 'selected' : '' }}>
                                    ✅ Active (Lock applied to all agents)
                                </option>
                                <option value="0" {{ old('status', $lock->status) == '0' ? 'selected' : '' }}>
                                    ⛔ Inactive (No lock applied)
                                </option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i>
                                Changes apply immediately to non-overridden agents
                            </small>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> Changes apply to all agents without individual override.
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('locksystem.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Update Lock System
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
