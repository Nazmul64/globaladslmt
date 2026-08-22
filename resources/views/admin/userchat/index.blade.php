@extends('admin.master')
@section('content')

{{-- ====================================================================
  ✅ ADMIN CHAT INTERFACE - WITH NOTIFICATION & SOUND
  ✅ Features: Real-time notification, Sound alert, Auto sorting
==================================================================== --}}

<style>
/* ====================================================================
   CUSTOM STYLES FOR CHAT INTERFACE
==================================================================== */
.chat-wrapper {
  height: calc(100vh - 200px);
  min-height: 600px;
}

.chat-sidebar {
  overflow-y: auto;
  max-height: calc(100vh - 200px);
}

.chat-main {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 200px);
}

@media (max-width: 768px) {
  .chat-wrapper {
    flex-direction: column !important;
    height: auto !important;
  }
  .chat-sidebar {
    width: 100% !important;
    max-height: 250px !important;
  }
  .chat-main {
    height: 500px !important;
  }
}

#chatBox {
  flex-grow: 1;
  overflow-y: auto;
  height: 100%;
  background: #f8f9fa;
  scroll-behavior: smooth;
}

.user-item {
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.user-item:hover {
  background: #e9ecef !important;
  transform: translateX(5px);
}

.user-item.active {
  background: #0d6efd !important;
  color: white !important;
}

/* ✅ NEW MESSAGE HIGHLIGHT */
.user-item.has-new-message {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(16, 185, 129, 0.03)) !important;
  border-left: 4px solid #28a745 !important;
  animation: pulse-border 2s ease-in-out infinite;
}

@keyframes pulse-border {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
  }
  50% {
    box-shadow: 0 0 0 4px rgba(40, 167, 69, 0);
  }
}

.message-bubble {
  max-width: 70%;
  word-wrap: break-word;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.message-bubble img {
  cursor: pointer;
  transition: transform 0.2s ease;
}

.message-bubble img:hover {
  transform: scale(1.05);
}

.unread-count {
  animation: pulse 1.5s infinite;
  position: absolute;
  top: 8px;
  right: 8px;
  min-width: 20px;
  height: 20px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: bold;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.7;
    transform: scale(1.1);
  }
}

.chat-input-area {
  background: white;
  border-top: 2px solid #dee2e6;
  padding: 15px;
}

.message-time {
  font-size: 11px;
  color: #6c757d;
  margin-top: 5px;
}

.admin-message {
  background: #e3f2fd !important;
  border-left: 3px solid #2196f3;
}

.user-message {
  background: #f1f3f5 !important;
  border-left: 3px solid #6c757d;
}

.user-message.unread {
  background: #d4edda !important;
  border-left: 3px solid #28a745;
  font-weight: 500;
}

.image-preview-modal {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.9);
  justify-content: center;
  align-items: center;
}

.image-preview-modal img {
  max-width: 90%;
  max-height: 90%;
  object-fit: contain;
}

.image-preview-close {
  position: absolute;
  top: 20px;
  right: 40px;
  color: white;
  font-size: 40px;
  font-weight: bold;
  cursor: pointer;
  z-index: 10000;
}

.image-preview-close:hover {
  color: #ff0000;
}

.typing-indicator {
  display: none;
  padding: 10px;
  font-style: italic;
  color: #6c757d;
}

.online-indicator {
  width: 10px;
  height: 10px;
  background: #28a745;
  border-radius: 50%;
  display: inline-block;
  margin-right: 5px;
}

/* ✅ NOTIFICATION TOAST */
.notification-toast {
  position: fixed;
  top: 80px;
  right: 20px;
  background: white;
  padding: 15px 20px;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.25);
  display: none;
  z-index: 9999;
  animation: slideInRight 0.3s ease;
  border-left: 4px solid #28a745;
  max-width: 350px;
  min-width: 300px;
}

.notification-toast.show {
  display: block;
}

@keyframes slideInRight {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.notification-toast .toast-header-custom {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
  padding-bottom: 8px;
  border-bottom: 1px solid #e9ecef;
}

.notification-toast .toast-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: 10px;
  object-fit: cover;
}

.notification-toast .toast-user-name {
  font-size: 15px;
  font-weight: 600;
  color: #212529;
}

.notification-toast .toast-body-custom {
  font-size: 13px;
  color: #6c757d;
  padding-left: 50px;
}

