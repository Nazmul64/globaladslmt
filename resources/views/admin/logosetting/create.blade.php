@extends('admin.master')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold">
                <i class="bi bi-image me-2 text-primary"></i>Upload Platform Logo
            </h4>
            <p class="text-muted small mb-0">Set your official application logo for header, mobile app, and push notifications</p>
        </div>
        <a href="{{ route('logosetting.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Logo View
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
        {{-- Upload Form Card --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-cloud-arrow-up me-2 text-primary"></i>Choose Logo File
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('logosetting.store') }}" method="POST" enctype="multipart/form-data" id="logoUploadForm">
                        @csrf

                        {{-- File Input & Drop Area --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark mb-2">Select Image File</label>
                            
                            <div class="border-2 border-dashed rounded-3 p-4 text-center bg-light" id="dropZone" style="border-style: dashed; cursor: pointer;">
                                <i class="bi bi-file-earmark-image fs-1 text-primary mb-2 d-block"></i>
                                <span class="fw-bold text-dark d-block">Click here to browse or drag & drop logo</span>
                                <small class="text-muted d-block mt-1">Supports: PNG, JPG, JPEG, SVG, WEBP (Max: 5MB)</small>
                                <input type="file" name="photo" id="logoFileInput" class="d-none" accept="image/png, image/jpeg, image/jpg, image/svg+xml, image/webp" required>
                            </div>
                            
                            <div id="fileSelectedInfo" class="mt-2 text-success small fw-semibold d-none">
                                <i class="bi bi-check2-circle me-1"></i> Selected: <span id="fileNameDisplay"></span> (<span id="fileSizeDisplay"></span>)
                            </div>
                        </div>

                        {{-- Save Button --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="submitBtn">
                                <i class="bi bi-check2-circle me-1"></i> Save & Apply Logo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Live Preview Card --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-eye me-2 text-info"></i>Logo Preview
                    </h6>
                </div>
                <div class="card-body p-4 text-center d-flex flex-column align-items-center justify-content-center">
                    
                    {{-- New Preview Container --}}
                    <div id="newPreviewContainer" class="p-4 rounded-3 border bg-white shadow-sm mb-3 d-none" style="max-width: 260px; width: 100%;">
                        <span class="badge bg-success mb-2">New Preview</span>
                        <img id="logoPreviewImage" src="#" alt="New Logo Preview" class="img-fluid rounded" style="max-height: 140px; object-fit: contain;">
                    </div>

                    {{-- Current Logo Container (if exists) --}}
                    @if(isset($logo) && $logo->photo && file_exists(public_path('uploads/logo/'.$logo->photo)))
                        <div id="currentLogoContainer" class="p-4 rounded-3 border bg-white shadow-sm mb-3" style="max-width: 260px; width: 100%;">
                            <span class="badge bg-secondary mb-2">Current Active Logo</span>
                            <img src="{{ asset('uploads/logo/'.$logo->photo) }}" alt="Current Logo" class="img-fluid rounded" style="max-height: 140px; object-fit: contain;">
                        </div>
                    @else
                        <div id="placeholderContainer" class="p-4 rounded-3 border bg-light text-muted" style="max-width: 260px; width: 100%;">
                            <i class="bi bi-image fs-1 d-block mb-2 text-secondary"></i>
                            <span class="small">No logo selected yet.<br>Select a file on the left to see live preview.</span>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('logoFileInput');
    const previewContainer = document.getElementById('newPreviewContainer');
    const previewImage = document.getElementById('logoPreviewImage');
    const placeholderContainer = document.getElementById('placeholderContainer');
    const fileSelectedInfo = document.getElementById('fileSelectedInfo');
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    const fileSizeDisplay = document.getElementById('fileSizeDisplay');
    const logoUploadForm = document.getElementById('logoUploadForm');

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
        if (!file.type.startsWith('image/')) {
            if (typeof toastr !== 'undefined') {
                toastr.error('Please select a valid image file (PNG, JPG, SVG, WEBP).');
            } else {
                alert('Please select a valid image file (PNG, JPG, SVG, WEBP).');
            }
            fileInput.value = '';
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            if (typeof toastr !== 'undefined') {
                toastr.error('File size exceeds 5MB limit. Please choose a smaller image.');
            } else {
                alert('File size exceeds 5MB limit.');
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
            if (placeholderContainer) {
                placeholderContainer.classList.add('d-none');
            }
            if (typeof toastr !== 'undefined') {
                toastr.info('Logo preview loaded! Click "Save & Apply Logo" to save.');
            }
        };
        reader.readAsDataURL(file);
    }

    if (logoUploadForm) {
        logoUploadForm.addEventListener('submit', function(e) {
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                if (typeof toastr !== 'undefined') {
                    toastr.error('Please select a logo image before submitting.');
                }
            }
        });
    }
});
</script>
@endsection
