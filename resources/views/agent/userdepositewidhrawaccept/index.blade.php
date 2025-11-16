@extends('agent.master')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3 text-center">💵 User Deposit Requests</h4>

    <div class="table-responsive shadow-sm">
        <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-primary">
                <tr>
                    <th>User Name</th>
                    <th>Amount ($)</th>
                    <th>Photo</th>
                    <th>Status</th>
                    <th width="180">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    <tr>
                        {{-- User Name --}}
                        <td>{{ $req->user->name ?? 'N/A' }}</td>

                        {{-- Amount --}}
                        <td>${{ number_format($req->amount, 2) }}</td>

                        {{-- Photo Thumbnail --}}
                        <td>
                            @if($req->photo)
                                <a href="{{ asset($req->photo) }}" target="_blank">
                                    <img src="{{ asset($req->photo) }}"
                                         width="50" height="50" class="rounded border"
                                         style="object-fit: cover;">
                                </a>
                            @else
                                <span class="text-muted">No Image</span>
                            @endif
                        </td>

                        {{-- Status Badge --}}
                        <td>
                            @php
                                $badgeColors = [
                                    'pending'         => 'warning',
                                    'agent_confirmed' => 'info',
                                    'user_submitted'  => 'primary',
                                    'completed'       => 'success',
                                    'rejected'        => 'danger',
                                    'orderrelasce'    => 'secondary',
                                ];
                            @endphp
                            <span class="badge bg-{{ $badgeColors[$req->status] ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                            </span>
                        </td>

                        {{-- Action Buttons --}}
                        <td>
                            @if($req->status === 'pending')
                                <form action="{{ route('agent.deposit.accept', $req->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm w-100">✔ Accept</button>
                                </form>

                            @elseif($req->status === 'user_submitted')
                                <button type="button" class="btn btn-primary btn-sm w-100"
                                        data-bs-toggle="modal" data-bs-target="#paymentModal{{ $req->id }}">
                                    👁 View & Confirm
                                </button>

                                {{-- Modal --}}
                                <div class="modal fade" id="paymentModal{{ $req->id }}" tabindex="-1"
                                     aria-labelledby="paymentModalLabel{{ $req->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-light">
                                                <h5 class="modal-title" id="paymentModalLabel{{ $req->id }}">
                                                    Payment Details - {{ $req->user->name ?? 'N/A' }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <p><strong>Transaction ID:</strong> {{ $req->transaction_id ?? 'N/A' }}</p>
                                                <p><strong>Sender Account:</strong> {{ $req->sender_account ?? 'N/A' }}</p>
                                                <p><strong>Screenshot:</strong></p>
                                                @if($req->photo)
                                                    <a href="{{ asset($req->photo) }}" target="_blank">
                                                        <img src="{{ asset($req->photo) }}"
                                                             class="img-fluid rounded border shadow-sm">
                                                    </a>
                                                @else
                                                    <p class="text-muted">No screenshot uploaded.</p>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <form action="{{ route('agent.deposit.final', $req->id) }}" method="POST" class="w-100">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success w-100">
                                                        💰 Confirm Deposit
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">No Action</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-muted">No deposit requests found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
