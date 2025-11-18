@extends('frontend.master')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
/* Minimal Custom CSS - Only Bootstrap Classes Used */
.custom-tab {
    cursor: pointer;
    transition: all 0.3s;
}
.payment-img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    cursor: pointer;
}
.loading-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
}
.loading-overlay.show {
    display: flex !important;
    justify-content: center;
    align-items: center;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.spinner-custom {
    width: 60px;
    height: 60px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #0d6efd;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
.modal-backdrop {
    --bs-backdrop-zindex:none;
    --bs-backdrop-bg:none;
}
.dashboard-grid .row {
    max-width:2000px;
    margin: 0 auto;
    display:flex;
    grid-template-columns: repeat(1, 1fr);
    gap: 20px;
}
</style>

<div class="container mt-4">
    <!-- Category Tabs -->
    <div class="d-flex justify-content-center mb-4">
        <div class="btn-group flex-wrap shadow-sm" role="group">
            <button class="btn btn-primary custom-tab" onclick="filterPostsByCategory('all')" data-category="all">
                <i class="fas fa-th-large me-2"></i>All
            </button>
            @foreach($categories as $category)
                <button class="btn btn-outline-primary custom-tab"
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

                $isPostOwnerVerified = App\Models\Kyc::where('user_id', $post->user->id)
                    ->where('status', 'approved')
                    ->exists();

                $lastActive = App\Models\User::where('id', $post->user->id)
                        ->value('last_active_at');

                $isOnline = false;
                if ($lastActive) {
                    $isOnline = \Carbon\Carbon::parse($lastActive)
                        ->greaterThan(\Carbon\Carbon::now()->subMinutes(5));
                }
            @endphp

            <div class="col-12 col-md-6 col-lg-4 post-item" data-category-id="{{ $post->category_id }}" data-type="{{ $postType }}">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <!-- Header -->
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:50px;height:50px;font-size:20px;">
                                    {{ strtoupper(substr($post->user->name ?? 'A', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <strong class="fs-6">{{ $post->user->name ?? 'Unknown' }}</strong>
                                        @if($isPostOwnerVerified)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i> VERIFIED
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle"></i> UNVERIFIED
                                            </span>
                                        @endif
                                    </div>

                                    @php
                                        $completedWithdraw = App\Models\UserWidhrawrequest::where('agent_id', $post->user->id)
                                            ->where('status', 'completed')
                                            ->count();

                                        $completedDeposit = App\Models\Userdepositerequest::where('agent_id', $post->user->id)
                                            ->where('status', 'completed')
                                            ->count();

                                        $totalOrders = $completedDeposit + $completedWithdraw;
                                        $successPercentage = $totalOrders > 0 ? ($completedWithdraw / $totalOrders) * 100 : 0;
                                        $successPercentage = round($successPercentage, 1);
                                    @endphp

                                    <small class="text-muted">
                                        <i class="fas fa-star text-warning"></i> {{ $successPercentage }}% | {{ $totalOrders }} orders
                                    </small>

                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fs-4 fw-bold text-success">{{ number_format($post->rate_balance, 2) }}</div>
                                <small class="text-muted">{{ $post->dollarsign->dollarsigned ?? 'BDT' }}/USDT</small>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="d-flex flex-wrap gap-2 mb-3 pb-3 border-bottom">
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-coins text-warning"></i> {{ number_format($post->agentamounts->sum('amount') ?? 0, 2) }} USDT
                            </span>
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-chart-line text-info"></i> {{ $post->trade_limit }}$-${{ $post->trade_limit_two }}
                            </span>
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-clock {{ $isOnline ? 'text-success' : 'text-secondary' }}"></i>
                                {{ $isOnline ? 'Online' : 'Offline' }}
                            </span>
                        </div>

                        <!-- Payment Methods -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <small class="fw-bold">
                                    <i class="fas fa-wallet text-primary"></i> Payment Methods:
                                </small>
                                @if($post->dollarsign)
                                    @php
                                        $currency = strtolower($post->dollarsign->dollarsigned ?? 'bdt');
                                        $currencyClass = in_array($currency, ['usd', 'dollar', '$']) ? 'danger'
                                            : (in_array($currency, ['eur', 'euro', '€']) ? 'primary'
                                            : (in_array($currency, ['gbp', 'pound', '£']) ? 'warning' : 'info'));
                                        $currencyLabel = in_array($currency, ['usd', 'dollar', '$']) ? 'USD'
                                            : (in_array($currency, ['eur', 'euro', '€']) ? 'EUR'
                                            : (in_array($currency, ['gbp', 'pound', '£']) ? 'GBP' : 'BDT'));
                                    @endphp
                                    <span class="badge bg-{{ $currencyClass }}">{{ $currencyLabel }}</span>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-primary">
                                    <i class="fas fa-mobile-alt me-1"></i>{{ $post->payment_name }}
                                </span>
                                <span class="badge bg-{{ $postType === 'deposit' ? 'info' : 'success' }}">
                                    {{ $post->category->category_name ?? $post->category->name ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <!-- Payment Screenshots -->
                        @if($post->photo)
                            @php
                                $photos = is_string($post->photo) ? json_decode($post->photo, true) : $post->photo;
                            @endphp
                            @if(is_array($photos) && count($photos) > 0)
                                <div class="mb-3">
                                    <div class="d-flex gap-2 overflow-auto">
                                        @foreach($photos as $photo)
                                            <img src="{{ asset($photo) }}"
                                                 alt="Payment"
                                                 class="payment-img rounded border"
                                                 onclick="showImageModal('{{ asset($photo) }}')">
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif

                        <!-- Action Buttons -->
                        @if($postType === 'deposit')
                            <button type="button"
                                    class="btn btn-primary w-100 fw-bold"
                                    onclick="openDepositRequestModal({{ $post->id }}, {{ $post->user->id }}, {{ $post->trade_limit }}, {{ $post->trade_limit_two }})">
                                <i class="fas fa-arrow-down me-2"></i>Deposit
                            </button>
                        @elseif($postType === 'withdraw')
                            <button type="button"
                                    class="btn btn-success w-100 fw-bold"
                                    onclick="openWithdrawRequestModal({{ $post->id }}, {{ $post->user->id }}, {{ $post->trade_limit }}, {{ $post->trade_limit_two }})">
                                <i class="fas fa-arrow-up me-2"></i>Withdraw
                            </button>
                        @else
                            <div class="d-flex gap-2">
                                <button type="button"
                                        class="btn btn-primary flex-fill fw-bold"
                                        onclick="openDepositRequestModal({{ $post->id }}, {{ $post->user->id }}, {{ $post->trade_limit }}, {{ $post->trade_limit_two }})">
                                    <i class="fas fa-arrow-down me-1"></i>Deposit
                                </button>
                                <button type="button"
                                        class="btn btn-success flex-fill fw-bold"
                                        onclick="openWithdrawRequestModal({{ $post->id }}, {{ $post->user->id }}, {{ $post->trade_limit }}, {{ $post->trade_limit_two }})">
                                    <i class="fas fa-arrow-up me-1"></i>Withdraw
                                </button>
                            </div>
                        @endif
                    </div>
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
    <div class="text-center text-white">
        <div class="spinner-custom mx-auto mb-3"></div>
        <p>Processing...</p>
    </div>
</div>

{{-- Image Preview Modal --}}
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Screenshot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Payment" class="img-fluid">
            </div>
        </div>
    </div>
</div>

{{-- Deposit Request Modal --}}
<div class="modal fade" id="depositRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="depositRequestForm">
                @csrf
                <input type="hidden" id="deposit_post_id" name="post_id">
                <input type="hidden" id="deposit_agent_id" name="agent_id">
                <input type="hidden" name="type" value="deposit">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-arrow-down me-2"></i>Request Deposit
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Enter the amount you want to deposit. Agent will confirm your request.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount (USDT)</label>
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
<div class="modal fade" id="depositConfirmModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="depositConfirmForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="confirm_deposit_id" name="deposit_id">

                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-check-circle me-2"></i>Agent Confirmed - Submit Payment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <a href="{{ route('frontend.user.toagent.chat') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-comment me-1"></i> Live Chat
                        </a>
                        <a href="{{ route('p2p.diposite.history') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-history me-1"></i> Deposit History
                        </a>
                        <a href="{{ route('p2p.widthraw.history') }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-history me-1"></i> Withdraw History
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-wallet me-2"></i>Payment Method
                        </label>
                        <div class="input-group">
                            <select class="form-select" id="paymentMethodSelect" required>
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
                            <button type="button" class="btn btn-success" onclick="copySelectedPaymentNumber()">
                                <i class="fas fa-copy me-1"></i> Copy
                            </button>
                        </div>
                        <small class="text-muted">Select and copy the account number</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Transaction ID <span class="text-danger">*</span></label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="e.g., TRX123456789" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sender Account <span class="text-danger">*</span></label>
                        <input type="text" name="sender_account" class="form-control" placeholder="Your account number" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Screenshot <span class="text-danger">*</span></label>
                        <input type="file" name="photo" class="form-control" accept="image/*" required>
                        <small class="text-muted">Upload payment screenshot (Max: 5MB)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success w-100 fw-bold">
                        <i class="fas fa-check me-2"></i>Confirm & Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Withdraw Request Modal - FIXED WITHOUT payment_method_id --}}
<div class="modal fade" id="withdrawRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="withdrawRequestForm">
                @csrf
                <input type="hidden" id="withdraw_post_id" name="post_id">
                <input type="hidden" id="withdraw_agent_id" name="agent_id">
                <input type="hidden" name="type" value="withdraw">
                <input type="hidden" id="hidden_sender_account" name="sender_account">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-arrow-up me-2"></i>Request Withdraw
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Payment Method Selection --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-wallet me-2"></i>Payment Method <span class="text-danger">*</span>
                        </label>
                        <select id="withdraw_payment_method_select" class="form-select" required>
                            <option value="">Select Payment Method</option>
                            @foreach ($payment_method as $item)
                                <option value="{{ $item->id }}"
                                        data-method-name="{{ $item->method_name }}"
                                        data-method-number="{{ $item->method_number ?? '' }}">
                                    {{ $item->method_name }} - {{ $item->method_number ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select where you want to receive payment</small>
                    </div>

                    {{-- Receiver Account Number --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-phone me-2"></i>Receiver Account Number <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="withdraw_account_number"
                               class="form-control"
                               placeholder="Enter your account number (e.g., 01712345678)"
                               required>
                        <small class="text-muted">Enter the account number where you'll receive payment</small>
                    </div>

                    {{-- Quick Links --}}
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <a href="{{ route('frontend.user.toagent.chat') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-comment me-1"></i> Live Chat
                        </a>
                        <a href="{{ route('p2p.diposite.history') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-history me-1"></i> Deposit History
                        </a>
                        <a href="{{ route('p2p.widthraw.history') }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-history me-1"></i> Withdraw History
                        </a>
                    </div>

                    {{-- Amount Input --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-dollar-sign me-2"></i>Amount (USDT) <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               id="withdraw_amount"
                               name="amount"
                               class="form-control form-control-lg"
                               placeholder="Enter amount"
                               step="0.01"
                               required>
                        <small class="text-muted" id="withdraw_limit_text"></small>
                    </div>

                    {{-- Transaction ID / Instruction --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-sticky-note me-2"></i>Instruction / Note
                        </label>
                        <textarea name="transaction_id"
                                  id="withdraw_transaction_id"
                                  class="form-control"
                                  rows="2"
                                  placeholder="Any special instruction for agent (Optional)"></textarea>
                        <small class="text-muted">Optional: Add any special note for the agent</small>
                    </div>

                    {{-- Summary Box --}}
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Important:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Double-check your payment method and account number</li>
                            <li>Wait for agent confirmation</li>
                            <li>Release funds only after receiving payment</li>
                        </ul>
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
<div class="modal fade" id="withdrawReleaseModal" tabindex="-1" data-bs-backdrop="static">
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
                <button id="releaseWithdrawBtn" class="btn btn-success btn-lg w-100 fw-bold">
                    <i class="fas fa-unlock me-2"></i>Release Withdraw
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    function showLoading() {
        document.getElementById('loadingOverlay').classList.add('show');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.remove('show');
    }

    function showAlert(message, type = 'info') {
        const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
        alert(`${icon} ${message}`);
    }

    function getCSRFToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    window.copySelectedPaymentNumber = function() {
        const select = document.getElementById('paymentMethodSelect');
        if (!select || !select.value) {
            showAlert('Please select a payment method first', 'error');
            return;
        }

        const selectedOption = select.options[select.selectedIndex];
        const methodNumber = selectedOption.getAttribute('data-method-number');

        if (!methodNumber || methodNumber === 'N/A') {
            showAlert('No valid number to copy', 'error');
            return;
        }

        navigator.clipboard.writeText(methodNumber).then(() => {
            showAlert(`✅ Copied: ${methodNumber}`, 'success');
        }).catch(() => {
            showAlert('Failed to copy number', 'error');
        });
    };

    window.showImageModal = function(imageUrl) {
        document.getElementById('modalImage').src = imageUrl;
        new bootstrap.Modal(document.getElementById('imageModal')).show();
    };

    window.openDepositRequestModal = function(postId, agentId, minLimit, maxLimit) {
        document.getElementById('deposit_post_id').value = postId;
        document.getElementById('deposit_agent_id').value = agentId;
        const amountInput = document.getElementById('deposit_amount');
        amountInput.min = minLimit;
        amountInput.max = maxLimit;
        document.getElementById('deposit_limit_text').textContent = `Limit: ${minLimit} - ${maxLimit} USDT`;
        new bootstrap.Modal(document.getElementById('depositRequestModal')).show();
    };

    window.openWithdrawRequestModal = function(postId, agentId, minLimit, maxLimit) {
        document.getElementById('withdraw_post_id').value = postId;
        document.getElementById('withdraw_agent_id').value = agentId;
        const amountInput = document.getElementById('withdraw_amount');
        amountInput.min = minLimit;
        amountInput.max = maxLimit;
        document.getElementById('withdraw_limit_text').textContent = `Limit: ${minLimit} - ${maxLimit} USDT`;
        new bootstrap.Modal(document.getElementById('withdrawRequestModal')).show();
    };

    window.filterPostsByCategory = function(categoryId) {
        document.querySelectorAll('.custom-tab').forEach(tab => {
            const tabCategory = tab.getAttribute('data-category');
            if(tabCategory === String(categoryId)) {
                tab.classList.remove('btn-outline-primary');
                tab.classList.add('btn-primary');
            } else {
                tab.classList.remove('btn-primary');
                tab.classList.add('btn-outline-primary');
            }
        });

        document.querySelectorAll('.post-item').forEach(post => {
            const postCategoryId = post.getAttribute('data-category-id');
            post.style.display = (categoryId === 'all' || postCategoryId === String(categoryId)) ? 'block' : 'none';
        });
    };

    document.addEventListener('DOMContentLoaded', function(){
        let depositId = null;
        let withdrawId = null;
        let depositConfirmModalOpened = false;
        let withdrawReleaseModalOpened = false;

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
            .then(res => res.ok ? res.json() : Promise.reject())
            .then(data => {
                if(data.status === 'agent_confirmed') {
                    if(type === 'deposit' && !depositConfirmModalOpened) {
                        depositId = data.deposit_id;
                        document.getElementById('confirm_deposit_id').value = depositId;
                        depositConfirmModalOpened = true;
                        new bootstrap.Modal(document.getElementById('depositConfirmModal')).show();
                    }
                    if(type === 'withdraw' && !withdrawReleaseModalOpened) {
                        withdrawId = data.withdraw_id;
                        withdrawReleaseModalOpened = true;
                        new bootstrap.Modal(document.getElementById('withdrawReleaseModal')).show();
                    }
                }
            })
            .catch(() => {});
        }

        setInterval(() => pollStatus('deposit'), 5000);
        setInterval(() => pollStatus('withdraw'), 5000);

        // ========== DEPOSIT REQUEST FORM ==========
        document.getElementById('depositRequestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHTML = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
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
            .then(res => res.ok ? res.json() : res.json().then(err => Promise.reject(err)))
            .then(data => {
                if(data.success) {
                    showAlert(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('depositRequestModal')).hide();
                    this.reset();
                    depositConfirmModalOpened = false;
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(err => showAlert(err.message || 'Failed to send deposit request', 'error'))
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
                hideLoading();
            });
        });

        // ========== DEPOSIT CONFIRMATION FORM ==========
        document.getElementById('depositConfirmForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if(!depositId) {
                showAlert('Invalid deposit ID', 'error');
                return;
            }

            const paymentSelect = document.getElementById('paymentMethodSelect');
            if (!paymentSelect || !paymentSelect.value) {
                showAlert('Please select a payment method', 'error');
                return;
            }

            const formData = new FormData(this);
            const selectedOption = paymentSelect.options[paymentSelect.selectedIndex];
            formData.append('payment_method', `${selectedOption.getAttribute('data-method-name')}: ${selectedOption.getAttribute('data-method-number')}`);

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHTML = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
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
            .then(res => res.ok ? res.json() : res.json().then(err => Promise.reject(err)))
            .then(data => {
                if(data.success) {
                    showAlert(data.message, 'success');
                    depositId = null;
                    depositConfirmModalOpened = false;
                    bootstrap.Modal.getInstance(document.getElementById('depositConfirmModal')).hide();
                    this.reset();
                    paymentSelect.selectedIndex = 0;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(err => showAlert(err.message || 'Failed to submit deposit', 'error'))
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
                hideLoading();
            });
        });

        // ========== WITHDRAW REQUEST FORM - FIXED WITHOUT payment_method_id ==========
        document.getElementById('withdrawRequestForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Get payment method details
            const paymentMethodSelect = document.getElementById('withdraw_payment_method_select');
            const accountNumber = document.getElementById('withdraw_account_number').value.trim();
            const amount = document.getElementById('withdraw_amount').value;

            // Validation
            if (!paymentMethodSelect.value) {
                showAlert('Please select a payment method', 'error');
                return;
            }

            if (!accountNumber) {
                showAlert('Please enter your receiver account number', 'error');
                return;
            }

            if (!amount || parseFloat(amount) <= 0) {
                showAlert('Please enter a valid amount', 'error');
                return;
            }

            // Get selected payment method info
            const selectedOption = paymentMethodSelect.options[paymentMethodSelect.selectedIndex];
            const methodName = selectedOption.getAttribute('data-method-name');
            const methodNumber = selectedOption.getAttribute('data-method-number');

            // Combine payment method + account number in sender_account field
            // Format: "Bkash: 01712345678"
            const senderAccountValue = `${methodName}: ${accountNumber}`;
            document.getElementById('hidden_sender_account').value = senderAccountValue;

            const formData = new FormData(this);

            // Debug: Log FormData contents
            console.log('=== Withdraw Form Data ===');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHTML = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
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
                console.log('Response Status:', res.status);
                return res.ok ? res.json() : res.json().then(err => Promise.reject(err));
            })
            .then(data => {
                console.log('Response Data:', data);
                if(data.success) {
                    showAlert(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('withdrawRequestModal')).hide();
                    this.reset();
                    document.getElementById('hidden_sender_account').value = '';
                    withdrawReleaseModalOpened = false;
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(err => {
                console.error('Withdraw Error:', err);
                showAlert(err.message || 'Failed to send withdraw request', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
                hideLoading();
            });
        });

        // ========== WITHDRAW RELEASE BUTTON ==========
        document.getElementById('releaseWithdrawBtn').addEventListener('click', function() {
            if(!withdrawId) {
                showAlert('Invalid withdraw ID', 'error');
                return;
            }

            if(!confirm('⚠️ Are you sure you want to release this withdraw?\n\nThis action cannot be undone.')) {
                return;
            }

            const originalHTML = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Releasing...';
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
            .then(res => res.ok ? res.json() : res.json().then(err => Promise.reject(err)))
            .then(data => {
                if(data.success) {
                    const balanceMsg = data.new_balance ? `\n\nNew Balance: ${data.new_balance} USDT` : '';
                    showAlert(data.message + balanceMsg, 'success');
                    withdrawId = null;
                    withdrawReleaseModalOpened = false;
                    bootstrap.Modal.getInstance(document.getElementById('withdrawReleaseModal')).hide();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(err => showAlert(err.message || 'Failed to release withdraw', 'error'))
            .finally(() => {
                this.disabled = false;
                this.innerHTML = originalHTML;
                hideLoading();
            });
        });

        // ========== MODAL CLOSE HANDLERS ==========
        document.getElementById('depositConfirmModal').addEventListener('hidden.bs.modal', function () {
            if (depositId) {
                depositConfirmModalOpened = false;
            }
            const paymentSelect = document.getElementById('paymentMethodSelect');
            if (paymentSelect) paymentSelect.selectedIndex = 0;
        });

        document.getElementById('withdrawReleaseModal').addEventListener('hidden.bs.modal', function () {
            if (withdrawId) {
                withdrawReleaseModalOpened = false;
            }
        });

        document.getElementById('withdrawRequestModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('withdrawRequestForm').reset();
            document.getElementById('hidden_sender_account').value = '';
            document.getElementById('withdraw_payment_method_select').selectedIndex = 0;
            document.getElementById('withdraw_account_number').value = '';
        });
    });
})();
</script>

@endsection
