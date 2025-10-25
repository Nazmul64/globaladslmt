@extends('admin.master')
@section('content')

<main class="dashboard-main">
  <div class="dashboard-main-body">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h6 class="fw-semibold mb-0">Edit Deposit Limit</h6>
      <a href="{{ route('depositelimit.index') }}" class="btn btn-sm btn-outline-primary">Back</a>
    </div>
    <div class="card p-3">
      <form action="{{ route('depositelimit.update', $depositelimit->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
          <label for="min_deposit" class="form-label">Minimum Deposit</label>
          <input type="number" class="form-control" name="min_deposit" id="min_deposit" value="{{ old('min_deposit', $depositelimit->min_deposit) }}" required>
        </div>

        <div class="mb-3">
          <label for="max_deposit" class="form-label">Maximum Deposit</label>
          <input type="number" class="form-control" name="max_deposit" id="max_deposit" value="{{ old('max_deposit', $depositelimit->max_deposit) }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
      </form>
    </div>
  </div>
</main>

@endsection
