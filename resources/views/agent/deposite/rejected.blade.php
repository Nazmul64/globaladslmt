@extends('agent.master')

@section('content')
<div class="container mt-4">
    <h4>Rejected Deposits</h4>

    @if($rejected->count() > 0)
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Transaction ID</th>
                <th>Screenshot</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rejected as $deposit)
            <tr>
                <td>{{ $deposit->id }}</td>
                <td>{{ number_format($deposit->amount, 2) }}৳</td>
                <td>{{ $deposit->payment_method->method_number ?? '-' }}</td>
                <td>{{ $deposit->transaction_id }}</td>
                <td>
                    @if($deposit->photo)
                        <img src="{{ asset('uploads/agentdeposite/'.$deposit->photo) }}" alt="Deposit Photo" style="max-width:100px;">
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ ucfirst($deposit->status) }}</td>
                <td>{{ $deposit->created_at->format('d-m-Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>No rejected deposits found.</p>
    @endif
</div>
@endsection
