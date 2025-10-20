@extends('admin.master')
@section('content')
<div class="container mt-4">
    <h2>Add New Item</h2>

    <form action="{{ route('whychooseu.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" placeholder="Enter title">
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Enter description"></textarea>
        </div>

        <div class="mb-3">
            <label>Icon (Font Awesome class)</label>
            <input type="text" name="icon" class="form-control" placeholder="e.g. fab fa-facebook">
        </div>

        <button class="btn btn-success">Create</button>
    </form>
</div>
@endsection
