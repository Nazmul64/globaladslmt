@extends('frontend.master')

@section('content')
<div class="container py-4">
    <div class="chat-app" id="chatApp">
        <!-- LEFT: Contacts -->
        <aside class="left-pane" id="contactsPane">
            <div class="left-top">
                <div class="brand">
                    <img src="{{ asset('uploads/logo.png') }}" alt="logo" class="brand-logo">
                    <h3>Chat</h3>
                </div>
                <div class="search-wrap">

                    <input id="searchInput" type="text" placeholder="Search contacts..." autocomplete="off">
                </div>
            </div>

            <div class="contacts-list" id="contactsList">
                @forelse($contacts as $user)
                    <div class="contact-item" data-user-id="{{ $user->id }}" data-name="{{ $user->name }}"
                         data-photo="{{ $user->photo ? asset('uploads/profile/' . $user->photo) : asset('uploads/logo.png') }}">
                        <div class="avatar-wrap">
                            <img class="avatar" src="{{ $user->photo ? asset('uploads/profile/' . $user->photo) : asset('uploads/logo.png') }}" alt="{{ $user->name }}">
                            <span class="online-dot"></span>
                        </div>
                        <div class="meta">
                            <div class="name">{{ $user->name }}</div>
                            <div class="sub">Click to start chat</div>
                        </div>
                        <span class="badge message-badge" data-badge="{{ $user->id }}" style="display:none">0</span>
                    </div>
                @empty
                    <div class="no-contacts">
                        <i class="fas fa-user-friends"></i>
                        <p>No friends found</p>
                    </div>
                @endforelse
            </div>
        </aside>

        <!-- RIGHT: Chat Panel -->
        <main class="right-pane" id="chatPane">
            <div class="empty-state" id="emptyState">
                <i class="fas fa-comments"></i>
                <h2>Welcome to Chat</h2>
                <p>Select a contact to start messaging</p>
            </div>

            <div class="chat-card" id="chatCard" style="display:none">
                <header class="chat-header">
                    <button id="backBtn" class="back-btn" title="Back"><i class="fas fa-arrow-left"></i></button>

                    <div class="user-info">
                        <img id="chatAvatar" class="chat-avatar" src="{{ asset('uploads/logo.png') }}" alt="avatar">
                        <div class="user-meta">
                            <div id="chatUserName" class="chat-name">User</div>
                            <div id="typingIndicator" class="typing" style="display:none">
                                typing<span class="dots">...</span>
                            </div>
                        </div>
                    </div>

                    <div class="header-actions">
                        <button class="icon-btn" id="moreBtn" title="More"><i class="fas fa-ellipsis-v"></i></button>
                    </div>
                </header>

                <section class="messages-wrap" id="chatMessages" aria-live="polite"></section>

                <footer class="composer">
                    <button id="attachBtn" class="btn attach" title="Attach Image"><i class="fas fa-paperclip"></i></button>
                    <input id="imageInput" type="file" accept="image/*" style="display:none">
                    <button id="emojiBtn" class="btn emoji" title="Emoji"><i class="far fa-smile"></i></button>
                    <input id="messageInput" class="message-field" type="text" placeholder="Type message..." autocomplete="off">
                    <button id="sendBtn" class="btn send" title="Send"><i class="fas fa-paper-plane"></i></button>
                    <div id="emojiPicker" class="emoji-picker" style="display:none"></div>
                </footer>
            </div>
        </main>
    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', () => {
    const state = {
        myUserId: '{{ auth()->id() }}',
        myAvatar: '{{ auth()->user()->photo ? asset("uploads/profile/" . auth()->user()->photo) : asset("uploads/logo.png") }}',
        currentUserId: null,
        currentUserName: null,
        currentUserAvatar: null,
        csrf: '{{ csrf_token() }}',
        endpoints: {
            messages: '{{ route("frontend.user.chat.messages") }}',
            submit: '{{ route("frontend.user.chat.submit") }}',
            unread: '{{ route("frontend.user.chat.unread") }}'
        },
        typingTimeout: null,
        selectedImage: null
    };

    const contactsList = document.getElementById('contactsList');
    const searchInput = document.getElementById('searchInput');
    const chatCard = document.getElementById('chatCard');
    const chatMessages = document.getElementById('chatMessages');
    const emptyState = document.getElementById('emptyState');
    const backBtn = document.getElementById('backBtn');
    const chatUserName = document.getElementById('chatUserName');
    const chatAvatar = document.getElementById('chatAvatar');
    const typingIndicator = document.getElementById('typingIndicator');
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    const attachBtn = document.getElementById('attachBtn');
    const imageInput = document.getElementById('imageInput');
    const emojiBtn = document.getElementById('emojiBtn');
    const emojiPicker = document.getElementById('emojiPicker');
    const leftPane = document.getElementById('contactsPane');

    const emojis = ['😊','😂','❤️','👍','😍','😢','😎','🎉','🔥','✨','💯','🙌','👏','💪','🎊','⭐','💖','💕','😘','😁','😆','🤗','🥰','😇','🤩','😋','😜','😉','🤔','😴','🥳','🤭','😱','🙏','👌','✌️','🤝'];

    function renderEmojis(){
        emojiPicker.innerHTML = '';
        emojis.forEach(e => {
            const sp = document.createElement('span');
            sp.className = 'emoji-item';
            sp.textContent = e;
            sp.addEventListener('click', () => {
                messageInput.value += e;
                emojiPicker.style.display = 'none';
                messageInput.focus();
            });
            emojiPicker.appendChild(sp);
        });
    }
    renderEmojis();

    contactsList.addEventListener('click', (ev) => {
        const item = ev.target.closest('.contact-item');
        if(!item) return;
        openChat(item.dataset.userId, item.dataset.name, item.dataset.photo);
        if(window.innerWidth <= 900) leftPane.classList.add('hidden');
    });

    backBtn.addEventListener('click', () => {
        leftPane.classList.remove('hidden');
    });

    // ===== ENHANCED SEARCH FUNCTIONALITY =====
    searchInput.addEventListener('input', () => {
        const term = searchInput.value.trim().toLowerCase();
        let found = false;

        document.querySelectorAll('.contact-item').forEach(it => {
            const nm = (it.dataset.name || '').toLowerCase();
            if(nm.includes(term)){
                it.style.display = 'flex';
                found = true;
            } else {
                it.style.display = 'none';
            }
        });

        // Handle "No contacts" message
        const noRes = document.querySelector('.no-contacts');
        if(noRes) {
            if(term && !found) {
                noRes.style.display = 'flex';
                noRes.innerHTML = '<i class="fas fa-search"></i><p>No results found</p>';
            } else if(!term && !found) {
                noRes.style.display = 'flex';
                noRes.innerHTML = '<i class="fas fa-user-friends"></i><p>No friends found</p>';
            } else {
                noRes.style.display = 'none';
            }
        }
    });

    // Clear search on focus (mobile friendly)
    searchInput.addEventListener('focus', () => {
        if(window.innerWidth <= 480) {
            searchInput.select();
        }
    });
    // ===== END ENHANCED SEARCH =====

    attachBtn.addEventListener('click', () => imageInput.click());
    imageInput.addEventListener('change', (e) => {
        const f = e.target.files[0];
        if(!f) return;
        if(!f.type.startsWith('image/')) return alert('Please select an image file');
        state.selectedImage = f;
        sendMessage(true);
    });

    emojiBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        emojiPicker.style.display = (emojiPicker.style.display === 'block') ? 'none' : 'block';
    });

    sendBtn.addEventListener('click', () => sendMessage(false));
    messageInput.addEventListener('keydown', (e) => {
        if(e.key === 'Enter' && !e.shiftKey){
            e.preventDefault();
            sendMessage(false);
        }
    });

    messageInput.addEventListener('input', () => {
        if(!state.currentUserId) return;
        showTypingLocal();
    });

    function showTypingLocal(){
        typingIndicator.style.display = 'block';
        if(state.typingTimeout) clearTimeout(state.typingTimeout);
        state.typingTimeout = setTimeout(() => {
            typingIndicator.style.display = 'none';
            state.typingTimeout = null;
        }, 1500);
    }

    function openChat(userId, name, avatar){
        state.currentUserId = userId;
        state.currentUserName = name;
        state.currentUserAvatar = avatar;
        chatUserName.textContent = name;
        chatAvatar.src = avatar;
        emptyState.style.display = 'none';
        chatCard.style.display = 'flex';
        chatMessages.innerHTML = '<div style="text-align:center;padding:1rem;color:#fff;opacity:0.7"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        loadMessages(userId);
    }

    function renderMessage(content, isSent = false, isImage = false, time = ''){
        const row = document.createElement('div');
        row.className = `message-row ${isSent ? 'sent' : 'received'}`;
        const avatar = document.createElement('img');
        avatar.className = 'msg-avatar';
        avatar.src = isSent ? state.myAvatar : state.currentUserAvatar;
        avatar.alt = 'avatar';
        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble';
        if(isImage){
            const img = document.createElement('img');
            img.src = content;
            img.style.maxWidth = '180px';
            img.style.maxHeight = '200px';
            img.style.width = 'auto';
            img.style.height = 'auto';
            img.style.borderRadius = '6px';
            img.style.display = 'block';
            img.style.objectFit = 'cover';
            img.style.cursor = 'pointer';
            img.alt = 'Image';
            img.addEventListener('click', () => {
                window.open(content, '_blank');
            });
            bubble.appendChild(img);
        } else {
            bubble.innerHTML = escapeHtml(content);
        }
        const t = document.createElement('div');
        t.className = 'msg-time';
        t.textContent = time || '';
        bubble.appendChild(t);

        row.appendChild(avatar);
        row.appendChild(bubble);
        chatMessages.appendChild(row);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function escapeHtml(text){
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    async function loadMessages(userId){
        try {
            const url = state.endpoints.messages + '?user_id=' + encodeURIComponent(userId);
            const res = await fetch(url, {
                headers: {
                    'X-CSRF-TOKEN': state.csrf,
                    'Accept': 'application/json'
                }
            });
            const json = await res.json();
            chatMessages.innerHTML = '';
            if(json.success && Array.isArray(json.data) && json.data.length){
                json.data.forEach(m => {
                    renderMessage(m.image || m.message, !!m.is_sent, !!m.image, m.created_at);
                });
            } else {
                chatMessages.innerHTML = `<div style="color:#fff;opacity:.9;text-align:center;padding:2rem">No messages yet. Say hello 👋</div>`;
            }
            fetchUnreadCounts();
        } catch(err){
            console.error('loadMessages error', err);
            chatMessages.innerHTML = `<div style="color:#fff;opacity:.9;text-align:center;padding:2rem">Failed to load messages</div>`;
        }
    }

    async function sendMessage(isImage=false){
        if(!state.currentUserId) return alert('Select a contact first');
        if(isImage && !state.selectedImage) return;
        if(!isImage && !messageInput.value.trim()) return;

        const fd = new FormData();
        fd.append('receiver_id', state.currentUserId);
        if(isImage){
            fd.append('image', state.selectedImage);
        } else {
            fd.append('message', messageInput.value.trim());
        }

        const orig = sendBtn.innerHTML;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        sendBtn.disabled = true;

        try {
            const res = await fetch(state.endpoints.submit, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': state.csrf,
                    'Accept': 'application/json'
                },
                body: fd
            });
            const json = await res.json();
            sendBtn.innerHTML = orig;
            sendBtn.disabled = false;
            if(json.success){
                renderMessage(json.data.image || json.data.message, true, !!json.data.image, json.data.created_at);
                messageInput.value = '';
                state.selectedImage = null;
                imageInput.value = '';
                fetchUnreadCounts();
            } else {
                alert(json.message || 'Failed to send message');
            }
        } catch(err){
            console.error('sendMessage error', err);
            sendBtn.innerHTML = orig;
            sendBtn.disabled = false;
            alert('Network error. Please try again.');
        }
    }

    async function fetchUnreadCounts(){
        try {
            const res = await fetch(state.endpoints.unread, {
                headers: {
                    'X-CSRF-TOKEN': state.csrf,
                    'Accept': 'application/json'
                }
            });
            const json = await res.json();
            if(json.success && json.counts){
                document.querySelectorAll('.message-badge').forEach(b => {
                    b.style.display='none';
                    b.textContent='0'
                });
                Object.entries(json.counts).forEach(([senderId, count]) => {
                    const badge = document.querySelector(`.message-badge[data-badge="${senderId}"]`);
                    if(badge){
                        const c = parseInt(count) || 0;
                        if(c > 0){
                            badge.textContent = c;
                            badge.style.display = 'inline-block';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                });
            }
        } catch(err){
            console.error('fetchUnreadCounts', err);
        }
    }

    fetchUnreadCounts();
    setInterval(fetchUnreadCounts, 10000);

    document.addEventListener('click', (e) => {
        if(!emojiPicker.contains(e.target) && e.target !== emojiBtn && !emojiBtn.contains(e.target)){
            emojiPicker.style.display = 'none';
        }
    });

    // Initial mobile state
    if(window.innerWidth <= 900){
        leftPane.classList.remove('hidden');
        chatCard.style.display = 'none';
        emptyState.style.display = 'flex';
    }
});
</script>
@endsection
