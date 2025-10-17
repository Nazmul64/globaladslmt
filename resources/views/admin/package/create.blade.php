@extends('admin.master')

@section('content')
<div class="container-fluid px-4">
    <h5 class="mt-4">Add Package</h5>

    <div class="card mb-4">

        <div class="card-body">
             <a class="btn btn-success mb-3" href="{{ route('package.index') }}">Back to Packages</a>
            <form action="{{ route('package.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Package Name</label>
                    <input type="text" name="package_name" class="form-control" value="{{ old('package_name') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Price ($)</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Daily Income ($)</label>
                    <input type="number" step="0.01" name="daily_income" class="form-control" value="{{ old('daily_income') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Daily Limit</label>
                    <input type="number" name="daily_limit" class="form-control" value="{{ old('daily_limit') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Package Photo</label>
                    <input type="file" name="photo" class="form-control">
                </div>

                <button type="submit" class="btn btn-success">Create Package</button>
                <a href="{{ route('package.index') }}" class="btn btn-secondary">Cancel</a>
            </form>

        </div>
    </div>
</div>
@endsection
