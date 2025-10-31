@extends('frontend.master')

@section('content')
<div class="globalmoneywidhraw py-4">

    <!-- Balance Section -->
    <div class="balance-section text-center mb-4">
        <div class="balance-label fw-bold">YOUR BALANCE</div>
        <div class="balance-label text-danger">{{ auth()->user()->balance ?? 0 }} Coin</div>
        <div class="balance-label">
            Minimum Withdraw: {{ round($widthraw_max_min->min_withdraw_limit ?? '') }}$
        </div>
        <div class="balance-label">
            Maximum Withdraw (Optional): {{ round($widthraw_max_min->max_withdraw_limit ?? '') }}$
        </div>
        <div class="balance-label text-info">
            Withdraw Charge: {{ round($widthraw_charge ?? '') }}$
        </div>
    </div>

    <!-- Withdraw Form -->
    <div class="form-container mx-auto" style="max-width: 500px;">
        <form action="{{ route('user.withdraw.store') }}" method="POST">
            @csrf

            <!-- Payment Options -->
            <div class="payment-options mb-3 d-flex justify-content-around">
                <div class="radio-option">
                    <input type="radio" id="nogod" name="payment_method" value="nogod" checked>
                    <label for="nogod">Nogod</label>
                </div>
                <div class="radio-option">
                    <input type="radio" id="bkash" name="payment_method" value="bkash">
                    <label for="bkash">Bkash</label>
                </div>
                <div class="radio-option">
                    <input type="radio" id="binance" name="payment_method" value="binance">
                    <label for="binance">Binance</label>
                </div>
            </div>

            <!-- Select Payment Method -->
            <div class="input-group mb-3">
                <label class="w-100 fw-semibold mb-1">Select Payment Method</label>
                <div class="input-field position-relative">
                    <i class="fas fa-wallet position-absolute top-50 translate-middle-y ms-2"></i>
                    <select name="payment_method_id" class="form-control ps-4" required>
                        <option value="">-- Select Payment Method --</option>
                        @foreach ($payment_methods as $item)
                            <option value="{{ $item->id }}">{{ ucfirst($item->method_name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Account Number (for Nogod/Bkash) -->
            <div class="input-group mb-3" id="accountGroup">
                <label class="w-100 fw-semibold mb-1">Account Number</label>
                <div class="input-field position-relative">
                    <i class="fas fa-university position-absolute top-50 translate-middle-y ms-2"></i>
                    <input type="text" name="account_number" class="form-control ps-4" placeholder="Enter Account Number" required>
                </div>
            </div>

            <!-- Binance Wallet Address -->
            <div class="input-group mb-3" id="binanceGroup" style="display: none;">
                <label class="w-100 fw-semibold mb-1">Binance Wallet Address</label>
                <div class="input-field position-relative">
                    <i class="fab fa-bitcoin position-absolute top-50 translate-middle-y ms-2"></i>
                    <input type="text" name="wallet_address" class="form-control ps-4" placeholder="Enter Binance Wallet Address">
                </div>
            </div>

            <!-- Withdraw Amount -->
            <div class="input-group mb-3">
                <label class="w-100 fw-semibold mb-1">Withdraw Amount</label>
                <div class="input-field position-relative">
                    <i class="fas fa-dollar-sign position-absolute top-50 translate-middle-y ms-2"></i>
                    <input type="number" name="amount" class="form-control ps-4"
                           placeholder="Enter amount" min="{{ $widthraw_max_min->min_withdraw_limit ?? 1 }}"
                           max="{{ $widthraw_max_min->max_withdraw_limit ?? 1000 }}" required>
                </div>
            </div>

            <!-- Withdraw Button -->
            <button type="submit" class="btn btn-success w-100 fw-bold py-2">WITHDRAW</button>
        </form>
    </div>
</div>

<!-- Script to toggle Binance/Account field -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    const accountGroup = document.getElementById('accountGroup');
    const binanceGroup = document.getElementById('binanceGroup');

    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value === 'binance') {
                binanceGroup.style.display = 'block';
                accountGroup.style.display = 'none';
            } else {
                binanceGroup.style.display = 'none';
                accountGroup.style.display = 'block';
            }
        });
    });
});
</script>
@endsection
