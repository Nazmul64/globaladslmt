@extends('admin.master')
@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Supports List</h4>
        <a href="{{ route('support.create') }}" class="btn btn-primary">Add New Support</a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>URL Link</th>
                    <th>Icon</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($supports as $index => $support)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $support->name }}</td>
                    <td><a href="{{ $support->url_link }}" target="_blank">{{ $support->url_link }}</a></td>
                    <td><i class="{{ $support->icon }}"></i> {{ $support->icon }}</td>
                    <td>
                        <a href="{{ route('support.edit', $support->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('support.destroy', $support->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Are you sure to delete this support?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No supports found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
