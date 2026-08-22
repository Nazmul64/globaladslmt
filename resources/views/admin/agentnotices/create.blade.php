@extends('admin.master')

@section('content')
<div class="container">
    <h3>Create Notice</h3>

    <form action="{{ route('agentnotices.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Notice</label>
            <textarea name="notices" class="form-control" required></textarea>
        </div>

        <button class="btn btn-success">Save</button>
    </form>
</div>
@endsection
