@extends('frontend.master')

@section('content')
<div class="userchate">
    <!-- Contacts Sidebar -->
    <div class="contacts-sidebar scrollbar-hide" id="contactsSidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-users"></i> Agents</h3>
        </div>

        <div class="search-box" style="position: relative;">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" id="searchInput" placeholder="Search agents...">
        </div>

        <div class="contacts-list scrollbar-hide" id="contactsList">
            @forelse($agents as $agent)
                <div class="contact-item"
                     data-user-id="{{ $agent->id }}"
                     data-name="{{ $agent->name }}">
                    <div class="contact-avatar-wrapper">
                        <img src="{{ $agent->profile_photo_url ? asset('uploads/agent/'.$agent->profile_photo_url) : 'https://ui-avatars.com/api/?name=' . urlencode($agent->name) . '&background=667eea&color=fff' }}"
                             class="contact-avatar"
                             alt="{{ $agent->name }}">
                        <div class="online-status"></div>
                    </div>
                    <div class="contact-info">
                        <div class="contact-name">{{ $agent->name }}</div>
                        <div class="contact-last-msg">Agent</div>
                    </div>
                    <span class="message-badge" data-badge="{{ $agent->id }}">0</span>
                </div>
            @empty
                <div style="text-align: center; padding: 30px; color: #999;">
                    <i class="fas fa-users" style="font-size: 50px; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>No agents available</p>
                </div>
            @endforelse
        </div>
        <div class="no-results" id="noResults">
            <i class="fas fa-search" style="font-size: 40px; margin-bottom: 10px; opacity: 0.3;"></i>
            <p>No agents found</p>
        </div>
    </div>

    <!-- Chat Panel -->
    <div class="chat-panel">
        <!-- Empty State -->
        <div class="empty-chat-state" id="emptyChatState">
            <i class="fas fa-comments"></i>
            <h2>Welcome to Agent Chat</h2>
            <p>Select an agent to start messaging</p>
        </div>

        <!-- Active Chat -->
        <div class="chat-content" id="chatContent">
            <!-- Chat Header -->
            <div class="chat-header">
                <button class="icon-btn back-btn" onclick="showContactsList()">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="chat-user-info">
                    <img src="" class="chat-user-avatar" id="chatAvatar" alt="">
                    <div class="chat-user-details">
                        <div class="chat-user-name" id="chatUserName"></div>
                    </div>
                </div>
                <div class="chat-actions">
                    <button class="icon-btn" title="Refresh" onclick="loadChatHistory(true)">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>

            <!-- Chat Messages -->
            <div class="chat-messages scrollbar-hide" id="chatMessages">
                <div class="loading-indicator" id="loadingIndicator">
                    <i class="fas fa-spinner fa-spin"></i> Loading messages...
                </div>
            </div>

            <!-- Chat Input -->
            <div class="chat-input-container" style="position: relative;">
                <button class="attach-btn" onclick="document.getElementById('imageInput').click()" title="Attach image">
                    <i class="fas fa-paperclip"></i>
                </button>
                <input type="file" id="imageInput" accept="image/*" onchange="handleImageSelect(event)">

                <button class="emoji-btn" onclick="toggleEmojiPicker()" title="Add emoji">
                    <i class="far fa-smile"></i>
                </button>

                <textarea class="message-input"
                       id="messageInput"
                       placeholder="Type your message here..."
                       rows="1"
                       maxlength="5000"></textarea>

                <button class="send-btn" onclick="sendMessage()" id="sendBtn">
                    <i class="fas fa-paper-plane"></i>
                </button>

                <!-- Emoji Picker -->
                <div class="emoji-picker scrollbar-hide" id="emojiPicker"></div>

                <!-- Image Preview -->
                <div class="image-preview-container" id="imagePreviewContainer">
                    <button class="remove-image" onclick="removeSelectedImage()">×</button>
                    <img src="" alt="Preview" class="image-preview" id="imagePreview">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ===== GLOBAL VARIABLES =====
let selectedImage = null;
let currentUserId = null;
let currentUserName = null;
let currentUserAvatar = null;
let messageRefreshInterval = null;
let unreadRefreshInterval = null;

// ===== CONFIGURATION =====
const CONFIG = {
    messageRefreshRate: 3000, // 3 seconds
    unreadRefreshRate: 5000,  // 5 seconds
};

// Emojis collection
const emojis = [
    '😊','😂','❤️','👍','😍','😢','😎','🎉','🔥','✨','💯','🙌','👏','💪','🎊',
    '🌟','⭐','💖','💕','💗','😘','😁','😆','🤗','🥰','😇','🤩','😋','😜','🤪','😉',
    '🤔','😴','😷','🤒','🤕','🥳','🥺','😭','😤','😡','🤬','😱','😨','😰','😥','😓',
    '🙏','👌','✌️','🤞','🤝','💐','🌹','🌺','🌸','🌼','🌻','🌷','🎂','🎁','🎈','🎀'
];

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', function() {
    initEmojiPicker();
    attachEventListeners();
    updateUnreadCounts();

    // Start periodic updates
    unreadRefreshInterval = setInterval(updateUnreadCounts, CONFIG.unreadRefreshRate);
});

