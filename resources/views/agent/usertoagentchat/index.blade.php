@extends('agent.master')

@section('content')
<style>
    /* Root Variables */
    :root {
        --primary-color: #6366f1;
        --primary-dark: #4f46e5;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --bg-light: #f8fafc;
        --bg-card: #ffffff;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --border-color: #e2e8f0;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
        --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
    }

    /* Reset Bootstrap conflicts */
    .chat-container * {
        box-sizing: border-box;
    }

    /* Main Container */
    .chat-container {
        background: var(--bg-light);
        min-height: 100vh;
        padding: 0;
        margin: 0;
        width: 100%;
    }

    /* Chat Wrapper */
    .chat-wrapper {
        display: flex;
        width: 100%;
        height: calc(100vh - 60px);
        overflow: hidden;
        background: var(--bg-light);
    }

    /* ===== SIDEBAR ===== */
    .chat-sidebar {
        width: 300px;
        min-width: 300px;
        background: white;
        display: flex;
        flex-direction: column;
        height: 100%;
        border-right: 1px solid var(--border-color);
        flex-shrink: 0;
    }

    /* Search Header */
    .chat-search {
        padding: 14px;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        flex-shrink: 0;
    }

    .chat-search input {
        width: 100%;
        padding: 10px 14px;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        background: rgba(255, 255, 255, 0.95);
    }

    .chat-search input:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
    }

    /* User List Container */
    .chat-all-list {
        flex: 1;
        overflow-y: auto;
        padding: 8px;
        background: white;
    }

    .chat-all-list::-webkit-scrollbar {
        width: 5px;
    }

    .chat-all-list::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 10px;
    }

    /* User Item */
    .user-item {
        padding: 12px;
        margin-bottom: 6px;
        border-radius: 10px;
        cursor: pointer;
        background: white;
        border: 1px solid transparent;
        display: flex !important;
        align-items: center;
        transition: all 0.2s ease;
        position: relative;
    }

    .user-item:hover {
        background: var(--bg-light);
        border-color: var(--border-color);
        transform: translateX(3px);
    }

    .user-item.active {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(79, 70, 229, 0.05));
        border-color: var(--primary-color);
    }

    /* New Message Highlight */
    .user-item.has-new-message {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(16, 185, 129, 0.03));
        border-color: var(--success-color);
        animation: pulse-border 2s ease-in-out infinite;
    }

    @keyframes pulse-border {
        0%, 100% {
            border-color: var(--success-color);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
        }
        50% {
            border-color: var(--success-color);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0);
        }
    }

    /* Avatar Container */
    .user-avatar-container {
        position: relative;
        width: 45px;
        height: 45px;
        min-width: 45px;
        flex-shrink: 0;
        margin-right: 10px;
    }

    /* Avatar */
    .user-item img {
        width: 45px;
        height: 45px;
        border: 2px solid var(--bg-light);
    }

    /* Unread Badge */
    .unread-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background: var(--danger-color);
        color: white;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 10px;
        min-width: 18px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        display: none;
        animation: bounce-in 0.3s ease;
    }

    .unread-badge.show {
        display: block;
    }

    @keyframes bounce-in {
        0% {
            transform: scale(0);
        }
        50% {
            transform: scale(1.2);
        }
        100% {
            transform: scale(1);
        }
    }

    /* User Info */
    .user-info {
        flex: 1;
        min-width: 0;
    }

    .user-info h6 {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 3px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-info p {
        font-size: 12px;
        color: var(--text-secondary);
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-info p.new-message {
        color: var(--success-color);
        font-weight: 600;
    }

    /* ===== CHAT MAIN ===== */
    .chat-main {
        flex: 1;
        background: white;
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }

    /* Chat Header */
    .chat-header {
        padding: 16px 20px;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        flex-shrink: 0;
        display: flex;
        align-items: center;
    }

    .chat-user-info {
        flex: 1;
        display: flex;
        align-items: center;
    }

    .chat-header img {
        width: 45px;
        height: 45px;
        min-width: 45px;
        border: 3px solid rgba(255, 255, 255, 0.3);
        margin-right: 12px;
    }

    .chat-header h6 {
        font-size: 17px;
        font-weight: 600;
        color: white;
        margin: 0;
    }

    .chat-header small {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.8);
        font-style: italic;
    }

    /* Back Button */
    .back-btn-mobile {
        display: none;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        margin-right: 10px;
    }

    .back-btn-mobile:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* Messages Area */
    .chat-message-list {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: linear-gradient(to bottom, #f8fafc, #ffffff);
    }

    .chat-message-list::-webkit-scrollbar {
        width: 7px;
    }

    .chat-message-list::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 10px;
    }

    /* Messages */
    .message-item {
        margin-bottom: 14px;
        display: flex;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .message-item.sent {
        justify-content: flex-end;
    }

    .message-item.received {
        justify-content: flex-start;
    }

    .message-bubble {
        padding: 11px 15px;
        border-radius: 14px;
        max-width: 70%;
        word-wrap: break-word;
    }

    .message-item.sent .message-bubble {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        border-bottom-right-radius: 4px;
    }

    .message-item.received .message-bubble {
        background: white;
        color: var(--text-primary);
        border: 1px solid var(--border-color);
        border-bottom-left-radius: 4px;
    }

    .message-item img {
        max-width: 240px;
        border-radius: 10px;
        cursor: pointer;
    }

    /* Message Input */
    .chat-message-box {
        padding: 16px 20px;
        background: white;
        border-top: 1px solid var(--border-color);
        flex-shrink: 0;
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .chat-message-box input[type="text"] {
        flex: 1;
        padding: 11px 16px;
        border: 2px solid var(--border-color);
        border-radius: 10px;
        font-size: 14px;
    }

    .chat-message-box input[type="text"]:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .chat-message-box .btn {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: none;
        cursor: pointer;
    }

    .btn-attach {
        background: var(--success-color);
        color: white;
    }

    .btn-send {
        background: var(--primary-color);
        color: white;
    }

    .btn-attach:hover {
        background: #059669;
    }

    .btn-send:hover {
        background: var(--primary-dark);
    }

    /* Empty State */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        text-align: center;
        padding: 40px;
    }

    .empty-state i {
        font-size: 60px;
        margin-bottom: 18px;
        opacity: 0.3;
        color: var(--text-secondary);
    }

    .empty-state h5 {
        font-size: 19px;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--text-primary);
    }

    .empty-state p {
        font-size: 14px;
        color: var(--text-secondary);
    }

    /* Hidden class */
    .d-none {
        display: none !important;
    }

    /* Notification Toast */
    .notification-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: none;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        border-left: 4px solid var(--success-color);
        max-width: 300px;
    }

    .notification-toast.show {
        display: block;
    }

    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .notification-toast .toast-header {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }

    .notification-toast .toast-header img {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        margin-right: 10px;
    }

    .notification-toast .toast-header strong {
        font-size: 14px;
        color: var(--text-primary);
    }

    .notification-toast .toast-body {
        font-size: 13px;
        color: var(--text-secondary);
    }

    /* ===== MOBILE ===== */
    @media (max-width: 768px) {
        .chat-wrapper {
            height: 100vh;
        }

        .chat-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            min-width: 100%;
            height: 100vh;
            z-index: 1000;
            transform: translateX(0);
            transition: transform 0.3s ease;
        }

        .chat-sidebar.hidden {
            transform: translateX(-100%);
        }

        .chat-main {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            z-index: 1001;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }

        .chat-main.active {
            transform: translateX(0);
        }

        .back-btn-mobile {
            display: flex !important;
        }

        .message-bubble {
            max-width: 85%;
        }

        .chat-message-list {
            padding: 14px;
        }

        .chat-message-box {
            padding: 12px 14px;
            gap: 8px;
        }

        .chat-message-box input[type="text"] {
            font-size: 16px;
        }

        .chat-message-box .btn {
            width: 38px;
            height: 38px;
            min-width: 38px;
        }

        .notification-toast {
            top: 10px;
            right: 10px;
            max-width: calc(100% - 20px);
        }
    }
