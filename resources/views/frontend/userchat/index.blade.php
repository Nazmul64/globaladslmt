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
                    <i class="fas fa-search search-icon"></i>
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

<style>
:root{
    --bg:#ffffff;
    --primary:#28a745;
    --muted:#f5f5f7;
    --text:#222;
    --accent:#ff6b4a;
    --panel-shadow: 0 6px 18px rgba(34,34,34,0.08);
}

* {
    box-sizing: border-box;
}

/* Layout - Container Mode */
.chat-app{
    display:flex;
    gap:1rem;
    height:75vh;
    max-height:900px;
    max-width:1200px;
    margin:0 auto;
    border-radius:10px;
    overflow:hidden;
    background:linear-gradient(180deg,#fbfbfb,#fff);
    box-shadow:var(--panel-shadow);
}

/* Left pane */
.left-pane{
    width:320px;
    min-width:260px;
    background:linear-gradient(180deg,#f2d0d9,#c57aa0);
    color:#fff;
    display:flex;
    flex-direction:column;
    padding:1rem;
}

.left-top{
    margin-bottom:0.75rem;
}

.brand{
    display:flex;
    align-items:center;
    gap:0.6rem;
    margin-bottom:1rem;
}

.brand h3{
    margin:0;
    font-size:1.3rem;
    font-weight:600;
}

.brand-logo{
    width:40px;
    height:40px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid rgba(255,255,255,0.3);
}

/* SEARCH BAR - DESKTOP & TABLET */
.search-wrap{
    display:flex;
    align-items:center;
    gap:0.6rem;
    background:rgba(255,255,255,0.25);
    padding:0.65rem 0.85rem;
    border-radius:12px;
    backdrop-filter:blur(10px);
    border:1.5px solid rgba(255,255,255,0.3);
    box-shadow:0 3px 10px rgba(0,0,0,0.15);
    transition:all 0.3s ease;
}

.search-wrap:focus-within{
    background:rgba(255,255,255,0.35);
    border-color:rgba(255,255,255,0.5);
    box-shadow:0 4px 15px rgba(0,0,0,0.2);
}

.search-icon{
    color:#fff;
    font-size:16px;
    flex-shrink:0;
    opacity:1;
}

.search-wrap input{
    background:transparent;
    border:none;
    outline:none;
    color:#fff;
    width:100%;
    font-size:15px;
    font-weight:400;
}

.search-wrap input::placeholder{
    color:rgba(255,255,255,0.95);
    font-weight:400;
}

/* Contacts list */
.contacts-list{
    overflow:auto;
    padding-right:6px;
    flex:1;
    margin-top:0.5rem;
}

.contact-item{
    display:flex;
    align-items:center;
    gap:0.75rem;
    padding:0.7rem;
    border-radius:10px;
    cursor:pointer;
    transition:all 0.2s;
    margin-bottom:0.35rem;
    background:rgba(255,255,255,0.05);
}

.contact-item:hover{
    background:rgba(255,255,255,0.15);
    transform:translateX(3px);
}

.avatar-wrap{
    position:relative;
    flex-shrink:0;
}

.avatar{
    width:50px;
    height:50px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid rgba(255,255,255,0.3);
}

.online-dot{
    position:absolute;
    right:2px;
    bottom:2px;
    width:11px;
    height:11px;
    background:#2ee06a;
    border-radius:50%;
    box-shadow:0 0 0 3px rgba(0,0,0,0.1);
}

.meta{
    flex:1;
    min-width:0;
}

.meta .name{
    font-weight:700;
    font-size:15px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.meta .sub{
    font-size:13px;
    opacity:0.9;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

/* Badge */
.message-badge{
    background:#dc3545;
    color:#fff;
    padding:4px 8px;
    border-radius:999px;
    font-size:12px;
    flex-shrink:0;
    font-weight:600;
}

.no-contacts{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    padding:2rem;
    text-align:center;
    opacity:0.7;
}

.no-contacts i{
    font-size:3rem;
    margin-bottom:1rem;
}

/* Right pane */
.right-pane{
    flex:1;
    background:var(--accent);
    position:relative;
    display:flex;
    flex-direction:column;
    min-width:0;
}

.empty-state{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    color:#fff;
    height:100%;
    text-align:center;
    padding:2rem;
}

.empty-state i{
    font-size:4rem;
    margin-bottom:1rem;
    opacity:0.8;
}

.empty-state h2{
    margin:0.5rem 0;
    font-size:1.75rem;
}

.empty-state p{
    opacity:0.9;
    font-size:1rem;
}

.chat-card{
    display:flex;
    flex-direction:column;
    height:100%;
}

/* Header */
.chat-header{
    display:flex;
    align-items:center;
    gap:0.75rem;
    padding:0.75rem 1rem;
    background:rgba(255,255,255,0.06);
    backdrop-filter:blur(4px);
    flex-shrink:0;
}

.chat-header .back-btn{
    display:none;
    background:transparent;
    border:none;
    color:#fff;
    font-size:20px;
    padding:8px;
    border-radius:6px;
    cursor:pointer;
    flex-shrink:0;
}

.chat-header .back-btn:hover{
    background:rgba(255,255,255,0.1);
}

.user-info{
    display:flex;
    align-items:center;
    gap:0.75rem;
    flex:1;
    min-width:0;
}

.chat-avatar{
    width:48px;
    height:48px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid rgba(255,255,255,0.12);
    flex-shrink:0;
}

.user-meta{
    color:#fff;
    min-width:0;
    flex:1;
}

.chat-name{
    font-weight:700;
    font-size:16px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.typing{
    font-size:13px;
    opacity:0.9;
}

.header-actions{
    flex-shrink:0;
}

.icon-btn{
    background:transparent;
    border:none;
    color:#fff;
    font-size:18px;
    padding:8px;
    cursor:pointer;
    border-radius:6px;
}

.icon-btn:hover{
    background:rgba(255,255,255,0.1);
}

/* Messages */
.messages-wrap{
    flex:1;
    overflow-y:auto;
    overflow-x:hidden;
    padding:1rem;
    display:flex;
    flex-direction:column;
    gap:0.6rem;
    background:transparent;
}

.message-row{
    display:flex;
    gap:0.5rem;
    align-items:flex-end;
    max-width:85%;
}

.message-row.sent{
    margin-left:auto;
    flex-direction:row-reverse;
}

.msg-avatar{
    width:36px;
    height:36px;
    border-radius:50%;
    object-fit:cover;
    flex-shrink:0;
}

.msg-bubble{
    padding:10px 12px;
    border-radius:10px;
    box-shadow:0 2px 6px rgba(0,0,0,0.05);
    font-size:14px;
    line-height:1.4;
    word-wrap:break-word;
    overflow-wrap:break-word;
}

.message-row.sent .msg-bubble{
    background:#dff8dc;
}

.message-row.received .msg-bubble{
    background:#ffffff;
}

.msg-time{
    display:block;
    font-size:11px;
    color:#666;
    margin-top:6px;
}

/* Composer - FULLY RESPONSIVE */
.composer{
    display:flex;
    align-items:center;
    gap:0.5rem;
    padding:0.75rem;
    background:#fff;
    border-top:1px solid #e6e6e6;
    flex-shrink:0;
}

.composer .btn{
    background:transparent;
    border:1px solid rgba(0,0,0,0.1);
    padding:0.5rem;
    border-radius:8px;
    cursor:pointer;
    font-size:18px;
    color:#666;
    flex-shrink:0;
    display:flex;
    align-items:center;
    justify-content:center;
    min-width:40px;
    height:40px;
    transition:all 0.2s;
}

.composer .btn:hover{
    background:#f5f5f5;
}

.composer .btn.send{
    background:var(--primary);
    color:#fff;
    border:none;
    min-width:44px;
    height:44px;
}

.composer .btn.send:hover{
    background:#218838;
}

.composer .btn.send:disabled{
    background:#ccc;
    cursor:not-allowed;
}

.message-field{
    flex:1;
    padding:0.65rem 0.75rem;
    border-radius:8px;
    border:1px solid #e6e6e6;
    font-size:14px;
    outline:none;
    min-width:0;
}

.message-field:focus{
    border-color:var(--primary);
}

/* Emoji picker */
.emoji-picker{
    position:absolute;
    bottom:68px;
    left:60px;
    background:#fff;
    border-radius:8px;
    padding:8px;
    box-shadow:0 8px 28px rgba(0,0,0,0.15);
    max-width:280px;
    max-height:200px;
    overflow:auto;
    z-index:100;
}

.emoji-item{
    cursor:pointer;
    padding:6px;
    display:inline-block;
    font-size:20px;
    transition:transform 0.1s;
}

.emoji-item:hover{
    transform:scale(1.2);
}

/* Responsive - Tablet */
@media (max-width:900px){
    .chat-app{
        gap:0;
        border-radius:0;
        height:90vh;
    }

    .left-pane{
        width:100%;
        min-width:0;
        position:absolute;
        left:0;
        top:0;
        bottom:0;
        z-index:50;
        transform:translateX(0);
        transition:transform 0.3s ease;
        border-radius:0;
    }

    .left-pane.hidden{
        transform:translateX(-100%);
    }

    .chat-header .back-btn{
        display:inline-flex;
    }

    .right-pane{
        width:100%;
    }

    .composer{
        gap:0.4rem;
        padding:0.6rem;
    }

    .composer .btn{
        min-width:38px;
        height:38px;
        font-size:16px;
        padding:0.4rem;
    }

    .composer .btn.send{
        min-width:42px;
        height:42px;
    }

    .message-field{
        padding:0.6rem;
        font-size:14px;
    }
}

/* Mobile - Small Devices (MAIN MOBILE SECTION) */
@media (max-width:480px){
    .chat-app{
        height:56vh;
        max-height:100vh;
    }

    .left-pane{
        margin-top: 98px;
        padding:0.85rem;
    }

    .brand{
        margin-bottom:0.85rem;
    }

    .brand h3{
        font-size:1.15rem;
    }

    .brand-logo{
        width:36px;
        height:36px;
    }

    /* ===== MOBILE SEARCH BAR - ENHANCED & HIGHLY VISIBLE ===== */
    .search-wrap{
        padding:0.7rem 0.9rem;
        border-radius:10px;
        background:rgba(255,255,255,0.4);
        border:2px solid rgba(255,255,255,0.5);
        box-shadow:0 4px 12px rgba(0,0,0,0.2);
    }

    .search-wrap:focus-within{
        background:rgba(255,255,255,0.5);
        border-color:rgba(255,255,255,0.7);
        box-shadow:0 5px 18px rgba(0,0,0,0.25);
    }

    .search-icon{
        font-size:16px;
        color:#fff;
        opacity:1;
        text-shadow:0 1px 3px rgba(0,0,0,0.2);
    }

    .search-wrap input{
        font-size:15px;
        color:#fff;
        font-weight:500;
        text-shadow:0 1px 2px rgba(0,0,0,0.1);
    }

    .search-wrap input::placeholder{
        color:#fff;
        font-weight:500;
        opacity:0.95;
        text-shadow:0 1px 2px rgba(0,0,0,0.15);
    }
    /* ===== END MOBILE SEARCH BAR ===== */

    .avatar{
        width:46px;
        height:46px;
    }

    .chat-avatar{
        width:44px;
        height:44px;
    }

    .meta .name{
        font-size:14px;
    }

    .meta .sub{
        font-size:12px;
    }

    .chat-name{
        font-size:15px;
    }

    /* Composer for very small screens */
    .composer{
        gap:0.3rem;
        padding:0.5rem 0.4rem;
    }

    .composer .btn{
        min-width:36px;
        height:36px;
        font-size:15px;
        padding:0.35rem;
        border-radius:6px;
    }

    .composer .btn.send{
        min-width:40px;
        height:40px;
    }

    .message-field{
        padding:0.55rem 0.6rem;
        font-size:13px;
        border-radius:6px;
    }

    .message-row{
        max-width:90%;
    }

    .msg-bubble{
        font-size:13px;
        padding:8px 10px;
    }

    .msg-avatar{
        width:32px;
        height:32px;
    }

    .emoji-picker{
        left:40px;
        bottom:56px;
        max-width:240px;
    }

    .emoji-item{
        font-size:18px;
        padding:5px;
    }
}

/* Extra Small Devices */
@media (max-width:360px){
    .brand h3{
        font-size:1.05rem;
    }

    .brand-logo{
        width:32px;
        height:32px;
    }

    /* Extra small mobile search */
    .search-wrap{
        padding:0.65rem 0.8rem;
        background:rgba(255,255,255,0.42);
        border:2px solid rgba(255,255,255,0.55);
    }

    .search-icon{
        font-size:15px;
    }

    .search-wrap input{
        font-size:14px;
    }

    .search-wrap input::placeholder{
        color:#fff;
        font-weight:500;
        opacity:0.95;
    }

    .composer{
        gap:0.25rem;
        padding:0.4rem 0.3rem;
    }

    .composer .btn{
        min-width:32px;
        height:32px;
        font-size:14px;
        padding:0.3rem;
    }

    .composer .btn.send{
        min-width:36px;
        height:36px;
    }

    .message-field{
        padding:0.5rem;
        font-size:13px;
    }
}

/* Landscape mode for mobiles */
@media (max-width:900px) and (orientation:landscape){
    .chat-app{
        height:100vh;
    }

    .messages-wrap{
        padding:0.75rem;
    }

    .composer{
        padding:0.5rem;
    }
}
</style>

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
