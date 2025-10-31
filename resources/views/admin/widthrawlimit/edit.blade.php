@extends('admin.master')

@section('content')
<div class="container mt-4">
    <h2>Edit Withdraw Limit</h2>
    <a href="{{ route('widthrawlimit.index') }}" class="btn btn-secondary mb-3">Back</a>
    <form action="{{ route('widthrawlimit.update', $widthrawlimit->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group mb-3">
            <label>Max Withdraw Limit</label>
            <input type="number" name="max_withdraw_limit" class="form-control" step="0.01" value="{{ old('max_withdraw_limit', $widthrawlimit->max_withdraw_limit) }}" required>
        </div>
        <div class="form-group mb-3">
            <label>Min Withdraw Limit</label>
            <input type="number" name="min_withdraw_limit" class="form-control" step="0.01" value="{{ old('min_withdraw_limit', $widthrawlimit->min_withdraw_limit) }}" required>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
    </form>
</div>
@endsection
