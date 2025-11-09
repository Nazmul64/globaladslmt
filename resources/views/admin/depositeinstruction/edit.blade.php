@extends('admin.master')

@section('content')
<div class="container mt-4">
    <h2>Edit Deposit Instruction</h2>

    <form action="{{ route('depositeinstruction.update', $depositeinstruction->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Video URL</label>
            <input type="text" name="video_url" class="form-control" value="{{ $depositeinstruction->video_url }}" required>
        </div>

        <div class="mb-3">
            <label>Membership Title</label>
            <input type="text" name="member_ship_instructions_title" class="form-control" value="{{ $depositeinstruction->member_ship_instructions_title }}" required>
        </div>

        <div class="mb-3">
            <label>Membership Description</label>
            <textarea name="member_ship_instructions_description" class="form-control" required>{{ $depositeinstruction->member_ship_instructions_description }}</textarea>
        </div>

        <div class="mb-3">
            <label>Deposit Title</label>
            <input type="text" name="deposite_instructions_title" class="form-control" value="{{ $depositeinstruction->deposite_instructions_title }}" required>
        </div>

        <div class="mb-3">
            <label>Deposit Description</label>
            <textarea name="deposite_instructions_description" class="form-control" required>{{ $depositeinstruction->deposite_instructions_description }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
    </form>
</div>
@endsection
