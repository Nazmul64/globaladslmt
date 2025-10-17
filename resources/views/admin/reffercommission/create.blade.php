@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3>Add Referral Commission Level</h3>

    <form action="{{ route('reffercommission.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Referral Level</label>
            <input type="text" name="reffer_level" class="form-control" value="{{ old('reffer_level') }}">
            @error('reffer_level')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label>Commission (%)</label>
            <input type="number" name="commission_percentage" class="form-control" value="{{ old('commission_percentage') }}" step="0.01">
            @error('commission_percentage')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Add Level</button>
    </form>
</div>
@endsection
