@extends('frontend.master')

@section('content')
<div class="container mt-5">
    <div class="friendrequest-container">
        <div class="friendrequest-header d-flex justify-content-between align-items-center mb-3">
            <h2>Friend Requests</h2>
            <a href="#" class="friendrequest-see-all">See all</a>
        </div>

        <div class="friendrequest-grid">
            @forelse ($requests as $request)
                <div class="friendrequest-card">
                   @if(isset($request->photo) && !empty($request->photo))
                        <img src="{{ asset('uploads/profile/' . $request->photo) }}"
                            alt="{{ $request->name ?? 'User' }}"
                            class="friendrequest-image">
                    @else
                        <img src="{{ asset('uploads/logo.png') }}"
                            alt="Default Logo"
                            class="friendrequest-image">
                    @endif


                    <div class="friendrequest-info">
                        <div class="friendrequest-name">{{ $request['name'] }}</div>
                        <div class="friendrequest-mutual-friends">
                            {{ $request['mutual_friends'] ?? 'No mutual friends' }}
                        </div>
                        <div class="friendrequest-button-group">
                            <button class="friendrequest-btn friendrequest-btn-confirm"
                                    onclick="acceptRequest({{ $request['id'] }}, this)">
                                Confirm
                            </button>
                            <button class="friendrequest-btn friendrequest-btn-delete"
                                    onclick="rejectRequest({{ $request['id'] }}, this)">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-muted">No friend requests found.</p>
            @endforelse
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function acceptRequest(senderId, btn) {
    $.ajax({
        url: "{{ route('user.friend.request.accept') }}",
        type: 'POST',
        data: {
            sender_id: senderId,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            alert(response.message);
            $(btn).closest('.friendrequest-card').fadeOut();
        },
        error: function(xhr) {
            alert(xhr.responseJSON?.message || 'Something went wrong.');
        }
    });
}

function rejectRequest(senderId, btn) {
    $.ajax({
        url: "{{ route('user.friend.request.reject') }}",
        type: 'POST',
        data: {
            sender_id: senderId,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            alert(response.message);
            $(btn).closest('.friendrequest-card').fadeOut();
        },
        error: function(xhr) {
            alert(xhr.responseJSON?.message || 'Something went wrong.');
        }
    });
}
</script>
@endsection
