@extends('agent.master')

@section('content')
<table class="table table-bordered">
    <thead>
        <tr>
            <th>User</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($requests as $req)
        <tr>
            <td>{{ $req->user->name }}</td>
            <td>{{ $req->amount }}</td>
            <td><span class="badge bg-warning">{{ $req->status }}</span></td>
            <td>
                <form action="{{ route('agent.deposit.accept', $req->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">Accept</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection