@extends('admin.master')

@section('content')
<!-- Summernote Text Editor CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0">Create Privacy Policy</h3>
        <a href="{{ route('privacy.index') }}" class="btn btn-secondary shadow-sm">
            <i class="ri-arrow-left-line me-1"></i> Back to List
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('privacy.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control form-control-lg @error('title') is-invalid @enderror"
                           value="{{ old('title') }}" placeholder="Enter policy title (e.g. Privacy Policy, Data Protection)" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Description / Content <span class="text-danger">*</span></label>
                    <textarea id="summernote" name="description"
                              class="form-control @error('description') is-invalid @enderror"
                              required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex align-items-center gap-2 mt-4">
                    <button type="submit" class="btn btn-success btn-lg px-4">
                        <i class="ri-save-line me-1"></i> Save Privacy Policy
                    </button>
                    <a href="{{ route('privacy.index') }}" class="btn btn-outline-secondary btn-lg px-4">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Summernote Text Editor JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
    $(document).ready(function() {
        $('#summernote').summernote({
            placeholder: 'Write privacy policy description here...',
            tabsize: 2,
            height: 350,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'italic', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });
</script>
@endsection

