@extends('admin.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">👥 Manage Individual Agent Locks</h4>
                    <a href="{{ route('locksystem.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Settings
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    {{-- Global Lock Status (from database) --}}
                    @if($globalLock && $globalLock->status)
                    <div class="alert alert-info">
                        <i class="fas fa-lock"></i> <strong>Global Lock Active:</strong>
                        ${{ number_format($globalLock->locked_amount, 2) }}
                        <small class="text-muted">(Applies to all agents unless individually overridden)</small>
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-unlock"></i> <strong>No Global Lock Active</strong> - Only individual locks will apply
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Total Approved Deposit</th>
                                    <th>Locked Amount</th>
                                    <th>Available Balance</th>
                                    <th>Lock Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($agents as $agent)
                                @php
                                    // ✅ Calculate effective locked amount based on real-time global lock status
                                    $effectiveLockedAmount = 0;

                                    if ($agent->is_locked_override) {
                                        // Agent has custom lock - use their locked_amount
                                        $effectiveLockedAmount = $agent->locked_amount;
                                    } else {
                                        // Agent follows global lock
                                        if ($globalLock && $globalLock->status == 1) {
                                            // Global lock is ACTIVE - apply it
                                            $effectiveLockedAmount = $globalLock->locked_amount;
                                        } else {
                                            // Global lock is INACTIVE - no lock
                                            $effectiveLockedAmount = 0;
                                        }
                                    }

                                    // ✅ Calculate available balance
                                    $effectiveAvailableBalance = max(0, ($agent->total_deposit ?? 0) - $effectiveLockedAmount);
                                @endphp
                                <tr>
                                    <td>{{ $agent->id }}</td>
                                    <td><strong>{{ $agent->name }}</strong></td>
                                    <td>{{ $agent->email }}</td>
                                    <td>
                                        <strong class="text-primary">
                                            ${{ number_format($agent->total_deposit ?? 0, 2) }}
                                        </strong>
                                    </td>
                                    <td>
                                        <strong class="text-{{ $effectiveLockedAmount > 0 ? 'danger' : 'success' }}">
                                            ${{ number_format($effectiveLockedAmount, 2) }}
                                        </strong>
                                        @if(!$agent->is_locked_override && $globalLock && $globalLock->status == 1)
                                            <small class="text-muted d-block">(from global)</small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-info">
                                            ${{ number_format($effectiveAvailableBalance, 2) }}
                                        </strong>
                                    </td>
                                    <td>
                                        @if($agent->is_locked_override)
                                            @if($agent->locked_amount > 0)
                                                <span class="badge bg-danger">🔒 Custom Locked</span>
                                            @else
                                                <span class="badge bg-success">🔓 Custom Unlocked</span>
                                            @endif
                                        @else
                                            @if($globalLock && $globalLock->status == 1)
                                                <span class="badge bg-info">🌐 Following Global (Active)</span>
                                            @else
                                                <span class="badge bg-secondary">🌐 Following Global (Inactive)</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            {{-- Unlock Button --}}
                                            <form action="{{ route('locksystem.toggle-agent', $agent->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="lock_type" value="unlock">
                                                <button type="submit" class="btn btn-success" title="Unlock this agent"
                                                        onclick="return confirm('Unlock {{ $agent->name }}? This will override global lock.')">
                                                    <i class="fas fa-unlock"></i>
                                                </button>
                                            </form>

                                            {{-- Lock Button (uses global amount from database) --}}
                                            @if($globalLock)
                                            <form action="{{ route('locksystem.toggle-agent', $agent->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="lock_type" value="lock">
                                                <input type="hidden" name="custom_amount" value="{{ $globalLock->locked_amount }}">
                                                <button type="submit" class="btn btn-danger"
                                                        title="Lock with ${{ number_format($globalLock->locked_amount, 2) }}"
                                                        onclick="return confirm('Lock {{ $agent->name }} with ${{ number_format($globalLock->locked_amount, 2) }}? This will override global lock.')">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            </form>
                                            @endif

                                            {{-- Reset to Global --}}
                                            @if($agent->is_locked_override)
                                            <form action="{{ route('locksystem.reset-agent', $agent->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary" title="Reset to global"
                                                        onclick="return confirm('Reset {{ $agent->name }} to global settings?')">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-3x mb-2 d-block"></i>
                                        No agents found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-light mt-3">
                        <h6><i class="fas fa-info-circle"></i> How It Works:</h6>
                        <ul class="mb-0">
                            <li><strong>Total Approved Deposit:</strong> Sum of all approved deposits from agent_deposites table</li>
                            <li><strong>Locked Amount (Real-time):</strong>
                                <ul>
                                    <li>If agent has <code>is_locked_override = true</code>: Shows their custom locked_amount</li>
                                    <li>If <code>is_locked_override = false</code> AND global lock is ACTIVE: Shows global locked_amount</li>
                                    <li>If <code>is_locked_override = false</code> AND global lock is INACTIVE: Shows $0.00</li>
                                </ul>
                            </li>
                            <li><strong>Available Balance:</strong> Total Deposit - Effective Locked Amount (calculated in real-time)</li>
                            <li><strong>🌐 Following Global (Active):</strong> Global lock is on, agent follows it</li>
                            <li><strong>🌐 Following Global (Inactive):</strong> Global lock is off, agent has $0 lock</li>
                            <li><strong>🔒 Custom Locked:</strong> Agent has individual lock (ignores global)</li>
                            <li><strong>🔓 Custom Unlocked:</strong> Agent is individually unlocked (ignores global)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
