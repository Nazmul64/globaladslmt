@extends('admin.master')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>✅ Approved Deposits</h4>
        <a href="{{ route('admin.agent.deposite.pending') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-clock"></i> Pending List
        </a>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-bordered mb-0">
                <thead class="table-success">
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
                    @forelse($agent_deposite as $key => $deposit)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ number_format($deposit->amount, 2) }} ৳</td>
                            <td>{{ $deposit->sender_account }}</td>
                            <td>{{ $deposit->transaction_id }}</td>
                            <td>
                                @if($deposit->photo)
                                    <a href="{{ asset('storage/'.$deposit->photo) }}" target="_blank">
                                        <img src="{{ asset('storage/'.$deposit->photo) }}" alt="screenshot"
                                             style="width:50px; height:50px; border-radius:5px;">
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td><span class="badge bg-success">Approved</span></td>
                            <td>{{ $deposit->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">No approved deposits yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
