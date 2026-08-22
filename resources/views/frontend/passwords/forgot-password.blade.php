@extends('frontend.master')

@section('content')
<div class="container" style="max-width:600px;margin-top:40px">
    <h3>Forgot Password</h3>

    {{-- Success message --}}
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

 <form method="POST" action="{{ route('user.password.email') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email"
               value="{{ old('email') }}" required autofocus>
    </div>

    <button type="submit" class="btn btn-primary">Send Reset Link</button>
    <a href="{{ route('login') }}" class="btn btn-link">Back to Login</a>
</form>

</div>
@endsection
