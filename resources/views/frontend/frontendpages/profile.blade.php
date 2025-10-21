@extends('frontend.master')

@section('content')
<div class="stats-section">
    <div class="stats-grid">
        <!-- Total Coins -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="stat-value">-46</div>
            <div class="stat-label">Total Coins</div>
        </div>

        <!-- Tasks Done -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-value">127</div>
            <div class="stat-label">Tasks Done</div>
        </div>

        <!-- Profile -->
        <div class="stat-card">
            <div class="stat-icon">
                <a href="{{ route('frontend.profile.main') }}">
                    <i class="fas fa-user" style="color:white;"></i>
                </a>
            </div>
            <div class="stat-value">
                <a href="{{ route('frontend.profile.main') }}" style="text-decoration: none; color: inherit;">Profile</a>
            </div>
        </div>

        <!-- Password -->
        <div class="stat-card">
            <div class="stat-icon">
                 <a href="{{ route('frontend.profile.main') }}">
                    <i class="fas fa-key" style="color:white;"></i>
                </a>
            </div>
            <div class="stat-value">
                <a href="{{ route('frontend.password.change') }}" style="text-decoration: none; color: inherit;">Password Change</a>
            </div>
        </div>
        <!-- KYC Submit -->
       <div class="stat-card">
            <div class="stat-icon">
                 <a href="{{ route('frontend.key') }}">
                    <i class="fas fa-key" style="color:white;"></i>
                </a>
            </div>
            <div class="stat-value">
                <a href="{{ route('frontend.key') }}" style="text-decoration: none; color: inherit;">Key Submit</a>
            </div>
        </div>
        @php
            use Illuminate\Support\Facades\Auth;
            $user = Auth::user(); // get currently logged-in user
        @endphp

        <!-- User Info -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-value">Join Date</div>
            <div class="stat-label">{{ $user->created_at ? $user->created_at->format('d M Y') : '' }}</div>
            <div class="stat-label">{{ $user->name ?? '' }}</div>
        </div>

</div>
@endsection
