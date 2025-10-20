@extends('admin.master')

@section('content')
<div class="container mt-4">
    <h3>Package Buy List</h3>

    <!-- Search Input -->
    <div class="mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="Search by user, package or status...">
    </div>

    <table class="table table-bordered table-striped mt-3" id="packageBuyTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Package</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Purchase Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($packageBuys as $buy)
            <tr>
                <td>{{ $buy->id }}</td>
                <td>{{ $buy->user->name ?? 'N/A' }}</td>
                <td>{{ $buy->package->package_name ?? 'N/A' }}</td>
                <td>{{ round($buy->amount) }} BDT</td>
                <td>{{ ucfirst($buy->status) }}</td>
                <td>{{ $buy->created_at->format('d M Y, h:i A') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- JS for client-side search -->
<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#packageBuyTable tbody tr');

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>
@endsection
