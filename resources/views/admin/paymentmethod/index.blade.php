@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3>Payment Methods</h3>

    <a href="{{ route('paymentmethod.create') }}" class="btn btn-primary mb-3">Add New Method</a>

    <!-- Search Input -->
    <div class="mb-3">
        <input type="text" id="methodSearch" class="form-control" placeholder="Search payment methods by name or status">
    </div>

    @if($methods->count() > 0)
    <table class="table table-bordered" id="methodsTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Method Name</th>
                <th>Method Number</th>
                <th>Photo</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($methods as $method)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $method->method_name }}</td>
                <td>{{ $method->method_number }}</td>
                <td>
                    @if($method->photo)
                        <img src="{{ asset('uploads/paymentmethod/'.$method->photo) }}" alt="Photo" width="50">
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    @if($method->status == 'active')
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('paymentmethod.edit', $method->id) }}" class="btn btn-warning btn-sm">Edit</a>

                    <form action="{{ route('paymentmethod.destroy', $method->id) }}" method="POST" style="display:inline-block;">
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
        <div class="alert alert-info">No payment methods found.</div>
    @endif
</div>

<!-- JS for Search -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('methodSearch');
    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('#methodsTable tbody tr');

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
