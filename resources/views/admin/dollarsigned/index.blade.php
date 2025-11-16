@extends('admin.master')

@section('content')
<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Dollar & Taka Signed List</h3>
        <a href="{{ route('dollarsiged.create') }}" class="btn btn-primary">Add New</a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Dollar Signed</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tollar_signed as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->dollarsigned }}</td>
                <td>{{ $item->created_at->format('d M Y') }}</td>
                <td>
                    <a href="{{ route('dollarsiged.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('dollarsiged.destroy', $item->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
