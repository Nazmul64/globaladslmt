@extends('admin.master')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Approved Withdraw Requests</h4>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Agent Name</th>
                <th>Payment Method</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($withdraws as $key => $withdraw)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $withdraw->agent->email ?? 'N/A' }}</td>
                    <td>{{ $withdraw->agent->mobile ?? 'N/A' }}</td>
                    <td>{{ $withdraw->agent->name ?? 'N/A' }}</td>
                    <td>{{ $withdraw->paymentMethod->method_name ?? 'N/A' }}</td>
                    <td>{{ number_format($withdraw->amount, 2) }}$</td>
                    <td>
                        <span class="badge bg-success">{{ ucfirst($withdraw->status) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No approved withdraw requests.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
