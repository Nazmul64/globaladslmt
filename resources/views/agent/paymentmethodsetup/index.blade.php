@extends('agent.master')
@section('content')

<div class="container mt-5">
    <h3>Payment Methods</h3>

    <a href="{{ route('paymentsetup.create') }}" class="btn btn-primary mb-3">Add New Method</a>

    <input type="text" id="methodSearch" class="form-control mb-3" placeholder="Search by name or status">

    @if($methods->count())
        <table class="table table-bordered" id="methodsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Number</th>
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
                            <img src="{{ asset('uploads/agentpaymentsetup/'.$method->photo) }}" width="50">
                        @else N/A @endif
                    </td>
                    <td><span class="badge bg-{{ $method->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($method->status) }}</span></td>
                    <td>
                        <a href="{{ route('paymentsetup.edit', $method->id) }}" class="btn btn-warning btn-sm">Edit</a>

                        <form action="{{ route('paymentsetup.destroy', $method->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button onclick="return confirm('Delete permanently?')" class="btn btn-danger btn-sm">Delete</button>
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

<script>
document.getElementById('methodSearch').addEventListener('keyup', function () {
    const value = this.value.toLowerCase();
    document.querySelectorAll('#methodsTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
    });
});
</script>

@endsection
