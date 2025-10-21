@extends('admin.master')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>KYC Verification List</h4>
        <!-- 🔍 Search Bar -->
        <input type="text" id="kycSearch" class="form-control w-25" placeholder="Search KYC...">
    </div>

    <div class="table-responsive">
        <table id="kycTable" class="table table-bordered table-hover text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Document Type</th>
                    <th>First Photo</th>
                    <th>Second Photo</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kycs as $index => $kyc)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ ucfirst($kyc->document_type) }}</td>
                    <td><img src="{{ asset('uploads/agent_kyc/'.$kyc->document_first_part_photo) }}" width="60" height="60" class="rounded-circle border"></td>
                    <td><img src="{{ asset('uploads/agent_kyc/'.$kyc->document_secound_part_photo) }}" width="60" height="60" class="rounded-circle border"></td>
                    <td>
                        @if($kyc->status == 'pending')
                            <span class="badge bg-warning text-dark">Pending</span>
                        @elseif($kyc->status == 'approved')
                            <span class="badge bg-success">Approved</span>
                        @else
                            <span class="badge bg-danger">Rejected</span>
                        @endif
                    </td>
                    <td>{{ $kyc->created_at->format('d M, Y') }}</td>
                    <td>
                        @if($kyc->status == 'pending')
                            <form action="{{ route('agent.kyc.approve', $kyc->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                            </form>
                            <form action="{{ route('agent.kyc.reject', $kyc->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        @elseif($kyc->status == 'approved')
                            <span class="text-success fw-bold">✔ Verified</span>
                        @else
                            <span class="text-danger fw-bold">✘ Rejected</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">No KYC records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- 🔍 JavaScript Live Search -->
<script>
document.getElementById('kycSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#kycTable tbody tr');

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>
@endsection
