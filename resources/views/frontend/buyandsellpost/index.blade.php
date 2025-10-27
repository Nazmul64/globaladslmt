@extends('frontend.master')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
.modal-backdrop.show {
    background-color: transparent !important;
    z-index: 1040 !important;
}
.modal {
    z-index: 1050 !important;
}
</style>

<div class="container mt-3">

    {{-- Category Tabs --}}
    <div class="agentanduserbuyandsell-header mb-3 d-flex flex-wrap gap-2">
        @foreach ($categories as $item)
            <div class="agentanduserbuyandsell-tab px-3 py-2 rounded {{ $loop->first ? 'bg-primary text-white' : 'bg-light' }}"
                 data-tab="{{ $item->id }}" style="cursor:pointer;">
                 {{ $item->category_name }}
            </div>
        @endforeach
    </div>

    {{-- Posts by Category --}}
    <div class="agentanduserbuyandsell-buyPosts p-3">
        @foreach ($categories as $category)
            <div class="buy-posts-category" id="category-{{ $category->id }}" style="{{ $loop->first ? '' : 'display:none;' }}">
                @foreach ($all_agentbuysellpost->where('category_id', $category->id) as $post)
                    <div class="agentanduserbuyandsell-trader-card mb-3 p-3 border rounded shadow-sm">
                        {{-- Trader Info --}}
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:40px;height:40px;">
                                {{ strtoupper(substr($post->user->name ?? 'A', 0, 1)) }}
                            </div>
                            <div class="ms-2">
                                <strong>{{ $post->user->name ?? 'Unknown' }}</strong>
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                        </div>

                        <div class="mb-2"><strong>{{ $post->trade_limit }}</strong> <span class="text-muted">USDT</span></div>
                        <div class="text-muted mb-1">Limit: {{ $post->trade_limit }} - {{ $post->trade_limit_two }}</div>
                        <div class="text-muted mb-2">Available: <strong>{{ $post->available_balance }} USDT</strong></div>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="fas fa-clock text-success"></i>
                            <span>{{ $post->duration }} min</span>
                            <span class="badge bg-success">{{ $post->payment_name }}</span>
                        </div>

                        {{-- Deposit Request Form --}}
                        <form action="{{ route('userwidhraw.request') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="deposit">
                            <input type="hidden" name="agent_id" value="{{ $post->user->id }}">
                            <input type="number" name="amount" class="form-control mb-2" placeholder="Enter amount" min="1" value="{{ old('amount') }}" required>
                            <button type="submit" class="btn btn-primary w-100">Request Deposit</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>

{{-- Bootstrap Modal --}}
<div class="modal fade" id="depositModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="depositForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="modal_agent_id" name="agent_id">
        <div class="modal-header">
          <h5 class="modal-title">Submit Payment Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
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
                <input type="file" name="photo" class="form-control" accept="image/*" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success w-100">Submit Payment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){

    const depositForm = document.getElementById('depositForm');
    let depositId = null;
    let modalOpened = false;

    // Category switch
    document.querySelectorAll('.agentanduserbuyandsell-tab').forEach(tab => {
        tab.addEventListener('click', function(){
            document.querySelectorAll('.agentanduserbuyandsell-tab').forEach(t => t.classList.remove('bg-primary','text-white'));
            this.classList.add('bg-primary','text-white');
            const id = this.dataset.tab;
            document.querySelectorAll('.buy-posts-category').forEach(c => c.style.display='none');
            document.getElementById('category-' + id).style.display='block';
        });
    });

    // Polling agent confirmation
    setInterval(() => {
        fetch('{{ route("user.deposit.status") }}', { headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')} })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'agent_confirmed' && !modalOpened){
                depositId = data.deposit_id;
                modalOpened = true;
                const modal = new bootstrap.Modal(document.getElementById('depositModal'));
                modal.show();
            }
        })
        .catch(err => console.error('Polling error:', err));
    }, 1000);

    // AJAX submit
    depositForm.addEventListener('submit', function(e){
        e.preventDefault();
        if(!depositId){
            alert('Please wait for agent confirmation.');
            return;
        }

        let formData = new FormData(depositForm);
        fetch(`/user/deposit/submit/${depositId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(res => res.json().catch(() => { throw new Error("Invalid JSON response from server") }))
        .then(data => {
            if(data.success){
                alert('Payment submitted successfully!');
                depositForm.reset();
                depositId = null;
                modalOpened = false;
                const modalInstance = bootstrap.Modal.getInstance(document.getElementById('depositModal'));
                modalInstance.hide();
            } else {
                alert('Error: ' + (data.message || 'Something went wrong!'));
            }
        })
        .catch(err => alert('AJAX Error: ' + err.message));
    });

});
</script>

@endsection
