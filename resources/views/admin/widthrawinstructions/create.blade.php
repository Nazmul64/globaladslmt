@extends('admin.master')

@section('content')
<div class="container">
    <h1>Add widthraw Instruction</h1>

    <form action="{{ route('widthrawInstruction.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="instructions">Instruction</label>
            <textarea name="instructions" id="deposite_instructions" class="form-control" rows="4">{{ old('instructions') }}</textarea>
            @error('instructions')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <button class="btn btn-success mt-2">Save</button>
    </form>
</div>
@endsection