// ===== EMOJI PICKER =====
function initEmojiPicker() {
    const picker = document.getElementById('emojiPicker');
    picker.innerHTML = '';
    emojis.forEach(emoji => {
        const span = document.createElement('span');
        span.className = 'emoji-item';
        span.textContent = emoji;
        span.onclick = () => insertEmoji(emoji);
        picker.appendChild(span);
    });
}

function toggleEmojiPicker() {
    document.getElementById('emojiPicker').classList.toggle('active');
}

function insertEmoji(emoji) {
    const input = document.getElementById('messageInput');
    const cursorPos = input.selectionStart;
    const textBefore = input.value.substring(0, cursorPos);
    const textAfter = input.value.substring(cursorPos);
    input.value = textBefore + emoji + textAfter;
    input.focus();
    input.setSelectionRange(cursorPos + emoji.length, cursorPos + emoji.length);
    document.getElementById('emojiPicker').classList.remove('active');
}

// ===== IMAGE HANDLING =====
function handleImageSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        alert('Please select an image file');
        event.target.value = '';
        return;
    }

    if (file.size > 5242880) { // 5MB limit
        alert('Image size must be less than 5MB');
        event.target.value = '';
        return;
    }

    selectedImage = file;

    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('imagePreview').src = e.target.result;
        document.getElementById('imagePreviewContainer').classList.add('active');
    };
    reader.readAsDataURL(file);
}

function removeSelectedImage() {
    selectedImage = null;
    document.getElementById('imageInput').value = '';
    document.getElementById('imagePreviewContainer').classList.remove('active');
}

// ===== CHAT FUNCTIONS =====
function openChat(userId, userName, avatarSrc) {
    // Hide empty state, show chat
    document.getElementById('emptyChatState').style.display = 'none';
    document.getElementById('chatContent').classList.add('active');

    // Set current chat
    currentUserId = userId;
    currentUserName = userName;
    currentUserAvatar = avatarSrc;

    // Update UI
    document.getElementById('chatUserName').textContent = userName;
    document.getElementById('chatAvatar').src = avatarSrc;

    // Mark active contact
    document.querySelectorAll('.contact-item').forEach(item => {
        item.classList.remove('active');
    });
    const activeContact = document.querySelector(`[data-user-id="${userId}"]`);
    if (activeContact) {
        activeContact.classList.add('active');
    }

    // Mobile: Hide sidebar
    if (window.innerWidth <= 768) {
        document.getElementById('contactsSidebar').classList.add('mobile-hidden');
    }

    // Clear previous interval
    if (messageRefreshInterval) {
        clearInterval(messageRefreshInterval);
    }

    // Load messages
    loadChatHistory(true);

    // Auto-refresh messages
    messageRefreshInterval = setInterval(() => {
        if (currentUserId) {
            loadChatHistory(false);
        }
    }, CONFIG.messageRefreshRate);
}

