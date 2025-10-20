@extends('admin.master')
@section('content')
<div class="container mt-4">
    <h3>Stepguide List</h3>
    <a href="{{ route('stepguide.create') }}" class="btn btn-primary mb-3">
        <i class="fa-solid fa-plus"></i> Add New Stepguide
    </a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Description</th>
                <th>Icon</th>
                <th>Serial Number</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stepguides as $key => $step)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $step->title }}</td>
                <td>{{ $step->description }}</td>
                <td><i class="{{ $step->icon }}"></i> {{ $step->icon }}</td>
                <td>{{ $step->serial_number }}</td>
                <td class="d-flex">
                    <a href="{{ route('stepguide.edit', $step->id) }}" class="btn btn-sm btn-warning x-2 me-2">
                        <i class="fa-solid fa-edit"></i>
                    </a>
                    <form action="{{ route('stepguide.destroy', $step->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger x-2 me-2" onclick="return confirm('Are you sure?')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
