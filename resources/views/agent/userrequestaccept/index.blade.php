@extends('agent.master')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Friend Requests</h4>
            <a href="#" class="btn btn-light btn-sm">See all</a>
        </div>

        <div class="card-body">
            @if($requests->isEmpty())
                <p class="text-center text-muted">No friend requests found.</p>
            @else
                <div class="row">
                    @foreach ($requests as $request)
                        <div class="col-md-4 mb-4">
                            <div class="card friendrequest-card shadow-sm border-0 h-100">
                                <div class="card-body text-center">
                                    {{-- Profile Photo --}}
                                    @if(!empty($request['photo']))
                                        <img src="{{ asset('uploads/profile/' . $request['photo']) }}"
                                            alt="{{ $request['name'] }}"
                                            class="rounded-circle mb-3"
                                            width="100" height="100">
                                    @else
                                        <img src="{{ asset('uploads/logo.png') }}"
                                            alt="Default Logo"
                                            class="rounded-circle mb-3"
                                            width="100" height="100">
                                    @endif

                                    {{-- User Info --}}
                                    <h5 class="card-title mb-1">{{ $request['name'] }}</h5>
                                    <p class="text-muted small mb-3">{{ $request['email'] }}</p>
                                    <p class="text-muted mb-3">
                                        {{ $request['mutual_friends'] ?? 'No mutual friends' }}
                                    </p>

                                    {{-- Action Buttons --}}
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-success btn-sm"
                                            onclick="acceptRequest({{ $request['id'] }}, this)">
                                            <i class="fas fa-check-circle"></i> Confirm
                                        </button>
                                        <button class="btn btn-danger btn-sm"
                                            onclick="rejectRequest({{ $request['id'] }}, this)">
                                            <i class="fas fa-times-circle"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

{{-- jQuery --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function acceptRequest(senderId, btn) {
    $.ajax({
        url: "{{ route('agent.friend.request.accept') }}", // ✅ Update route name as per your route file
        type: 'POST',
        data: {
            sender_id: senderId,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            showToast(response.message, 'success');
            $(btn).closest('.col-md-4').fadeOut();
        },
        error: function(xhr) {
            showToast(xhr.responseJSON?.message || 'Something went wrong.', 'danger');
        }
    });
}

function rejectRequest(senderId, btn) {
    $.ajax({
        url: "{{ route('agent.friend.request.reject') }}", // ✅ Update route name as per your route file
        type: 'POST',
        data: {
            sender_id: senderId,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            showToast(response.message, 'warning');
            $(btn).closest('.col-md-4').fadeOut();
        },
        error: function(xhr) {
            showToast(xhr.responseJSON?.message || 'Something went wrong.', 'danger');
        }
    });
}

/**
 * Bootstrap Toast Notification (Custom)
 */
function showToast(message, type = 'info') {
    const toast = $(`
        <div class="toast align-items-center text-bg-${type} border-0 position-fixed bottom-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `);

    $('body').append(toast);
    const bsToast = new bootstrap.Toast(toast[0]);
    bsToast.show();

    toast.on('hidden.bs.toast', function () {
        toast.remove();
    });
}
</script>
@endsection
