@extends('admin.master')

@section('content')
<div class="container">
    <h1>Edit widthraw Instruction</h1>

    <form action="{{ route('widthrawInstruction.update', $depositeinstruction->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="instructions">Instruction</label>
            <textarea name="instructions" id="instructions" class="form-control" rows="4">{{ old('instructions', $depositeinstruction->instructions) }}</textarea>
            @error('instructions')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <button class="btn btn-primary mt-2">Update</button>
    </form>
</div>
@endsection
