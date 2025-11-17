@extends('agent.master')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Withdraw Requests</h4>

    <table class="table table-bordered table-striped align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th>User</th>
                <th>Amount</th>
                <th>Number Recive</th>
                <th>Instruction</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
                <tr>
                    <td>{{ $req->user->name ?? 'Unknown' }}</td>
                    <td>{{ number_format($req->amount, 2) }} USDT</td>
                    <td>{{ ($req->sender_account ?? '') }}</td>
                    <td>{{ ($req->transaction_id ?? '') }}</td>
                    <td>
                        <span class="badge
                            @if($req->status == 'pending') bg-warning
                            @elseif($req->status == 'agent_confirmed') bg-info
                            @elseif($req->status == 'completed') bg-success
                            @endif">
                            {{ ucfirst($req->status) }}
                        </span>
                    </td>
                    <td>
                        @if($req->status == 'pending')
                            <form action="{{ route('agent.withdraw.accept', $req->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Accept</button>
                            </form>
                        @elseif($req->status == 'agent_confirmed')
                            <form action="{{ route('agent.withdraw.release', $req->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Release</button>
                            </form>
                        @else
                            <span class="text-success fw-bold">Completed</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">No withdraw requests available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
