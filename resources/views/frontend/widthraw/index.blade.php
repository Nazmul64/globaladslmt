@extends('frontend.master')

@section('content')
<div class="globalmoneywidhraw py-4">

    <!-- Balance Section -->
    <!-- Balance Section -->
<section class="text-center mb-4">
    <h5 class="fw-bold">YOUR BALANCE</h5>
    <ul class="list-unstyled mb-0">
        <li class="text-danger">{{ auth()->user()->balance ?? 0 }} Coin</li>
        <li>Minimum Withdraw: {{ round($widthraw_max_min->min_withdraw_limit ?? '') }}$</li>
        <li>Maximum Withdraw (Optional): {{ round($widthraw_max_min->max_withdraw_limit ?? '') }}$</li>
        <li class="text-info">Withdraw Charge: {{ round($widthraw_charge ?? '') }}$</li>
    </ul>
</section>


    <!-- Withdraw Form -->
    <div class="form-container container px-3">
        <form action="{{ route('user.withdraw.store') }}" method="POST" class="bg-white p-3 rounded shadow-sm">
            @csrf

            <!-- Payment Options -->
            <div class="payment-options mb-3 d-flex justify-content-between flex-wrap text-center">
                <div class="form-check">
                    <input type="radio" id="nogod" name="payment_method" value="nogod" class="form-check-input" checked>
                    <label for="nogod" class="form-check-label">Nogod</label>
                </div>
                <div class="form-check">
                    <input type="radio" id="bkash" name="payment_method" value="bkash" class="form-check-input">
                    <label for="bkash" class="form-check-label">Bkash</label>
                </div>
                <div class="form-check">
                    <input type="radio" id="binance" name="payment_method" value="binance" class="form-check-input">
                    <label for="binance" class="form-check-label">Binance</label>
                </div>
            </div>

            <!-- Select Payment Method -->
            <div class="mb-3">
                <label class="fw-semibold mb-1">Select Payment Method</label>
                <select name="payment_method_id" class="form-control">
                    <option value="">-- Select Payment Method --</option>
                    @foreach ($payment_methods as $item)
                        <option value="{{ $item->id }}">{{ ucfirst($item->method_name) }}</option>
                    @endforeach
                </select>
                @error('payment_method_id')
                    <span class="text-danger">{{$message}}</span>
                @enderror
            </div>

            <!-- Account Number -->
            <div class="mb-3" id="accountGroup">
                <label class="fw-semibold mb-1">Account Number</label>
                <input type="text" name="account_number" class="form-control" placeholder="Enter Account Number">
                @error('account_number')
                    <span class="text-danger">{{$message}}</span>
                @enderror
            </div>

            <!-- Binance Wallet -->
            <div class="mb-3" id="binanceGroup" style="display: none;">
                <label class="fw-semibold mb-1">Binance Wallet Address</label>
                <input type="text" name="wallet_address" class="form-control" placeholder="Enter Binance Wallet Address">
                @error('wallet_address')
                    <span class="text-danger">{{$message}}</span>
                @enderror
            </div>

            <!-- Withdraw Amount -->
            <div class="mb-3">
                <label class="fw-semibold mb-1">Withdraw Amount</label>
                <input type="number" name="amount" class="form-control"
                       placeholder="Enter amount"
                       min="{{ $widthraw_max_min->min_withdraw_limit ?? "" }}"
                       max="{{ $widthraw_max_min->max_withdraw_limit ?? "" }}">
                @error('amount')
                    <span class="text-danger">{{$message}}</span>
                @enderror
            </div>

            <!-- Withdraw Button -->
            <button type="submit" class="btn btn-success w-100 fw-bold py-2">WITHDRAW</button>
        </form>
    </div>
</div>

<!-- Script -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const radios = document.querySelectorAll('input[name="payment_method"]');
    const accountGroup = document.getElementById('accountGroup');
    const binanceGroup = document.getElementById('binanceGroup');

    radios.forEach(radio => {
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
