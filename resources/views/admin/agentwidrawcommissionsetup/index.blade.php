@extends('admin.master')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Agent Withdraw Commission</h4>

    <a href="{{ route('agentwidthrawcommission.create') }}" class="btn btn-primary mb-3">
        + Add Commission
    </a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Min Withdraw</th>
                <th>Max Withdraw</th>
                <th>Withdraw Charge (%)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($commissions as $key => $commission)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ round($commission->min_widthraw) }}$</td>
                    <td>{{ round($commission->max_widthraw) }}$</td>
                    <td>{{ $commission->widthraw_charge }}%</td>
                    <td>
                        <a href="{{ route('agentwidthrawcommission.edit', $commission->id) }}" class="btn btn-sm btn-info">Edit</a>

                        <form action="{{ route('agentwidthrawcommission.destroy', $commission->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No commissions found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
