@extends('frontend.master')

@section('content')

<div class="agentanduserbuyandsell-header mb-3">
    @foreach ($categories as $item)
        <div class="agentanduserbuyandsell-tab {{ $loop->first ? 'active' : '' }}" data-tab="{{ $item->id }}">
            {{ $item->category_name }}
        </div>
    @endforeach
</div>

<div class="agentanduserbuyandsell-buyPosts p-3">
    @foreach ($categories as $category)
        <div class="buy-posts-category" id="category-{{ $category->id }}" style="{{ $loop->first ? '' : 'display:none;' }}">
            @foreach ($all_agentbuysellpost->where('category_id', $category->id) as $post)
                <div class="agentanduserbuyandsell-trader-card mb-3 p-3 border rounded">
                    <div class="agentanduserbuyandsell-trader-header d-flex align-items-center mb-2">
                        <div class="agentanduserbuyandsell-trader-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                            {{ strtoupper(substr($post->user->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="ms-2">
                            <strong>{{ $post->user->name ?? 'Unknown' }}</strong>
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                    </div>

                    <div class="mb-2">
                        <strong>{{ $post->trade_limit }}</strong> <span class="text-muted">USDT</span>
                    </div>
                    <div class="text-muted mb-1">
                        Limit: {{ $post->trade_limit }} - {{ $post->trade_limit_two }}
                    </div>
                    <div class="text-muted mb-2">
                        Available: <strong>{{ $post->available_balance }} USDT</strong>
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fas fa-clock text-success"></i>
                        <span>{{ $post->duration }} min</span>
                        <span class="badge bg-success">{{ $post->payment_name }}</span>
                    </div>

                    <form action="{{ route('userwidhraw.request') }}" method="POST" class="deposit-request-form">
                        @csrf
                        <input type="hidden" name="type" value="deposit">
                        <input type="hidden" name="agent_id" value="{{ $post->user->id }}">
                        <input type="number" name="amount" class="form-control mb-2" placeholder="Enter amount" min="1" required>
                        <button type="submit" class="btn btn-primary w-100">Request Deposit</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endforeach
</div>

{{-- Deposit Panel --}}
<div id="depositPanel" style="display:none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
    background: #fff; padding: 20px; z-index: 1050; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.3); width: 350px;">
    <h5 class="mb-3">Submit Payment Details</h5>
    <form id="depositForm" action="#" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-2">
            <label>Transaction ID</label>
            <input type="text" name="transaction_id" class="form-control" required>
        </div>
        <div class="mb-2">
            <label>Sender Account</label>
            <input type="text" name="sender_account" class="form-control" required>
        </div>
        <div class="mb-2">
            <label>Payment Screenshot</label>
            <input type="file" name="photo" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100 mt-2">Submit Payment</button>
    </form>
    <button id="closeDepositPanel" class="btn btn-secondary w-100 mt-2">Close</button>
</div>

<div id="overlay" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%;
    background: rgba(0,0,0,0.5); z-index:1040;"></div>

<script>
    // Category tab switch
    document.querySelectorAll('.agentanduserbuyandsell-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.agentanduserbuyandsell-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const categoryId = this.getAttribute('data-tab');
            document.querySelectorAll('.buy-posts-category').forEach(cat => cat.style.display = 'none');
            document.getElementById('category-' + categoryId).style.display = 'block';
        });
    });

    // Close Deposit Panel
    const closeBtn = document.getElementById('closeDepositPanel');
    if(closeBtn){
        closeBtn.addEventListener('click', function(){
            document.getElementById('depositPanel').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        });
    }

    // Ajax Polling every 1 second
    setInterval(() => {
        fetch('{{ route("user.deposit.status") }}')
        .then(res => res.json())
        .then(data => {
            if(data.status === 'agent_confirmed' && document.getElementById('depositPanel').style.display === 'none') {
                document.getElementById('depositPanel').style.display = 'block';
                document.getElementById('overlay').style.display = 'block';
                document.getElementById('depositForm').action = '/user/deposit/submit/' + data.deposit_id;
            }
        });
    }, 1000); // 1 second
</script>

@endsection
