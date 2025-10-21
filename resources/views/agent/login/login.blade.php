<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agent Login</title>

  <link rel="icon" href="{{ asset('admin/assets/images/favicon.png') }}" type="image/png">
  <link rel="stylesheet" href="{{ asset('admin/assets/css/lib/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('admin/assets/css/style.css') }}">
</head>
<body>

<section class="auth bg-base d-flex flex-wrap">
  <div class="auth-left d-lg-block d-none">
    <div class="d-flex align-items-center flex-column h-100 justify-content-center">
      <img src="{{ asset('admin/assets/images/auth/auth-img.png') }}" alt="">
    </div>
  </div>

  <div class="auth-right py-32 px-24 d-flex flex-column justify-content-center">
    <div class="max-w-464-px mx-auto w-100">
      <div>
        <h4 class="mb-12 text-center">Welcome to Agent Login</h4>
      </div>

      @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif

      @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      <form action="{{ route('agent.submit') }}" method="POST">
        @csrf
        <div class="icon-field mb-16">
          <span class="icon top-50 translate-middle-y">
            <i class="ri-mail-line"></i>
          </span>
          <input type="email" name="email" class="form-control h-56-px bg-neutral-50 radius-12" placeholder="Email" required>
        </div>

        <div class="position-relative mb-20">
          <div class="icon-field">
            <span class="icon top-50 translate-middle-y">
              <i class="ri-lock-password-line"></i>
            </span>
            <input type="password" name="password" class="form-control h-56-px bg-neutral-50 radius-12" id="password" placeholder="Password" required>
          </div>
          <span class="toggle-password ri-eye-line cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light" data-toggle="#password"></span>
        </div>

        <div class="form-check d-flex align-items-center mb-3">
          <input class="form-check-input" type="checkbox" id="remember">
          <label class="form-check-label ms-2" for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 radius-12">Login</button>

        <div class="text-center mt-3">
          <p>Don't have an account? <a href="{{ route('agent.register') }}" class="text-primary">Register here</a></p>
        </div>
      </form>
    </div>
  </div>
</section>

<script src="{{ asset('admin/assets/js/lib/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/lib/bootstrap.bundle.min.js') }}"></script>

<script>
  // Password Show/Hide
  $('.toggle-password').on('click', function () {
    $(this).toggleClass("ri-eye-off-line");
    const input = $($(this).attr("data-toggle"));
    if (input.attr("type") === "password") {
      input.attr("type", "text");
    } else {
      input.attr("type", "password");
    }
  });
</script>

</body>
</html>
