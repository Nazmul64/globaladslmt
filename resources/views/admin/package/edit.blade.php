@extends('admin.master')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold">
                <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Package: {{ $package->package_name }}
            </h4>
            <p class="text-muted small mb-0">Update package pricing, limits, validity, and photo</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('package.index') }}">
            <i class="bi bi-arrow-left me-1"></i> Back to Packages
        </a>
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-x-circle me-1"></i> Please fix the following errors:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Form Card --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-gear-fill me-2 text-primary"></i>Package Information
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('package.update', $package->id) }}" method="POST" enctype="multipart/form-data" id="packageEditForm">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Package Name <span class="text-danger">*</span></label>
                                <input type="text" name="package_name" class="form-control form-control-lg" value="{{ old('package_name', $package->package_name) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Price ($) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">$</span>
                                    <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $package->price) }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Daily Income ($) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">$</span>
                                    <input type="number" step="0.01" name="daily_income" class="form-control" value="{{ old('daily_income', $package->daily_income) }}" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Daily Limit (Ads/Tasks) <span class="text-danger">*</span></label>
                                <input type="number" name="daily_limit" class="form-control" value="{{ old('daily_limit', $package->daily_limit) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Validity (Days) <span class="text-danger">*</span></label>
                                <input type="text" name="validity" class="form-control" value="{{ old('validity', $package->validity) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Ad Break (Seconds) <span class="text-danger">*</span></label>
                                <input type="number" name="ad_brack" class="form-control" value="{{ old('ad_brack', $package->ad_brack) }}" required>
                            </div>

                            {{-- File Upload Area --}}
                            <div class="col-md-12 mt-4">
                                <label class="form-label fw-semibold text-dark">Change / Replace Package Photo</label>
                                <div class="border-2 border-dashed rounded-3 p-4 text-center bg-light" id="dropZone" style="border-style: dashed; cursor: pointer;">
                                    <i class="bi bi-cloud-arrow-up fs-1 text-primary mb-2 d-block"></i>
                                    <span class="fw-bold text-dark d-block">Click to browse or drag & drop replacement photo</span>
                                    <small class="text-muted d-block mt-1">Supports: JPG, JPEG, PNG, GIF, SVG, WEBP, BMP, AVIF, JFIF (Max: 10MB)</small>
                                    <input type="file" name="photo" id="packageFileInput" class="d-none" accept="image/*">
                                </div>

                                <div id="fileSelectedInfo" class="mt-2 text-success small fw-semibold d-none">
                                    <i class="bi bi-check2-circle me-1"></i> Selected: <span id="fileNameDisplay"></span> (<span id="fileSizeDisplay"></span>)
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm" id="submitBtn">
                                <i class="bi bi-save me-1"></i> Update Package
                            </button>
                            <a href="{{ route('package.index') }}" class="btn btn-secondary px-4">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Live & Current Previews --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 20px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-eye me-2 text-info"></i>Photo Previews
                    </h6>
                </div>
                <div class="card-body p-4 text-center">
                    {{-- New Selected Preview --}}
                    <div id="newPreviewContainer" class="p-3 rounded-3 border bg-white shadow-sm mb-3 d-none">
                        <span class="badge bg-success mb-2">New Selected Preview</span>
                        <img id="packagePreviewImage" src="#" alt="New Preview" class="img-fluid rounded shadow-sm" style="max-height: 180px; width: 100%; object-fit: contain;">
                    </div>

                    {{-- Current Photo --}}
                    @if($package->photo && file_exists(public_path('uploads/package/'.$package->photo)))
                        <div id="currentPhotoContainer" class="p-3 rounded-3 border bg-light shadow-sm">
                            <span class="badge bg-secondary mb-2">Current Active Photo</span>
                            <img src="{{ asset('uploads/package/'.$package->photo) }}?v={{ time() }}" alt="Current Photo" class="img-fluid rounded shadow-sm" style="max-height: 180px; width: 100%; object-fit: contain;">
                            <div class="mt-2 small text-muted text-break"><code>{{ $package->photo }}</code></div>
                        </div>
                    @else
                        <div id="placeholderContainer" class="p-4 rounded-3 border bg-light text-muted">
                            <i class="bi bi-image fs-1 d-block mb-2 text-secondary"></i>
                            <span class="small">No active photo found on server.<br>Choose an image on the left to set one.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Live Preview & Validation Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('packageFileInput');
    const previewContainer = document.getElementById('newPreviewContainer');
    const previewImage = document.getElementById('packagePreviewImage');
    const fileSelectedInfo = document.getElementById('fileSelectedInfo');
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    const fileSizeDisplay = document.getElementById('fileSizeDisplay');

    // Trigger click on file input
    dropZone.addEventListener('click', () => fileInput.click());

    // Drag & Drop effects
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-primary', 'bg-white');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-primary', 'bg-white');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'bg-white');
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            handleFilePreview(fileInput.files[0]);
        }
    });

    // File Input change
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            handleFilePreview(this.files[0]);
        }
    });

    function handleFilePreview(file) {
        const validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'avif', 'jfif'];
        const fileExt = file.name.split('.').pop().toLowerCase();

        if (!file.type.startsWith('image/') && !validExtensions.includes(fileExt)) {
            if (typeof toastr !== 'undefined') {
                toastr.error('Invalid format! Please select an image file (JPG, PNG, GIF, SVG, WEBP, BMP, AVIF, JFIF).');
            } else {
                alert('Invalid format! Please select an image file (JPG, PNG, GIF, SVG, WEBP).');
            }
            fileInput.value = '';
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            if (typeof toastr !== 'undefined') {
                toastr.error('File size exceeds 10MB limit! Please select a smaller photo.');
            } else {
                alert('File size exceeds 10MB limit.');
            }
            fileInput.value = '';
            return;
        }

        fileNameDisplay.textContent = file.name;
        fileSizeDisplay.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        fileSelectedInfo.classList.remove('d-none');

        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.classList.remove('d-none');
            if (typeof toastr !== 'undefined') {
                toastr.info('New photo loaded for preview! Click "Update Package" to save.');
            }
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endsection
