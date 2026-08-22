@extends('admin.master')

@section('content')
<div class="container-fluid py-4">

    <!-- Header + Search -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 text-success">
            <i class="fa-solid fa-check-circle me-2"></i>
            Approved KYC List
        </h4>

        <input
            type="text"
            id="approvedKycSearch"
            class="form-control w-25"
            placeholder="🔍 Search by name, email, document..."
        >
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">

            @if($kycs->isEmpty())
                <div class="alert alert-info text-center">
                    No approved KYC found.
                </div>
            @else
                <table
                    class="table table-bordered table-hover align-middle text-center"
                    id="approvedKycTable"
                >
                    <thead class="table-success">
                        <tr>
                            <th>#</th>
                            <th>Phone</th>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Document Type</th>
                            <th>First Photo</th>
                            <th>Second Photo</th>
                            <th>Status</th>
                            <th>Approved Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($kycs as $key => $kyc)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ optional($kyc->user)->mobile ?? 'N/A' }}</td>
                            <td>{{ optional($kyc->user)->name ?? 'N/A' }}</td>
                            <td>{{ optional($kyc->user)->email ?? 'N/A' }}</td>

                            <td class="fw-semibold">
                                {{ ucfirst($kyc->document_type) }}
                            </td>

                            <td>
                                @if($kyc->document_first_part_photo)
                                    <img
                                        src="{{ asset('uploads/kyc/'.$kyc->document_first_part_photo) }}"
                                        class="rounded border kyc-img"
                                        width="70"
                                        data-img="{{ asset('uploads/kyc/'.$kyc->document_first_part_photo) }}"
                                        style="cursor:pointer"
                                    >
                                @else
                                    N/A
                                @endif
                            </td>

                            <td>
                                @if($kyc->document_secound_part_photo)
                                    <img
                                        src="{{ asset('uploads/kyc/'.$kyc->document_secound_part_photo) }}"
                                        class="rounded border kyc-img"
                                        width="70"
                                        data-img="{{ asset('uploads/kyc/'.$kyc->document_secound_part_photo) }}"
                                        style="cursor:pointer"
                                    >
                                @else
                                    N/A
                                @endif
                            </td>

                            <td>
                                <span class="badge bg-success">
                                    Approved
                                </span>
                            </td>

                            <td>
                                {{ $kyc->updated_at->format('d M, Y') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            @endif

        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="approvedImageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">KYC Document Preview</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="approvedPreviewImage" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<!-- Live Search Script -->
<script>
document.getElementById('approvedKycSearch').addEventListener('keyup', function () {
    let value = this.value.toLowerCase();
    document.querySelectorAll('#approvedKycTable tbody tr').forEach(row => {
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
        document.getElementById('approvedPreviewImage').src = this.dataset.img;
        new bootstrap.Modal(
            document.getElementById('approvedImageModal')
        ).show();
    });
});
</script>

@endsection
