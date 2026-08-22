@extends('frontend.master')

@section('content')
<div class="container" style="max-width:600px;margin-top:40px">
    <h3>Reset Password</h3>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('user.password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control"
                   value="{{ old('email', $email) }}" required>
        </div>

        <div class="mb-3 position-relative">
            <label>New Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
            <span onclick="togglePassword('password')" style="cursor:pointer; position:absolute; right:10px; top:38px;">👁️</span>
        </div>

        <div class="mb-3 position-relative">
            <label>Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
            <span onclick="togglePassword('password_confirmation')" style="cursor:pointer; position:absolute; right:10px; top:38px;">👁️</span>
        </div>

        <button type="submit" class="btn btn-success">Reset Password</button>
        <a href="{{ route('login') }}" class="btn btn-link">Back to Login</a>
    </form>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    field.type = field.type === "password" ? "text" : "password";
}
</script>

@endsection
