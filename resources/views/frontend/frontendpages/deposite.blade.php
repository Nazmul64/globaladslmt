@extends('frontend.master')

@section('head')
<style>
.method-number-display {
    word-break: break-all;
    overflow-wrap: anywhere;
    white-space: normal;
    font-size: 0.95rem;
    font-weight: 600;
    line-height: 1.4;
    padding: 8px 12px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin: 8px 0;
}
.usd-rate-text {
    font-size: 0.85rem;
    color: #4a5568;
    margin-top: 4px;
    font-weight: 500;
}
</style>
@endsection

@section('content')
<div class="form-section">

    <!-- Payment Method Selection - Horizontal Cards -->
    <div class="payment-methods-horizontal mb-4">
        @foreach($payment_methods as $method)
        <div class="payment-method-card"
             onclick="selectPaymentMethodFromCard('{{ strtolower($method->method_name ?? '') }}', {{ $method->id }}, '{{ addslashes($method->number_type ?? 'Account Number') }}', '{{ addslashes($method->usd_rate ?? '') }}')"
             id="card-{{ strtolower($method->method_name ?? '') }}">

            <div class="method-header">
                @if($method->photo)
                    <img src="{{ asset('uploads/paymentmethod/'.$method->photo) }}"
                         alt="{{ $method->method_name }}" class="method-logo">
                @endif
                <h6 class="method-title">{{ $method->method_name }}</h6>
                @if($method->usd_rate)
                    <span class="badge bg-light text-dark border">{{ $method->usd_rate }}</span>
                @endif
            </div>

            @if($method->method_number)
                <div class="method-number-display" onclick="event.stopPropagation()">
                    <span class="text-muted small d-block mb-1">{{ $method->number_type ?? 'Account Number' }}:</span>
                    {{ $method->method_number }}
                </div>

                <button type="button"
                        class="copy-button btn btn-sm btn-outline-primary"
                        onclick="event.stopPropagation(); copyNumber('{{ $method->method_number }}', this)">
                        Copy
                </button>
            @endif

        </div>
        @endforeach
    </div>

    <!-- Send Money Section -->
    <div id="sendMoneySection">
        <h5 class="mb-3">Send Money</h5>

        <form action="{{ route('deposit.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="payment_method" id="selectedMethod">

            <div class="form-content show" id="form-content">

                <label class="form-label">Amount</label>
                <input type="number" name="amount" class="form-input" placeholder="Enter deposit amount">
                <div id="usdRateDisplay" class="usd-rate-text mb-3"></div>
                @error('amount') <span class="text-danger">{{ $message }}</span> @enderror

                <label class="form-label" id="senderAccountLabel">Your Account Number / Wallet Address</label>
                <input type="text" name="sender_account" class="form-input" placeholder="Enter your account number / address">
                @error('sender_account') <span class="text-danger">{{ $message }}</span> @enderror

                <label class="form-label">Transaction ID</label>
                <input type="text" name="transaction_id" class="form-input" placeholder="Enter Transaction ID">
                @error('transaction_id') <span class="text-danger">{{ $message }}</span> @enderror

                <label class="form-label">Screenshot Image</label>
                <input type="file" name="photo" class="form-input">
                @error('photo') <span class="text-danger">{{ $message }}</span> @enderror

                <button type="submit" class="submit-btn mt-3">Submit</button>
            </div>
        </form>
    </div>

</div>


<!-- JS -->
<script>
function selectPaymentMethodFromCard(name, id, numberType, usdRate) {
    document.getElementById('selectedMethod').value = id;

    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.classList.remove('active');
    });

    const targetCard = document.getElementById('card-' + name);
    if (targetCard) targetCard.classList.add('active');

    // Update label dynamically
    const labelEl = document.getElementById('senderAccountLabel');
    if (labelEl && numberType) {
        labelEl.innerText = 'Your ' + numberType;
    }

    // Update USD Rate display below Amount field
    const usdRateEl = document.getElementById('usdRateDisplay');
    if (usdRateEl) {
        if (usdRate && usdRate.trim() !== '') {
            usdRateEl.innerHTML = '<i class="fas fa-info-circle text-primary me-1"></i> USD Rate: <strong>' + usdRate + '</strong>';
        } else {
            usdRateEl.innerHTML = '';
        }
    }
}

// Copy Method Number
function copyNumber(number, btn) {
    navigator.clipboard.writeText(number)
        .then(() => {
            const originalText = btn.innerText;
            btn.innerText = "Copied!";
            btn.classList.add('copied');

            setTimeout(() => {
                btn.innerText = originalText;
                btn.classList.remove('copied');
            }, 1500);
        })
        .catch(() => alert("Failed to copy"));
}
</script>


<!-- Styles -->
<style>
.payment-methods-horizontal { display: flex; gap: 15px; overflow-x: auto; padding: 10px 0; }
.payment-method-card { background: white; border: 2px solid #e0e0e0; border-radius: 8px; padding: 15px; min-width: 180px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); cursor: pointer; transition: .3s ease; position: relative; }
.payment-method-card:hover { border-color: #007bff; transform: translateY(-2px); }
.payment-method-card.active { border: 3px solid #28a745; background: #f0fff4; box-shadow: 0 6px 12px rgba(40,167,69,0.25); }
.payment-method-card.active::before { content:"✓"; position:absolute; top:8px; right:8px; background:#28a745; color:white; width:22px; height:22px; border-radius:50%; display:flex; justify-content:center; align-items:center; font-size:13px; }
.method-header { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.method-logo { width: 40px; height: 40px; object-fit: contain; }
.method-title { font-size: 16px; font-weight: 600; margin: 0; color: #333; }
.method-number-input { width: 100%; padding: 8px 10px; border:1px solid #ddd; border-radius:5px; background:#f8f9fa; font-weight:600; font-size:14px; margin-bottom: 8px; }
.copy-button { width: 100%; padding: 9px; background:#ff6b4a; color:white; border:none; border-radius:5px; font-weight:600; cursor:pointer; transition:.3s; }
.copy-button:hover { background:#ff5533; }
.copy-button.copied { background:#28a745 !important; }
.form-input { width: 100%; padding: 10px; border:1px solid #ccc; border-radius:6px; margin-bottom: 12px; }
.submit-btn { background:#28a745; color:white; border:none; padding:10px 18px; border-radius:6px; font-weight:600; cursor:pointer; }
.submit-btn:hover { background:#218838; }
@media (max-width:768px) { .payment-methods-horizontal { flex-direction:column; } .payment-method-card { min-width: 100%; } }
</style>
@endsection
