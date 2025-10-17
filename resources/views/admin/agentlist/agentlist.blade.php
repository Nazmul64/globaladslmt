@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3>Pending Agents Approval</h3>

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
                <th>Country</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($agents as $agent)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $agent->name }}</td>
                <td>{{ $agent->email }}</td>
                <td>{{ $agent->country }}</td>
                <td>{{ ucfirst($agent->status) }}</td>
                <td>
                    <a href="{{ route('admin.agent.approve', $agent->id) }}" class="btn btn-success btn-sm">Approve</a>
                    <a href="{{ route('admin.agent.reject', $agent->id) }}" class="btn btn-danger btn-sm">Reject</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="alert alert-warning mt-3">
        Agent not found in our database.
    </div>
    @endif
</div>

<!-- JS for Search -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('agentSearch');
        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('#agentsTable tbody tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const match = Array.from(cells).some(cell =>
                    cell.textContent.toLowerCase().includes(filter)
                );
                row.style.display = match ? '' : 'none';
                if(match) visibleCount++;
            });

            // Show message if no rows match search
            const noDataMsg = document.getElementById('noDataMessage');
            if (!noDataMsg) {
                const msg = document.createElement('div');
                msg.id = 'noDataMessage';
                msg.className = 'alert alert-warning mt-3';
                msg.innerText = 'No agents match your search.';
                document.querySelector('.container').appendChild(msg);
            }
            document.getElementById('noDataMessage').style.display = visibleCount === 0 ? 'block' : 'none';
        });
    });
</script>
@endsection
