@extends('admin.master')

@section('content')
<div class="container mt-4">
    <div class="card border-0 shadow">
        <div class="card-header bg-danger text-white">
            <h4 class="mb-0"><i class="fa-solid fa-xmark-circle me-2"></i> Rejected KYC List</h4>
        </div>
        <div class="card-body">
            @php
                $kycs = \App\Models\Kyc::with('user')->where('status', 'rejected')->latest()->get();
            @endphp

            @if($kycs->isEmpty())
                <div class="alert alert-info text-center">No rejected KYC found.</div>
            @else
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-danger">
                        <tr>
                            <th>#</th>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Document Type</th>
                            <th>First Photo</th>
                            <th>Second Photo</th>
                            <th>Status</th>
                            <th>Rejected Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kycs as $key => $kyc)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $kyc->user->name ?? 'N/A' }}</td>
                                <td>{{ $kyc->user->email ?? 'N/A' }}</td>
                                <td>{{ ucfirst($kyc->document_type) }}</td>
                                <td>
                                    <img src="{{ asset('uploads/kyc/'.$kyc->document_first_part_photo) }}"
                                         alt="First Part" width="80" class="rounded border">
                                </td>
                                <td>
                                    <img src="{{ asset('uploads/kyc/'.$kyc->document_secound_part_photo) }}"
                                         alt="Second Part" width="80" class="rounded border">
                                </td>
                                <td>
                                    <span class="badge bg-danger">Rejected</span>
                                </td>
                                <td>{{ $kyc->updated_at->format('d M, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
