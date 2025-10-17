@extends('admin.master')

@section('content')
<div class="container mt-5">
    <h3>Edit Referral Commission Level</h3>

    <form action="{{ route('reffercommission.update', $reffercommission->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Referral Level</label>
            <input type="text" name="reffer_level" class="form-control" value="{{ old('reffer_level', $reffercommission->reffer_level) }}">
            @error('reffer_level')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label>Commission (%)</label>
            <input type="number" name="commission_percentage" class="form-control" value="{{ old('commission_percentage', $reffercommission->commission_percentage) }}" step="0.01">
            @error('commission_percentage')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-success">Update Level</button>
    </form>
</div>
@endsection
