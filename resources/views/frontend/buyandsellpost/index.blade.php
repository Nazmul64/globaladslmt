@extends('frontend.master')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
.modal-backdrop.show { background-color: rgba(0,0,0,0.5) !important; z-index: 1040 !important; }
.modal { z-index: 1050 !important; }

/* Modern Card Design */
.trader-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
    overflow: hidden;
}
.trader-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}

/* Tabs Design */
.custom-tabs {
    display: inline-flex;
    background: #f3f4f6;
    border-radius: 10px;
    padding: 4px;
    gap: 4px;
    flex-wrap: wrap;
}
.custom-tab {
    padding: 10px 24px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 500;
    border: none;
    background: transparent;
    color: #6b7280;
}
.custom-tab.active {
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    color: #1f2937;
}

/* Verified Badge - Green */
.verified-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #dcfce7;
    color: #166534;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

/* Unverified Badge - Red */
.unverified-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #fee2e2;
    color: #991b1b;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

/* Rate Display */
.rate-display {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
}

/* Currency Badge Icons */
.currency-badges {
    display: flex;
    gap: 6px;
}
.currency-icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid #e5e7eb;
}
.currency-icon:hover {
    transform: scale(1.1);
    border-color: #10b981;
}
.currency-icon.bdt {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}
.currency-icon.usd {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}
.currency-icon.eur {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}
.currency-icon.gbp {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
}

/* Image Carousel */
.payment-images {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding: 8px 0;
    scrollbar-width: thin;
}
.payment-images::-webkit-scrollbar {
    height: 4px;
}
.payment-images::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;
}
.payment-img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    flex-shrink: 0;
    border: 2px solid #e5e7eb;
    transition: all 0.2s;
}
.payment-img:hover {
    border-color: #10b981;
    transform: scale(1.05);
}

/* Stats */
.stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #6b7280;
    font-size: 13px;
}
.stat-value {
    color: #1f2937;
    font-weight: 600;
}

/* Action Buttons */
.btn-custom {
    border-radius: 8px;
    font-weight: 600;
    padding: 12px;
    transition: all 0.3s;
}
.btn-deposit {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border: none;
    color: white;
}
.btn-deposit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    color: white;
}
.btn-withdraw {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    color: white;
}
.btn-withdraw:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    color: white;
}

/* Image Modal */
.image-modal-img {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
}

/* Loading Overlay */
.loading-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}
.loading-overlay.show {
    display: flex !important;
}
.loading-content {
    text-align: center;
    color: white;
}
.loading-spinner {
    width: 60px;
    height: 60px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Payment Method Selector with Copy Button */
.payment-method-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: 10px;
    transition: all 0.3s;
    cursor: pointer;
}
.payment-method-item:hover {
    border-color: #10b981;
    background-color: #f0fdf4;
}
.payment-method-item.selected {
    border-color: #10b981;
    background-color: #dcfce7;
}
.payment-method-info {
    flex: 1;
}
.payment-method-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 14px;
}
.payment-method-number {
    color: #6b7280;
    font-size: 13px;
    font-family: monospace;
}
.copy-btn {
    background: #10b981;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.copy-btn:hover {
    background: #059669;
    transform: scale(1.05);
}
.copy-btn.copied {
    background: #3b82f6;
}
</style>

