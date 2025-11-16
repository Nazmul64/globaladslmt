@extends('admin.master')

@section('content')
<div class="container mt-3">
    <h3>Add Dollar & Taka Signed</h3>

    <form action="{{ route('dollarsiged.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="dollarsigned" class="form-label">Dollar Signed</label>
            <input type="text" class="form-control" id="dollarsigned" name="dollarsigned" value="{{ old('dollarsigned') }}" required>
        </div>

        <button type="submit" class="btn btn-success">Save</button>
        <a href="{{ route('dollarsiged.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