.notification-toast .close-toast {
  position: absolute;
  top: 10px;
  right: 10px;
  background: none;
  border: none;
  font-size: 20px;
  color: #6c757d;
  cursor: pointer;
  width: 25px;
  height: 25px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s;
}

.notification-toast .close-toast:hover {
  background: #f1f3f5;
  color: #212529;
}

/* ✅ NEW MESSAGE INDICATOR ON USER AVATAR */
.user-avatar-wrapper {
  position: relative;
  display: inline-block;
}

.new-message-dot {
  position: absolute;
  top: 0;
  right: 0;
  width: 12px;
  height: 12px;
  background: #28a745;
  border: 2px solid white;
  border-radius: 50%;
  display: none;
  animation: blink 1.5s infinite;
}

.new-message-dot.show {
  display: block;
}

@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}
</style>

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h6 class="fw-semibold mb-0">User Chats</h6>
        <small class="text-muted">Real-time messaging with users</small>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-secondary" onclick="refreshAllChats()">
          <i class="fas fa-sync-alt"></i> Refresh
        </button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-primary">
          <i class="fas fa-home"></i> Dashboard
        </a>
      </div>
    </div>

    <div class="chat-wrapper d-flex gap-3">
      {{-- ====================================================================
          SIDEBAR - USER LIST
      ==================================================================== --}}
      <div class="chat-sidebar card p-3" style="width:320px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="mb-0">
            <i class="fas fa-users"></i> Users
          </h6>
          <span class="badge bg-primary" id="totalUsers">{{ count($users) }}</span>
        </div>

        {{-- Search Users --}}
        <div class="mb-3">
          <input type="text" class="form-control form-control-sm" id="searchUsers"
                 placeholder="Search users...">
        </div>

        {{-- User List --}}
        <div id="userList">
          @forelse($users as $user)
          <div class="user-item p-2 border rounded mb-2"
               data-id="{{ $user->id }}"
               data-name="{{ $user->name }}"
               data-email="{{ $user->email }}"
               data-last-time="{{ $user->latest_message_time ?? 0 }}"
               data-last-id="{{ $user->latest_message_id ?? 0 }}">
            <div class="d-flex align-items-center">
              {{-- User Avatar --}}
              <div class="user-avatar-wrapper me-2">
                @php
                    $userAvatarSrc = (!empty($user->photo) && file_exists(public_path('uploads/profile/' . $user->photo)))
                        ? asset('uploads/profile/' . $user->photo)
                        : asset('uploads/profile/avator.jpg');
                @endphp
                <img src="{{ $userAvatarSrc }}"
                     onerror="this.onerror=null;this.src='{{ asset('uploads/profile/avator.jpg') }}';"
                     class="rounded-circle user-avatar"
                     width="35"
                     height="35"
                     style="object-fit: cover;">
                <span class="new-message-dot {{ ($user->unread_count ?? 0) > 0 ? 'show' : '' }}" id="dot-{{ $user->id }}"></span>
              </div>

              {{-- User Info --}}
              <div class="flex-grow-1" style="overflow: hidden;">
                <strong class="d-block user-name text-truncate">{{ $user->name }}</strong>
                <small class="text-muted user-last-msg d-block text-truncate">{{ $user->last_message_text ?: Str::limit($user->email, 20) }}</small>
              </div>

              {{-- Unread Badge --}}
              <span class="badge bg-success text-white unread-count"
                    id="unread-{{ $user->id }}"
                    style="{{ ($user->unread_count ?? 0) > 0 ? 'display:inline-flex;' : 'display:none;' }}">{{ $user->unread_count ?? 0 }}</span>
            </div>
          </div>
          @empty
          <div class="text-center text-muted py-3">
            <i class="fas fa-inbox fa-2x mb-2"></i>
            <p>No users found</p>
          </div>
          @endforelse
        </div>
      </div>

      {{-- ====================================================================
          CHAT AREA - MAIN CHAT INTERFACE
      ==================================================================== --}}
      <div class="chat-main card flex-grow-1 d-flex flex-column">
        {{-- Chat Header --}}
        <div class="border-bottom p-3 bg-light">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 id="chatUserName" class="mb-0">
                <i class="fas fa-comment-dots"></i> Select a user to start chatting
              </h6>
              <small class="text-muted" id="chatUserEmail"></small>
            </div>
            <div id="chatActions" style="display:none;">
              <button class="btn btn-sm btn-outline-secondary" onclick="clearChatHistory()">
                <i class="fas fa-trash"></i> Clear
              </button>
              <button class="btn btn-sm btn-outline-primary" onclick="downloadChatHistory()">
                <i class="fas fa-download"></i> Export
              </button>
            </div>
          </div>
        </div>

        {{-- Chat Messages Box --}}
        <div id="chatBox" class="p-3">
          <div class="text-center text-muted py-5">
            <i class="fas fa-comments fa-3x mb-3 text-secondary"></i>
            <h6>No chat selected</h6>
            <p>Select a user from the list to start messaging</p>
          </div>
        </div>

        {{-- Typing Indicator --}}
        <div class="typing-indicator px-3">
          <small><i class="fas fa-circle-notch fa-spin"></i> User is typing...</small>
        </div>

        {{-- Chat Input Area --}}
        <div class="chat-input-area">
          <form id="chatForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="receiver_id" id="receiver_id">

            <div class="d-flex gap-2 align-items-center">
              {{-- Message Input --}}
              <input type="text"
                     class="form-control"
                     name="message"
                     id="message"
                     placeholder="Type a message..."
                     autocomplete="off">

              {{-- Image Upload --}}
              <label for="chatImage" class="btn btn-outline-secondary mb-0" title="Upload Image">
                <i class="fas fa-image"></i>
              </label>
              <input type="file"
                     name="image"
                     id="chatImage"
                     class="d-none"
                     accept="image/*">

              {{-- Send Button --}}
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Send
              </button>
            </div>

            {{-- Image Preview --}}
            <div id="imagePreview" class="mt-2" style="display:none;">
              <div class="d-flex align-items-center gap-2 p-2 bg-light rounded">
                <img id="previewImg" src="" width="50" height="50" class="rounded">
                <span id="previewName" class="text-muted small"></span>
                <button type="button" class="btn btn-sm btn-danger ms-auto" onclick="removeImagePreview()">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
      </div>
    </div>

