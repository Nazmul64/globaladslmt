@extends('admin.master')

@section('content')
<div class="card p-3">
    <h5>Site Logo</h5>

    @if($logo)
        <img src="{{ asset('uploads/logo/'.$logo->photo) }}" width="120">
        <br><br>
        <a href="{{ route('logosetting.edit',$logo->id) }}" class="btn btn-sm btn-primary">Edit</a>
    @else
        <a href="{{ route('logosetting.create') }}" class="btn btn-sm btn-success">Add Logo</a>
    @endif
</div>
@endsection
