@extends('admin.master')

@section('content')
<div class="container">
    <h1>Widthraw Instructions</h1>
    <a href="{{ route('widthrawInstruction.create') }}" class="btn btn-primary mb-3">Add Instruction</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Instruction</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($instructions as $instruction)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $instruction->instructions }}</td>
                    <td>
                        <a href="{{ route('widthrawInstruction.edit', $instruction->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('widthrawInstruction.destroy', $instruction->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No widthrawInstruction found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
