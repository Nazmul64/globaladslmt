@extends('admin.master')

@section('content')
<div class="container mt-5">
    <div class="card shadow border-0">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Rejected KYC List</h5>
        </div>

        <div class="card-body">


            @if($rejectedKycs->isEmpty())
                <div class="alert alert-info text-center">No rejected KYC records found.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center align-middle">
                        <thead class="table-danger">
                            <tr>
                                <th>#</th>
                                <th>Document Type</th>
                                <th>Front Photo</th>
                                <th>Back Photo</th>
                                <th>Status</th>
                                <th>Rejected Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rejectedKycs as $index => $kyc)
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
                                        <span class="badge bg-danger">Rejected</span>
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
