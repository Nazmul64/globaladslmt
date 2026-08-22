@extends('admin.master')

@section('content')
<div class="card p-3">
    <h5>Edit Logo</h5>

    <form action="{{ route('logosetting.update',$logo->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <img src="{{ asset('uploads/logo/'.$logo->photo) }}" width="120">
        <br><br>

        <input type="file" name="photo" class="form-control">
        <br>
        <button class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