function loadChatHistory(showLoading = true) {
    if (!currentUserId) return;

    const chatMessages = document.getElementById('chatMessages');
    const loadingIndicator = document.getElementById('loadingIndicator');

    if (showLoading) {
        loadingIndicator.classList.add('active');
        chatMessages.innerHTML = '<div class="loading-indicator active"><i class="fas fa-spinner fa-spin"></i> Loading messages...</div>';
    }

    fetch(`{{ route('agentsuser.toagent.userto.chat.messages') }}?receiver_id=${currentUserId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Failed to load messages');
        return res.json();
    })
    .then(response => {
        if (!response.success) {
            throw new Error(response.message || 'Failed to load messages');
        }

        const data = response.data;

        // Save scroll position
        const wasAtBottom = chatMessages.scrollHeight - chatMessages.scrollTop <= chatMessages.clientHeight + 100;

        // Clear previous messages
        chatMessages.innerHTML = '';

        if (data.length === 0) {
            chatMessages.innerHTML = `
                <div style="text-align: center; padding: 40px 20px; color: #999;">
                    <i class="far fa-comment-dots" style="font-size: 60px; margin-bottom: 15px; opacity: 0.3;"></i>
                    <p style="font-size: 16px;">No messages yet. Start the conversation!</p>
                </div>
            `;
        } else {
            data.forEach(msg => {
                appendMessage(msg);
            });
        }

        // Scroll to bottom if was at bottom or first load
        if (wasAtBottom || showLoading) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Update unread counts
        updateUnreadCounts();
    })
    .catch(error => {
        console.error('Error loading chat:', error);
        chatMessages.innerHTML = `
            <div style="text-align: center; padding: 40px 20px; color: #f44336;">
                <i class="fas fa-exclamation-circle" style="font-size: 50px; margin-bottom: 15px;"></i>
                <p>Failed to load messages. Please try again.</p>
                <button onclick="loadChatHistory(true)" style="margin-top: 15px; padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 20px; cursor: pointer;">Retry</button>
            </div>
        `;
    });
}

function appendMessage(msg) {
    const chatMessages = document.getElementById('chatMessages');
    const wrapper = document.createElement('div');
    const isSent = msg.sender_id == {{ auth()->id() }};

    wrapper.className = 'message-wrapper ' + (isSent ? 'sent' : 'received');

    let content = '';
    if (msg.image) {
        content += `<img src="/${msg.image}" class="message-image" onclick="window.open('/${msg.image}', '_blank')" alt="Image" loading="lazy">`;
    }
    if (msg.message) {
        content += escapeHtml(msg.message);
    }

    const avatarSrc = isSent
        ? 'https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=667eea&color=fff'
        : currentUserAvatar;

    const messageTime = formatMessageTime(msg.created_at);

    wrapper.innerHTML = `
        <img src="${avatarSrc}" class="message-avatar" alt="Avatar" loading="lazy">
        <div class="message-bubble">
            ${content}
            <span class="message-time">${messageTime}</span>
        </div>
    `;

    chatMessages.appendChild(wrapper);
}

function sendMessage() {
    if (!currentUserId) {
        alert('Please select an agent to chat with');
        return;
    }

    const input = document.getElementById('messageInput');
    const messageText = input.value.trim();
    const sendBtn = document.getElementById('sendBtn');

    if (!messageText && !selectedImage) {
        alert('Please enter a message or select an image');
        return;
    }

    // Disable send button
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

    const formData = new FormData();
    formData.append('receiver_id', currentUserId);
    formData.append('message', messageText);
    if (selectedImage) {
        formData.append('image', selectedImage);
    }

    fetch('{{ route('agentuser.chat.agents.submit') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => {
        if (!res.ok) throw new Error('Failed to send message');
        return res.json();
    })
    .then(data => {
        if (data.success) {
            input.value = '';
            removeSelectedImage();
            loadChatHistory(false);
        } else {
            throw new Error(data.message || 'Failed to send message');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Failed to send message. Please try again.');
    })
    .finally(() => {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send';
    });
}

// ===== UNREAD COUNTS =====
function updateUnreadCounts() {
    fetch('{{ route('user.chat.agent.unread') }}', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.unread_by_user) {
            // Reset all badges first
            document.querySelectorAll('.message-badge').forEach(badge => {
                badge.classList.remove('show');
                badge.textContent = '0';
            });

            // Update badges with counts
            Object.keys(data.unread_by_user).forEach(userId => {
                const badge = document.querySelector(`[data-badge="${userId}"]`);
                if (badge) {
                    const count = data.unread_by_user[userId];
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.classList.add('show');
                    }
                }
            });
        }
    })
    .catch(error => console.error('Error updating unread counts:', error));
}

// ===== UTILITY FUNCTIONS =====
function showContactsList() {
    document.getElementById('contactsSidebar').classList.remove('mobile-hidden');

    if (window.innerWidth <= 768) {
        document.getElementById('chatContent').classList.remove('active');
        document.getElementById('emptyChatState').style.display = 'flex';

        if (messageRefreshInterval) {
            clearInterval(messageRefreshInterval);
        }

        currentUserId = null;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML.replace(/\n/g, '<br>');
}

function formatMessageTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;

    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

// ===== EVENT LISTENERS =====
function attachEventListeners() {
    // Contact item clicks
    document.querySelectorAll('.contact-item').forEach(item => {
        item.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-name');
            const avatarSrc = this.querySelector('.contact-avatar').src;
            openChat(userId, userName, avatarSrc);
        });
    });

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase().trim();
        let hasResults = false;

        document.querySelectorAll('.contact-item').forEach(item => {
            const name = item.getAttribute('data-name').toLowerCase();
            if (name.includes(term)) {
                item.classList.remove('hidden');
                hasResults = true;
            } else {
                item.classList.add('hidden');
            }
        });

        document.getElementById('noResults').classList.toggle('show', !hasResults && term !== '');
    });

    // Enter key to send (Shift+Enter for new line)
    document.getElementById('messageInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Auto-resize textarea
    document.getElementById('messageInput').addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // Close emoji picker on outside click
    document.addEventListener('click', function(e) {
        const emojiPicker = document.getElementById('emojiPicker');
        const emojiBtn = document.querySelector('.emoji-btn');
        if (!emojiPicker.contains(e.target) && !emojiBtn.contains(e.target)) {
            emojiPicker.classList.remove('active');
        }
    });
}

// ===== CLEANUP =====
window.addEventListener('beforeunload', function() {
    if (messageRefreshInterval) {
        clearInterval(messageRefreshInterval);
    }
    if (unreadRefreshInterval) {
        clearInterval(unreadRefreshInterval);
    }
});
</script>
@endsection
