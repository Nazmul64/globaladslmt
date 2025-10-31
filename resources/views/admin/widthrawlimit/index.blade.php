@extends('admin.master')

@section('content')
<div class="container mt-4">
    <h2>Withdraw Limits</h2>
    <a href="{{ route('widthrawlimit.create') }}" class="btn btn-primary mb-3">Add New Limit</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Max Withdraw Limit</th>
                <th>Min Withdraw Limit</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($limits as $limit)
            <tr>
                <td>{{ $limit->id }}</td>
                <td>{{ $limit->max_withdraw_limit }}</td>
                <td>{{ $limit->min_withdraw_limit }}</td>
                <td>
                    <a href="{{ route('widthrawlimit.edit', $limit->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('widthrawlimit.destroy', $limit->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
