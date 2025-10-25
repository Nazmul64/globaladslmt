@extends('admin.master')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>❌ Rejected Deposits</h4>
        <a href="{{ route('admin.agent.deposite.pending') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-clock"></i> Pending List
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($agent_deposite->isEmpty())
        <div class="alert alert-info text-center">
            No rejected deposits found.
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped table-bordered mb-0">
                    <thead class="table-danger">
                        <tr>
                            <th>#</th>
                            <th>Amount</th>
                            <th>Sender Account</th>
                            <th>Transaction ID</th>
                            <th>Screenshot</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agent_deposite as $key => $deposit)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ number_format($deposit->amount, 2) }} ৳</td>
                                <td>{{ $deposit->sender_account }}</td>
                                <td>{{ $deposit->transaction_id }}</td>
                                <td>
                                    @if($deposit->photo)
                                        <a href="{{ asset('uploads/agentdeposite/'.$deposit->photo) }}" target="_blank">
                                            <img src="{{ asset('uploads/agentdeposite/'.$deposit->photo) }}"
                                                 alt="Deposit Photo" width="50" height="50" style="object-fit: cover;">
                                        </a>
                                    @else
                                        <span class="text-muted">No Image</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-danger">Rejected</span></td>
                                <td>{{ $deposit->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
