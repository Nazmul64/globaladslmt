@extends('frontend.master')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
/* Modal layering & clickable backdrop */
.modal-backdrop.show { background-color: transparent !important; z-index: 1040 !important; }
.modal { z-index: 1050 !important; }
.agentanduserbuyandsell-tab { cursor: pointer; }
</style>

<div class="container mt-3">

    {{-- Deposit / Withdraw Tabs --}}
    <div class="d-flex gap-2 mb-3">
        <div id="depositTab" class="px-3 py-2 rounded bg-primary text-white">Deposit</div>
        <div id="withdrawTab" class="px-3 py-2 rounded bg-light">Withdraw</div>
    </div>

    {{-- Buy/Sell Posts by Category --}}
    <div class="agentanduserbuyandsell-buyPosts p-3">
        @foreach ($categories as $category)
            <div class="buy-posts-category" id="category-{{ $category->id }}" style="{{ $loop->first ? '' : 'display:none;' }}">
                @foreach ($all_agentbuysellpost->where('category_id', $category->id) as $post)
                    <div class="agentanduserbuyandsell-trader-card mb-3 p-3 border rounded shadow-sm">

                        {{-- Agent Info --}}
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
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

                        {{-- Deposit Form --}}
                        <form action="{{ route('user.deposit.request') }}" method="POST" class="deposit-form mb-2" style="display:block;">
                            @csrf
                            <input type="hidden" name="type" value="deposit">
                            <input type="hidden" name="agent_id" value="{{ $post->user->id }}">
                            <input type="number" name="amount" class="form-control mb-2" placeholder="Enter amount to deposit" min="1" required>
                            <button type="submit" class="btn btn-primary w-100">Request Deposit</button>
                        </form>

                        {{-- Withdraw Form --}}
                        <form action="{{ route('user.withdraw.request') }}" method="POST" class="withdraw-form" style="display:none;">
                            @csrf
                            <input type="hidden" name="type" value="withdraw">
                            <input type="hidden" name="agent_id" value="{{ $post->user->id }}">
                            <input type="number" name="amount" class="form-control mb-2" placeholder="Enter amount to withdraw" min="1" required>
                            <button type="submit" class="btn btn-success w-100">Request Withdraw</button>
                        </form>

                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>

{{-- Deposit Modal --}}
<div class="modal fade" id="depositModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="depositForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="modal_deposit_id" name="deposit_id">
        <div class="modal-header">
          <h5 class="modal-title">Submit Deposit Confirmation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                <label>Screenshot</label>
                <input type="file" name="photo" class="form-control" accept="image/*" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary w-100">Confirm Deposit</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Withdraw Modal (Existing, kept unchanged) --}}
<div class="modal fade" id="withdrawModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="withdrawForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="modal_withdraw_id" name="withdraw_id">
        <div class="modal-header">
          <h5 class="modal-title">Submit Withdraw Confirmation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-2">
                <label>Transaction ID</label>
                <input type="text" name="transaction_id" class="form-control" required>
            </div>
            <div class="mb-2">
                <label>Receiver Account</label>
                <input type="text" name="receiver_account" class="form-control" required>
            </div>
            <div class="mb-2">
                <label>Screenshot (Optional)</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success w-100">Confirm Withdraw</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ✅ NEW: Withdraw Release Modal --}}
<div class="modal fade" id="withdrawReleaseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content text-center">
      <div class="modal-header">
        <h5 class="modal-title w-100">Withdraw Release Confirmation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="fw-semibold text-muted mb-3">Your withdraw request has been confirmed by the agent.</p>
        <p>Click the button below to release your funds.</p>
      </div>
      <div class="modal-footer">
        <button id="releaseWithdrawBtn" class="btn btn-success w-100">Release Withdraw</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){

    const depositTab = document.getElementById('depositTab');
    const withdrawTab = document.getElementById('withdrawTab');

    // Deposit / Withdraw tab toggle
    depositTab.addEventListener('click', () => toggleForms('deposit'));
    withdrawTab.addEventListener('click', () => toggleForms('withdraw'));

    function toggleForms(type){
        document.querySelectorAll('.agentanduserbuyandsell-trader-card').forEach(card=>{
            card.querySelector('.deposit-form').style.display = type==='deposit' ? 'block':'none';
            card.querySelector('.withdraw-form').style.display = type==='withdraw' ? 'block':'none';
        });
        if(type==='deposit'){
            depositTab.classList.add('bg-primary','text-white'); depositTab.classList.remove('bg-light');
            withdrawTab.classList.add('bg-light'); withdrawTab.classList.remove('bg-primary','text-white');
        } else {
            withdrawTab.classList.add('bg-primary','text-white'); withdrawTab.classList.remove('bg-light');
            depositTab.classList.add('bg-light'); depositTab.classList.remove('bg-primary','text-white');
        }
    }

    // Polling
    let depositId = null;
    let withdrawId = null;
    let depositModalOpened = false;
    let withdrawModalOpened = false;

    setInterval(() => pollStatus('deposit'), 2000);
    setInterval(() => pollStatus('withdraw'), 2000);

    function pollStatus(type){
        const route = type==='deposit' ? '{{ route("user.deposit.status") }}' : '{{ route("user.withdraw.status") }}';
        fetch(route, {headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')}})
        .then(res=>res.json())
        .then(data=>{
            if(data.status==='agent_confirmed'){
                if(type==='deposit' && !depositModalOpened){
                    depositId = data.deposit_id;
                    depositModalOpened=true;
                    new bootstrap.Modal(document.getElementById('depositModal')).show();
                }
                if(type==='withdraw' && !withdrawModalOpened){
                    withdrawId = data.withdraw_id;
                    withdrawModalOpened=true;
                    new bootstrap.Modal(document.getElementById('withdrawReleaseModal')).show();
                }
            }
        }).catch(err=>console.error(err));
    }

    // Deposit submission
    document.getElementById('depositForm').addEventListener('submit', function(e){
        e.preventDefault();
        if(!depositId){ alert('Wait for agent confirmation'); return; }
        submitForm(this, `/user/deposit/submit/${depositId}`, 'deposit');
    });

    // Withdraw Release
    document.getElementById('releaseWithdrawBtn').addEventListener('click', function(){
        if(!withdrawId){ alert('Wait for agent confirmation'); return; }
        fetch(`/user/withdraw/submit/${withdrawId}`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')}
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                alert('Withdraw released successfully');
                withdrawId=null;
                withdrawModalOpened=false;
                bootstrap.Modal.getInstance(document.getElementById('withdrawReleaseModal')).hide();
            } else {
                alert('Error: '+(data.message||'Something went wrong'));
            }
        }).catch(err=>alert('AJAX error: '+err.message));
    });

    function submitForm(form, url, type){
        let formData = new FormData(form);
        fetch(url, {method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')}, body:formData})
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                alert(type==='deposit' ? 'Deposit confirmed successfully' : 'Withdraw confirmed successfully');
                form.reset();
                if(type==='deposit'){
                    depositId=null; depositModalOpened=false; bootstrap.Modal.getInstance(document.getElementById('depositModal')).hide();
                }
            } else alert('Error: '+(data.message||'Something went wrong'));
        }).catch(err=>alert('AJAX error: '+err.message));
    }

});
</script>

@endsection
