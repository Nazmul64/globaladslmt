@extends('agent.master')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3 text-center fw-bold">💸 Withdraw Requests</h4>

    <div class="table-responsive shadow-sm">
        <table class="table table-bordered table-striped text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Number Receive</th>
                    <th>Instruction / Transaction</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    <tr @if($req->status == 'pending') class="table-warning" @endif>
                        <td>{{ $req->user->name ?? 'Unknown' }}</td>
                        <td>{{ number_format($req->amount, 2) }} USDT</td>
                        <td>{{ $req->sender_account ?? '-' }}</td>
                        <td>{{ $req->transaction_id ?? '-' }}</td>
                        <td>
                            @php
                                $badge = match($req->status) {
                                    'pending' => 'bg-warning',
                                    'agent_confirmed' => 'bg-info',
                                    'completed' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ ucfirst($req->status) }}</span>
                        </td>
                        <td>
                            @if($req->status == 'pending')
                                <div class="d-flex gap-1 justify-content-center">
                                    <form action="{{ route('agent.withdraw.accept', $req->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Accept</button>
                                    </form>

                                    <form action="{{ route('agent.withdraw.rejected', $req->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                </div>
                            @elseif($req->status == 'agent_confirmed')
                                <span class="text-primary fw-bold">Waiting for Release</span>
                            @elseif($req->status == 'completed')
                                <span class="text-success fw-bold">Completed</span>
                            @elseif($req->status == 'rejected')
                                <span class="text-danger fw-bold">Rejected</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No withdraw requests available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Links -->
    <div class="d-flex justify-content-center mt-3">
        {{ $requests->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
