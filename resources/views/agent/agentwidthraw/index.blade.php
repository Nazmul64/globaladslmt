@extends('agent.master')

@section('content')
<div class="container my-5" style="max-width: 520px;">

    {{-- Balance Information --}}
    <div class="text-center mb-4">
        <h5 class="fw-bold text-uppercase">Your Balance</h5>
        <p class="text-primary fw-bold mb-1 fs-4">{{ number_format($total_deposite ?? 0, 2) }} USD</p>

        {{-- ✅ Show Locked Amount --}}
        @if($locked_amount > 0)
            <p class="text-danger fw-semibold mb-1">
                <i class="bi bi-lock-fill"></i> Locked: {{ number_format($locked_amount, 2) }} USD
            </p>
        @endif

        {{-- ✅ Show Available Balance --}}
        <p class="text-success fw-bold mb-1 fs-5">
            Available: {{ number_format($available_balance ?? 0, 2) }} USD
        </p>

        <div class="small text-muted">
            <p class="mb-1">Minimum Withdraw: <strong>{{ number_format($Agentwidthraw_setup->min_widthraw ?? 0, 2) }} USD</strong></p>
            @if(isset($Agentwidthraw_setup->max_widthraw) && $Agentwidthraw_setup->max_widthraw > 0)
                <p class="mb-1">Maximum Withdraw: <strong>{{ number_format($Agentwidthraw_setup->max_widthraw, 2) }} USD</strong></p>
            @endif
            <p class="text-info fw-semibold mb-0">Withdraw Charge: {{ number_format($Agentwidthraw_setup->widthraw_charge ?? 0, 2) }}%</p>
        </div>
    </div>

    {{-- ✅ Check if withdrawal is allowed --}}
    @if($available_balance <= 0)
        <div class="alert alert-warning text-center" role="alert">
            <strong>Withdrawal not available!</strong><br>
            Your entire balance is locked. Please contact support.
        </div>
    @else
        {{-- Withdrawal Form --}}
        <form action="{{ route('agentwithdrawstore') }}" method="POST" id="withdrawForm">
            @csrf

            {{-- Payment Method Selection --}}
            <div class="mb-4">
                <label class="form-label fw-semibold">Select Payment Method</label>
                <div class="d-flex flex-wrap gap-3">
                    @forelse($payment_method ?? [] as $index => $method)
                        <div class="form-check">
                            <input
                                class="form-check-input payment-radio"
                                type="radio"
                                name="payment_method_id"
                                id="payment_{{ $method->id }}"
                                value="{{ $method->id }}"
                                data-name="{{ $method->method_name }}"
                                data-type="{{ $method->type }}"
                                data-placeholder="{{ $method->placeholder ?? 'Enter details' }}"
                                {{ $index == 0 ? 'checked' : '' }}
                                required>
                            <label class="form-check-label" for="payment_{{ $method->id }}">
                                {{ $method->method_name }}
                            </label>
                        </div>
                    @empty
                        <p class="text-danger">No payment methods available. Please contact support.</p>
                    @endforelse
                </div>
                @error('payment_method_id')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                @enderror
            </div>

            {{-- Dynamic Payment Details --}}
            @if(isset($payment_method) && count($payment_method) > 0)
                <div id="dynamicForm">

                    {{-- Account/Wallet Input --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" id="methodLabel">Account Number</label>
                        <input
                            type="text"
                            name="account"
                            id="methodInput"
                            class="form-control"
                            placeholder="Enter account details"
                            required>
                        @error('account')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Account Holder Name --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="account_number">Account Holder Name / Additional Info</label>
                        <input
                            type="text"
                            name="account_number"
                            id="account_number"
                            class="form-control"
                            placeholder="Enter account holder name"
                            required>
                        @error('account_number')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Withdraw Amount --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="withdrawAmount">Withdraw Amount (USD)</label>
                        <input
                            type="number"
                            name="amount"
                            id="withdrawAmount"
                            class="form-control"
                            placeholder="Enter amount"
                            min="{{ $Agentwidthraw_setup->min_widthraw ?? 0 }}"
                            max="{{ min($available_balance, $Agentwidthraw_setup->max_widthraw ?? $available_balance) }}"
                            step="0.01"
                            required>
                        @error('amount')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                        <small class="text-muted d-block mt-1" id="chargeInfo"></small>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" class="btn btn-success w-100 fw-bold py-2 text-uppercase">
                        Submit Withdrawal Request
                    </button>
                </div>
            @endif
        </form>
    @endif

    {{-- Additional Info --}}
    <div class="alert alert-info mt-4 small" role="alert">
        <strong>Note:</strong> Withdrawal requests are processed within 24-48 hours. Locked amounts cannot be withdrawn.
    </div>

</div>

{{-- JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('.payment-radio');
    const methodLabel = document.getElementById('methodLabel');
    const methodInput = document.getElementById('methodInput');
    const amountInput = document.getElementById('withdrawAmount');
    const chargeInfo = document.getElementById('chargeInfo');
    const withdrawCharge = {{ $Agentwidthraw_setup->widthraw_charge ?? 0 }};
    const minWithdraw = {{ $Agentwidthraw_setup->min_widthraw ?? 0 }};
    const availableBalance = {{ $available_balance ?? 0 }}; // ✅ Available balance
    const maxWithdraw = {{ $Agentwidthraw_setup->max_widthraw ?? 0 }};
    const effectiveMax = Math.min(availableBalance, maxWithdraw || availableBalance);

    function loadMethod(radio) {
        const name = radio.dataset.name || 'Payment';
        const type = radio.dataset.type || 'bank';
        const placeholder = radio.dataset.placeholder || 'Enter details';

        if (type === 'crypto') {
            methodLabel.textContent = name + ' Wallet Address';
        } else if (type === 'bank') {
            methodLabel.textContent = name + ' Account Number';
        } else {
            methodLabel.textContent = name + ' Details';
        }

        methodInput.placeholder = placeholder;
        methodInput.value = '';
        methodInput.focus();
    }

    function updateChargeInfo() {
        const amount = parseFloat(amountInput.value) || 0;

        if (amount > 0) {
            const charge = (amount * withdrawCharge) / 100;
            const receiveAmount = amount - charge;

            chargeInfo.innerHTML = `Charge: <strong>$${charge.toFixed(2)}</strong> | You will receive: <strong class="text-success">$${receiveAmount.toFixed(2)}</strong>`;
        } else {
            chargeInfo.innerHTML = '';
        }
    }

    function validateAmount() {
        const amount = parseFloat(amountInput.value) || 0;

        if (amount < minWithdraw) {
            amountInput.setCustomValidity(`Minimum withdrawal amount is $${minWithdraw}`);
        } else if (amount > effectiveMax) {
            amountInput.setCustomValidity(`Maximum available withdrawal is $${effectiveMax.toFixed(2)}`);
        } else {
            amountInput.setCustomValidity('');
        }
    }

    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            loadMethod(this);
        });
    });

    if (amountInput) {
        amountInput.addEventListener('input', function() {
            updateChargeInfo();
            validateAmount();
        });

        amountInput.addEventListener('blur', validateAmount);
    }

    const firstChecked = document.querySelector('.payment-radio:checked');
    if (firstChecked) {
        loadMethod(firstChecked);
    }

    const form = document.getElementById('withdrawForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    }
});
</script>

<style>
    .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }

    .form-control:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
    }

    #chargeInfo {
        font-size: 0.9rem;
    }
</style>

@endsection
