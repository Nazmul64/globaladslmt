@extends('frontend.master')

@section('content')
<div class="container-fluid my-5 x-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card passwordChange-card border-0 shadow-lg p-4" style="width:372px; justify-content:center; margin-right:-27px;">

                @php
                    $kyc = Auth::user()->kyc ?? null;
                @endphp

                {{-- Show KYC Status --}}
                @if($kyc)
                    <div class="mb-3 text-center">
                        <h5>Your KYC Status:</h5>
                        @if($kyc->status == 'pending')
                            <span class="badge bg-warning text-dark">Pending</span>
                        @elseif($kyc->status == 'approved')
                            <span class="badge bg-success">Verified</span>
                        @else
                            <span class="badge bg-danger">Rejected</span>
                        @endif
                    </div>
                @endif

                {{-- Show form only if KYC is rejected or not submitted --}}
                @if(!$kyc || $kyc->status == 'rejected')
                <form enctype="multipart/form-data" method="POST" action="{{ route('frontend.key.submit') }}">
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
                            <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>

                    <!-- First Photo -->
                    <div class="text-center mb-3">
                        <label class="form-label fw-semibold">First Photo</label>
                        <img src="{{ asset('uploads/avator.jpg') }}" class="rounded-circle border border-3 border-primary passwordChange-profile-pic" id="firstPhotoPreview" style="width:6rem;height:6rem;object-fit:cover;" />
                        <input type="file" name="document_first_part_photo" accept="image/*" class="form-control mt-2" onchange="previewImage(event, 'firstPhotoPreview')" required>
                        @error('document_first_part_photo')
                            <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>

                    <!-- Second Photo -->
                    <div class="text-center mb-3">
                        <label class="form-label fw-semibold">Second Photo</label>
                        <img src="{{ asset('uploads/avator.jpg') }}" class="rounded-circle border border-3 border-primary passwordChange-profile-pic" id="secondPhotoPreview" style="width:6rem;height:6rem;object-fit:cover;" />
                        <input type="file" name="document_secound_part_photo" accept="image/*" class="form-control mt-2" onchange="previewImage(event, 'secondPhotoPreview')" required>
                        @error('document_secound_part_photo')
                            <span class="text-danger">{{$message}}</span>
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
