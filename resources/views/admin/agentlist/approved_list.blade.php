@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3>Approved Agents</h3>

    <!-- Search Input -->
    <div class="mb-3">
        <input type="text" id="agentSearch" class="form-control" placeholder="Search agents by name, email or country">
    </div>

    @if($agents->count() > 0)

    <table class="table table-bordered mt-3" id="agentsTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Country</th>
                <th>Status</th>
                <th>View Agent</th>
            </tr>
        </thead>

        <tbody>
            @foreach($agents as $agent)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $agent->name }}</td>
                <td>{{ $agent->email }}</td>
                <td>{{ $agent->mobile }}</td>
                <td>{{ $agent->country }}</td>
                <td>
                    <span class="btn btn-success btn-sm">{{ ucfirst($agent->status) }}</span>
                </td>

                <td>
                    <a href="{{ route('admin.impersonate', $agent->id) }}"
                       class="btn btn-sm btn-info">
                       👁️ View User
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- No data found message (hidden by default) -->
    <div id="noDataMessage" class="alert alert-warning mt-3" style="display:none;">
        No agents match your search.
    </div>

    @else
    <div class="alert alert-warning mt-3">
        No approved agents found in the database.
    </div>
    @endif
</div>

<!-- JS for Search -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('agentSearch');
    const tableRows = document.querySelectorAll('#agentsTable tbody tr');
    const noDataMessage = document.getElementById('noDataMessage');

    searchInput.addEventListener('keyup', function () {
        const filter = this.value.toLowerCase();
        let visibleCount = 0;

        tableRows.forEach(row => {
            const match = Array.from(row.querySelectorAll('td')).some(cell =>
                cell.textContent.toLowerCase().includes(filter)
            );

            row.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        // Show/Hide "No Data Found" Message
        noDataMessage.style.display = visibleCount === 0 ? 'block' : 'none';
    });
});
</script>

@endsection
