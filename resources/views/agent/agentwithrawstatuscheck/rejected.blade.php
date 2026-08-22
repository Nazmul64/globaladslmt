@extends('agent.master')

@section('content')
<div class="container mt-4">
    <h4>Rejected Agent Withdraw Requests</h4>

    {{-- Summary --}}
    <div class="mb-3">
        <span class="badge bg-warning">Pending: {{ $summary['pending'] }}</span>
        <span class="badge bg-success">Approved: {{ $summary['approved'] }}</span>
        <span class="badge bg-danger">Rejected: {{ $summary['rejected'] }}</span>
        <span class="badge bg-primary">Total: {{ $summary['total'] }}</span>
    </div>

    @if($withdraws->isEmpty())
        <div class="alert alert-info">No rejected withdraw requests.</div>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Agent</th>
                    <th>Payment Method</th>
                    <th>Account / Wallet</th>
                    <th>Amount</th>
                    <th>Rejected At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($withdraws as $withdraw)
                <tr>
                    <td>{{ $withdraw->agent->name ?? 'N/A' }}</td>
                    <td>{{ $withdraw->paymentMethod->name ?? 'N/A' }}</td>
                    <td>{{ $withdraw->wallet_address }}</td>
                    <td>{{ $withdraw->amount }}</td>
                    <td>{{ $withdraw->updated_at->format('d M Y, h:i A') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
