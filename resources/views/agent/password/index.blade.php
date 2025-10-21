@extends('agent.master')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow p-4">

                <h5 class="text-center mb-4">Change Password</h5>

                <form action="{{ route('agent.password.submit') }}" method="POST">
                    @csrf

                    <!-- Current Password -->
                    <div class="mb-3 position-relative">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" name="current_password" id="current_password" class="form-control">
                        <span toggle="#current_password" class="toggle-password position-absolute" style="top:38px; right:10px; cursor:pointer;">
                            👁️
                        </span>
                        @error('current_password')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div class="mb-3 position-relative">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" name="new_password" id="new_password" class="form-control">
                        <span toggle="#new_password" class="toggle-password position-absolute" style="top:38px; right:10px; cursor:pointer;">
                            👁️
                        </span>
                        @error('new_password')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Confirm New Password -->
                    <div class="mb-3 position-relative">
                        <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control">
                        <span toggle="#new_password_confirmation" class="toggle-password position-absolute" style="top:38px; right:10px; cursor:pointer;">
                            👁️
                        </span>
                        @error('new_password_confirmation')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Update Password</button>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- Show/Hide Password Script -->
<script>
    const togglePassword = document.querySelectorAll('.toggle-password');

    togglePassword.forEach(function(element) {
        element.addEventListener('click', function() {
            const input = document.querySelector(this.getAttribute('toggle'));
            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = '🙈';
            } else {
                input.type = 'password';
                this.textContent = '👁️';
            }
        });
    });
</script>
@endsection
