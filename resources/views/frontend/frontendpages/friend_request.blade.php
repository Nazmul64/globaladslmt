
<div class="friendrequest-container mt-4">
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

    // AJAX Search
    $('#searchInput').on('keyup', function(){
        let query = $(this).val();

        $.ajax({
            url: "{{ route('frontend.request') }}",
            type: 'GET',
            data: {query: query},
            success: function(users) {
                let html = '';
                if(users.length > 0){
                    users.forEach(user => {
                        html += `
                        <div class="friendrequest-card">
                            <img src="${user.photo ?? 'https://via.placeholder.com/150'}" alt="${user.name}" class="friendrequest-image">
                            <div class="friendrequest-info">
                                <div class="friendrequest-name">${user.name}</div>
                                <div class="friendrequest-email text-muted">${user.email}</div>
                                <div class="friendrequest-button-group">
                                    <button class="friendrequest-btn friendrequest-btn-confirm">Confirm</button>
                                    <button class="friendrequest-btn friendrequest-btn-delete">Delete</button>
                                </div>
                            </div>
                        </div>`;
                    });
                } else {
                    html = `<div class="text-muted">No friends found</div>`;
                }
                $('#resultList').html(html);
            }
        });
    });
});
</script>
