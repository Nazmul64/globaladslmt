@extends('admin.master')

@section('content')
<div class="container">
    <h3>Edit Notice</h3>

    <form action="{{ route('agentnotices.update', $notice->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Notice</label>
            <textarea name="notices" class="form-control" required>{{ $notice->notices }}</textarea>
        </div>

        <button class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