{{-- ====================================================================
    NOTIFICATION TOAST
==================================================================== --}}
<div class="notification-toast" id="notificationToast">
  <button class="close-toast" onclick="closeNotification()">&times;</button>
  <div class="toast-header-custom">
    <img src="" alt="Avatar" class="toast-avatar" id="toastAvatar">
    <div>
      <div class="toast-user-name" id="toastUserName"></div>
      <small class="text-muted">New message</small>
    </div>
  </div>
  <div class="toast-body-custom" id="toastMessage"></div>
</div>

{{-- ====================================================================
    IMAGE PREVIEW MODAL
==================================================================== --}}
<div class="image-preview-modal" id="imageModal">
  <span class="image-preview-close" onclick="closeImageModal()">&times;</span>
  <img id="modalImage" src="">
</div>

{{-- ====================================================================
    NOTIFICATION SOUND
==================================================================== --}}
<audio id="notificationSound" preload="auto">
  <source src="https://cdn.freesound.org/previews/316/316847_4939433-lq.mp3" type="audio/mpeg">
</audio>

{{-- ====================================================================
    JAVASCRIPT - CHAT FUNCTIONALITY
==================================================================== --}}
<script>
document.addEventListener("DOMContentLoaded", function() {
    // ====================================================================
    // VARIABLES & ELEMENTS
    // ====================================================================
    const chatBox = document.getElementById("chatBox");
    const chatForm = document.getElementById("chatForm");
    const receiverInput = document.getElementById("receiver_id");
    const messageInput = document.getElementById("message");
    const chatUserName = document.getElementById("chatUserName");
    const chatUserEmail = document.getElementById("chatUserEmail");
    const chatActions = document.getElementById("chatActions");
    const imageInput = document.getElementById("chatImage");
    const imagePreview = document.getElementById("imagePreview");
    const previewImg = document.getElementById("previewImg");
    const previewName = document.getElementById("previewName");
    const searchInput = document.getElementById("searchUsers");
    const notificationSound = document.getElementById("notificationSound");

    let selectedUser = null;
    let lastMessageId = 0;
    let messagePolling = null;
    let lastMessageIds = {}; // Track last message per user

    // ====================================================================
    // ✅ FETCH UNREAD COUNTS & CHECK NEW MESSAGES
    // ====================================================================
    async function fetchUnread() {
        try {
            const res = await fetch("{{ route('admin.user.unread') }}");
            const responseData = await res.json();

            const unreadData = responseData.unread || responseData || {};
            const latestData = responseData.latest || {};

            const userItems = Array.from(document.querySelectorAll('.user-item'));
            const userList = document.getElementById('userList');

            let usersWithNewMessages = [];

            // Update timestamps and message previews
            userItems.forEach(item => {
                const userId = item.dataset.id;
                if (latestData[userId]) {
                    item.dataset.lastTime = latestData[userId].last_time || 0;
                    item.dataset.lastId = latestData[userId].last_id || 0;
                    const msgSnippet = latestData[userId].last_message;
                    const msgElement = item.querySelector('.user-last-msg');
                    if (msgElement && msgSnippet) {
                        msgElement.innerText = msgSnippet;
                    }
                }
            });

            // Sort users: Latest message timestamp FIRST (descending order)
            userItems.sort((a, b) => {
                const timeA = parseInt(a.dataset.lastTime) || 0;
                const timeB = parseInt(b.dataset.lastTime) || 0;
                if (timeB !== timeA) {
                    return timeB - timeA;
                }
                const countA = unreadData[a.dataset.id] || 0;
                const countB = unreadData[b.dataset.id] || 0;
                return countB - countA;
            });

            // Re-append in sorted order and update UI badges
            userItems.forEach(item => {
                const userId = item.dataset.id;
                const badge = document.getElementById(`unread-${userId}`);
                const dot = document.getElementById(`dot-${userId}`);
                const count = unreadData[userId] || 0;

                if(count > 0){
                    if(badge) {
                        badge.style.display = "inline-flex";
                        badge.innerText = count;
                    }
                    if(dot) dot.classList.add('show');
                    if(!item.classList.contains('active')) {
                        item.classList.add('has-new-message');
                    }
                    usersWithNewMessages.push(userId);
                } else {
                    if(badge && selectedUser != userId) badge.style.display = "none";
                    if(dot && selectedUser != userId) dot.classList.remove('show');
                    if(selectedUser != userId) item.classList.remove('has-new-message');
                }

                userList.appendChild(item);
            });

            checkForNewMessages(usersWithNewMessages);

        } catch(error) {
            console.error("Error fetching unread:", error);
        }
    }

    // ====================================================================
    // ✅ CHECK FOR NEW MESSAGES & SHOW NOTIFICATION
    // ====================================================================
    async function checkForNewMessages(usersWithUnread) {
        for(const userId of usersWithUnread) {
            // Skip if this user's chat is currently open
            if(selectedUser == userId) continue;

            try {
                const res = await fetch(`/admin/to/chat/fetch/${userId}?last_id=0&limit=1`);
                const messages = await res.json();

                if(messages.length > 0) {
                    const latestMsg = messages[messages.length - 1];

                    // Check if this is a new message
                    if(!lastMessageIds[userId] || latestMsg.id > lastMessageIds[userId]) {
                        lastMessageIds[userId] = latestMsg.id;

                        // Only show notification for user messages (not admin)
                        if(latestMsg.sender_id != {{ Auth::id() }}) {
                            const userItem = document.querySelector(`[data-id="${userId}"]`);
                            const userName = userItem?.dataset.name || 'User';
                            const userAvatar = userItem?.querySelector('.user-avatar')?.src || '';

                            showNotification(userName, latestMsg.message || 'Sent an image', userAvatar);
                            playNotificationSound();
                        }
                    }
                }
            } catch(error) {
                console.error(`Error checking messages for user ${userId}:`, error);
            }
        }
    }

    // ====================================================================
    // ✅ SHOW NOTIFICATION TOAST
    // ====================================================================
    function showNotification(userName, message, avatarSrc) {
        const toast = document.getElementById('notificationToast');
        const toastUserName = document.getElementById('toastUserName');
        const toastMessage = document.getElementById('toastMessage');
        const toastAvatar = document.getElementById('toastAvatar');

        toastUserName.textContent = userName;
        toastMessage.textContent = message.substring(0, 60) + (message.length > 60 ? '...' : '');
        toastAvatar.src = avatarSrc || '{{ asset("uploads/avator.jpg") }}';

        toast.classList.add('show');

        // Auto hide after 5 seconds
        setTimeout(() => {
            toast.classList.remove('show');
        }, 5000);
    }

    // ====================================================================
    // ✅ PLAY NOTIFICATION SOUND
    // ====================================================================
    function playNotificationSound() {
        notificationSound.play().catch(error => {
            console.log('Could not play notification sound:', error);
        });
    }

    // ====================================================================
    // ✅ CLOSE NOTIFICATION
    // ====================================================================
    window.closeNotification = function() {
        document.getElementById('notificationToast').classList.remove('show');
    };

    // Initial fetch and interval
    fetchUnread();
    setInterval(fetchUnread, 3000); // Check every 3 seconds

    // ====================================================================
    // ✅ SELECT USER - START CHAT
    // ====================================================================
    document.querySelectorAll(".user-item").forEach(item => {
        item.addEventListener("click", async function() {
            // Remove active class from all
            document.querySelectorAll(".user-item").forEach(u => {
                u.classList.remove("active");
            });

            // Add active class to selected
            this.classList.add("active");
            this.classList.remove("has-new-message");

            selectedUser = this.dataset.id;
            receiverInput.value = selectedUser;

            // Clear unread badge and dot
            const badge = document.getElementById(`unread-${selectedUser}`);
            const dot = document.getElementById(`dot-${selectedUser}`);
            if(badge) badge.style.display = "none";
            if(dot) dot.classList.remove('show');

            // Update header
            const avatarSrc = this.querySelector('.user-avatar')?.src || '';
            chatUserName.innerHTML = `<span class="online-indicator"></span>${this.dataset.name}`;
            chatUserEmail.innerText = this.dataset.email;
            chatActions.style.display = "block";

            // Mark as read
            try {
                await fetch(`/admin/to/chat/mark-read/${selectedUser}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                });
            } catch(error) {
                console.error("Error marking as read:", error);
            }

            // Reset and load messages
            lastMessageId = 0;
            chatBox.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading messages...</div>';

            // Clear previous polling
            if(messagePolling) {
                clearInterval(messagePolling);
            }

            // Load messages
            await loadMessages();

            // Start polling for new messages
            messagePolling = setInterval(loadMessages, 2000);
        });
    });

    // ====================================================================
    // ✅ LOAD MESSAGES - FETCH FROM SERVER
    // ====================================================================
    async function loadMessages() {
        if(!selectedUser) return;

        try {
            const res = await fetch(`/admin/to/chat/fetch/${selectedUser}?last_id=${lastMessageId}`);
            const data = await res.json();

            if(data.length > 0) {
                // Clear loading message on first load
                if(lastMessageId === 0) {
                    chatBox.innerHTML = '';
                }

                data.forEach(msg => {
                    if(msg.id > lastMessageId){
                        appendMessage(msg);
                        lastMessageId = msg.id;
                        lastMessageIds[selectedUser] = msg.id;
                    }
                });

                // Mark new messages as read
                await fetch(`/admin/to/chat/mark-read/${selectedUser}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                });
            } else if(lastMessageId === 0) {
                // No messages yet
                chatBox.innerHTML = `
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-comments fa-3x mb-3"></i>
                        <p>No messages yet. Start the conversation!</p>
                    </div>
                `;
            }
        } catch(error) {
            console.error("Error loading messages:", error);
        }
    }

    function appendMessage(msg){
        if (!msg) return;
        if (msg.id && document.getElementById(`msg-${msg.id}`)) {
            return; // Prevent duplicate message from being rendered!
        }

        const isAdmin = msg.sender_id === {{ Auth::id() }};
        const side = isAdmin ? 'text-end' : 'text-start';

        // Determine bubble styling
        let bubbleClass = 'message-bubble ';

        if (isAdmin) {
            bubbleClass += 'admin-message';
        } else if (!msg.is_read) {
            bubbleClass += 'user-message unread';
        } else {
            bubbleClass += 'user-message';
        }

        let content = `<div id="msg-${msg.id}" class="${side} mb-3">`;
        content += `<div class="p-3 rounded d-inline-block ${bubbleClass}">`;

        // Message Text
        if(msg.message) {
            content += `<div>${escapeHtml(msg.message)}</div>`;
        }

        // Image
        if(msg.image) {
            const imageUrl = msg.image.startsWith('http')
                ? msg.image
                : `{{ asset('') }}${msg.image}`;

            content += `
                <div class="mt-2">
                    <img src="${imageUrl}"
                         class="rounded shadow-sm"
                         style="max-width: 250px; cursor: pointer;"
                         onclick="openImageModal('${imageUrl}')"
                         onerror="this.src='{{ asset('uploads/avator.jpg') }}'; this.style.opacity='0.5';">
                </div>
            `;
        }

        // Timestamp
        const timestamp = new Date(msg.created_at).toLocaleString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
        content += `<div class="message-time text-end">${timestamp}</div>`;

        content += `</div></div>`;

        chatBox.innerHTML += content;
        scrollToBottom();
    }

    // ====================================================================
    // ✅ SEND MESSAGE - HANDLE FORM SUBMIT
    // ====================================================================
    chatForm.addEventListener("submit", async function(e){
        e.preventDefault();

        if(!receiverInput.value) {
            alert("Please select a user first!");
            return;
        }

        const messageText = messageInput.value.trim();
        const imageFile = imageInput.files[0];

        if(!messageText && !imageFile) {
            alert("Please type a message or select an image!");
            return;
        }

        const formData = new FormData(chatForm);

        try {
            const res = await fetch("{{ route('admin.chat.send') }}", {
                method: "POST",
                body: formData
            });

            const data = await res.json();

            if(data.success){
                // Clear inputs
                messageInput.value = '';
                imageInput.value = '';
                removeImagePreview();

                // Append sent message
                appendMessage(data.chat);

                // Update tracking IDs to prevent polling from duplicate appending
                if (data.chat && data.chat.id) {
                    lastMessageId = Math.max(lastMessageId, data.chat.id);
                    lastMessageIds[selectedUser] = Math.max(lastMessageIds[selectedUser] || 0, data.chat.id);
                }

                // Update selected user's position in sidebar to top
                if(selectedUser) {
                    const userItem = document.querySelector(`.user-item[data-id="${selectedUser}"]`);
                    if(userItem) {
                        const nowTs = Math.floor(Date.now() / 1000);
                        userItem.dataset.lastTime = nowTs;
                        if(data.chat && data.chat.id) userItem.dataset.lastId = data.chat.id;
                        const msgElement = userItem.querySelector('.user-last-msg');
                        if(msgElement) {
                            const txt = data.chat.message ? (data.chat.message.length > 30 ? data.chat.message.substring(0, 30) + '...' : data.chat.message) : '📷 Image';
                            msgElement.innerText = txt;
                        }
                        const userList = document.getElementById('userList');
                        userList.insertBefore(userItem, userList.firstChild);
                    }
                }

                // Success feedback
                messageInput.placeholder = "Message sent! ✓";
                setTimeout(() => {
                    messageInput.placeholder = "Type a message...";
                }, 2000);
            } else {
                alert("Failed to send message: " + (data.message || "Unknown error"));
            }
        } catch(error) {
            console.error("Error sending message:", error);
            alert("Failed to send message. Please try again.");
        }
    });

    // ====================================================================
    // ✅ IMAGE PREVIEW - SHOW SELECTED IMAGE
    // ====================================================================
    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if(file) {
            if(!file.type.startsWith('image/')) {
                alert('Please select an image file!');
                this.value = '';
                return;
            }

            if(file.size > 5 * 1024 * 1024) {
                alert('Image size should be less than 5MB!');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewName.textContent = file.name;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    // ====================================================================
    // ✅ SEARCH USERS
    // ====================================================================
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.user-item').forEach(item => {
            const name = item.dataset.name.toLowerCase();
            const email = item.dataset.email.toLowerCase();

            if(name.includes(query) || email.includes(query)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // ====================================================================
    // ✅ UTILITY FUNCTIONS
    // ====================================================================

    function scrollToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    window.removeImagePreview = function() {
        imageInput.value = '';
        imagePreview.style.display = 'none';
    };

    window.openImageModal = function(src) {
        document.getElementById('modalImage').src = src;
        document.getElementById('imageModal').style.display = 'flex';
    };

    window.closeImageModal = function() {
        document.getElementById('imageModal').style.display = 'none';
    };

    window.refreshAllChats = function() {
        fetchUnread();
        if(selectedUser) {
            loadMessages();
        }
    };

    window.clearChatHistory = function() {
        if(confirm('Are you sure you want to clear chat history with this user?')) {
            alert('Clear functionality coming soon!');
        }
    };

    window.downloadChatHistory = function() {
        if(selectedUser) {
            alert('Download functionality coming soon!');
        }
    };

    // ====================================================================
    // ✅ KEYBOARD SHORTCUTS
    // ====================================================================
    messageInput.addEventListener('keydown', function(e) {
        if(e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit'));
        }
    });

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if(e.key === 'Escape') {
            closeImageModal();
            closeNotification();
        }
    });
});
</script>

@endsection
