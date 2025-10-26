@extends('admin.master')

@section('content')
<div class="container mt-4">
    <h4>Edit Commission Setup</h4>
    <form action="{{ route('agentcommission.update', $commission->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Deposit Agent Commission (%)</label>
                <input type="number" step="0.01" name="deposit_agent_commission" class="form-control"
                       value="{{ $commission->deposit_agent_commission }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Withdraw Total Commission (%)</label>
                <input type="number" step="0.01" name="withdraw_total_commission" class="form-control"
                       value="{{ $commission->withdraw_total_commission }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Agent Share (%)</label>
                <input type="number" step="0.01" name="agent_share_percent" class="form-control"
                       value="{{ $commission->agent_share_percent }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Admin Share (%)</label>
                <input type="number" step="0.01" name="admin_share_percent" class="form-control"
                       value="{{ $commission->admin_share_percent }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Commission Type</label>
                <select name="commission_type" class="form-control">
                    <option value="percent" {{ $commission->commission_type == 'percent' ? 'selected' : '' }}>Percent</option>
                    <option value="fixed" {{ $commission->commission_type == 'fixed' ? 'selected' : '' }}>Fixed</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="1" {{ $commission->status == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $commission->status == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <button class="btn btn-primary mt-2">Update</button>
        <a href="{{ route('agentcommission.index') }}" class="btn btn-secondary mt-2">Cancel</a>
    </form>
</div>
@endsection
