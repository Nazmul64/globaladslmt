@extends('admin.master')

@section('content')
<div class="container">
    <h1>Edit Deposit Instruction</h1>

    <form action="{{ route('depositeinstructions.update', $depositeinstruction->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="deposite_instructions">Instruction</label>
            <textarea name="deposite_instructions" id="deposite_instructions" class="form-control" rows="4">{{ old('deposite_instructions', $depositeinstruction->deposite_instructions) }}</textarea>
            @error('deposite_instructions')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <button class="btn btn-primary mt-2">Update</button>
    </form>
</div>
@endsection
