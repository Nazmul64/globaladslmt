@extends('admin.master')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-danger text-white">
            <h5><i class="fa-solid fa-xmark me-2"></i> Rejected Withdraw Requests</h5>
        </div>

        <div class="card-body">
            @if($rejected_requests->isEmpty())
                <div class="alert alert-info text-center">No rejected withdraw requests found.</div>
            @else
                <table class="table table-bordered table-striped text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Requested At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rejected_requests as $key => $req)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $req->user->name ?? 'N/A' }}</td>
                            <td>{{ $req->user->email ?? 'N/A' }}</td>
                            <td>${{ number_format($req->amount,2) }}</td>
                            <td>{{ ucfirst($req->payment_name->method_name  ?? 'Unknown') }}</td>
                            <td>{{ $req->created_at->format('d M, Y h:i A') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
