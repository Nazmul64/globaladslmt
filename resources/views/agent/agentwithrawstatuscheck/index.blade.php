@extends('agent.master')

@section('content')
<div class="container mt-4">
    <h4>Agent Withdraw Requests</h4>

    {{-- Summary --}}
    <div class="mb-3">
        <span class="badge bg-warning">Pending: {{ $summary['pending'] }}</span>
        <span class="badge bg-success">Approved: {{ $summary['approved'] }}</span>
        <span class="badge bg-danger">Rejected: {{ $summary['rejected'] }}</span>
        <span class="badge bg-primary">Total: {{ $summary['total'] }}</span>
    </div>

    @if($withdraws->isEmpty())
        <div class="alert alert-info">No withdraw requests found.</div>
    @else
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Agent</th>
                    <th>Payment Method</th>
                    <th>Account / Wallet</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($withdraws as $withdraw)
                <tr>
                    <td>{{ $withdraw->agent->name ?? 'N/A' }}</td>
                    <td>{{ $withdraw->paymentMethod->method_name ?? 'N/A' }}</td>
                    <td>{{ $withdraw->wallet_address }}</td>
                    <td>{{ $withdraw->amount }}</td>
                    <td>
                        <span class="badge
                            {{ $withdraw->status=='pending' ? 'bg-warning' : ($withdraw->status=='approved' ? 'bg-success' : 'bg-danger') }}">
                            {{ ucfirst($withdraw->status) }}
                        </span>
                    </td>
                    <td>
                        @if($withdraw->status=='pending')
                        @else
                        -
                        @endif
                    </td>
                    <td>{{ $withdraw->created_at->format('d M Y, h:i A') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
