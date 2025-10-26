@extends('frontend.master')

@section('content')

<div class="agentanduserbuyandsell-header">
    @foreach ($categories as $item)
        <div class="agentanduserbuyandsell-tab {{ $loop->first ? 'active' : '' }}"
             data-tab="{{ $item->id }}">
             {{ $item->category_name }}
        </div>
    @endforeach
</div>

<div class="agentanduserbuyandsell-buyPosts p-3">
    @foreach ($categories as $category)
        <div class="buy-posts-category" id="category-{{ $category->id }}"
             style="{{ $loop->first ? '' : 'display:none;' }}">
            @foreach ($all_agentbuysellpost->where('category_id', $category->id) as $post)
                <div class="agentanduserbuyandsell-trader-card">
                    <div class="agentanduserbuyandsell-trader-header">
                        <div class="agentanduserbuyandsell-trader-avatar">
                            {{ strtoupper(substr($post->user->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="agentanduserbuyandsell-trader-name">{{ $post->user->name ?? 'Unknown' }}</div>
                        <div class="agentanduserbuyandsell-verified-badge">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>

                    <div class="agentanduserbuyandsell-price">
                        {{ $post->trade_limit }} <span class="agentanduserbuyandsell-price-unit">USDT</span>
                    </div>
                    <div class="agentanduserbuyandsell-limits">
                        Limit: {{ $post->trade_limit }} - {{ $post->trade_limit_two }}
                    </div>
                    <div class="agentanduserbuyandsell-available">
                        Available <strong>{{ $post->available_balance }} USDT</strong>
                    </div>

                    <div class="agentanduserbuyandsell-payment-methods">
                        <i class="fas fa-clock" style="color: #22c55e;"></i>
                        <span>{{ $post->duration }} min</span>
                        <div class="agentanduserbuyandsell-payment-badge">
                            <i class="fas fa-check-circle" style="color: #22c55e;"></i> {{ $post->payment_name }}
                        </div>
                    </div>

                    <button class="agentanduserbuyandsell-buy-btn">Request</button>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
<script>
    document.querySelectorAll('.agentanduserbuyandsell-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.agentanduserbuyandsell-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        const categoryId = this.getAttribute('data-tab');
        document.querySelectorAll('.buy-posts-category').forEach(cat => cat.style.display = 'none');
        document.getElementById('category-' + categoryId).style.display = 'block';
    });
});

</script>
@endsection
