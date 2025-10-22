<div class="top-header">
    <div class="header-content">
        <i class="fas fa-bars menu-icon" onclick="toggleSidebar()"></i>
        <div class="app-title">Global Money Ltd</div>

        <!-- Search Bar -->
        <div class="search-bar">
            <form id="searchForm" onsubmit="return false;">
                <input type="text" class="search-input" id="searchInput" name="query" placeholder="" autocomplete="off">
                <button type="button" class="search-button" onclick="performSearch()">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <i class="fas fa-bell notification-icon" onclick="showNotification()"></i>
    </div>
</div>

@php
use App\Models\User;

$query = request()->get('query');

if ($query) {
    $users = User::where('name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->get();
} else {
    $users = collect();
}
@endphp

<!-- Search Results -->
<div class="friendrequest-container mt-4" id="friendSection" style="display: none;">
    <div class="friendrequest-header">
        <h2>Friend Requests</h2>
        <a href="#" class="friendrequest-see-all">See all</a>
    </div>

    <div class="friendrequest-grid" id="resultList">
        @foreach($users as $user)
            @php
                $sentRequest = \App\Models\ChatRequest::where('sender_id', auth()->id())->where('receiver_id', $user->id)->first();
            @endphp
            <div class="friendrequest-card">
            @if(!empty($user->photo))
                <img src="{{ asset('uploads/profile/' . $user->photo) }}" class="friendrequest-image">
            @else
                <img src="{{ asset('uploads/logo.png') }}"
                    alt="Default Logo"
                    class="friendrequest-image">
            @endif
                <div class="friendrequest-info">
                    <div class="friendrequest-name">{{ $user->name }}</div>
                    <div class="friendrequest-button-group">
                        @if($sentRequest)
                            <button class="friendrequest-btn friendrequest-btn-cancel"
                                    onclick="cancelFriendRequest({{ $user->id }}, this)">Cancel</button>
                        @else
                            <button class="friendrequest-btn friendrequest-btn-add"
                                    onclick="sendFriendRequest({{ $user->id }}, this)">Add Friend</button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Include simple toastr notification -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(document).ready(function(){

    // Typing Animation
    const searchInput = document.getElementById('searchInput');
    const text = "Search your friend";
    let index = 0, isDeleting = false, typingTimeout;

    function typeWriter() {
        if (!isDeleting && index <= text.length) {
            searchInput.placeholder = text.substring(0, index);
            index++;
            typingTimeout = setTimeout(typeWriter, 150);
        } else if (isDeleting && index >= 0) {
            searchInput.placeholder = text.substring(0, index);
            index--;
            typingTimeout = setTimeout(typeWriter, 100);
        } else {
            if (!isDeleting) {
                typingTimeout = setTimeout(() => { isDeleting = true; typeWriter(); }, 2000);
            } else {
                isDeleting = false; index = 0;
                typingTimeout = setTimeout(typeWriter, 500);
            }
        }
    }
    window.addEventListener('load', () => typeWriter());
    searchInput.addEventListener('focus', () => { clearTimeout(typingTimeout); searchInput.placeholder = "Search your friend"; });

    // AJAX Search
    $('#searchInput').on('keyup', function() {
        let query = $(this).val().trim();

        if (query === '') {
            $('#friendSection').slideUp();
            $('#resultList').html('');
            return;
        }

        $.ajax({
            url: "{{ url()->current() }}",
            type: 'GET',
            data: { query: query },
            success: function(html) {
                let newHtml = $(html).find('#resultList').html();
                $('#resultList').html(newHtml);
                $('#friendSection').slideDown();
            }
        });
    });

});

// Send Friend Request
function sendFriendRequest(userId, btn) {
    $.ajax({
        url: "{{ route('user.friend.request') }}",
        type: 'POST',
        data: {
            receiver_id: userId,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            // Change button to Cancel
            $(btn).removeClass('friendrequest-btn-add')
                  .addClass('friendrequest-btn-cancel')
                  .text('Cancel')
                  .attr('onclick', 'cancelFriendRequest(' + userId + ', this)');

            // Show notification
            toastr.success('Your friend request is successful. Please wait for your friend to accept.');
        },
        error: function(xhr) {
            toastr.error(xhr.responseJSON.message || 'Something went wrong.');
        }
    });
}

// Cancel Friend Request
function cancelFriendRequest(userId, btn) {
    $.ajax({
        url: "{{ route('user.friend.request.cancel') }}",
        type: 'POST',
        data: {
            receiver_id: userId,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            $(btn).removeClass('friendrequest-btn-cancel')
                  .addClass('friendrequest-btn-add')
                  .text('Add Friend')
                  .attr('onclick', 'sendFriendRequest(' + userId + ', this)');
            toastr.info('Friend request cancelled.');
        },
        error: function(xhr) {
            toastr.error(xhr.responseJSON.message || 'Something went wrong.');
        }
    });
}
</script>

<style>
.friendrequest-btn-add {
    background-color: #007bff;
    color: #fff;
}

.friendrequest-btn-cancel {
    background-color: #dc3545; /* Red color */
    color: #fff;
}
</style>
