@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3>All Agents</h3>

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
                <td>
                    @if($agent->status == 'pending')
                        <span class="badge bg-warning text-dark">Pending</span>
                    @elseif($agent->status == 'approved')
                        <span class="badge bg-success">Approved</span>
                    @else
                        <span class="badge bg-danger">Rejected</span>
                    @endif
                </td>
                <td>
                    @if($agent->status == 'pending')
                        <a href="{{ route('admin.agent.approve', $agent->id) }}" class="btn btn-success btn-sm">Approve</a>
                        <a href="{{ route('admin.agent.reject', $agent->id) }}" class="btn btn-danger btn-sm">Reject</a>
                    @endif
                    <a href="{{ route('agentcreate.edit', $agent->id) }}" class="btn btn-primary btn-sm">Edit</a>
                    <form action="{{ route('agentcreate.destroy', $agent->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this agent?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <div class="alert alert-info mt-3">
            No agents in the database.
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
