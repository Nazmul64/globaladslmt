@extends('admin.master')

@section('content')
<div class="container-fluid mt-4">

    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fa-solid fa-check me-2"></i>
                Approved Withdraw Requests
            </h5>
        </div>

        <div class="card-body">

            <!-- Search Bar -->
            <div class="row mb-3">
                <div class="col-md-4 ms-auto">
                    <input
                        type="text"
                        id="approvedSearch"
                        class="form-control"
                        placeholder="🔍 Search by name, phone, email, method..."
                    >
                </div>
            </div>

            @if($approved_requests->isEmpty())
                <div class="alert alert-info text-center">
                    No approved withdraw requests found.
                </div>
            @else
                <div class="table-responsive">
                    <table
                        class="table table-bordered table-hover align-middle text-center"
                        id="approvedTable"
                    >
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Phone</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Amount ($)</th>
                                <th>Method</th>
                                <th>Approved At</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($approved_requests as $key => $req)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ optional($req->user)->mobile ?? 'N/A' }}</td>
                                    <td>{{ optional($req->user)->name ?? 'N/A' }}</td>
                                    <td>{{ optional($req->user)->email ?? 'N/A' }}</td>
                                    <td class="fw-bold text-success">
                                        ${{ number_format($req->amount, 2) }}
                                    </td>
                                    <td>
                                        {{ ucfirst(optional($req->payment_name)->method_name ?? 'Unknown') }}
                                    </td>
                                    <td>
                                        {{ $req->created_at->format('d M, Y h:i A') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            @endif

        </div>
    </div>
</div>

<!-- ================= SEARCH SCRIPT ================= -->
<script>
document.getElementById('approvedSearch').addEventListener('keyup', function () {
    let value = this.value.toLowerCase();
    let rows = document
        .getElementById('approvedTable')
        .getElementsByTagName('tbody')[0]
        .getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        let rowText = rows[i].innerText.toLowerCase();
        rows[i].style.display = rowText.includes(value) ? '' : 'none';
    }
});
</script>

@endsection
