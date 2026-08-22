@extends('admin.master')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Edit Withdraw Commission</h4>

    <form action="{{ route('agentwidthrawcommission.update', $commission->id) }}" method="POST">
        @csrf
        @method('PUT')



        <div class="mb-3">
            <label class="form-label">Min Withdraw (%)</label>
            <input type="number" step="0.01" name="min_widthraw" class="form-control" value="{{ old('min_widthraw', $commission->min_widthraw) }}">
            @error('min_widthraw') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Max Withdraw (%)</label>
            <input type="number" step="0.01" name="max_widthraw" class="form-control" value="{{ old('max_widthraw', $commission->max_widthraw) }}">
            @error('max_widthraw') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Withdraw Charge (%)</label>
            <input type="number" step="0.01" name="widthraw_charge" class="form-control" value="{{ old('widthraw_charge', $commission->widthraw_charge) }}">
            @error('widthraw_charge') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('agentwidthrawcommission.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
