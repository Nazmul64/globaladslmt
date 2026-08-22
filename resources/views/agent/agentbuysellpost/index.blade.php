@extends('agent.master')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Buy/Sell Posts</h4>
        <a href="{{ route('agentbuysellpost.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Post
        </a>
    </div>

    @php
        $agent = auth()->user();
        $total_deposited = \App\Models\AgentDeposite::where('agent_id', $agent->id)->sum('amount');
        $locked_amount = $agent->locked_amount ?? 0;
        // ✅ Main Balance = Total Deposited - Locked Amount
        $main_balance = $total_deposited - $locked_amount;
        // ✅ Available for POST = শুধু Main Balance (Locked Amount ব্যবহার করা যাবে না)
        $available_balance = $main_balance;
    @endphp

    <!-- ✅ Balance Summary Card -->
    <div class="card mb-4 border-success">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <p class="text-muted small mb-1">Main Balance</p>
                    <h5 class="text-primary mb-0">${{ number_format($main_balance, 2) }}</h5>
                </div>
                @if($locked_amount > 0)
                <div class="col-md-3">
                    <p class="text-muted small mb-1"><i class="bi bi-lock-fill"></i> Locked Amount (Display Only)</p>
                    <h5 class="text-warning mb-0">${{ number_format($locked_amount, 2) }}</h5>
                </div>
                @endif
                <div class="col-md-3">
                    <p class="text-muted small mb-1">Available for Posts</p>
                    <h5 class="text-success mb-0">${{ number_format($available_balance, 2) }}</h5>
                </div>
                <div class="col-md-3">
                    <p class="text-muted small mb-1">Total Posts</p>
                    <h5 class="text-info mb-0">{{ $posts->count() }}</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- ✅ Posts Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th>Dollar Sign</th>
                            <th>Photos</th>
                            <th>Trade Limit</th>
                            <th>Rate</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                        <tr>
                            <td><span class="badge bg-secondary">#{{ $post->id }}</span></td>
                            <td>
                                <span class="fw-semibold">{{ $post->category?->category_name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $post->dollarsign?->dollarsigned ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @if($post->photo)
                                    @php
                                        $photos = is_array(json_decode($post->photo)) ? json_decode($post->photo) : [$post->photo];
                                    @endphp
                                    <div class="d-flex gap-1">
                                        @foreach($photos as $index => $photo)
                                            @if($index < 2)
                                                <img
                                                    src="{{ asset($photo) }}"
                                                    width="40"
                                                    height="40"
                                                    class="rounded border"
                                                    style="object-fit: cover; cursor: pointer;"
                                                    data-bs-toggle="tooltip"
                                                    title="Click to view">
                                            @endif
                                        @endforeach
                                        @if(count($photos) > 2)
                                            <span class="badge bg-dark align-self-center">+{{ count($photos) - 2 }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">No photos</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">
                                    <span class="text-muted">Min:</span> <strong>${{ number_format($post->trade_limit, 2) }}</strong><br>
                                    <span class="text-muted">Max:</span> <strong>${{ number_format($post->trade_limit_two, 2) }}</strong>
                                </div>
                            </td>
                            <td>
                                <span class="fw-semibold text-success">${{ number_format($post->rate_balance, 2) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $post->payment_name }}</span>
                            </td>
                            <td>
                                @php
                                    $statusConfig = match($post->status) {
                                        'approved' => ['class' => 'bg-success', 'icon' => 'check-circle'],
                                        'pending' => ['class' => 'bg-warning text-dark', 'icon' => 'clock'],
                                        'rejected' => ['class' => 'bg-danger', 'icon' => 'x-circle'],
                                        default => ['class' => 'bg-secondary', 'icon' => 'question-circle']
                                    };
                                @endphp
                                <span class="badge {{ $statusConfig['class'] }}">
                                    <i class="bi bi-{{ $statusConfig['icon'] }}"></i>
                                    {{ ucfirst($post->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a
                                        href="{{ route('agentbuysellpost.edit', $post->id) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="tooltip"
                                        title="Edit Post">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form
                                        action="{{ route('agentbuysellpost.destroy', $post->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this post?');"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="tooltip"
                                            title="Delete Post">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <p class="mb-0">No posts found. Create your first post to get started!</p>
                                    <a href="{{ route('agentbuysellpost.create') }}" class="btn btn-sm btn-primary mt-3">
                                        <i class="bi bi-plus-circle"></i> Create Post
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ✅ Info Note -->
    <div class="alert alert-info mt-3" role="alert">
        <i class="bi bi-info-circle"></i>
        <strong>Important Balance Rules:</strong>
        <ul class="mb-0 mt-2">
            <li><strong>Deposit Posts:</strong> Can ONLY use Main Balance (${{ number_format($main_balance, 2) }})</li>
            <li><strong>Withdraw Posts:</strong> Can use Main Balance + Locked Amount = Total ${{ number_format($main_balance + $locked_amount, 2) }}</li>
            <li><strong>Other Posts:</strong> Can use Main Balance only</li>
            @if($locked_amount > 0)
            <li class="text-warning"><strong>Note:</strong> Locked Amount (${{ number_format($locked_amount, 2) }}) cannot be withdrawn</li>
            @endif
        </ul>
    </div>
</div>

<!-- ✅ Enable Bootstrap Tooltips -->
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
@endsection
