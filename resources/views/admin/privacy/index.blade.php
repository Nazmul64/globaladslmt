@extends('admin.master')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Privacy Policies</h2>
        <a href="{{ route('privacy.create') }}" class="btn btn-primary">
            + Create New Policy
        </a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Description</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($policies as $policy)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $policy->title }}</td>
                <td>{{ Str::limit(strip_tags($policy->description), 80) }}</td>
                <td>{{ $policy->created_at->format('d M Y') }}</td>
                <td>
                    <a href="{{ route('privacy.edit', $policy->id) }}"
                       class="btn btn-sm btn-warning">Edit</a>

                    <form action="{{ route('privacy.destroy', $policy->id) }}"
                          method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure you want to delete this policy?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted">No privacy policies found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $policies->links() }}
    </div>

</div>
@endsection
