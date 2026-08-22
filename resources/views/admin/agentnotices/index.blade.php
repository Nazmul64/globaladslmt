@extends('admin.master')

@section('content')
<div class="container">
    <h3>Agent Notices</h3>

    <a href="{{ route('agentnotices.create') }}" class="btn btn-primary mb-3">Add Notice</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Notice</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notices as $key => $notice)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $notice->notices }}</td>
                <td>
                    <a href="{{ route('agentnotices.edit', $notice->id) }}" class="btn btn-sm btn-warning">Edit</a>

                    <form action="{{ route('agentnotices.destroy', $notice->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
