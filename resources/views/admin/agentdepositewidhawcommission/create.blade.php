@extends('admin.master')

@section('content')
<div class="container mt-4">
    <h4>Add New Commission Setup</h4>
    <form action="{{ route('agentcommission.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Deposit Agent Commission (%)</label>
                <input type="number" step="0.01" name="deposit_agent_commission" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Withdraw Total Commission (%)</label>
                <input type="number" step="0.01" name="withdraw_total_commission" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Agent Share (%)</label>
                <input type="number" step="0.01" name="agent_share_percent" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Admin Share (%)</label>
                <input type="number" step="0.01" name="admin_share_percent" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Commission Type</label>
                <select name="commission_type" class="form-control">
                    <option value="percent">Percent</option>
                    <option value="fixed">Fixed</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>
        <button class="btn btn-success mt-2">Save</button>
        <a href="{{ route('agentcommission.index') }}" class="btn btn-secondary mt-2">Cancel</a>
    </form>
</div>
@endsection