</style>

<main class="chat-container">
    <!-- Notification Toast -->
    <div class="notification-toast" id="notificationToast">
        <div class="toast-header">
            <img src="" alt="Avatar" id="toastAvatar">
            <strong id="toastName"></strong>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>

    <!-- Audio for notification sound -->
    <audio id="notificationSound" preload="auto">
        <source src="https://cdn.freesound.org/previews/316/316847_4939433-lq.mp3" type="audio/mpeg">
    </audio>

    <div class="chat-wrapper">
        <!-- ===== SIDEBAR ===== -->
        <div class="chat-sidebar" id="usersSidebar">
            <!-- Search -->
            <div class="chat-search">
                <input type="text"
                       id="searchUserInput"
                       placeholder="Search users...">
            </div>

            <!-- User List -->
            <div class="chat-all-list" id="userList">
                @foreach($users as $user)
                    <div class="user-item"
                         data-user-id="{{ $user->id }}"
                         data-name="{{ strtolower($user->name) }}"
                         data-last-message-time="0">
                        <div class="user-avatar-container">
                            <img src="{{ $user->photo ? asset('uploads/profile/'.$user->photo) : 'https://i.pravatar.cc/150?img=' . rand(1,70) }}"
                                 alt="{{ $user->name }}"
                                 class="rounded-circle">
                            <span class="unread-badge" id="unread-{{ $user->id }}">0</span>
                        </div>
                        <div class="user-info">
                            <h6>{{ $user->name }}</h6>
                            <p id="lastMsg-{{ $user->id }}">Click to start chat</p>
                        </div>
                    </div>
                @endforeach

                <!-- No Results -->
                <div class="empty-state" id="noResults" style="display: none; padding: 50px 15px;">
                    <i class="fas fa-user-slash"></i>
                    <h5>No users found</h5>
                    <p>Try a different name</p>
                </div>
            </div>
        </div>

        <!-- ===== CHAT PANEL ===== -->
        <div class="chat-main" id="chatPanel">
            <!-- Header -->
            <div class="chat-header">
                <button class="back-btn-mobile" id="backButton" onclick="closeChat()">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="chat-user-info">
                    <img src=""
                         id="chatAvatar"
                         class="rounded-circle d-none">
                    <div>
                        <h6 id="chatUserName"></h6>
                        <small id="typingIndicator" class="d-none">typing...</small>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div class="chat-message-list" id="chatMessages">
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h5>Select a chat</h5>
                    <p>Choose a user from the list to start messaging</p>
                </div>
            </div>

            <!-- Input -->
            <div class="chat-message-box">
                <input type="file" id="imageInput" accept="image/*" style="display:none;">
                <button type="button"
                        class="btn btn-attach"
                        onclick="document.getElementById('imageInput').click()">
                    <i class="fas fa-paperclip"></i>
                </button>
                <input type="text"
                       id="messageInput"
                       placeholder="Type a message..."
                       autocomplete="off">
                <button type="button"
                        class="btn btn-send"
                        onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</main>

