@extends('admin.master')

@section('content')
<div class="container-fluid px-4">
    <h5 class="mt-4">Packages</h5>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-box"></i> Packages List</span>
            <a href="{{ route('package.create') }}" class="btn btn-primary btn-sm">Add Package</a>
        </div>

        <div class="card-body">
            <!-- Search Input -->
            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="Search Packages...">
            </div>

            <table class="table table-bordered table-striped" id="packagesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Daily Income</th>
                        <th>Daily Limit</th>
                        <th>Photo</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $package)
                        <tr>
                            <td>{{ $package->id }}</td>
                            <td>{{ $package->package_name }}</td>
                            <td>${{ $package->price }}</td>
                            <td>${{ $package->daily_income }}</td>
                            <td>{{ $package->daily_limit }}</td>
                            <td>
                                @if($package->photo)
                                    <img src="{{ asset('uploads/package/'.$package->photo) }}" alt="Package Image" width="50">
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('package.edit', $package->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('package.destroy', $package->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No Packages Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JavaScript Search -->

<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#packagesTable tbody tr');

        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
</script>
@endsection
