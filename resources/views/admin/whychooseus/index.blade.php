@extends('admin.master')
@section('content')
<div class="container mt-4">
    <h2>Why Choose Us</h2>

    <div class="d-flex justify-content-between mb-3">
        <a href="{{ route('whychooseu.create') }}" class="btn btn-primary">Add New</a>
        <input type="text" id="searchInput" class="form-control w-25" placeholder="Search by title or description">
    </div>

    <table class="table table-bordered table-striped" id="whyChooseUsTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Icon</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="title">{{ $item->title }}</td>
                    <td><i class="{{ $item->icon }}"></i></td>
                    <td class="description">{{ $item->description }}</td>
                    <td>
                        <a href="{{ route('whychooseu.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('whychooseu.destroy', $item->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const table = document.getElementById('whyChooseUsTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();

            for (let i = 0; i < rows.length; i++) {
                const title = rows[i].querySelector('.title').textContent.toLowerCase();
                const description = rows[i].querySelector('.description').textContent.toLowerCase();

                if (title.includes(filter) || description.includes(filter)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    });
</script>
@endsection
