@extends('agent.master')

@section('content')
<main class="dashboard-main">
    <div class="chat-wrapper d-flex">
        <!-- Sidebar -->
        <div class="chat-sidebar card" id="usersSidebar">
            <div class="chat-search p-2">
                <input type="text" id="searchUserInput" placeholder="Search users..." class="form-control">
            </div>
            <div class="chat-all-list" id="userList">
                @foreach($users as $user)
                    <div class="chat-sidebar-single user-item d-flex align-items-center justify-content-between"
                         data-user-id="{{ $user->id }}"
                         data-name="{{ $user->name }}">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $user->profile_photo_url ? asset('uploads/users/'.$user->profile_photo_url) : 'https://i.pravatar.cc/150?img=' . rand(1,70) }}"
                                 alt="User"
                                 class="rounded-circle"
                                 style="width:40px; height:40px;">
                            <div>
                                <h6 class="mb-0">{{ $user->name }}</h6>
                                <p class="mb-0 text-xs" id="lastMsg-{{ $user->id }}">Click to start chat</p>
                            </div>
                        </div>
                        <span class="badge bg-danger unread-count" id="badge-{{ $user->id }}">0</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Chat panel -->
        <div class="chat-main card flex-grow-1" id="chatPanel">
            <div class="chat-header d-flex align-items-center p-2 border-bottom">
                <div class="chat-user-info d-flex align-items-center gap-2">
                    <img src="" id="chatAvatar" class="rounded-circle" style="width:40px; height:40px;">
                    <div>
                        <h6 id="chatUserName" class="mb-0"></h6>
                        <small id="typingIndicator" class="text-muted d-none">typing...</small>
                    </div>
                </div>
            </div>

            <div class="chat-message-list p-3" id="chatMessages" style="height:400px; overflow-y:auto;"></div>

            <div class="chat-message-box p-2 border-top d-flex gap-2">
                <input type="file" id="imageInput" accept="image/*" style="display:none;" onchange="handleImageSelect(event)">
                <button type="button" class="btn btn-outline-success" onclick="document.getElementById('imageInput').click()">
                    <i class="fas fa-paperclip"></i>
                </button>
                <input type="text" id="messageInput" class="form-control" placeholder="Type a message...">
                <button type="button" class="btn btn-primary" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</main>

<script>
    let selectedImage = null;
    let currentUserId = null;

    // Image select handler
    function handleImageSelect(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            selectedImage = file;
        }
    }

    // Open chat with selected user
    function openChat(userId, userName, avatarSrc) {
        currentUserId = userId;
        document.getElementById('chatUserName').textContent = userName;
        document.getElementById('chatAvatar').src = avatarSrc;
        loadChatHistory();
    }

    // Load chat messages
    function loadChatHistory() {
        if (!currentUserId) return;

        fetch('{{ route("agent.chat.messages") }}?receiver_id=' + currentUserId)
            .then(res => res.json())
            .then(data => {
                const chatMessages = document.getElementById('chatMessages');
                chatMessages.innerHTML = '';

                data.forEach(msg => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'd-flex mb-2 ' + (msg.sender_id == {{ Auth::id() }} ? 'justify-content-end' : 'justify-content-start');

                    let content = '';
                    if (msg.image) {
                        content += `<img src="/${msg.image}" class="rounded mb-1" style="max-width:150px;">`;
                    }
                    if (msg.message) {
                        content += `<div class="p-2 bg-light rounded">${msg.message}</div>`;
                    }

                    wrapper.innerHTML = content;
                    chatMessages.appendChild(wrapper);
                });

                chatMessages.scrollTop = chatMessages.scrollHeight;
            });
    }

    // Send message
    function sendMessage() {
        if (!currentUserId) return;

        const msgInput = document.getElementById('messageInput');
        const msgText = msgInput.value.trim();

        if (!msgText && !selectedImage) return;

        const formData = new FormData();
        formData.append('receiver_id', currentUserId);
        formData.append('message', msgText);
        if (selectedImage) {
            formData.append('image', selectedImage);
        }

        fetch('{{ route("agent.chat.send") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            msgInput.value = '';
            selectedImage = null;
            loadChatHistory();
            updateUnreadCounts();
        });
    }

    // Update unread message badges
    function updateUnreadCounts() {
        fetch('{{ route("agent.chat.unread") }}')
            .then(res => res.json())
            .then(data => {
                document.querySelectorAll('.unread-count').forEach(badge => {
                    const uid = badge.id.split('-')[1];
                    badge.textContent = data[uid] || 0;
                });
            });
    }

    // User item click handler
    document.querySelectorAll('.user-item').forEach(item => {
        item.addEventListener('click', function() {
            const uid = this.dataset.userId;
            const uname = this.dataset.name;
            const avatar = this.querySelector('img').src;
            openChat(uid, uname, avatar);
            this.querySelector('.unread-count').textContent = 0;
        });
    });

    // Search users
    document.getElementById('searchUserInput').addEventListener('input', function() {
        const term = this.value.toLowerCase();
        document.querySelectorAll('.user-item').forEach(item => {
            item.style.display = item.dataset.name.toLowerCase().includes(term) ? 'flex' : 'none';
        });
    });

    // Poll for unread messages every 10 seconds
    setInterval(updateUnreadCounts, 10000);
</script>
@endsection
