@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3 class="mb-4">Pending Deposits</h3>

    <!-- Search Input -->
    <div class="mb-3">
        <input type="text" id="depositSearch" class="form-control" placeholder="Search by transaction ID, sender, or amount">
    </div>

    @if($deposite_list->count() > 0)
    <table class="table table-bordered mt-3" id="depositsTable">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Amount</th>
                <th>Transaction ID</th>
                <th>Sender Account</th>
                <th>Photo</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deposite_list as $deposit)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $deposit->amount }}৳</td>
                <td>{{ $deposit->transaction_id }}</td>
                <td>{{ $deposit->sender_account }}</td>
                <td>
                    @if($deposit->photo)
                        <img src="{{ asset('uploads/agentdeposite/' . $deposit->photo) }}" alt="Deposit Photo" width="50" height="50" style="object-fit: cover;">
                    @else
                        <span class="text-muted">No Image</span>
                    @endif
                </td>
                <td>
                    <span class="badge
                        @if($deposit->status == 'pending') bg-warning text-dark
                        @elseif($deposit->status == 'approved') bg-success
                        @else bg-danger @endif">
                        {{ ucfirst($deposit->status) }}
                    </span>
                </td>
                <td>
                    @if($deposit->status == 'pending')
                        <form action="{{ route('admin.agentdeposit.approve', $deposit->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                        </form>
                        <form action="{{ route('admin.agentdeposit.reject', $deposit->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <div class="alert alert-info mt-3">
            No pending deposits found.
        </div>
    @endif
</div>

<!-- JS for Search -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('depositSearch');
    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('#depositsTable tbody tr');

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const match = Array.from(cells).some(cell =>
                cell.textContent.toLowerCase().includes(filter)
            );
            row.style.display = match ? '' : 'none';
        });
    });
});
</script>
@endsection
