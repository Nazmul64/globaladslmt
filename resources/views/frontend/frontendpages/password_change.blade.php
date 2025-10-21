@extends('frontend.master')

@section('content')
<div class="container-fluid my-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card passwordChange-card border-0 shadow-lg p-4" style="width:372px; justify-content:center; margin-right:-27px;">

                <!-- Profile Update Form -->
                <form enctype="multipart/form-data" method="POST" action="{{ route('frontend.password.submit') }}">
                    @csrf

                    <!-- New Password -->
                    <div class="mb-3 position-relative">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" class="form-control" name="new_password" id="newPassword" placeholder="Enter your new password">
                        <span class="toggle-password" onclick="togglePassword('newPassword')" style="position:absolute; right:10px; top:38px; cursor:pointer;">
                            👁️
                        </span>
                    </div>

                    <!-- Password -->
                    <div class="mb-3 position-relative">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password">
                        <span class="toggle-password" onclick="togglePassword('password')" style="position:absolute; right:10px; top:38px; cursor:pointer;">
                            👁️
                        </span>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3 position-relative">
                        <label class="form-label fw-semibold">Confirm Password</label>
                        <input type="password" class="form-control" name="confirm_password" id="confirmPassword" placeholder="Confirm your password">
                        <span class="toggle-password" onclick="togglePassword('confirmPassword')" style="position:absolute; right:10px; top:38px; cursor:pointer;">
                            👁️
                        </span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-100 py-2 passwordChange-btn fw-bold">Update Profile</button>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const input = document.getElementById(fieldId);
    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}
</script>
@endsection
