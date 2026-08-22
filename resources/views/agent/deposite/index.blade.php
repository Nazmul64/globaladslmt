@extends('agent.master')

@section('content')
<div class="container mt-4" style="max-width:500px;">
    <h4 class="mb-3">Send Money</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Payment method select -->
    <div class="mb-3">
        <label class="form-label">Select & Copy Payment Method</label>
        <div class="input-group">
            <select id="payment_method_id" class="form-select">
                <option value="" data-number="" data-photo="">-- Select Method --</option>
                @foreach($payment_methods as $method)
                    <option
                        value="{{ $method->id }}"
                        data-number="{{ $method->method_number }}"
                        data-photo="{{ asset('uploads/paymentmethod/'.$method->photo) }}">
                        {{ $method->method_name }} - {{ $method->method_number }}
                    </option>
                @endforeach
            </select>
            <button type="button" id="copyButton" class="btn btn-outline-secondary">Copy</button>
        </div>
        <small id="copyMessage" class="text-success d-none"></small>
    </div>

    <!-- Image preview -->
    <div id="paymentImagePreview" class="text-center mb-3"></div>

    <!-- Deposit form -->
    <form action="{{ route('agent.deposite.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="payment_method_id" id="payment_method_hidden">

        <div class="mb-3">
            <label class="form-label">Amount</label>
            <input type="number" name="amount" class="form-control">
            @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Sender Account No</label>
            <input type="text" name="sender_account" class="form-control">
            @error('sender_account') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Transaction ID</label>
            <input type="text" name="transaction_id" class="form-control">
            @error('transaction_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Payment Screenshot</label>
            <input type="file" name="photo" class="form-control">
            @error('photo') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button class="btn btn-success w-100">Submit Deposit</button>
    </form>
</div>

{{-- JavaScript --}}
<script>
const paymentSelect = document.getElementById('payment_method_id');
const hiddenPayment = document.getElementById('payment_method_hidden');
const copyButton = document.getElementById('copyButton');
const copyMessage = document.getElementById('copyMessage');
const imagePreview = document.getElementById('paymentImagePreview');

// Copy number
copyButton.onclick = () => {
    const selected = paymentSelect.options[paymentSelect.selectedIndex];
    const number = selected.dataset.number;

    if (!number) {
        copyMessage.className = 'text-danger';
        copyMessage.textContent = 'Please select a payment method';
        copyMessage.classList.remove('d-none');
        return;
    }

    navigator.clipboard.writeText(number).then(() => {
        copyMessage.className = 'text-success';
        copyMessage.textContent = 'Copied: ' + number;
        copyMessage.classList.remove('d-none');
        setTimeout(() => copyMessage.classList.add('d-none'), 2000);
    });
};

// Change event
paymentSelect.onchange = () => {
    const selected = paymentSelect.options[paymentSelect.selectedIndex];
    hiddenPayment.value = selected.value;

    const photo = selected.dataset.photo;
    imagePreview.innerHTML = photo ? `<img src="${photo}" height="80">` : '';
};
</script>
@endsection
