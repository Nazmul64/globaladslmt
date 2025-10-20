@extends('frontend.master')
@section('content')
<div class="container">

    <!-- Recent Payments -->
    <div class="section-title">
        <i class="fas fa-history"></i>
        Recent Transactions
    </div>

    <div class="payment-list">
        @forelse($deposits as $deposit)
        <div class="payment-card">
            <div class="payment-header">
                <div class="payment-method">
                    <div class="method-icon">
                        @if($deposit->method == 'PayPal')
                            <i class="fab fa-paypal"></i>
                        @elseif($deposit->method == 'Bkash')
                            <i class="fas fa-mobile-alt"></i>
                        @else
                            <i class="fas fa-university"></i>
                        @endif
                    </div>
                    <span>{{ $deposit->method }}</span>
                </div>
                <div class="payment-amount">৳{{ round($deposit->amount) }}</div>
            </div>
            <div class="payment-details">
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value">#{{ $deposit->transaction_id ?? '' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">{{ $deposit->created_at->format('M d, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    @if($deposit->status == 'completed')
                        <span class="status-badge status-success">Completed</span>
                    @elseif($deposit->status == 'pending')
                        <span class="status-badge status-pending">Processing</span>
                    @else
                        <span class="status-badge status-secondary">{{ ucfirst($deposit->status) }}</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-muted p-3">
            No transactions found.
        </div>
        @endforelse
    </div>
</div>
@endsection
