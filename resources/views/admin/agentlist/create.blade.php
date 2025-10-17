@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3>Create New Agent</h3>
    <a class="btn btn-success"href="{{ route('agentcreate.index') }}">Back to Agent List</a>
    <form action="{{ route('agentcreate.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label>Country</label>
            <select name="country" class="form-control">
                <option value="">Select Country</option>
                @foreach($countries as $country)
                    <option value="{{ $country }}" {{ old('country') == $country ? 'selected' : '' }}>{{ $country }}</option>
                @endforeach
            </select>
            @error('country') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control">
            @error('password') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Create Agent</button>
    </form>
</div>
@endsection