<div class="container mt-4">
    <!-- Dynamic Category Tabs -->
    <div class="d-flex justify-content-center mb-4">
        <div class="custom-tabs">
            <button class="custom-tab active" onclick="filterPostsByCategory('all')" data-category="all">
                <i class="fas fa-th-large me-2"></i>All
            </button>

            @foreach($categories as $category)
                <button class="custom-tab"
                        onclick="filterPostsByCategory('{{ $category->id }}')"
                        data-category="{{ $category->id }}">
                    @php
                        $categoryName = strtolower(trim($category->category_name ?? $category->name ?? ''));
                        $icon = in_array($categoryName, ['deposit', 'buy']) ? 'fa-arrow-down'
                            : (in_array($categoryName, ['withdraw', 'sell']) ? 'fa-arrow-up' : 'fa-tag');
                    @endphp
                    <i class="fas {{ $icon }} me-2"></i>{{ $category->category_name ?? $category->name }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Posts Container -->
    <div class="row g-3" id="postsContainer">
        @forelse ($all_agentbuysellpost as $post)
            @php
                $categoryName = strtolower(trim($post->category->category_name ?? $post->category->name ?? ''));
                $isDeposit = in_array($categoryName, ['deposit', 'buy', 'deposits', 'বাই', 'ডিপোজিট']) ||
                             str_contains($categoryName, 'deposit') || str_contains($categoryName, 'buy');
                $isWithdraw = in_array($categoryName, ['withdraw', 'sell', 'withdraws', 'সেল', 'উইথড্র']) ||
                              str_contains($categoryName, 'withdraw') || str_contains($categoryName, 'sell');
                $postType = $isDeposit ? 'deposit' : ($isWithdraw ? 'withdraw' : 'other');

                // Check if post owner is verified
                $isPostOwnerVerified = App\Models\Kyc::where('user_id', $post->user->id)
                    ->where('status', 'approved')
                    ->exists();
            @endphp

            <div class="col-12 post-item" data-category-id="{{ $post->category_id }}" data-type="{{ $postType }}">
                <div class="trader-card p-3">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width:45px;height:45px;font-size:18px;font-weight:700;">
                                {{ strtoupper(substr($post->user->name ?? 'A', 0, 1)) }}
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <strong style="font-size:15px;">{{ $post->user->name ?? 'Unknown' }}</strong>
                                    @if($isPostOwnerVerified)
                                        <span class="verified-badge">
                                            <i class="fas fa-check-circle"></i> VERIFIED
                                        </span>
                                    @else
                                        <span class="unverified-badge">
                                            <i class="fas fa-times-circle"></i> UNVERIFIED
                                        </span>
                                    @endif
                                </div>
                                <div class="text-muted" style="font-size:12px;">
                                    <i class="fas fa-star text-warning"></i> 98.7% | 7653 orders
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="rate-display text-success">{{ number_format($post->rate_balance, 2) }}</div>
                            <div class="text-muted" style="font-size:12px;">
                                {{ $post->dollarsign->dollarsigned ?? 'BDT' }}/USDT
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-3 mb-3 pb-3 border-bottom">
                        <div class="stat-item">
                            <i class="fas fa-coins text-warning"></i>
                            <span>Quantity: <span class="stat-value">{{ number_format($post->agentamounts->sum('amount') ?? 0, 2) }} USDT</span></span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-chart-line text-info"></i>
                            <span>Limit: <span class="stat-value">{{ $post->trade_limit }}-{{ $post->trade_limit_two }}</span></span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-clock text-success"></i>
                            <span><span class="stat-value">{{ $post->duration ?? 15 }} min</span></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-wallet text-primary"></i>
                                <strong style="font-size:14px;">Payment Methods:</strong>
                            </div>

                            @if($post->dollarsign)
                                <div class="currency-badges">
                                    @php
                                        $currency = strtolower($post->dollarsign->dollarsigned ?? 'bdt');
                                        $currencyClass = in_array($currency, ['usd', 'dollar', '$']) ? 'usd'
                                            : (in_array($currency, ['eur', 'euro', '€']) ? 'eur'
                                            : (in_array($currency, ['gbp', 'pound', '£']) ? 'gbp' : 'bdt'));
                                        $currencyLabel = $currencyClass === 'usd' ? 'USD'
                                            : ($currencyClass === 'eur' ? 'EUR'
                                            : ($currencyClass === 'gbp' ? 'GBP' : 'BDT'));
                                    @endphp
                                    <div class="currency-icon {{ $currencyClass }}" title="{{ $currencyLabel }}">
                                        {{ $currencyLabel }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-primary">
                                <i class="fas fa-mobile-alt me-1"></i>{{ $post->payment_name }}
                            </span>
                            <span class="badge {{ $postType === 'deposit' ? 'bg-info' : 'bg-success' }}">
                                {{ $post->category->category_name ?? $post->category->name ?? 'N/A' }}
                            </span>
                        </div>
                    </div>

                    @if($post->photo)
                        @php
                            $photos = is_string($post->photo) ? json_decode($post->photo, true) : $post->photo;
                        @endphp
                        @if(is_array($photos) && count($photos) > 0)
                            <div class="mb-3">
                                <div class="text-muted mb-2" style="font-size:13px;">
                                    <i class="fas fa-images me-1"></i>Payment Screenshots:
                                </div>
                                <div class="payment-images">
                                    @foreach($photos as $photo)
                                        <img src="{{ asset($photo) }}"
                                             alt="Payment Method"
                                             class="payment-img"
                                             onclick="showImageModal('{{ asset($photo) }}')">
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif

                    @if($postType === 'deposit')
                        <button type="button"
                                class="btn btn-deposit btn-custom w-100"
                                onclick="openDepositRequestModal({{ $post->id }}, {{ $post->user->id }}, {{ $post->trade_limit }}, {{ $post->trade_limit_two }})">
                            <i class="fas fa-arrow-down me-2"></i>Deposit
                        </button>
                    @elseif($postType === 'withdraw')
                        <button type="button"
                                class="btn btn-withdraw btn-custom w-100"
                                onclick="openWithdrawRequestModal({{ $post->id }}, {{ $post->user->id }}, {{ $post->trade_limit }}, {{ $post->trade_limit_two }})">
                            <i class="fas fa-arrow-up me-2"></i>Withdraw
                        </button>
                    @else
                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-deposit btn-custom flex-fill"
                                    onclick="openDepositRequestModal({{ $post->id }}, {{ $post->user->id }}, {{ $post->trade_limit }}, {{ $post->trade_limit_two }})">
                                <i class="fas fa-arrow-down me-2"></i>Deposit
                            </button>
                            <button type="button"
                                    class="btn btn-withdraw btn-custom flex-fill"
                                    onclick="openWithdrawRequestModal({{ $post->id }}, {{ $post->user->id }}, {{ $post->trade_limit }}, {{ $post->trade_limit_two }})">
                                <i class="fas fa-arrow-up me-2"></i>Withdraw
                            </button>
                        </div>
                    @endif

                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>No posts available at the moment.
                </div>
            </div>
        @endforelse
    </div>
</div>

{{-- Loading Overlay --}}
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <p>Processing...</p>
    </div>
</div>

{{-- Image Preview Modal --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Method Screenshot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Payment Screenshot" class="image-modal-img">
            </div>
        </div>
    </div>
</div>

{{-- Deposit Request Modal --}}
<div class="modal fade" id="depositRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="depositRequestForm">
                @csrf
                <input type="hidden" id="deposit_post_id" name="post_id">
                <input type="hidden" id="deposit_agent_id" name="agent_id">
                <input type="hidden" name="type" value="deposit">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-arrow-down text-primary me-2"></i>Request Deposit
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Enter the amount you want to deposit. Agent will confirm your request.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (USDT)</label>
                        <input type="number"
                               id="deposit_amount"
                               name="amount"
                               class="form-control form-control-lg"
                               placeholder="Enter amount"
                               step="0.01"
                               required>
                        <small class="text-muted" id="deposit_limit_text"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Send Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Deposit Confirmation Modal --}}
<div class="modal fade" id="depositConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="depositConfirmForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="confirm_deposit_id" name="deposit_id">

                <div class="modal-header text-white" style="background-color:#FF825E !important;">
                    <h5 class="modal-title" style="color:white;">
                        <i class="fas fa-check-circle me-2" style="color:white;"></i>Agent Confirmed - Submit Payment Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Great!</strong> Agent has confirmed your deposit request. Now submit your payment details.
                    </div>

                    {{-- Payment Methods Dropdown with Copy Button --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-wallet me-2"></i>Select Payment Method
                        </label>
                        <div class="input-group">
                            <select class="form-select" id="paymentMethodSelect" aria-label="Select payment method" required>
                                <option value="" selected disabled>Choose Payment Method</option>
                                @if(isset($payment_method) && count($payment_method) > 0)
                                    @foreach($payment_method as $method)
                                        <option value="{{ $method->method_number ?? '' }}"
                                                data-method-name="{{ $method->method_name ?? 'N/A' }}"
                                                data-method-number="{{ $method->method_number ?? 'N/A' }}">
                                            {{ $method->method_name ?? 'N/A' }} - {{ $method->method_number ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>No payment methods available</option>
                                @endif
                            </select>
                            <button type="button"
                                    class="btn btn-success"
                                    id="copySelectedNumberBtn"
                                    onclick="copySelectedPaymentNumber()"
                                    style="min-width: 100px;">
                                <i class="fas fa-copy me-1"></i> Copy Number
                            </button>
                        </div>
                        <small class="text-muted">Select a method and click "Copy Number" to copy the account number</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Transaction ID <span class="text-danger">*</span></label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="e.g., TRX123456789" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sender Account <span class="text-danger">*</span></label>
                        <input type="text" name="sender_account" class="form-control" placeholder="Your account number" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Screenshot <span class="text-danger">*</span></label>
                        <input type="file" name="photo" class="form-control" accept="image/*" required>
                        <small class="text-muted">Upload screenshot of your payment (Max: 5MB)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-check me-2"></i>Confirm & Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Withdraw Request Modal --}}
<div class="modal fade" id="withdrawRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="withdrawRequestForm">
                @csrf
                <input type="hidden" id="withdraw_post_id" name="post_id">
                <input type="hidden" id="withdraw_agent_id" name="agent_id">
                <input type="hidden" name="type" value="withdraw">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-arrow-up text-success me-2"></i>Request Withdraw
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Enter the amount you want to withdraw. Agent will confirm your request.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (USDT)</label>
                        <input type="number"
                               id="withdraw_amount"
                               name="amount"
                               class="form-control form-control-lg"
                               placeholder="Enter amount"
                               step="0.01"
                               required>
                        <small class="text-muted" id="withdraw_limit_text"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-2"></i>Send Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Withdraw Release Modal --}}
<div class="modal fade" id="withdrawReleaseModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content text-center">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title w-100">
                    <i class="fas fa-check-circle me-2"></i>Agent Confirmed - Release Funds
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
                <h5 class="mt-3 mb-2">Payment Confirmed!</h5>
                <p class="text-muted">Agent has confirmed receiving your payment.</p>
                <p class="fw-bold text-success">Click below to release your USDT funds.</p>
            </div>
            <div class="modal-footer">
                <button id="releaseWithdrawBtn" class="btn btn-success btn-lg w-100">
                    <i class="fas fa-unlock me-2"></i>Release Withdraw
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    // Utility Functions
    function showLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) overlay.classList.add('show');
    }

    function hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) overlay.classList.remove('show');
    }

    function showAlert(message, type = 'info') {
        const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
        alert(`${icon} ${message}`);
    }

    function getCSRFToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    // Copy Selected Payment Number from Dropdown
    window.copySelectedPaymentNumber = function() {
        const select = document.getElementById('paymentMethodSelect');
        const copyBtn = document.getElementById('copySelectedNumberBtn');

        if (!select || !select.value) {
            showAlert('Please select a payment method first', 'error');
            return;
        }

        const selectedOption = select.options[select.selectedIndex];
        const methodName = selectedOption.getAttribute('data-method-name');
        const methodNumber = selectedOption.getAttribute('data-method-number');

        if (!methodNumber || methodNumber === 'N/A') {
            showAlert('No valid number to copy', 'error');
            return;
        }

        // Copy to clipboard
        navigator.clipboard.writeText(methodNumber).then(() => {
            const originalHTML = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
            copyBtn.classList.add('btn-primary');
            copyBtn.classList.remove('btn-success');

            showAlert(`✅ Copied: ${methodNumber}`, 'success');

            // Reset button after 2 seconds
            setTimeout(() => {
                copyBtn.innerHTML = originalHTML;
                copyBtn.classList.remove('btn-primary');
                copyBtn.classList.add('btn-success');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy:', err);
            showAlert('Failed to copy number', 'error');
        });
    };

    // Select Payment Method Function (Keep for backward compatibility)
    window.selectPaymentMethod = function(element) {
        // Remove selected class from all items
        document.querySelectorAll('.payment-method-item').forEach(item => {
            item.classList.remove('selected');
        });

        // Add selected class to clicked item
        element.classList.add('selected');

        // Store selected method data
        const methodName = element.getAttribute('data-method-name');
        const methodNumber = element.getAttribute('data-method-number');

        // Set hidden input value
        const hiddenInput = document.getElementById('selected_payment_method');
        if (hiddenInput) {
            hiddenInput.value = `${methodName}: ${methodNumber}`;
        }

        console.log('Selected payment method:', methodName, methodNumber);
    };

    // Make functions global
    window.showImageModal = function(imageUrl) {
        const img = document.getElementById('modalImage');
        if (img) {
            img.src = imageUrl;
            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            modal.show();
        }
    };

    window.openDepositRequestModal = function(postId, agentId, minLimit, maxLimit) {
        document.getElementById('deposit_post_id').value = postId;
        document.getElementById('deposit_agent_id').value = agentId;
        const amountInput = document.getElementById('deposit_amount');
        amountInput.min = minLimit;
        amountInput.max = maxLimit;
        document.getElementById('deposit_limit_text').textContent = `Limit: ${minLimit} - ${maxLimit} USDT`;
        const modal = new bootstrap.Modal(document.getElementById('depositRequestModal'));
        modal.show();
    };

    window.openWithdrawRequestModal = function(postId, agentId, minLimit, maxLimit) {
        document.getElementById('withdraw_post_id').value = postId;
        document.getElementById('withdraw_agent_id').value = agentId;
        const amountInput = document.getElementById('withdraw_amount');
        amountInput.min = minLimit;
        amountInput.max = maxLimit;
        document.getElementById('withdraw_limit_text').textContent = `Limit: ${minLimit} - ${maxLimit} USDT`;
        const modal = new bootstrap.Modal(document.getElementById('withdrawRequestModal'));
        modal.show();
    };

    window.filterPostsByCategory = function(categoryId) {
        document.querySelectorAll('.custom-tab').forEach(tab => {
            tab.classList.remove('active');
            if(tab.getAttribute('data-category') === String(categoryId)) {
                tab.classList.add('active');
            }
        });

        document.querySelectorAll('.post-item').forEach(post => {
            const postCategoryId = post.getAttribute('data-category-id');
            post.style.display = (categoryId === 'all' || postCategoryId === String(categoryId)) ? 'block' : 'none';
        });
    };

    // DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function(){
        console.log('🚀 Page loaded successfully');

        let depositId = null;
        let withdrawId = null;
        let depositConfirmModalOpened = false;
        let withdrawReleaseModalOpened = false;

        // Polling function
        function pollStatus(type) {
            const route = type === 'deposit'
                ? '{{ route("user.deposit.status") }}'
                : '{{ route("user.withdraw.status") }}';

            fetch(route, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                if(data.status === 'agent_confirmed') {
                    if(type === 'deposit' && !depositConfirmModalOpened) {
                        depositId = data.deposit_id;
                        document.getElementById('confirm_deposit_id').value = depositId;
                        depositConfirmModalOpened = true;
                        const modal = new bootstrap.Modal(document.getElementById('depositConfirmModal'));
                        modal.show();
                        console.log('✅ Deposit confirmed by agent');
                    }
                    if(type === 'withdraw' && !withdrawReleaseModalOpened) {
                        withdrawId = data.withdraw_id;
                        withdrawReleaseModalOpened = true;
                        const modal = new bootstrap.Modal(document.getElementById('withdrawReleaseModal'));
                        modal.show();
                        console.log('✅ Withdraw confirmed by agent');
                    }
                }
            })
            .catch(err => {
                console.error(`❌ ${type} polling error:`, err);
            });
        }

        // Start polling (every 5 seconds)
        const depositPollInterval = setInterval(() => pollStatus('deposit'), 5000);
        const withdrawPollInterval = setInterval(() => pollStatus('withdraw'), 5000);

        // Deposit Request Form Handler
        const depositRequestForm = document.getElementById('depositRequestForm');
        if (depositRequestForm) {
            depositRequestForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalHTML = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
                showLoading();

                fetch('{{ route("user.deposit.request") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCSRFToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(err => Promise.reject(err));
                    }
                    return res.json();
                })
                .then(data => {
                    if(data.success) {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('depositRequestModal')).hide();
                        depositRequestForm.reset();
                        depositConfirmModalOpened = false;
                        console.log('✅ Deposit request sent successfully');
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error('❌ Deposit Request Error:', err);
                    showAlert(err.message || 'Failed to send deposit request', 'error');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHTML;
                    hideLoading();
                });
            });
        }

        // Deposit Confirmation Form Handler
        const depositConfirmForm = document.getElementById('depositConfirmForm');
        if (depositConfirmForm) {
            depositConfirmForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if(!depositId) {
                    showAlert('Invalid deposit ID. Please try again.', 'error');
                    return;
                }

                // Check if payment method is selected
                const paymentSelect = document.getElementById('paymentMethodSelect');
                if (!paymentSelect || !paymentSelect.value) {
                    showAlert('⚠️ Please select a payment method', 'error');
                    return;
                }

                const formData = new FormData(this);

                // Add selected payment method info to form data
                const selectedOption = paymentSelect.options[paymentSelect.selectedIndex];
                const methodName = selectedOption.getAttribute('data-method-name');
                const methodNumber = selectedOption.getAttribute('data-method-number');
                formData.append('payment_method', `${methodName}: ${methodNumber}`);

                const submitBtn = this.querySelector('button[type="submit"]');
                const originalHTML = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
                showLoading();

                fetch(`{{ url('user/deposit/submit') }}/${depositId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCSRFToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(err => Promise.reject(err));
                    }
                    return res.json();
                })
                .then(data => {
                    if(data.success) {
                        showAlert(data.message, 'success');
                        depositId = null;
                        depositConfirmModalOpened = false;
                        bootstrap.Modal.getInstance(document.getElementById('depositConfirmModal')).hide();
                        depositConfirmForm.reset();
                        // Reset payment method selection
                        const paymentSelect = document.getElementById('paymentMethodSelect');
                        if (paymentSelect) paymentSelect.selectedIndex = 0;
                        console.log('✅ Deposit details submitted successfully');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error('❌ Deposit Confirm Error:', err);
                    showAlert(err.message || 'Failed to submit deposit', 'error');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHTML;
                    hideLoading();
                });
            });
        }

        // Withdraw Request Form Handler
        const withdrawRequestForm = document.getElementById('withdrawRequestForm');
        if (withdrawRequestForm) {
            withdrawRequestForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalHTML = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
                showLoading();

                fetch('{{ route("user.withdraw.request") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCSRFToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(err => Promise.reject(err));
                    }
                    return res.json();
                })
                .then(data => {
                    if(data.success) {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('withdrawRequestModal')).hide();
                        withdrawRequestForm.reset();
                        withdrawReleaseModalOpened = false;
                        console.log('✅ Withdraw request sent successfully');
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error('❌ Withdraw Request Error:', err);
                    showAlert(err.message || 'Failed to send withdraw request', 'error');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHTML;
                    hideLoading();
                });
            });
        }

        // Withdraw Release Button Handler
        const releaseWithdrawBtn = document.getElementById('releaseWithdrawBtn');
        if (releaseWithdrawBtn) {
            releaseWithdrawBtn.addEventListener('click', function() {
                if(!withdrawId) {
                    showAlert('Invalid withdraw ID. Please try again.', 'error');
                    return;
                }

                if(!confirm('⚠️ Are you sure you want to release this withdraw?\n\nThis action cannot be undone.')) {
                    return;
                }

                const originalHTML = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Releasing...';
                showLoading();

                fetch(`{{ url('user/withdraw/submit') }}/${withdrawId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCSRFToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({})
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(err => Promise.reject(err));
                    }
                    return res.json();
                })
                .then(data => {
                    if(data.success) {
                        const balanceMsg = data.new_balance ? `\n\nNew Balance: ${data.new_balance} USDT` : '';
                        showAlert(data.message + balanceMsg, 'success');
                        withdrawId = null;
                        withdrawReleaseModalOpened = false;
                        bootstrap.Modal.getInstance(document.getElementById('withdrawReleaseModal')).hide();
                        console.log('✅ Withdraw released successfully');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error('❌ Withdraw Release Error:', err);
                    showAlert(err.message || 'Failed to release withdraw', 'error');
                })
                .finally(() => {
                    releaseWithdrawBtn.disabled = false;
                    releaseWithdrawBtn.innerHTML = originalHTML;
                    hideLoading();
                });
            });
        }

        // Modal close event handlers - reset polling flags
        const depositConfirmModalEl = document.getElementById('depositConfirmModal');
        if (depositConfirmModalEl) {
            depositConfirmModalEl.addEventListener('hidden.bs.modal', function () {
                if (depositId) {
                    depositConfirmModalOpened = false;
                    console.log('ℹ️ Deposit modal closed, polling will reopen if still confirmed');
                }
                // Reset payment method dropdown
                const paymentSelect = document.getElementById('paymentMethodSelect');
                if (paymentSelect) paymentSelect.selectedIndex = 0;
            });
        }

        const withdrawReleaseModalEl = document.getElementById('withdrawReleaseModal');
        if (withdrawReleaseModalEl) {
            withdrawReleaseModalEl.addEventListener('hidden.bs.modal', function () {
                if (withdrawId) {
                    withdrawReleaseModalOpened = false;
                    console.log('ℹ️ Withdraw modal closed, polling will reopen if still confirmed');
                }
            });
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            clearInterval(depositPollInterval);
            clearInterval(withdrawPollInterval);
            console.log('🛑 Polling intervals cleared');
        });

        console.log('✅ All event listeners initialized');
    });
})();
</script>

@endsection
