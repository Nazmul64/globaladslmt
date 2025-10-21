@extends('frontend.master')

@section('content')
<div class="container my-5">
    <div class="refer-section">

        <!-- Header -->
        <div class="refer-header text-center mb-4">
            <h2><i class="fas fa-share-alt"></i> Refer & Earn</h2>
            <div class="refer-stats d-flex justify-content-center gap-4 flex-wrap mt-3">
                <div class="stat-item text-center">
                    <h4>{{ $referrals->count() }}</h4>
                    <small>Total Referrals</small>
                </div>
                <div class="stat-item text-center">
                    <h4>{{ $referrals->filter(fn($r) => $r->packagebuys->count() > 0)->count() }}</h4>
                    <small>Active Users</small>
                </div>
                <div class="stat-item text-center">
                    <h4>${{ number_format($total_refer_income, 2) }}</h4>
                    <small>Total Earnings</small>
                </div>
            </div>
        </div>

        <!-- Referral List -->
        <div class="refer-list-section mt-4">
            <h3><i class="fas fa-users"></i> Your Referrals</h3>
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Earning</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($referrals as $index => $ref)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle text-center me-2"
                                         style="width:40px;height:40px;line-height:40px;">
                                        {{ strtoupper(substr($ref->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $ref->name }}</strong><br>
                                        <small class="text-muted">{{ $ref->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($ref->packagebuys->count() > 0)
                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Active</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half me-1"></i> Pending</span>
                                @endif
                            </td>
                            <td>{{ $ref->created_at->format('M d, Y') }}</td>
                            <td>${{ number_format($ref->packagebuys->sum('amount') * 0.10, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-user-slash fa-2x d-block mb-2"></i>
                                No referrals found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
