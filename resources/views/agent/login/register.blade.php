<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Registration</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('admin/assets/images/favicon.png') }}" sizes="16x16">

    <!-- Bootstrap & Icons -->
    <link rel="stylesheet" href="{{ asset('admin/assets/css/lib/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('admin/assets/css/style.css') }}">

    <!-- jQuery & Toastr JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</head>
<body>

<section class="auth bg-base d-flex flex-wrap">

    <!-- Left Image Section -->
    <div class="auth-left d-lg-block d-none">
        <div class="d-flex align-items-center flex-column h-100 justify-content-center">
            <img src="{{ asset('admin/assets/images/auth/auth-img.png') }}" alt="Agent Illustration">
        </div>
    </div>

    <!-- Registration Form Section -->
    <div class="auth-right py-5 px-4 d-flex flex-column justify-content-center">
        <div class="max-w-464-px mx-auto w-100">
            <div class="text-center mb-4">
                <h4 class="fw-bold mb-3">Agent Registration</h4>
                <p class="text-muted">Create your agent account to start working with us.</p>
            </div>

            <form id="agentRegisterForm" action="{{ route('agent.register.submit') }}" method="POST">
                @csrf

                <!-- Name -->
                <div class="position-relative mb-3">
                    <span class="position-absolute top-50 translate-middle-y ms-3 text-secondary">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" name="name" class="form-control h-56-px bg-neutral-50 radius-12 ps-5" placeholder="Enter Your Name" value="{{ old('name') }}">
                    @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Email -->
                <div class="position-relative mb-3">
                    <span class="position-absolute top-50 translate-middle-y ms-3 text-secondary">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" name="email" class="form-control h-56-px bg-neutral-50 radius-12 ps-5" placeholder="Enter Your Email" value="{{ old('email') }}">
                    @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Country -->
                <div class="position-relative mb-3">
                    <select name="country" class="form-select h-56-px bg-neutral-50 radius-12">
                        <option value="" selected>Select Your Country</option>
                        @php
                            $countries = ['United States','Canada','United Kingdom','Australia','Germany','France','India','Japan','China','Bangladesh'];
                        @endphp
                        @foreach($countries as $country)
                            <option value="{{ $country }}" {{ old('country') == $country ? 'selected' : '' }}>{{ $country }}</option>
                        @endforeach
                    </select>
                    @error('country') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Password -->
                <div class="position-relative mb-3">
                    <span class="position-absolute top-50 translate-middle-y ms-3 text-secondary">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" name="password" id="password" class="form-control h-56-px bg-neutral-50 radius-12 ps-5" placeholder="Password">
                    <span class="toggle-password position-absolute end-0 top-50 translate-middle-y me-3 text-secondary" data-target="#password">
                        <i class="bi bi-eye"></i>
                    </span>
                    @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Confirm Password -->
                <div class="position-relative mb-3">
                    <span class="position-absolute top-50 translate-middle-y ms-3 text-secondary">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" name="password_confirmation" id="confirm_password" class="form-control h-56-px bg-neutral-50 radius-12 ps-5" placeholder="Confirm Password">
                </div>

                <!-- Register Button -->
                <button type="submit" class="btn btn-primary w-100 radius-12 py-3 mt-3">
                    Register
                </button>

                <!-- Login Redirect -->
                <div class="text-center mt-3">
                    <p class="text-muted mb-0">
                        Already have an account?
                        <a href="{{ route('agent.login') }}" class="text-primary text-decoration-underline">Login</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Bootstrap JS -->
<script src="{{ asset('admin/assets/js/lib/bootstrap.bundle.min.js') }}"></script>

<!-- Password Show/Hide Script -->
<script>
    $(document).ready(function(){
        $('.toggle-password').on('click', function() {
            const input = $($(this).attr('data-target'));
            const icon = $(this).find('i');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });
    });
</script>

<!-- ✅ Toastr Alert and Redirect -->
@if(Session::has('success'))
<script>
    $(document).ready(function(){
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: 3000
        };
        toastr.success("{{ Session::get('success') }}");
        setTimeout(function(){
            window.location.href = "{{ route('agent.login') }}";
        }, 2000);
    });
</script>
@endif

@if(Session::has('error'))
<script>
    $(document).ready(function(){
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: 5000
        };
        toastr.error("{{ Session::get('error') }}");
    });
</script>
@endif

</body>
</html>
