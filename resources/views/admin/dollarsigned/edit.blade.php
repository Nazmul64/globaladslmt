@extends('admin.master')

@section('content')
<div class="container mt-3">
    <h3>Edit Dollar & Taka Signed</h3>

    <form action="{{ route('dollarsiged.update', $takaandDollarsigend->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="dollarsigned" class="form-label">Dollar Signed</label>
            <input type="text" class="form-control" id="dollarsigned" name="dollarsigned" value="{{ old('dollarsigned', $takaandDollarsigend->dollarsigned) }}" required>
        </div>


        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('dollarsiged.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
