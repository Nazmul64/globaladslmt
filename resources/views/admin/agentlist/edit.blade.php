@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3>Edit Agent</h3>

    <form action="{{ route('agentcreate.update', $agent->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Name -->
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $agent->name) }}">
            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $agent->email) }}">
            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <!-- Country -->
        <div class="mb-3">
            <label class="form-label">Country</label>
            <select name="country" class="form-select">
                @php
                $countries = ['United States','Canada','United Kingdom','Australia','Germany','France','India','Japan','China','Bangladesh'];
                @endphp
                @foreach($countries as $country)
                    <option value="{{ $country }}" {{ old('country', $agent->country) == $country ? 'selected' : '' }}>{{ $country }}</option>
                @endforeach
            </select>
            @error('country') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="form-label">Password (Leave blank if not changing)</label>
            <input type="password" name="password" class="form-control">
            @error('password') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Update Agent</button>
    </form>
</div>
@endsection