<script>
    let selectedImage = null;
    let currentUserId = null;
    let currentUserName = null;
    let lastMessageIds = {};

    // Image handler
    document.getElementById('imageInput').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            selectedImage = file;
            console.log('Image selected:', file.name);
        }
    });

    // Open chat
    function openChat(userId, userName, avatarSrc) {
        currentUserId = userId;
        currentUserName = userName;

        document.getElementById('chatUserName').textContent = userName;
        const chatAvatar = document.getElementById('chatAvatar');
        chatAvatar.src = avatarSrc;
        chatAvatar.classList.remove('d-none');

        // Mobile handling
        const chatPanel = document.getElementById('chatPanel');
        const sidebar = document.getElementById('usersSidebar');

        if (window.innerWidth <= 768) {
            chatPanel.classList.add('active');
            sidebar.classList.add('hidden');
        }

        // Active state
        document.querySelectorAll('.user-item').forEach(item => {
            item.classList.remove('active');
        });
        const activeItem = document.querySelector(`[data-user-id="${userId}"]`);
        if (activeItem) {
            activeItem.classList.add('active');
            activeItem.classList.remove('has-new-message');
        }

        // Clear unread badge
        const unreadBadge = document.getElementById(`unread-${userId}`);
        if (unreadBadge) {
            unreadBadge.textContent = '0';
            unreadBadge.classList.remove('show');
        }

        loadChatHistory();

        // Mark messages as read
        markMessagesAsRead(userId);

        // Focus
        setTimeout(() => {
            document.getElementById('messageInput').focus();
        }, window.innerWidth <= 768 ? 300 : 0);
    }

    // Close chat
    function closeChat() {
        const chatPanel = document.getElementById('chatPanel');
        const sidebar = document.getElementById('usersSidebar');
        chatPanel.classList.remove('active');
        sidebar.classList.remove('hidden');
        currentUserId = null;
    }

    // Load messages
    function loadChatHistory() {
        if (!currentUserId) return;

        fetch('{{ route("agent.chat.messages") }}?receiver_id=' + currentUserId)
            .then(res => res.json())
            .then(data => {
                const chatMessages = document.getElementById('chatMessages');
                chatMessages.innerHTML = '';

                if (data.length === 0) {
                    chatMessages.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-comment-dots"></i>
                            <h5>No messages yet</h5>
                            <p>Start the conversation</p>
                        </div>
                    `;
                    return;
                }

                data.forEach(msg => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'message-item ' + (msg.sender_id == {{ Auth::id() }} ? 'sent' : 'received');

                    let content = '<div class="message-bubble">';
                    if (msg.image) {
                        content += `<img src="/${msg.image}" alt="Image">`;
                    }
                    if (msg.message) {
                        content += escapeHtml(msg.message);
                    }
                    content += '</div>';

                    messageDiv.innerHTML = content;
                    chatMessages.appendChild(messageDiv);
                });

                setTimeout(() => {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }, 100);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    // Send message
    function sendMessage() {
        if (!currentUserId) {
            alert('Please select a user first');
            return;
        }

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
            document.getElementById('imageInput').value = '';
            loadChatHistory();

            const lastMsgElement = document.getElementById(`lastMsg-${currentUserId}`);
            if (lastMsgElement) {
                lastMsgElement.textContent = msgText || 'Sent an image';
                lastMsgElement.classList.remove('new-message');
            }

            // Move to top
            moveUserToTop(currentUserId);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to send message');
        });
    }

    // Enter key
    document.getElementById('messageInput').addEventListener('keypress', function(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    });

    // Mark messages as read
    function markMessagesAsRead(userId) {
        fetch('{{ route("agent.chat.mark-read") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ sender_id: userId })
        })
        .catch(error => {
            console.error('Error marking as read:', error);
        });
    }

    // Check for new messages
    function checkNewMessages() {
        fetch('{{ route("agent.chat.check-new") }}')
            .then(res => res.json())
            .then(data => {
                data.forEach(msg => {
                    const userId = msg.sender_id;

                    // Skip if already processed
                    if (lastMessageIds[userId] === msg.id) return;
                    lastMessageIds[userId] = msg.id;

                    // Update last message
                    const lastMsgElement = document.getElementById(`lastMsg-${userId}`);
                    if (lastMsgElement) {
                        lastMsgElement.textContent = msg.message || 'Sent an image';
                        lastMsgElement.classList.add('new-message');
                    }

                    // Update unread badge
                    const unreadBadge = document.getElementById(`unread-${userId}`);
                    if (unreadBadge) {
                        const currentCount = parseInt(unreadBadge.textContent) || 0;
                        unreadBadge.textContent = currentCount + 1;
                        unreadBadge.classList.add('show');
                    }

                    // Add highlight to user item
                    const userItem = document.querySelector(`[data-user-id="${userId}"]`);
                    if (userItem && !userItem.classList.contains('active')) {
                        userItem.classList.add('has-new-message');
                    }

                    // Move user to top
                    moveUserToTop(userId);

                    // Show notification if not current chat
                    if (currentUserId != userId) {
                        showNotification(msg.sender_name, msg.message || 'Sent an image', msg.sender_photo);
                        playNotificationSound();
                    } else {
                        // If current chat, reload messages
                        loadChatHistory();
                        markMessagesAsRead(userId);
                    }
                });
            })
            .catch(error => {
                console.error('Error checking new messages:', error);
            });
    }

    // Move user to top of list
    function moveUserToTop(userId) {
        const userList = document.getElementById('userList');
        const userItem = document.querySelector(`[data-user-id="${userId}"]`);

        if (userItem && userList) {
            userItem.dataset.lastMessageTime = Date.now();
            userList.insertBefore(userItem, userList.firstChild);
        }
    }

    // Show notification toast
    function showNotification(userName, message, userPhoto) {
        const toast = document.getElementById('notificationToast');
        const toastName = document.getElementById('toastName');
        const toastMessage = document.getElementById('toastMessage');
        const toastAvatar = document.getElementById('toastAvatar');

        toastName.textContent = userName;
        toastMessage.textContent = message.substring(0, 50) + (message.length > 50 ? '...' : '');
        toastAvatar.src = userPhoto || 'https://i.pravatar.cc/150?img=' + Math.floor(Math.random() * 70);

        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
        }, 4000);
    }

    // Play notification sound
    function playNotificationSound() {
        const sound = document.getElementById('notificationSound');
        sound.play().catch(error => {
            console.log('Could not play sound:', error);
        });
    }

    // Update unread counts
    function updateUnreadCounts() {
        fetch('{{ route("agent.chat.unread") }}')
            .then(res => res.json())
            .then(data => {
                Object.keys(data).forEach(userId => {
                    const count = data[userId];
                    const badge = document.getElementById(`unread-${userId}`);
                    if (badge) {
                        badge.textContent = count;
                        if (count > 0) {
                            badge.classList.add('show');
                        } else {
                            badge.classList.remove('show');
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    // Click handler
    document.querySelectorAll('.user-item').forEach(item => {
        item.addEventListener('click', function() {
            const uid = this.dataset.userId;
            const uname = this.querySelector('h6').textContent;
            const avatar = this.querySelector('img').src;
            openChat(uid, uname, avatar);
        });
    });

    // Search
    document.getElementById('searchUserInput').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const userItems = document.querySelectorAll('.user-item');
        const noResults = document.getElementById('noResults');
        let visibleCount = 0;

        userItems.forEach(item => {
            const userName = item.dataset.name;

            if (searchTerm === '' || userName.includes(searchTerm)) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (visibleCount === 0 && searchTerm !== '') {
            noResults.style.display = 'flex';
        } else {
            noResults.style.display = 'none';
        }
    });

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Polling - check every 3 seconds
    setInterval(checkNewMessages, 3000);
    setInterval(updateUnreadCounts, 5000);

    // Initial load
    updateUnreadCounts();

    // Resize handler
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 768) {
                document.getElementById('chatPanel').classList.remove('active');
                document.getElementById('usersSidebar').classList.remove('hidden');
            }
        }, 250);
    });
</script>
@endsection
