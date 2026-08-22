@extends('admin.master')
@section('content')

<div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4>Theme List</h4>
        <a href="{{ route('themechange.create') }}" class="btn btn-primary">Add New Theme</a>
    </div>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Color Code</th>
                <th>Preview</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($themes as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->color_code }}</td>
                <td>
                    <div style="width:40px; height:40px; background:{{ $item->color_code }}; border-radius:4px;"></div>
                </td>
                <td>
                    <a href="{{ route('themechange.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>

                    <form action="{{ route('themechange.destroy', $item->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
