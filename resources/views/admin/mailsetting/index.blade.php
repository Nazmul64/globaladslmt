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
                Mail Configuration
            </h5>
        </div>
        <div class="card-body pt-1">

            {{-- 1. Send Test Mail Card --}}
            <div class="card border shadow-none mb-4" style="border-radius: 4px; border-color: #e2e8f0 !important;">
                <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center py-2 px-3" 
                     style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#testMailCollapse" aria-expanded="true">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-send me-2" style="font-size: 1.1rem; color: #4a5568;"></i>
                        <span style="font-size: 0.95rem; font-weight: 500; color: #2d3748;">Send Test Mail</span>
                    </div>
                    <i class="bi bi-chevron-up collapse-icon" style="font-size: 0.9rem; color: #718096;"></i>
                </div>
                <div id="testMailCollapse" class="collapse show">
                    <div class="card-body p-3">
                        <form action="{{ route('mailsetting.send-test') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="recipient_email" class="form-label mb-1" style="font-size: 0.85rem; font-weight: 600; color: #4a5568;">
                                    To / Recipient Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="recipient_email" 
                                       name="recipient_email" 
                                       placeholder="Recipient's Email" 
                                       required 
                                       style="font-size: 0.9rem; border-color: #cbd5e1; border-radius: 4px; padding: 0.45rem 0.75rem;">
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label mb-1" style="font-size: 0.85rem; font-weight: 600; color: #4a5568;">
                                    Message <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="message" 
                                          name="message" 
                                          rows="3" 
                                          placeholder="Message to be sent" 
                                          required 
                                          style="font-size: 0.9rem; border-color: #cbd5e1; border-radius: 4px; padding: 0.45rem 0.75rem; resize: vertical;"></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn" style="background-color: #d9253c; color: white; border-radius: 4px; padding: 0.45rem 1.25rem; font-size: 0.9rem; font-weight: 500;">
                                    <i class="bi bi-send me-1"></i> Send Email
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 2. Mail Configuration Card --}}
            <div class="card border shadow-none" style="border-radius: 4px; border-color: #e2e8f0 !important;">
                <div class="card-header bg-light border-bottom py-2 px-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-gear me-2" style="font-size: 1.1rem; color: #4a5568;"></i>
                        <span style="font-size: 0.95rem; font-weight: 500; color: #2d3748;">Mail Configuration</span>
                    </div>
                </div>
                <div class="card-body p-3">
                    <form action="{{ route('mailsetting.update') }}" method="POST">
                        @csrf
                        <div class="row">
                            {{-- Col Left --}}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mail_mailer" class="form-label mb-1" style="font-size: 0.85rem; font-weight: 600; color: #4a5568;">
                                        Mail Mailer
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="mail_mailer" 
                                           name="mail_mailer" 
                                           value="{{ old('mail_mailer', $mailsetting->mail_mailer) }}" 
                                           style="font-size: 0.9rem; border-color: #cbd5e1; border-radius: 4px; padding: 0.45rem 0.75rem;">
                                </div>
                                <div class="mb-3">
                                    <label for="mail_port" class="form-label mb-1" style="font-size: 0.85rem; font-weight: 600; color: #4a5568;">
                                        Mail Port
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="mail_port" 
                                           name="mail_port" 
                                           value="{{ old('mail_port', $mailsetting->mail_port) }}" 
                                           style="font-size: 0.9rem; border-color: #cbd5e1; border-radius: 4px; padding: 0.45rem 0.75rem;">
                                </div>
                                <div class="mb-3">
                                    <label for="mail_password" class="form-label mb-1" style="font-size: 0.85rem; font-weight: 600; color: #4a5568;">
                                        Mail Password
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control" 
                                               id="mail_password" 
                                               name="mail_password" 
                                               placeholder="Leave blank to keep current password" 
                                               style="font-size: 0.9rem; border-color: #cbd5e1; border-radius: 4px 0 0 4px; padding: 0.45rem 0.75rem;">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" style="border-color: #cbd5e1; border-left: none;">
                                            <i class="bi bi-eye" id="passwordEyeIcon" style="color: #718096;"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="mail_from_address" class="form-label mb-1" style="font-size: 0.85rem; font-weight: 600; color: #4a5568;">
                                        Mail From Address <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="mail_from_address" 
                                           name="mail_from_address" 
                                           value="{{ old('mail_from_address', $mailsetting->mail_from_address) }}" 
                                           required 
                                           style="font-size: 0.9rem; border-color: #cbd5e1; border-radius: 4px; padding: 0.45rem 0.75rem;">
                                </div>
                            </div>

                            {{-- Col Right --}}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mail_host" class="form-label mb-1" style="font-size: 0.85rem; font-weight: 600; color: #4a5568;">
                                        Mail Host
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="mail_host" 
                                           name="mail_host" 
                                           value="{{ old('mail_host', $mailsetting->mail_host) }}" 
                                           style="font-size: 0.9rem; border-color: #cbd5e1; border-radius: 4px; padding: 0.45rem 0.75rem;">
                                </div>
                                <div class="mb-3">
                                    <label for="mail_username" class="form-label mb-1" style="font-size: 0.85rem; font-weight: 600; color: #4a5568;">
                                        Mail User Name
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="mail_username" 
                                           name="mail_username" 
                                           value="{{ old('mail_username', $mailsetting->mail_username) }}" 
                                           style="font-size: 0.9rem; border-color: #cbd5e1; border-radius: 4px; padding: 0.45rem 0.75rem;">
                                </div>
                                <div class="mb-3">
                                    <label for="mail_encryption" class="form-label mb-1" style="font-size: 0.85rem; font-weight: 600; color: #4a5568;">
                                        Mail Encryption
                                    </label>
                                    <select class="form-select" 
                                            id="mail_encryption" 
                                            name="mail_encryption" 
                                            style="font-size: 0.9rem; border-color: #cbd5e1; border-radius: 4px; padding: 0.45rem 0.75rem;">
                                        <option value="SSL" {{ old('mail_encryption', $mailsetting->mail_encryption) == 'SSL' ? 'selected' : '' }}>SSL</option>
                                        <option value="TLS" {{ old('mail_encryption', $mailsetting->mail_encryption) == 'TLS' ? 'selected' : '' }}>TLS</option>
                                        <option value="" {{ old('mail_encryption', $mailsetting->mail_encryption) == '' ? 'selected' : '' }}>None</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="mail_from_name" class="form-label mb-1" style="font-size: 0.85rem; font-weight: 600; color: #4a5568;">
                                        Mail From Name
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="mail_from_name" 
                                           name="mail_from_name" 
                                           value="{{ old('mail_from_name', $mailsetting->mail_from_name) }}" 
                                           style="font-size: 0.9rem; border-color: #cbd5e1; border-radius: 4px; padding: 0.45rem 0.75rem;">
                                </div>
                            </div>
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

{{-- Inline CSS for Collapse Icon and custom details --}}
<style>
    .collapse-icon {
        transition: transform 0.2s ease-in-out;
    }
    .card-header[aria-expanded="false"] .collapse-icon {
        transform: rotate(180deg);
    }
    .input-group .btn:focus, .form-control:focus, .form-select:focus {
        box-shadow: none;
        border-color: #d9253c !important;
    }
</style>

{{-- Password visibility toggle javascript --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordField = document.getElementById('mail_password');
        const eyeIcon = document.getElementById('passwordEyeIcon');

        if (toggleBtn && passwordField && eyeIcon) {
            toggleBtn.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                
                if (type === 'password') {
                    eyeIcon.classList.remove('bi-eye-slash');
                    eyeIcon.classList.add('bi-eye');
                } else {
                    eyeIcon.classList.remove('bi-eye');
                    eyeIcon.classList.add('bi-eye-slash');
                }
            });
        }
    });
</script>
@endsection
