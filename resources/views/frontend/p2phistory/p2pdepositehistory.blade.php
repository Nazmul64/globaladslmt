@extends('frontend.master')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3 text-center">💸 P2P Withdraw History</h4>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Agent Name</th>
                    <th>Amount (USDT)</th>
                    <th>Status</th>
                    <th>Transaction ID</th>
                    <th>Sender Account</th>
                    <th>Screenshot</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($userdepositerequest as $key => $withdraw)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $withdraw->agent->name ?? 'N/A' }}</td>
                        <td>{{ number_format($withdraw->amount, 2) }}</td>
                        <td>
                            @php
                                $badgeColors = [
                                    'pending' => 'warning',
                                    'agent_confirmed' => 'info',
                                    'user_submitted' => 'primary',
                                    'completed' => 'success',
                                    'rejected' => 'danger',
                                    'orderrelasce' => 'secondary'
                                ];
                            @endphp
                            <span class="badge bg-{{ $badgeColors[$withdraw->status] ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $withdraw->status)) }}
                            </span>
                        </td>
                        <td>{{ $withdraw->transaction_id ?? 'N/A' }}</td>
                        <td>{{ $withdraw->sender_account ?? 'N/A' }}</td>
                        <td>
                            @if($withdraw->photo)
                                <a href="{{ asset($withdraw->photo) }}" target="_blank">
                                    <img src="{{ asset($withdraw->photo) }}" width="50" height="50" class="rounded border" style="object-fit: cover;">
                                </a>
                            @else
                                <span class="text-muted">No Image</span>
                            @endif
                        </td>
                        <td>{{ $withdraw->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-muted">No userdepositerequest history found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
