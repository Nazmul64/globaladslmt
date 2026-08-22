@extends('admin.master')

@section('content')
<div class="container-fluid mt-4">

    <!-- Withdraw Requests Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fa-solid fa-money-bill-transfer me-2"></i>
                Pending User Withdraw Requests
            </h5>
        </div>

        <div class="card-body">

            <!-- Commission Info -->
            <div class="alert alert-secondary mb-3">
                💡 <strong>Current Withdraw Commission:</strong> {{ $commission }}%
            </div>

            <!-- Search Bar -->
            <div class="row mb-3">
                <div class="col-md-4 ms-auto">
                    <input
                        type="text"
                        id="withdrawSearch"
                        class="form-control"
                        placeholder="🔍 Search by name, phone, email, account..."
                    >
                </div>
            </div>

            @if($user_widthraw_request->isEmpty())
                <!-- Empty State -->
                <div class="alert alert-info text-center">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    No pending withdraw requests found.
                </div>
            @else
                <!-- Table Section -->
                <div class="table-responsive">
                    <table
                        class="table table-bordered table-hover align-middle text-center"
                        id="withdrawTable"
                    >
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Phone</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Account</th>
                                <th>Amount ($)</th>
                                <th>Fee ({{ $commission }}%)</th>
                                <th>After Fee ($)</th>
                                <th>Method</th>
                                <th>Requested At</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($user_widthraw_request as $key => $req)
                                @php
                                    $fee = ($req->amount * $commission) / 100;
                                    $after_fee = $req->amount - $fee;
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ optional($req->user)->mobile ?? 'N/A' }}</td>
                                    <td>{{ optional($req->user)->name ?? 'N/A' }}</td>
                                    <td>{{ optional($req->user)->email ?? 'N/A' }}</td>
                                    <td>{{ $req->account_number ?? 'N/A' }}</td>
                                    <td class="fw-bold text-primary">
                                        ${{ number_format($req->amount, 2) }}
                                    </td>
                                    <td class="text-danger">
                                        -${{ number_format($fee, 2) }}
                                    </td>
                                    <td class="text-success fw-semibold">
                                        ${{ number_format($after_fee, 2) }}
                                    </td>
                                    <td>
                                        {{ ucfirst(optional($req->payment_name)->method_name ?? 'Unknown') }}
                                    </td>
                                    <td>
                                        {{ $req->created_at->format('d M, Y h:i A') }}
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">

                                            <!-- Approve -->
                                            <form
                                                action="{{ route('admin.widthraw.approve', $req->id) }}"
                                                method="POST"
                                            >
                                                @csrf
                                                @method('PUT')
                                                <button
                                                    type="submit"
                                                    class="btn btn-success btn-sm"
                                                    onclick="return confirm('Approve this withdraw request?')"
                                                >
                                                    <i class="fa-solid fa-check"></i>
                                                    Approve
                                                </button>
                                            </form>

                                            <!-- Reject -->
                                            <form
                                                action="{{ route('admin.widthraw.reject', $req->id) }}"
                                                method="POST"
                                            >
                                                @csrf
                                                @method('PUT')
                                                <button
                                                    type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Reject this withdraw request?')"
                                                >
                                                    <i class="fa-solid fa-xmark"></i>
                                                    Reject
                                                </button>
                                            </form>

                                        </div>
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
document.getElementById('withdrawSearch').addEventListener('keyup', function () {
    let searchValue = this.value.toLowerCase();
    let table = document.getElementById('withdrawTable');
    let rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        let rowText = rows[i].innerText.toLowerCase();
        rows[i].style.display = rowText.includes(searchValue) ? '' : 'none';
    }
});
</script>

@endsection
