@extends('admin.master')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">⏳ Pending Agent Deposits</h4>

    @if($agent_deposite->isEmpty())
        <div class="alert alert-info text-center">
            No pending deposits found.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Amount</th>
                        <th>Sender Account</th>
                        <th>Transaction ID</th>
                        <th>Screenshot</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agent_deposite as $key => $deposit)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td><strong>{{ number_format($deposit->amount, 2) }} ৳</strong></td>
                            <td>{{ $deposit->sender_account }}</td>
                            <td>{{ $deposit->transaction_id }}</td>
                            <td>
                                 <img src="{{ asset('uploads/agentdeposite/'.$deposit->photo ?? '') }}"alt="Deposit Photo" width="50" height="50" style="object-fit: cover;">
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">{{ ucfirst($deposit->status) }}</span>
                            </td>
                            <td>{{ $deposit->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                <div class="d-flex gap-2 justify-content-center">
                                    <form action="{{ route('admin.agentdeposit.approve', $deposit->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.agentdeposit.reject', $deposit->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
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
@endsection
