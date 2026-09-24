@extends('admin.master')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold">
                <i class="bi bi-images me-2 text-primary"></i>Platform Logo Management
            </h4>
            <p class="text-muted small mb-0">Manage and view the official branding logo across web and mobile platforms</p>
        </div>
        <div>
            @if($logo)
                <a href="{{ route('logosetting.edit', $logo->id) }}" class="btn btn-primary shadow-sm">
                    <i class="bi bi-pencil-square me-1"></i> Update Logo
                </a>
            @else
                <a href="{{ route('logosetting.create') }}" class="btn btn-success shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Add Logo
                </a>
            @endif
        </div>
    </div>

    {{-- Flash & Validation Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Main Logo Card --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-shield-check me-2 text-success"></i>Current Active Logo
                    </h6>
                    <span class="badge {{ ($logo && $logo->photo && file_exists(public_path('uploads/logo/'.$logo->photo))) ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ ($logo && $logo->photo && file_exists(public_path('uploads/logo/'.$logo->photo))) ? 'Active' : 'No Logo Uploaded' }}
                    </span>
                </div>
                <div class="card-body p-4 text-center">
                    @if($logo && $logo->photo && file_exists(public_path('uploads/logo/'.$logo->photo)))
                        <div class="p-4 rounded-3 border bg-light shadow-sm mb-4 d-inline-block" style="max-width: 320px; width: 100%;">
                            <img src="{{ asset('uploads/logo/'.$logo->photo) }}?v={{ time() }}" alt="Site Logo" class="img-fluid rounded" style="max-height: 160px; object-fit: contain;">
                        </div>
                        <div class="text-start bg-white p-3 rounded-3 border mb-4">
                            <div class="row g-2 text-muted small">
                                <div class="col-sm-4 fw-semibold text-dark">File Name:</div>
                                <div class="col-sm-8 text-break">{{ $logo->photo }}</div>
                                <div class="col-sm-4 fw-semibold text-dark">File Path:</div>
                                <div class="col-sm-8 text-break"><code>public/uploads/logo/{{ $logo->photo }}</code></div>
                                <div class="col-sm-4 fw-semibold text-dark">Status:</div>
                                <div class="col-sm-8 text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Live on Web & Mobile API</div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('logosetting.edit', $logo->id) }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-repeat me-1"></i> Change Logo
                            </a>
                        </div>
                    @else
                        <div class="p-5 rounded-3 border bg-light text-muted mb-4">
                            <i class="bi bi-image fs-1 d-block mb-3 text-secondary"></i>
                            <h6 class="fw-bold text-dark">No Platform Logo Uploaded Yet</h6>
                            <p class="small mb-3">Upload your brand logo (PNG, JPG, SVG, WEBP) to be displayed on top sidebar, header, and mobile app.</p>
                            <a href="{{ route('logosetting.create') }}" class="btn btn-primary px-4">
                                <i class="bi bi-cloud-arrow-up me-1"></i> Upload Logo Now
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Logo Specifications & Information --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-info-circle me-2 text-info"></i>Logo Guidelines & Specifications
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 py-3 d-flex align-items-start">
                            <div class="badge bg-primary-subtle text-primary rounded-pill p-2 me-3">
                                <i class="bi bi-aspect-ratio fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold text-dark">Recommended Dimensions</h6>
                                <p class="text-muted small mb-0">Width: 200px - 400px, Height: 50px - 100px with transparent background for best look on sidebar and mobile header.</p>
                            </div>
                        </div>

                        <div class="list-group-item px-0 py-3 d-flex align-items-start">
                            <div class="badge bg-success-subtle text-success rounded-pill p-2 me-3">
                                <i class="bi bi-filetype-png fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold text-dark">Supported Formats</h6>
                                <p class="text-muted small mb-0">PNG (preferred for transparent logos), JPG, JPEG, SVG, WEBP up to <strong>5 MB</strong>.</p>
                            </div>
                        </div>

                        <div class="list-group-item px-0 py-3 d-flex align-items-start">
                            <div class="badge bg-warning-subtle text-warning rounded-pill p-2 me-3">
                                <i class="bi bi-phone fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold text-dark">Mobile & Cross-Platform Sync</h6>
                                <p class="text-muted small mb-0">Logo is automatically synchronized via <code>/api/logosetting</code>, <code>/api/userbalanceshow</code>, and admin header.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
