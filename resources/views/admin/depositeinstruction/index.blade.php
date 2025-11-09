@extends('admin.master')

@section('content')
<div class="container mt-4">
    <h2>Deposit Instructions</h2>
    <a href="{{ route('depositeinstruction.create') }}" class="btn btn-primary mb-3">Add New</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Video URL</th>
                <th>Membership Title</th>
                <th>Deposit Title</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($instructions as $instruction)
                <tr>
                    <td>{{ $instruction->id }}</td>
                    <td>{{ $instruction->video_url }}</td>
                    <td>{{ $instruction->member_ship_instructions_title }}</td>
                    <td>{{ $instruction->deposite_instructions_title }}</td>
                    <td>
                        <a href="{{ route('depositeinstruction.edit', $instruction->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('depositeinstruction.destroy', $instruction->id) }}" method="POST" style="display:inline;">
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
