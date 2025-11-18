@extends('agent.master')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3 text-center fw-bold">💵 User Deposit Requests</h4>

    {{-- Search & Filter --}}
    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <input type="text" id="searchInput" class="form-control w-50" placeholder="Search by user name or ID...">
        <div class="d-flex gap-2">
            <a href="{{ route('agent.deposit.requests', ['rejected' => 1]) }}" class="btn btn-danger">
                View Rejected Requests
            </a>
            <a href="{{ route('agent.deposit.requests') }}" class="btn btn-primary">
                View All Requests
            </a>
        </div>
    </div>

    <div class="table-responsive shadow-sm">
        <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>User Name</th>
                    <th>Amount ($)</th>
                    <th>Photo</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="searchTable">
                @forelse($requests as $req)
                <tr>
                    <td>{{ $req->user->id ?? 'N/A' }}</td>
                    <td class="fw-semibold">{{ $req->user->name ?? 'N/A' }}</td>
                    <td>${{ number_format($req->amount, 2) }}</td>
                    <td>
                        @if($req->photo)
                            <a href="{{ asset($req->photo) }}" target="_blank">
                                <img src="{{ asset($req->photo) }}" width="50" height="50"
                                     class="rounded border" style="object-fit: cover;">
                            </a>
                        @else
                            <span class="text-muted">No Image</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $badgeColors = [
                                'pending' => 'warning',
                                'agent_confirmed' => 'info',
                                'user_submitted' => 'primary',
                                'completed' => 'success',
                                'rejected' => 'danger',
                            ];
                        @endphp
                        <span class="badge bg-{{ $badgeColors[$req->status] ?? 'secondary' }}">
                            {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                        </span>
                    </td>
                    <td>
                        @if($req->status === 'pending')
                            <div class="d-flex gap-1">
                                <form action="{{ route('agent.deposit.accept', $req->id) }}" method="POST" class="w-100">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm w-100">✔ Accept</button>
                                </form>
                                <form action="{{ route('agent.deposit.rejected', $req->id) }}" method="POST" class="w-100">
                                    @csrf
                                    <button onclick="return confirm('Are you sure to reject this request?')" type="submit" class="btn btn-danger btn-sm w-100">✖ Reject</button>
                                </form>
                            </div>
                        @elseif($req->status === 'user_submitted')
                            <button type="button" class="btn btn-primary btn-sm w-100"
                                    data-bs-toggle="modal" data-bs-target="#paymentModal{{ $req->id }}">
                                👁 View & Confirm
                            </button>

                            {{-- Modal --}}
                            <div class="modal fade" id="paymentModal{{ $req->id }}" tabindex="-1"
                                 aria-labelledby="paymentModalLabel{{ $req->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title" id="paymentModalLabel{{ $req->id }}">
                                                Payment Details - {{ $req->user->name ?? 'N/A' }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <p><strong>Transaction ID:</strong> {{ $req->transaction_id ?? 'N/A' }}</p>
                                            <p><strong>Sender Account:</strong> {{ $req->sender_account ?? 'N/A' }}</p>
                                            @if($req->photo)
                                                <a href="{{ asset($req->photo) }}" target="_blank">
                                                    <img src="{{ asset($req->photo) }}" class="img-fluid rounded border shadow-sm" alt="screenshot">
                                                </a>
                                            @else
                                                <p class="text-muted">No screenshot uploaded.</p>
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <form action="{{ route('agent.deposit.final', $req->id) }}" method="POST" class="w-100">
                                                @csrf
                                                <button type="submit" class="btn btn-success w-100">💰 Confirm Deposit</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($req->status === 'completed')
                            <span class="text-success fw-bold">Completed</span>
                        @elseif($req->status === 'rejected')
                            <span class="text-danger fw-bold">Rejected</span>
                        @else
                            <span class="text-muted">No Action</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-muted">No deposit requests found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $requests->links('pagination::bootstrap-5') }}
    </div>
</div>

{{-- Live Search --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const rows = document.querySelectorAll('#searchTable tr');

    searchInput.addEventListener('keyup', function () {
        const q = this.value.trim().toLowerCase();
        rows.forEach(row => {
            const id = (row.cells[0]?.textContent || '').trim().toLowerCase();
            const name = (row.cells[1]?.textContent || '').trim().toLowerCase();
            row.style.display = (id.includes(q) || name.includes(q)) ? '' : 'none';
        });
    });
});
</script>
@endsection
