@extends('admin.master')

@section('content')
<div class="container-fluid py-4">

    {{-- Success & Error Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 text-primary fw-bold">
                <i class="bi bi-hdd-network-fill me-2"></i>Server Mode Configuration
            </h4>
            <p class="text-muted mb-0 small">Switch between Local Development Server and Live Production Server easily from Admin Panel.</p>
        </div>
    </div>

    {{-- Active Server Overview Banner --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 {{ $setting->server_mode === 'local' ? 'bg-light-warning border-start border-warning border-4' : 'bg-light-success border-start border-success border-4' }}">
                <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <span class="text-uppercase text-muted fw-bold small">Current Active Server Mode</span>
                        <h3 class="mb-1 fw-bold mt-1">
                            @if($setting->server_mode === 'local')
                                <span class="badge bg-warning text-dark px-3 py-2">
                                    <i class="bi bi-pc-display me-2"></i>LOCAL SERVER MODE
                                </span>
                            @else
                                <span class="badge bg-success px-3 py-2">
                                    <i class="bi bi-globe2 me-2"></i>LIVE PRODUCTION SERVER MODE
                                </span>
                            @endif
                        </h3>
                        <p class="mb-0 text-secondary mt-2">
                            <strong>Active Base URL:</strong> 
                            <code class="fs-6 text-primary px-2 py-1 bg-white rounded border">{{ $setting->active_url }}</code>
                        </p>
                    </div>

                    {{-- Quick Action Toggle Buttons --}}
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.server.toggle') }}" method="POST">
                            @csrf
                            <input type="hidden" name="mode" value="local">
                            <button type="submit" class="btn btn-outline-warning fw-bold px-4 py-2 {{ $setting->server_mode === 'local' ? 'active shadow-sm' : '' }}">
                                <i class="bi bi-pc-display me-1"></i> Switch to Local Server
                            </button>
                        </form>

                        <form action="{{ route('admin.server.toggle') }}" method="POST">
                            @csrf
                            <input type="hidden" name="mode" value="live">
                            <button type="submit" class="btn btn-outline-success fw-bold px-4 py-2 {{ $setting->server_mode === 'live' ? 'active shadow-sm' : '' }}">
                                <i class="bi bi-globe2 me-1"></i> Switch to Live Server
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Form Card --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-secondary">
                <i class="bi bi-sliders me-2"></i>Server URL Settings
            </h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.server.update') }}" method="POST">
                @csrf

                <div class="row g-4">
                    {{-- Server Mode Radio Selection --}}
                    <div class="col-12">
                        <label class="form-label fw-bold">Select Active Server Mode:</label>
                        <div class="d-flex gap-4 mt-2">
                            <div class="form-check form-check-inline p-3 border rounded w-50 m-0 cursor-pointer shadow-sm">
                                <input class="form-check-input" type="radio" name="server_mode" id="mode_local" value="local" {{ $setting->server_mode === 'local' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold ms-2" for="mode_local">
                                    <i class="bi bi-pc-display text-warning fs-5 me-1"></i> Local Server Mode
                                    <div class="text-muted small fw-normal mt-1">Use for local development and testing on Emulator or Wi-Fi network.</div>
                                </label>
                            </div>

                            <div class="form-check form-check-inline p-3 border rounded w-50 m-0 cursor-pointer shadow-sm">
                                <input class="form-check-input" type="radio" name="server_mode" id="mode_live" value="live" {{ $setting->server_mode === 'live' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold ms-2" for="mode_live">
                                    <i class="bi bi-globe2 text-success fs-5 me-1"></i> Live Production Server Mode
                                    <div class="text-muted small fw-normal mt-1">Use for public users connecting to production site domain.</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Local Server URL --}}
                    <div class="col-md-6">
                        <label for="local_url" class="form-label fw-bold">
                            <i class="bi bi-pc-display text-warning me-1"></i> Local Server URL:
                        </label>
                        <input type="url" name="local_url" id="local_url" class="form-control form-control-lg @error('local_url') is-invalid @enderror" value="{{ old('local_url', $setting->local_url) }}" required placeholder="http://10.0.2.2:8000">
                        <small class="text-muted">Example: <code>http://10.0.2.2:8000</code> (Android Emulator) or <code>http://192.168.1.100:8000</code></small>
                        @error('local_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Live Server URL --}}
                    <div class="col-md-6">
                        <label for="live_url" class="form-label fw-bold">
                            <i class="bi bi-globe2 text-success me-1"></i> Live Production Server URL:
                        </label>
                        <input type="url" name="live_url" id="live_url" class="form-control form-control-lg @error('live_url') is-invalid @enderror" value="{{ old('live_url', $setting->live_url) }}" required placeholder="https://globalmoney.ltd">
                        <small class="text-muted">Example: <code>https://globalmoney.ltd</code></small>
                        @error('live_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-save me-2"></i> Save Server Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
