@extends('admin.master')

@section('content')
<div class="container">
    <h1>Add Deposit Instruction</h1>

    <form action="{{ route('depositeinstructions.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="deposite_instructions">Instruction</label>
            <textarea name="deposite_instructions" id="deposite_instructions" class="form-control" rows="4">{{ old('deposite_instructions') }}</textarea>
            @error('deposite_instructions')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <button class="btn btn-success mt-2">Save</button>
    </form>
</div>
@endsection
