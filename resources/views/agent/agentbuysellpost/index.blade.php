@extends('agent.master')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3 fw-bold">Buy/Sell Posts</h4>

    <a href="{{ route('agentbuysellpost.create') }}" class="btn btn-primary mb-3">
        <i class="bi bi-plus-circle"></i> Create New Post
    </a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Category</th>
                <th>Photo</th>
                <th>Trade Limit</th>
                <th>Available Balance</th>
                <th>Duration</th>
                <th>Payment Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($posts as $post)
            <tr>
                <td>{{ $post->id }}</td>
                <td>{{ $post->category?->category_name ?? 'N/A' }}</td>
                <td>
                    @if($post->photo)
                        <img src="{{ asset($post->photo) }}" width="60" class="rounded">
                    @endif
                </td>
                <td>{{ $post->trade_limit }} - {{ $post->trade_limit_two }}</td>
                <td>{{ $post->available_balance }}</td>
                <td>{{ $post->duration }} min</td>
                <td>{{ $post->payment_name }}</td>
                <td>{{ ucfirst($post->status) }}</td>
                <td class="d-flex gap-1">
                    <a href="{{ route('agentbuysellpost.edit', $post->id) }}" class="btn btn-sm btn-success">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <form action="{{ route('agentbuysellpost.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">No posts found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
