@extends('frontend.master')

@section('content')
<div class="container-fluid my-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card passwordChange-card border-0 shadow-lg p-4" style="width:372px; justify-content:center; margin-right:-27px;">

                <!-- Profile Update Form -->
                <form enctype="multipart/form-data" method="POST" action="{{ route('frontend.profile.update') }}">
                    @csrf

                    <!-- Profile Picture Section -->
                    <div class="text-center mb-3">
                        <div class="position-relative d-inline-block passwordChange-profile-wrapper">
                          <img
                                src="{{ Auth::user()->photo ? asset('uploads/profile/' . Auth::user()->photo) : asset('uploads/logo.png') }}"
                                class="rounded-circle border border-3 border-primary passwordChange-profile-pic"
                                id="passwordChange-profileImage"
                            />
                            <label for="passwordChange-photo" class="position-absolute bottom-0 end-0 bg-primary rounded-circle text-white p-2 passwordChange-upload-btn" style="cursor:pointer;">
                                <i class="fas fa-camera"></i>
                                <input type="file" id="passwordChange-photo" name="photo" accept="image/*" class="d-none" onchange="previewProfileImage(event)">
                            </label>
                        </div>

                        <h5 class="fw-bold mt-3" id="passwordChange-username">{{ Auth::user()->name }}</h5>
                    </div>

                    <!-- Name Input -->
                    <div class="mb-3">
                        <label for="passwordChange-name" class="form-label fw-semibold">Full Name</label>
                        <input type="text" class="form-control" id="passwordChange-name" name="name" placeholder="Enter your name" value="{{ Auth::user()->name }}">
                    </div>

                    <!-- Email Input -->
                    <div class="mb-3">
                        <label for="Email" class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" id="Email" name="email" placeholder="Enter your email" value="{{ Auth::user()->email }}">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-100 py-2 passwordChange-btn fw-bold">Update Profile</button>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- Live Preview Script -->
<script>
function previewProfileImage(event) {
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById('passwordChange-profileImage');
        output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
@endsection
