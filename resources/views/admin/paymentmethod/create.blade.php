@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3>Add Payment Method</h3>

    <form action="{{ route('paymentmethod.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="method_name" class="form-label">Method Name</label>
            <input type="text" name="method_name" class="form-control" value="{{ old('method_name') }}">
            @error('method_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label for="method_number" class="form-label">Method Number / Address</label>
            <input type="text" name="method_number" class="form-control" value="{{ old('method_number') }}">
            @error('method_number')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="number_type" class="form-label">Field Label (Account Number / Wallet Address)</label>
            <select name="number_type" class="form-select">
                <option value="Account Number" {{ old('number_type') == 'Account Number' ? 'selected' : '' }}>Account Number</option>
                <option value="Wallet Address" {{ old('number_type') == 'Wallet Address' ? 'selected' : '' }}>Wallet Address</option>
            </select>
            @error('number_type')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="usd_rate" class="form-label">USD Rate (Optional, e.g., 128BDT=1$)</label>
            <input type="text" name="usd_rate" class="form-control" value="{{ old('usd_rate') }}" placeholder="Leave blank if not set (e.g., 128BDT=1$)">
            @error('usd_rate')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="is_exchange_rate_active" class="form-label">Exchange Rate Status (Show/Calculate in App)</label>
            <select name="is_exchange_rate_active" class="form-select">
                <option value="0" {{ old('is_exchange_rate_active') == '0' ? 'selected' : '' }}>Disabled / বন্ধ (Off)</option>
                <option value="1" {{ old('is_exchange_rate_active') == '1' ? 'selected' : '' }}>Enabled / সক্রিয় (On)</option>
            </select>
            <small class="text-muted">যদি সক্রিয় (On) থাকে তাহলে অ্যাপসে এক্সচেঞ্জ রেট শো করবে এবং অ্যামাউন্ট ক্যালকুলেশন কাজ করবে।</small>
            @error('is_exchange_rate_active')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="is_account_number_active" class="form-label">Sender Account / Phone Number Field (Customer Input)</label>
            <select name="is_account_number_active" class="form-select">
                <option value="1" {{ old('is_account_number_active', '1') == '1' ? 'selected' : '' }}>Enabled / দেখাবে (On - Customer Enters Sender Number)</option>
                <option value="0" {{ old('is_account_number_active') == '0' ? 'selected' : '' }}>Disabled / বন্ধ (Off - For Binance/Crypto/Wallet, Hides Sender Number)</option>
            </select>
            <small class="text-muted">যদি বন্ধ (Off) থাকে, তাহলে কাস্টমার অ্যাপে একাউন্ট নাম্বার ইনপুট ফিল্ডটি দেখাবে না; শুধুমাত্র ট্রানজেকশন আইডি ও স্ক্রিনশট দিতে হবে।</small>
            @error('is_account_number_active')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="photo" class="form-label">Payment Method Logo / Image</label>
            <input type="file" name="photo" class="form-control" accept="image/*">
            @error('photo')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </select>
            @error('status')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-success">Add Method</button>
        <a href="{{ route('paymentmethod.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
