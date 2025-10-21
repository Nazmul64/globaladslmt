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

// বর্তমান request থেকে query নেওয়া
$query = request()->get('query');

if ($query) {
    $users = User::where('name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->get();
} else {
    $users = collect(); // প্রথমে কিছু দেখাবে না
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
        <div class="friendrequest-card">
            <img src="{{ $user->photo ?? 'https://via.placeholder.com/150' }}" alt="{{ $user->name }}" class="friendrequest-image">
            <div class="friendrequest-info">
                <div class="friendrequest-name">{{ $user->name }}</div>
                <div class="friendrequest-email text-muted">{{ $user->email }}</div>
                <div class="friendrequest-button-group">
                    <button class="friendrequest-btn friendrequest-btn-confirm">Confirm</button>
                    <button class="friendrequest-btn friendrequest-btn-delete">Delete</button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            $('#friendSection').slideUp(); // খালি হলে হাইড
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
                $('#friendSection').slideDown(); // সার্চ করলে স্মুথলি শো
            }
        });
    });

});
</script>
