@extends('admin.master')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">⏳ Pending Agent Deposits</h4>

    <!-- Search Input -->
    <div class="mb-3">
        <input type="text" id="depositSearch" class="form-control" placeholder="Search deposits...">
    </div>

    @if($agent_deposite->isEmpty())
        <div class="alert alert-info text-center">
            No pending deposits found.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Mobile</th>
                        <th>Method Name</th>
                        <th>Agent Name</th>
                        <th>Email</th>
                        <th>Amount ($)</th>
                        <th>Sender Account</th>
                        <th>Transaction ID</th>
                        <th>Screenshot</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agent_deposite as $index => $deposit)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $deposit->agentname->mobile ?? 'N/A' }}</td>
                            <td>{{ $deposit->paymentMethodname->method_name ?? 'N/A' }}</td>
                            <td>{{ $deposit->agentname->name ?? 'N/A' }}</td>
                            <td>{{ $deposit->agentname->email ?? 'N/A' }}</td>
                            <td><strong>{{ number_format($deposit->amount, 2) }}</strong></td>
                            <td>{{ $deposit->sender_account ?? 'N/A' }}</td>
                            <td>{{ $deposit->transaction_id ?? 'N/A' }}</td>
                            <td>
                                @if($deposit->photo)
                                    <img src="{{ asset('uploads/agentdeposite/' . $deposit->photo) }}"
                                         alt="Deposit Photo"
                                         width="50" height="50"
                                         style="object-fit: cover; border-radius: 5px;">
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusClass = match($deposit->status) {
                                        'pending' => 'bg-warning text-dark',
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        default => 'bg-secondary text-white',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ ucfirst($deposit->status) }}</span>
                            </td>
                            <td>{{ $deposit->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                <div class="d-flex gap-2 justify-content-center">
                                    @if($deposit->status === 'pending')
                                        <form action="{{ route('admin.agentdeposit.approve', $deposit->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                        <form action="{{ route('admin.agentdeposit.reject', $deposit->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                        </form>
                                    @elseif($deposit->status === 'approved')
                                        <span class="text-success fw-bold">✔ Approved</span>
                                    @else
                                        <span class="text-danger fw-bold">✘ Rejected</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- JS: Live Search -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('depositSearch');
    if (!searchInput) return;

    searchInput.addEventListener('keyup', function () {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('table tbody tr');

        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
        });
    });
});
</script>
@endsection
