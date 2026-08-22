@extends('agent.master')

@section('content')
<div class="container mt-4" style="max-width: 700px;">
    <h4 class="mb-3 fw-bold">Edit Buy/Sell Post</h4>

    <!-- ✅ Show Balance Breakdown -->
    <div class="card mb-3 p-3 bg-light border-success">
        <div class="row">
            <div class="col-md-4">
                <p class="mb-1 text-muted small">Main Balance</p>
                <p class="mb-0 fw-bold text-primary">${{ number_format($main_balance ?? 0, 2) }}</p>
            </div>
            @if($locked_amount > 0)
            <div class="col-md-4">
                <p class="mb-1 text-muted small"><i class="bi bi-lock-fill"></i> Locked Amount</p>
                <p class="mb-0 fw-bold text-warning">${{ number_format($locked_amount, 2) }}</p>
            </div>
            @endif
            <div class="col-md-4">
                <p class="mb-1 text-muted small">Available for This Post</p>
                <p class="mb-0 fw-bold text-success" id="available-balance">${{ number_format($total_available_for_post ?? 0, 2) }}</p>
            </div>
        </div>
        <hr class="my-2">
        <small class="text-info" id="balance-info">
            <i class="bi bi-info-circle"></i> Select category to see available balance.
        </small>
    </div>

    <!-- Show Validation Alert -->
    @if(session('error'))
        <div class="alert alert-danger fw-semibold">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('agentbuysellpost.update', $agentBuySellPost->id) }}" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
        @csrf
        @method('PUT')

        <!-- Category -->
        <div class="mb-3">
            <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                            data-category="{{ strtolower($category->category_name) }}"
                            {{ $agentBuySellPost->category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->category_name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Taka & Dollar Signed -->
        <div class="mb-3">
            <label for="dollarsigends_id" class="form-label fw-semibold">Taka & Dollar Signed <span class="text-danger">*</span></label>
            <select name="dollarsigends_id" id="dollarsigends_id" class="form-select @error('dollarsigends_id') is-invalid @enderror" required>
                @foreach($takaandDollarsigend as $item)
                    <option value="{{ $item->id }}" {{ $agentBuySellPost->dollarsigends_id == $item->id ? 'selected' : '' }}>
                        {{ $item->dollarsigned }} ({{ $item->takasigned }})
                    </option>
                @endforeach
            </select>
            @error('dollarsigends_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Photos -->
        <div class="mb-3">
            <label for="photo" class="form-label fw-semibold">Photos</label>

            <!-- Current Photos -->
            @if($agentBuySellPost->photo)
                <div class="mb-2">
                    <p class="small text-muted mb-1">Current Photos:</p>
                    <div class="d-flex flex-wrap gap-2">
                        @php
                            $photos = is_array(json_decode($agentBuySellPost->photo)) ? json_decode($agentBuySellPost->photo) : [$agentBuySellPost->photo];
                        @endphp
                        @foreach($photos as $photo)
                            <img src="{{ asset($photo) }}" width="100" height="100" class="rounded border" style="object-fit: cover;">
                        @endforeach
                    </div>
                </div>
            @endif

            <input type="file" class="form-control @error('photo.*') is-invalid @enderror" name="photo[]" id="photo" multiple accept="image/*">
            <small class="text-muted">Leave empty if you don't want to change photos. Upload new photos to replace existing ones.</small>
            @error('photo.*')
                <span class="text-danger d-block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Trade Limits -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="trade_limit" class="form-label fw-semibold">Trade Limit (Min) <span class="text-danger">*</span></label>
                <input
                    type="number"
                    class="form-control @error('trade_limit') is-invalid @enderror"
                    name="trade_limit"
                    id="trade_limit"
                    value="{{ old('trade_limit', $agentBuySellPost->trade_limit) }}"
                    required>
                <small class="text-muted" id="min-limit-max">Max: ${{ number_format($total_available_for_post, 2) }}</small>
                @error('trade_limit')
                    <span class="text-danger d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="trade_limit_two" class="form-label fw-semibold">Trade Limit (Max) <span class="text-danger">*</span></label>
                <input
                    type="number"
                    class="form-control @error('trade_limit_two') is-invalid @enderror"
                    name="trade_limit_two"
                    id="trade_limit_two"
                    value="{{ old('trade_limit_two', $agentBuySellPost->trade_limit_two) }}"
                    required>
                <small class="text-muted" id="max-limit-max">Max: ${{ number_format($total_available_for_post, 2) }}</small>
                @error('trade_limit_two')
                    <span class="text-danger d-block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Rate Balance -->
        <div class="mb-3">
            <label for="rate_balance" class="form-label fw-semibold">Rate Balance <span class="text-danger">*</span></label>
            <input
                type="number"
                step="0.01"
                class="form-control @error('rate_balance') is-invalid @enderror"
                name="rate_balance"
                id="rate_balance"
                value="{{ old('rate_balance', $agentBuySellPost->rate_balance) }}"
                required>
            @error('rate_balance')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Payment Name -->
        <div class="mb-3">
            <label for="payment_name" class="form-label fw-semibold">Payment Name <span class="text-danger">*</span></label>
            <input
                type="text"
                class="form-control @error('payment_name') is-invalid @enderror"
                name="payment_name"
                id="payment_name"
                value="{{ old('payment_name', $agentBuySellPost->payment_name) }}"
                required>
            @error('payment_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Status -->
        <div class="mb-3">
            <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
            <select class="form-select @error('status') is-invalid @enderror" name="status" id="status" required>
                <option value="approved" {{ $agentBuySellPost->status === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="pending" {{ $agentBuySellPost->status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="rejected" {{ $agentBuySellPost->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            @error('status')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Form Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('agentbuysellpost.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save"></i> Update Post
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category_id');
        const availableBalanceEl = document.getElementById('available-balance');
        const balanceInfoEl = document.getElementById('balance-info');
        const minLimitMaxEl = document.getElementById('min-limit-max');
        const maxLimitMaxEl = document.getElementById('max-limit-max');
        const tradeLimitMin = document.getElementById('trade_limit');
        const tradeLimitMax = document.getElementById('trade_limit_two');

        const mainBalance = {{ $main_balance ?? 0 }};
        const lockedAmount = {{ $locked_amount ?? 0 }};

        function updateAvailableBalance() {
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const categoryName = selectedOption.getAttribute('data-category');

            let availableBalance = 0;
            let infoText = '';

            if (categoryName === 'withdraw' || categoryName === 'withdrawal') {
                // Withdraw = Main + Locked
                availableBalance = mainBalance + lockedAmount;
                infoText = '<i class="bi bi-info-circle"></i> <strong>Withdraw Post:</strong> You can use Main Balance ($' +
                           mainBalance.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ') + Locked Amount ($' +
                           lockedAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ') = Total $' +
                           availableBalance.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            } else if (categoryName === 'deposit' || categoryName === 'deposite') {
                // Deposit = Only Main Balance
                availableBalance = mainBalance;
                infoText = '<i class="bi bi-exclamation-triangle"></i> <strong>Deposit Post:</strong> You can ONLY use Main Balance ($' +
                           availableBalance.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") +
                           '). Locked Amount cannot be used.';
            } else {
                // Other = Only Main Balance
                availableBalance = mainBalance;
                infoText = '<i class="bi bi-info-circle"></i> You can use Main Balance only ($' +
                           availableBalance.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ')';
            }

            availableBalanceEl.textContent = '$' + availableBalance.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            balanceInfoEl.innerHTML = infoText;
            minLimitMaxEl.textContent = 'Max: $' + availableBalance.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            maxLimitMaxEl.textContent = 'Max: $' + availableBalance.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");

            tradeLimitMin.setAttribute('max', availableBalance);
            tradeLimitMax.setAttribute('max', availableBalance);
        }

        categorySelect.addEventListener('change', updateAvailableBalance);

        // Initial update
        updateAvailableBalance();
    });
</script>
@endpush
@endsection
