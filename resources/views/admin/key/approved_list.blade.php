@extends('admin.master')

@section('content')
<div class="container-fluid py-4">

    <!-- Header + Search -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="fa-solid fa-user-check me-2"></i>User KYC Verification List
        </h4>

        <input
            type="text"
            id="kycSearch"
            class="form-control w-25"
            placeholder="🔍 Search by phone, name, email, status..."
        >
    </div>

    <!-- Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">

            <table
                id="kycTable"
                class="table table-bordered table-hover align-middle text-center"
            >
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Phone</th>
                        <th>User Name</th>
                        <th>Email</th>
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

                        <td>{{ optional($kyc->kycagent)->mobile ?? 'N/A' }}</td>
                        <td>{{ optional($kyc->kycagent)->name ?? 'N/A' }}</td>
                        <td>{{ optional($kyc->kycagent)->email ?? 'N/A' }}</td>

                        <td class="fw-semibold">
                            {{ ucfirst($kyc->document_type) }}
                        </td>

                        <!-- First Image -->
                        <td>
                            @if($kyc->document_first_part_photo)
                                <img
                                    src="{{ asset('uploads/kyc/'.$kyc->document_first_part_photo) }}"
                                    class="rounded-circle border kyc-img"
                                    width="55" height="55"
                                    data-img="{{ asset('uploads/kyc/'.$kyc->document_first_part_photo) }}"
                                    style="cursor:pointer"
                                >
                            @else
                                N/A
                            @endif
                        </td>

                        <!-- Second Image -->
                        <td>
                            @if($kyc->document_secound_part_photo)
                                <img
                                    src="{{ asset('uploads/kyc/'.$kyc->document_secound_part_photo) }}"
                                    class="rounded-circle border kyc-img"
                                    width="55" height="55"
                                    data-img="{{ asset('uploads/kyc/'.$kyc->document_secound_part_photo) }}"
                                    style="cursor:pointer"
                                >
                            @else
                                N/A
                            @endif
                        </td>

                        <!-- Status -->
                        <td>
                            @if($kyc->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($kyc->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </td>

                        <td>{{ $kyc->created_at->format('d M, Y') }}</td>

                        <!-- Action -->
                        <td>
                            @if($kyc->status === 'pending')
                                <form action="{{ route('admin.kyc.approve', $kyc->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-success btn-sm">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>

                                <form action="{{ route('admin.kyc.reject', $kyc->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-danger btn-sm">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            @elseif($kyc->status === 'approved')
                                <span class="text-success fw-bold">✔ Verified</span>
                            @else
                                <span class="text-danger fw-bold">✘ Rejected</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">
                            No KYC records found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">KYC Document Preview</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<!-- Live Search Script -->
<script>
document.getElementById('kycSearch').addEventListener('keyup', function () {
    let value = this.value.toLowerCase();
    document.querySelectorAll('#kycTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(value)
            ? ''
            : 'none';
    });
});
</script>

<!-- Image Preview Script -->
<script>
document.querySelectorAll('.kyc-img').forEach(img => {
    img.addEventListener('click', function () {
        document.getElementById('previewImage').src = this.dataset.img;
        new bootstrap.Modal(
            document.getElementById('imagePreviewModal')
        ).show();
    });
});
</script>

@endsection
