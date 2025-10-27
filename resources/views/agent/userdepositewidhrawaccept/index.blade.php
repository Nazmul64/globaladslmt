@extends('agent.master')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3 text-center">💵 User Deposit Requests</h4>

    <table class="table table-bordered table-hover align-middle text-center shadow-sm">
        <thead class="table-primary">
            <tr>
                <th>User Name</th>
                <th>Amount ($)</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($requests as $req)
            <tr>
                <td>{{ $req->user->name ?? 'N/A' }}</td>
                <td>${{ number_format($req->amount, 2) }}</td>
                <td>
                    <span class="badge
                        @if($req->status == 'pending') bg-warning
                        @elseif($req->status == 'agent_confirmed') bg-info
                        @elseif($req->status == 'user_submitted') bg-primary
                        @elseif($req->status == 'completed') bg-success
                        @else bg-danger @endif">
                        {{ ucfirst($req->status) }}
                    </span>
                </td>
                <td>
                    @if($req->status == 'pending')
                        <!-- Agent Accept -->
                        <form action="{{ route('agent.deposit.accept', $req->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                ✅ Accept
                            </button>
                        </form>

                    @elseif($req->status == 'user_submitted')
                        <!-- View Payment Modal Trigger -->
                        <button type="button" class="btn btn-primary btn-sm w-100"
                            data-bs-toggle="modal" data-bs-target="#paymentModal{{ $req->id }}">
                            👁 View Payment
                        </button>

                        <!-- Payment Modal -->
                        <div class="modal fade" id="paymentModal{{ $req->id }}" tabindex="-1"
                             aria-labelledby="paymentModalLabel{{ $req->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title">Payment Details - {{ $req->user->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body text-start">
                                        <p><strong>Transaction ID:</strong> {{ $req->transaction_id ?? 'N/A' }}</p>
                                        <p><strong>Sender Account:</strong> {{ $req->sender_account ?? 'N/A' }}</p>
                                        <p><strong>Screenshot:</strong></p>
                                        @if($req->photo)
                                            <img src="{{ asset('uploads/deposits/' . $req->photo) }}"
                                                 class="img-fluid rounded shadow-sm border">
                                        @else
                                            <p class="text-muted">No screenshot uploaded.</p>
                                        @endif
                                    </div>

                                    <div class="modal-footer">
                                        <form action="{{ route('agent.deposit.final', $req->id) }}" method="POST" class="w-100">
                                            @csrf
                                            <button type="submit" class="btn btn-success w-100">
                                                ✅ Confirm Deposit
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <span class="text-muted">No action available</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-muted">No deposit requests available.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
