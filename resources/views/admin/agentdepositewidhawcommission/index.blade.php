@extends('admin.master')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Agent Commission Setup List</h4>
        <a href="{{ route('agentcommission.create') }}" class="btn btn-primary btn-sm">+ Add New</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Deposit Commission (%)</th>
                <th>Withdraw Commission (%)</th>
                <th>Agent Share (%)</th>
                <th>Admin Share (%)</th>
                <th>Type</th>
                <th>Status</th>
                <th width="120">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($commissions as $key => $item)
                <tr>
                    <td>{{ $key+1 }}</td>
                    <td>{{ $item->deposit_agent_commission }}</td>
                    <td>{{ $item->withdraw_total_commission }}</td>
                    <td>{{ $item->agent_share_percent }}</td>
                    <td>{{ $item->admin_share_percent }}</td>
                    <td>{{ ucfirst($item->commission_type) }}</td>
                    <td>
                        @if($item->status == 1)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('agentcommission.edit', $item->id) }}" class="btn btn-sm btn-info">Edit</a>
                        <form action="{{ route('agentcommission.destroy', $item->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No data found!</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
