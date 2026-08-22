@extends('admin.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 col-lg-6 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-lock"></i> Set Lock Amount</h4>
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

                    <form action="{{ route('locksystem.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="locked_amount" class="form-label fw-bold">
                                Lock Amount ($) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fas fa-dollar-sign"></i>
                                </span>
                                <input type="number" name="locked_amount" id="locked_amount"
                                       class="form-control @error('locked_amount') is-invalid @enderror"
                                       step="0.01" min="0" value="{{ old('locked_amount', '0.00') }}"
                                       placeholder="Enter lock amount" required>
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
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>
                                    ✅ Active (Lock will be applied)
                                </option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>
                                    ⛔ Inactive (No lock applied)
                                </option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i>
                                If Active: Lock applied to all agents immediately. If Inactive: Saved but not applied.
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> Only affects agents without individual lock override.
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('locksystem.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Lock System
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
