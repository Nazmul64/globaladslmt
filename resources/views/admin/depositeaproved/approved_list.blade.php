@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3 class="mb-4">Approved Deposits</h3>

    <!-- Search Input -->
    <div class="mb-3">
        <input type="text" id="approvedDepositSearch" class="form-control" placeholder="Search by transaction ID, sender, or amount">
    </div>

    @if($approved->count() > 0)
    <table class="table table-bordered mt-3" id="approvedDepositsTable">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Amount</th>
                <th>Transaction ID</th>
                <th>Sender Account</th>
                <th>Photo</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($approved as $deposit)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $deposit->amount }}৳</td>
                <td>{{ $deposit->transaction_id }}</td>
                <td>{{ $deposit->sender_account }}</td>
                <td>
                    @if($deposit->photo)
                        <img src="{{ asset('uploads/deposits/' . $deposit->photo) }}" alt="Deposit Photo" width="50" height="50" style="object-fit: cover;">
                    @else
                        <span class="text-muted">No Image</span>
                    @endif
                </td>
                <td>
                    <span class="badge bg-success">{{ ucfirst($deposit->status) }}</span>
                </td>
                <td>{{ $deposit->created_at->format('d-m-Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <div class="alert alert-info mt-3">
            No approved deposits found.
        </div>
    @endif
</div>

<!-- JS for Search -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('approvedDepositSearch');
    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('#approvedDepositsTable tbody tr');

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
