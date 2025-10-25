@extends('agent.master')

@section('content')
<div class="container mt-4">
    <h4>Approved Deposits</h4>

    @if($approved->count() > 0)
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Amount</th>
                <th>Transaction ID</th>
                <th>Screenshot</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($approved as $deposit)
            <tr>
                <td>{{ $deposit->id }}</td>
                <td>{{ round($deposit->amount) }}$</td>
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
        <p>No approved deposits found.</p>
    @endif
</div>
@endsection
