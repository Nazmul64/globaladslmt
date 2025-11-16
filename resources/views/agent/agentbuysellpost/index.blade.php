@extends('agent.master')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3 fw-bold">Buy/Sell Posts</h4>

    <a href="{{ route('agentbuysellpost.create') }}" class="btn btn-primary mb-3">
        <i class="bi bi-plus-circle"></i> Create New Post
    </a>

    <table class="table table-bordered table-striped align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Category</th>
                <th>Dollar Signed</th>
                <th>Photo</th>
                <th>Trade Limit</th>
                <th>Balance Rate</th>
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
                <td>{{ $post->dollarsign?->dollarsigned ?? 'N/A' }}</td>
                <td>
                    @if($post->photo)
                        @php
                            $photos = is_array(json_decode($post->photo)) ? json_decode($post->photo) : [$post->photo];
                        @endphp
                        @foreach($photos as $photo)
                            <img src="{{ asset($photo) }}" width="50" height="50" class="rounded me-1 mb-1">
                        @endforeach
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $post->trade_limit }} - {{ $post->trade_limit_two }}</td>
                <td>{{ $post->rate_balance }}</td>
                <td>{{ $post->payment_name }}</td>
                <td>
                    @php
                        $badgeClass = match($post->status) {
                            'approved' => 'bg-success',
                            'pending' => 'bg-warning text-dark',
                            'rejected' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">
                        {{ ucfirst($post->status) }}
                    </span>
                </td>
                <td class="d-flex gap-1">
                    <a href="{{ route('agentbuysellpost.edit', $post->id) }}" class="btn btn-sm btn-success">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <form action="{{ route('agentbuysellpost.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
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
