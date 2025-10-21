@extends('admin.master')

@section('content')
<div class="container mt-5">
    <div class="card shadow border-0">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Approved KYC List</h5>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success text-center">{{ session('success') }}</div>
            @endif

            @if($approvedKycs->isEmpty())
                <div class="alert alert-info text-center">No approved KYC records found.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center align-middle">
                        <thead class="table-success">
                            <tr>
                                <th>#</th>
                                <th>Document Type</th>
                                <th>Front Photo</th>
                                <th>Back Photo</th>
                                <th>Status</th>
                                <th>Approved Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($approvedKycs as $index => $kyc)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ ucfirst($kyc->document_type ?? 'N/A') }}</td>

                                    <td>
                                        @if($kyc->document_first_part_photo)
                                            <img src="{{ asset('uploads/agent_kyc/'.$kyc->document_first_part_photo) }}"
                                                 width="60" height="60" class="rounded border">
                                        @else
                                            <span class="text-muted">No Photo</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($kyc->document_secound_part_photo)
                                            <img src="{{ asset('uploads/agent_kyc/'.$kyc->document_secound_part_photo) }}"
                                                 width="60" height="60" class="rounded border">
                                        @else
                                            <span class="text-muted">No Photo</span>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge bg-success">Approved</span>
                                    </td>

                                    <td>{{ $kyc->updated_at ? $kyc->updated_at->format('d M, Y') : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
