@extends('admin.master')
@section('content')

<main class="dashboard-main">
  <div class="dashboard-main-body">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h6 class="fw-semibold mb-0">Deposit Limits</h6>
      <a href="{{ route('depositelimit.create') }}" class="btn btn-primary btn-sm">Add New Limit</a>
    </div>
    <div class="card p-3">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Min Deposit</th>
            <th>Max Deposit</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($limits as $limit)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ round($limit->min_deposit) }}</td>
            <td>{{ round($limit->max_deposit) }}</td>
            <td>{{ $limit->created_at->format('d M, Y') }}</td>
            <td>
              <a href="{{ route('depositelimit.edit', $limit->id) }}" class="btn btn-sm btn-warning">Edit</a>

              <form action="{{ route('depositelimit.destroy', $limit->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-danger">Delete</button>
              </form>

            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</main>

@endsection
