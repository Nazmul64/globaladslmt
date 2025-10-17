@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3>Referral Commission Setups</h3>

    <a href="{{ route('reffercommission.create') }}" class="btn btn-primary mb-3">Add New Level</a>

    <!-- Search Input -->
    <div class="mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="Search by level or percentage">
    </div>

    @if($setups->count() > 0)
    <table class="table table-bordered" id="setupTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Level</th>
                <th>Commission (%)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($setups as $setup)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $setup->reffer_level }}</td>
                <td>{{ $setup->commission_percentage }}</td>
                <td>
                    <a href="{{ route('reffercommission.edit', $setup->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('reffercommission.destroy', $setup->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <div class="alert alert-info">No referral commission setups found.</div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('#setupTable tbody tr');

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
