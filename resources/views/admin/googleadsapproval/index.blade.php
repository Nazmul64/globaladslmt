@extends('admin.master')

@section('content')
<div class="container-fluid py-4">

    {{-- Alert Messages --}}
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Whoops!</strong> There were some problems with your input.
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Main Container Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-3 pb-2">
            <h5 class="card-title fw-normal mb-0" style="color: #2b303a; font-size: 1.15rem;">
                Google Ads Approval
            </h5>
        </div>
        <div class="card-body pt-1">

            {{-- Form Card --}}
            <div class="card border shadow-none" style="border-radius: 4px; border-color: #e2e8f0 !important;">
                <div class="card-header bg-light border-bottom py-2 px-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-google me-2" style="font-size: 1.1rem; color: #4a5568;"></i>
                        <span style="font-size: 0.95rem; font-weight: 500; color: #2d3748;">Google Ads Approval Script / Code</span>
                    </div>
                </div>
                <div class="card-body p-3">
                    <form action="{{ route('googleadsapproval.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="approval_text" class="form-label mb-1" style="font-size: 0.85rem; font-weight: 600; color: #4a5568;">
                                Enter Long Text / Script Code
                            </label>
                            <textarea class="form-control" 
                                      id="approval_text" 
                                      name="approval_text" 
                                      rows="12" 
                                      placeholder="Paste your Google Ads Approval html script or text code here..." 
                                      style="font-size: 0.9rem; font-family: monospace; border-color: #cbd5e1; border-radius: 4px; padding: 0.75rem; resize: vertical; line-height: 1.5; color: #2d3748;">{{ old('approval_text', $approval->approval_text) }}</textarea>
                        </div>

                        <div class="text-end mt-2">
                            <button type="submit" class="btn" style="background-color: #d9253c; color: white; border-radius: 4px; padding: 0.45rem 1.25rem; font-size: 0.9rem; font-weight: 500;">
                                <i class="bi bi-check-square me-1"></i> Save And Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .form-control:focus {
        box-shadow: none;
        border-color: #d9253c !important;
    }
</style>
@endsection
