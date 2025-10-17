@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3>Rejected Agents</h3>

    <!-- Search Input -->
    <div class="mb-3">
        <input type="text" id="agentSearch" class="form-control" placeholder="Search rejected agents by name, email or country">
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
            </tr>
        </thead>
        <tbody>
            @foreach($agents as $agent)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $agent->name }}</td>
                <td>{{ $agent->email }}</td>
                <td>{{ $agent->country }}</td>
                <td><a class="btn btn-danger">{{ ucfirst($agent->status) }}</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <div class="alert alert-info mt-3">
            No rejected agents in the database.
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
