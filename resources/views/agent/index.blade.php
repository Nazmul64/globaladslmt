@extends('agent.master')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Dashboard</h6>
    <ul class="d-flex align-items-center gap-2">
        <li class="fw-medium">
            <a href="{{ route('agent.dashboard') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                Dashboard
            </a>
        </li>
        <li>-</li>
        <li class="fw-medium">Agent</li>
    </ul>
</div>

{{-- Lock Status Alert --}}
@if($locked_amount > 0)
<div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
    <div class="d-flex align-items-center gap-2">
        <iconify-icon icon="solar:lock-bold" class="text-warning fs-4"></iconify-icon>
        <div>
            <strong>Account Lock Active!</strong><br>
            <small>
                Lock Amount: <strong>${{ number_format($locked_amount, 2) }}</strong> |
                Status: <span class="badge bg-warning">{{ $lock_status }}</span> |
                Available for withdrawal: <strong class="text-success">${{ number_format($available_balance, 2) }}</strong>
            </small>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row row-cols-xxxl-5 row-cols-lg-3 row-cols-sm-2 row-cols-1 gy-4">

    <!-- Total Deposit -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Total Deposit</p>
                        <h6 class="mb-0">${{ number_format($total_deposite, 2) }}</h6>
                        <small class="text-muted">All approved deposits</small>
                    </div>
                    <div class="w-50-px h-50-px bg-primary rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:wallet-bold" class="text-white text-2xl mb-0"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lock Amount -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-2 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Lock Amount</p>
                        <h6 class="mb-0 {{ $locked_amount > 0 ? 'text-danger' : 'text-success' }}">
                            ${{ number_format($locked_amount, 2) }}
                        </h6>
                        <small class="text-muted">
                            @if($locked_amount > 0)
                                <span class="badge bg-warning">{{ $lock_status }}</span>
                            @else
                                <span class="badge bg-success">No Lock</span>
                            @endif
                        </small>
                    </div>
                    <div class="w-50-px h-50-px {{ $locked_amount > 0 ? 'bg-danger' : 'bg-success' }} rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="{{ $locked_amount > 0 ? 'solar:lock-bold' : 'solar:lock-unlocked-bold' }}" class="text-white text-2xl mb-0"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Balance -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-3 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Available Balance</p>
                        <h6 class="mb-0 text-success">${{ number_format($available_balance, 2) }}</h6>
                        <small class="text-muted">
                            @if($locked_amount > 0)
                                (Deposit - Lock)
                            @else
                                Full access
                            @endif
                        </small>
                    </div>
                    <div class="w-50-px h-50-px bg-success rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:wallet-money-bold" class="text-white text-2xl mb-0"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Withdraw -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-4 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Total Withdraw</p>
                        <h6 class="mb-0">${{ number_format($total_widthraw, 2) }}</h6>
                        <small class="text-muted">All approved withdrawals</small>
                    </div>
                    <div class="w-50-px h-50-px bg-info rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:money-bag-bold" class="text-white text-2xl mb-0"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Commission -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-5 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Total Commission</p>
                        <h6 class="mb-0">${{ number_format($total_come, 2) }}</h6>
                        <small class="text-muted">Deposit + Withdraw commission</small>
                    </div>
                    <div class="w-50-px h-50-px bg-warning rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:dollar-bold" class="text-white text-2xl mb-0"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Balance Breakdown Card --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:calculator-bold" class="me-2"></iconify-icon>
                    Balance Breakdown
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Total Deposit:</span>
                                <strong class="text-primary fs-5">${{ number_format($total_deposite, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Locked Amount:</span>
                                <strong class="text-danger fs-5">- ${{ number_format($locked_amount, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-success-subtle rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Available Balance:</span>
                                <strong class="text-success fs-5">${{ number_format($available_balance, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Lock Information --}}
                @if($locked_amount > 0)
                <div class="alert alert-info mb-0">
                    <div class="d-flex align-items-start gap-2">
                        <iconify-icon icon="solar:info-circle-bold" class="fs-4 text-info"></iconify-icon>
                        <div>
                            <strong>Lock System Information:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Lock Status: <span class="badge bg-warning">{{ $lock_status }}</span></li>
                                <li>Locked Amount: <strong class="text-danger">${{ number_format($locked_amount, 2) }}</strong></li>
                                <li>You can only withdraw from available balance: <strong class="text-success">${{ number_format($available_balance, 2) }}</strong></li>
                                <li>Formula: <code>Available Balance = Total Deposit - Locked Amount</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
                @else
                <div class="alert alert-success mb-0">
                    <div class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:check-circle-bold" class="fs-4 text-success"></iconify-icon>
                        <strong>No lock applied. Full balance available for withdrawal!</strong>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Recent Activity Summary (Optional) --}}
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:wallet-bold" class="me-2"></iconify-icon>
                    Deposit Summary
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Total Approved Deposits:</span>
                    <strong class="text-primary">${{ number_format($total_deposite, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Commission Earned:</span>
                    <strong class="text-success">${{ number_format($deposit_income, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:money-bag-bold" class="me-2"></iconify-icon>
                    Withdrawal Summary
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Total Approved Withdrawals:</span>
                    <strong class="text-info">${{ number_format($total_widthraw, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Commission Earned:</span>
                    <strong class="text-success">${{ number_format($withdraw_income, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="notice-bar">
    <div class="notice-label">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        <span>নোটিশ</span>
    </div>
    <div class="notice-wrapper">
        <div class="notice-text">
            @foreach($notices as $notice)
                <span class="notice-item">
                    <span class="notice-icon">📢</span>
                    {{ $notice->notices }}
                </span>
            @endforeach
            {{-- duplicate for smooth loop --}}
            @foreach($notices as $notice)
                <span class="notice-item">
                    <span class="notice-icon">📢</span>
                    {{ $notice->notices }}
                </span>
            @endforeach
        </div>
    </div>
</div>

<style>
.notice-bar {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border-radius: 12px;
    overflow: hidden;
    font-weight: 500;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    margin: 20px 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* left label */
.notice-label {
    background: rgba(0, 0, 0, 0.2);
    padding: 15px 25px;
    font-weight: bold;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-right: 2px solid rgba(255, 255, 255, 0.3);
}

.notice-label svg {
    animation: pulse 2s ease-in-out infinite;
}

/* wrapper */
.notice-wrapper {
    overflow: hidden;
    white-space: nowrap;
    flex: 1;
    padding: 5px 0;
}

/* moving text */
.notice-text {
    display: inline-block;
    white-space: nowrap;
    animation: scroll 40s linear infinite;
    padding-left: 100%;
}

/* hover to pause */
.notice-bar:hover .notice-text {
    animation-play-state: paused;
}

/* each notice */
.notice-item {
    margin-right: 80px;
    font-size: 16px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.notice-icon {
    font-size: 20px;
    animation: bounce 1s ease-in-out infinite;
}

/* scroll animation */
@keyframes scroll {
    from {
        transform: translateX(0);
    }
    to {
        transform: translateX(-50%);
    }
}

/* pulse animation for icon */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

/* bounce animation for emoji */
@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-3px);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .notice-label {
        padding: 12px 15px;
        font-size: 14px;
    }

    .notice-label span {
        display: none;
    }

    .notice-item {
        font-size: 14px;
        margin-right: 50px;
    }
}
</style>

@endsection
