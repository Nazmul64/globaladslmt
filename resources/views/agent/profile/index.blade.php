@extends('agent.master')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
         <h5>Profile Update</h5>
        <div class="col-md-12">
            <div class="card border-0 shadow p-4 text-center">
                @php
                    $user = auth()->user();
                @endphp

                <!-- Profile Update Form -->
                <form action="{{ route('agent.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <!-- Profile Photo -->
                    <div class="mb-3">
                        <img id="profilePreview"
                             src="{{ asset('uploads/agent/' . ($user->photo ?? 'avator.jpg')) }}"
                             alt="Profile Photo"
                             class="rounded-circle border border-3 border-primary"
                             style="width: 120px; height: 120px; object-fit: cover;">
                        <input type="file" class="form-control mt-2" name="photo" id="photoInput">
                    </div>

                    <!-- Name -->
                    <div class="mb-3 text-start">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="{{ $user->name ?? '' }}">
                    </div>

                    <!-- Email -->
                    <div class="mb-3 text-start">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="{{ $user->email ?? '' }}">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Image Preview -->
<script>
    const photoInput = document.getElementById('photoInput');
    const profilePreview = document.getElementById('profilePreview');

    photoInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                profilePreview.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection
