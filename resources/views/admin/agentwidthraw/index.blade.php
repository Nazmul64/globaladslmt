@extends('admin.master')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Pending Agent Withdraw Requests</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

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
                <th width="180">Action</th>
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
                    <td>{{ number_format($withdraw->amount, 2) }} $</td>
                    <td>
                        <span class="badge bg-warning">{{ ucfirst($withdraw->status) }}</span>
                    </td>
                    <td>
                        <form action="{{ route('agent.widthraw.approve', $withdraw->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-success btn-sm">Approve</button>
                        </form>
                        <form action="{{ route('agent.widthraw.reject', $withdraw->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-danger btn-sm">Reject</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No pending withdraw requests found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
