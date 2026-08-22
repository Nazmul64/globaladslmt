@extends('admin.master')

@section('content')
<div class="container mt-4">

    <h4 class="mb-3">Agent Buy/Sell Posts</h4>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Agent</th>
                <th>Category</th>
                <th>Rate</th>
                <th>Trade Limit</th>
                <th>Payment</th>
                <th>Image</th>
                <th>Status</th>
                <th>Created</th>
                <th width="160">Action</th>
            </tr>
        </thead>

        <tbody>
        @forelse($posts as $key => $post)
            <tr>
                <td>{{ $key + 1 }}</td>

                <td>
                    {{ $post->agent->name ?? 'N/A' }}
                </td>

                <td>
                   {{ $post->dollarsign?->dollarsigned ?? 'N/A' }}
                </td>

                <td>
                    {{ $post->rate_balance }}
                </td>

                <td>
                    {{ $post->trade_limit }} - {{ $post->trade_limit_two }}
                </td>

                <td>
                    {{ $post->payment_name }}
                </td>

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

                <td>
                    @if($post->status)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </td>

                <td>
                    {{ $post->created_at->format('d M Y') }}
                </td>

                <td>
                    <a href="{{ route('agent.agentposts.edit', $post->id) }}"
                       class="btn btn-sm btn-primary mb-1 w-100">
                        Edit
                    </a>

                    <form action="{{ route('agent.agentposts.delete', $post->id) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this post?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger w-100">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center text-muted">
                    No agent posts found
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
