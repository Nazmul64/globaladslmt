@extends('admin.master')

@section('content')
<div class="card p-3">
    <h5>Add Logo</h5>

    <form action="{{ route('logosetting.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="photo" class="form-control" required>
        <br>
        <button class="btn btn-success">Save</button>
    </form>
</div>
@endsection
