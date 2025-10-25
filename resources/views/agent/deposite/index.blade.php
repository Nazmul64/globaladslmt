@extends('agent.master')

@section('content')
<div class="container mt-4" style="max-width: 500px;">
    <h4 class="mb-3">Send Money</h4>

    <div class="mb-3">
        <label for="payment_method" class="form-label">Select & Copy Payment Method</label>
        <div class="input-group">
            <select name="payment_method_id" id="payment_method" class="form-select">
                <option value="" data-number="" data-photo="">-- Select Method --</option>
                @foreach($payment_method as $method)
                    <option value="{{ $method->id }}" data-number="{{ $method->method_number }}" data-photo="{{ asset('uploads/paymentmethod/'.$method->photo) }}">
                        {{ $method->method_number }} - {{ $method->method_name }}
                    </option>
                @endforeach
            </select>
            <button class="btn btn-outline-secondary" type="button" id="copyButton">Copy</button>
        </div>
        <small id="copyMessage" class="form-text text-success" style="display:none;"></small>
    </div>

    <div id="paymentImagePreview" class="mb-3 text-center"></div>

    <form action="{{ route('agent.deposite.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" name="amount" class="form-control" placeholder="Enter amount">
            @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="sender_account" class="form-label">Sender Account No.</label>
            <input type="text" name="sender_account" class="form-control" placeholder="Enter sender account">
            @error('sender_account') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="transaction_id" class="form-label">Transaction ID</label>
            <input type="text" name="transaction_id" class="form-control" placeholder="Enter transaction ID">
            @error('transaction_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="photo" class="form-label">Screenshot</label>
            <input type="file" name="photo" class="form-control" accept="image/*">
            @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="btn btn-success w-100">Submit</button>
    </form>
</div>

<script>
const copyButton = document.getElementById('copyButton');
const paymentSelect = document.getElementById('payment_method');
const copyMessage = document.getElementById('copyMessage');
const paymentImagePreview = document.getElementById('paymentImagePreview');

copyButton.addEventListener('click', function() {
    const selected = paymentSelect.options[paymentSelect.selectedIndex];
    const number = selected.dataset.number;
    if(!number) {
        copyMessage.style.display = 'block';
        copyMessage.textContent = 'Please select a payment method first!';
        copyMessage.classList.add('text-danger');
        return;
    }
    navigator.clipboard.writeText(number).then(() => {
        copyMessage.style.display = 'block';
        copyMessage.textContent = 'Payment number copied: ' + number;
        copyMessage.classList.remove('text-danger');
        copyMessage.classList.add('text-success');
        setTimeout(()=>copyMessage.style.display='none',2000);
    });
});

paymentSelect.addEventListener('change', function() {
    const selected = paymentSelect.options[paymentSelect.selectedIndex];
    const photo = selected.dataset.photo;
    paymentImagePreview.innerHTML = photo ? `<img src="${photo}" style="max-height:80px;">` : '';
});
</script>
@endsection
