@extends('admin.master')

@section('content')
<div class="container-fluid my-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card border-0 shadow-lg p-4" style="width:372px; margin:auto;">
                <h5 class="text-center mb-3 fw-bold">KYC Verification</h5>

                @php
                    $kyc = Auth::user()->agentkyc ?? null;
                @endphp

                {{-- Show KYC Status --}}
                @if($kyc)
                    <div class="mb-3 text-center">
                        <h6>Your KYC Status:</h6>
                        @if($kyc->status == 'pending')
                            <span class="badge bg-warning text-dark">Pending</span>
                        @elseif($kyc->status == 'approved')
                            <span class="badge bg-success">Verified</span>
                        @else
                            <span class="badge bg-danger">Rejected</span>
                        @endif
                    </div>
                @endif

                {{-- KYC Form (Only if rejected or not submitted) --}}
                @if(!$kyc || $kyc->status == 'rejected')
                <form method="POST" action="{{ route('agent.key.submit') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Document Type -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Document Type</label>
                        <select name="document_type" class="form-control" required>
                            <option value="">-- Select Document Type --</option>
                            <option value="passport">Passport</option>
                            <option value="nid">NID</option>
                            <option value="driving_license">Driving License</option>
                        </select>
                        @error('document_type')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Front Side Photo -->
                    <div class="text-center mb-3">
                        <label class="form-label fw-semibold">Front Side Photo</label>
                        <img src="{{ asset('uploads/avator.jpg') }}" id="firstPhotoPreview"
                             class="rounded-circle border border-3 border-primary"
                             style="width:6rem;height:6rem;object-fit:cover;" />
                        <input type="file" name="document_first_part_photo"
                               class="form-control mt-2"
                               accept="image/*"
                               onchange="previewImage(event, 'firstPhotoPreview')" required>
                        @error('document_first_part_photo')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Back Side Photo -->
                    <div class="text-center mb-3">
                        <label class="form-label fw-semibold">Back Side Photo</label>
                        <img src="{{ asset('uploads/avator.jpg') }}" id="secondPhotoPreview"
                             class="rounded-circle border border-3 border-primary"
                             style="width:6rem;height:6rem;object-fit:cover;" />
                        <input type="file" name="document_secound_part_photo"
                               class="form-control mt-2"
                               accept="image/*"
                               onchange="previewImage(event, 'secondPhotoPreview')" required>
                        @error('document_secound_part_photo')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Submit KYC</button>
                </form>
                @else
                    <div class="text-center mt-3">
                        <span class="text-muted">You cannot upload again until admin reviews your KYC.</span>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<script>
function previewImage(event, previewId) {
    const reader = new FileReader();
    reader.onload = function(){
        document.getElementById(previewId).src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
@endsection
