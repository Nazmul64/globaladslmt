@extends('frontend.master')
@section('content')
<div class="container-content">
    <div class="section-title">
        <i class="fas fa-box-open"></i> Choose Your Package
    </div>

            @php
                $user = auth()->user();
                $user_balance = App\Models\Deposite::where('user_id', $user->id)->where('status', 'approved')->sum('amount');
             @endphp
      <p>Your Balance: {{ round($user_balance ?? 0) }} BDT</p>
    <div class="packages-grid">
        @foreach ($package_type as $item)
        <div class="package-card">
            <div class="package-icon">
               <img src="{{ asset('uploads/package/' . ($item->photo ?? 'default.png')) }}"
                     alt="Package Icon"
                     class="img-fluid"
                     style="width: 80px; height: 80px; object-fit: contain;">
            </div>
            <div class="package-name">{{ $item->package_name ?? '' }}</div>
            <ul class="package-features">
                <li><i class="fas fa-check-circle"></i> Price: {{ $item->price ?? '' }} BDT</li>
                <li><i class="fas fa-check-circle"></i> Daily Income: {{ $item->daily_income ?? '' }}</li>
                <li><i class="fas fa-check-circle"></i> Validity: {{ $item->daily_limit ?? '' }} days</li>
            </ul>
           <form action="{{ route('frontend.package.buy', $item->id) }}" method="POST">
                @csrf
                <button type="submit" class="buy-package-btn">
                    <i class="fas fa-shopping-cart"></i> Buy Now
                </button>
            </form>
        </div>
        @endforeach
    </div>

    <div class="info-section">
        <div class="section-title">
            <i class="fas fa-info-circle"></i> How It Works
        </div>
        <div class="info-item">
            <div class="info-icon">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="info-text">
                <div class="info-title">1. Buy Package</div>
                <div class="info-desc">Choose your preferred package and complete the payment using your balance</div>
            </div>
        </div>
    </div>
</div>
@endsection
