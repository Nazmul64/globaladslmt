@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3>Notice List</h3>

    <a href="{{ route('worknotice.create') }}" class="btn btn-primary mb-3">Add New Notice</a>

    <!-- Search -->
    <div class="mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="Search notices by title">
    </div>

    @if($notices->count() > 0)
    <table class="table table-bordered" id="noticeTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Description</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notices as $notice)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $notice->title }}</td>
                <td>{{ Str::limit($notice->description, 60) }}</td>
                <td>{{ $notice->created_at->format('d M, Y') }}</td>
                <td>
                    <a href="{{ route('worknotice.edit', $notice->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('worknotice.destroy', $notice->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure to delete this notice?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <div class="alert alert-info">No notices found.</div>
    @endif
</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#noticeTable tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>
@endsection
