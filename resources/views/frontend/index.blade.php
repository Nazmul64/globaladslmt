@extends('frontend.master')
@section('content')
 <div class="row">
<!-- Start Task Button -->
<div class="menu-card text-center">
    <div class="menu-icon-circle">
        <a href="javascript:void(0)" id="startTaskBtn">
            <i class="fas fa-bookmark" style="color:white;"></i>
        </a>
    </div>
    <div class="menu-label">Start Task</div>
</div>

<!-- Task Info Modal -->
<div class="modal fade" id="taskInfoModal" tabindex="-1" aria-labelledby="taskInfoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="taskInfoModalLabel">📝 Task Information</h5>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        @foreach ($work_notices as $item)
          <p>👉{{$item->title ?? ''}}</p>
          <p>✅{{$item->description ?? ''}}</p>
        @endforeach
      </div>
      <div class="modal-footer">
        <button type="button" id="goToTaskBtn" class="btn btn-success">Go to Task</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const startTaskBtn = document.getElementById("startTaskBtn");
    const goToTaskBtn = document.getElementById("goToTaskBtn");

    // Backend থেকে প্যাকেজ আছে কি না পাঠানো
    const hasPackage = @json($has_active_package);

    // Modal ওপেন (backdrop নেই)
    startTaskBtn.addEventListener("click", function() {
        const modal = new bootstrap.Modal(document.getElementById('taskInfoModal'), {
            backdrop: false, // overlay থাকবে না
            keyboard: true
        });
        modal.show();
    });

    // Go to Task ক্লিক
    goToTaskBtn.addEventListener("click", function() {
        if (hasPackage) {
            window.location.href = "{{ route('frontend.ads') }}";
        } else {
            alert("⚠️ Please buy a package first to access tasks!");
        }
    });
});
</script>

        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('frontend.profile')}}"><i class="fas fa-user"style="color:white;"></i></a>
            </div>
            <div class="menu-label">Profile</div>
        </div>

        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('frontend.refer.list')}}"><i class="fas fa-shopping-basket"style="color:white;"></i></a>
            </div>
            <div class="menu-label">Refer</div>
        </div>

        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('frontend.options')}}"><i class="fas fa-user-plus"style="color:white;"></i></a>
            </div>
            <div class="menu-label">Options</div>
        </div>

        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('frontend.widthraw')}}"><i class="fas fa-money-bill-wave"style="color:white;"></i></a>
            </div>
            <div class="menu-label">Withdraw</div>
        </div>
        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('frontend.payment.history')}}"><i class="fas fa-bell"style="color:white;"></i></a>
            </div>
            <div class="menu-label">Payment History</div>
        </div>

        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('user.accept.view')}}"><i class="fas fa-user-check"style="color:white;"></i></a>
            </div>
            <div class="menu-label">Friend Request</div>
        </div>

        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('frontend.stepguide')}}"><i class="fas fa-box"style="color:white;"></i></a>
            </div>
            <div class="menu-label">How To Work</div>
        </div>

        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('frontend.support')}}"><i class="fas fa-coins"style="color:white;"></i></a>
            </div>
            <div class="menu-label">Support</div>
        </div>
         <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('buy.sellpost')}}"><i class="fas fa-coins"style="color:white;"></i></a>
            </div>
            <div class="menu-label">P2P</div>
        </div>
         <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('total.deposite')}}"><i class="fas fa-coins"style="color:white;"></i></a>
            </div>
            <div class="menu-label">Total Deposite</div>
        </div>

    </div>
    <button class="usertoadminchat-floating-btn" id="usertoadminchatButton">
        <i class="fas fa-comments"></i>
        <span class="usertoadminchat-badge" id="usertoadminchatBadge">0</span>
    </button>

    <div class="usertoadminchat-window" id="usertoadminchatWindow">
        <div class="usertoadminchat-header">
            <div class="usertoadminchat-header-left">
                <button class="usertoadminchat-back-btn" id="usertoadminchatBackButton">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="usertoadminchat-admin-info">
                    <div class="usertoadminchat-admin-avatar">A</div>
                    <div class="usertoadminchat-admin-details">
                        <div class="usertoadminchat-title">Admin</div>
                        <div class="usertoadminchat-admin-status">
                            <span class="usertoadminchat-status-dot"></span>Online
                        </div>
                    </div>
                </div>
            </div>
            <button class="usertoadminchat-close-btn" id="usertoadminchatCloseButton">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="usertoadminchat-messages" id="usertoadminchatMessages">
            <div class="usertoadminchat-message usertoadminchat-admin">
                <div class="usertoadminchat-message-avatar">A</div>
                <div class="usertoadminchat-message-content">
                    <div class="usertoadminchat-admin-badge">Admin</div>
                    <div class="usertoadminchat-message-bubble">
                        অনুগ্রহ করে আপনার কাউন্টার নম্বরটি দিন, আমাদের একজন প্রতিনিধি আপনার সাথে যোগাযোগ করে কনফিউড সার্ভিসটি সম্পন্ন করে আনবেন, অথবা সরাসরি আমাদের হেল্পলাইন 01638000247 এ যোগাযোগ করুন । সময়টা 8 টা থেকে রাত 11 টা পর্জন্ত । ধনবাদ ।
                    </div>
                    <div class="usertoadminchat-message-time">11:33 AM</div>
                </div>
            </div>

            <div class="usertoadminchat-message usertoadminchat-user">
                <div class="usertoadminchat-message-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="usertoadminchat-message-content">
                    <div class="usertoadminchat-message-bubble">
                        Hello! How can I help?
                    </div>
                    <div class="usertoadminchat-message-time">11:33 AM</div>
                </div>
            </div>

            <div class="usertoadminchat-message usertoadminchat-admin usertoadminchat-hidden" id="usertoadminchatTypingContainer">
                <div class="usertoadminchat-message-avatar">A</div>
                <div class="usertoadminchat-typing-indicator" id="usertoadminchatTypingIndicator">
                    <div class="usertoadminchat-typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="usertoadminchat-emoji-picker" id="usertoadminchatEmojiPicker">
            <div class="usertoadminchat-emoji-grid" id="usertoadminchatEmojiGrid"></div>
        </div>

        <div class="usertoadminchat-input-area">
            <div class="usertoadminchat-image-preview-container" id="usertoadminchatImagePreviewContainer">
                <img src="" alt="Preview" class="usertoadminchat-preview-image" id="usertoadminchatPreviewImage">
                <button class="usertoadminchat-remove-preview" id="usertoadminchatRemovePreviewBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="usertoadminchat-input-controls">
                <input type="file" class="usertoadminchat-file-input" id="usertoadminchatFileInput" accept="image/*">
                <button class="usertoadminchat-attach-btn" id="usertoadminchatAttachButton">
                    <i class="fas fa-paperclip"></i>
                </button>
                <button class="usertoadminchat-emoji-btn" id="usertoadminchatEmojiButton">
                    <i class="fas fa-smile"></i>
                </button>
                <input
                    type="text"
                    class="usertoadminchat-input"
                    id="usertoadminchatInput"
                    placeholder="Write a reply..."
                >
                <button class="usertoadminchat-send-btn" id="usertoadminchatSendButton">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="usertoadminchat-hidden">
        <div id="usertoadminchatUserMessageTemplate" class="usertoadminchat-message usertoadminchat-user">
            <div class="usertoadminchat-message-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="usertoadminchat-message-content">
                <div class="usertoadminchat-message-bubble">
                    <span class="usertoadminchat-message-text"></span>
                    <div class="usertoadminchat-message-image usertoadminchat-hidden">
                        <img src="" alt="Uploaded image">
                    </div>
                </div>
                <div class="usertoadminchat-message-time"></div>
            </div>
        </div>

        <div id="usertoadminchatAdminMessageTemplate" class="usertoadminchat-message usertoadminchat-admin">
            <div class="usertoadminchat-message-avatar">A</div>
            <div class="usertoadminchat-message-content">
                <div class="usertoadminchat-admin-badge">Admin</div>
                <div class="usertoadminchat-message-bubble">
                    <span class="usertoadminchat-message-text"></span>
                    <div class="usertoadminchat-message-image usertoadminchat-hidden">
                        <img src="" alt="Admin image">
                    </div>
                </div>
                <div class="usertoadminchat-message-time"></div>
            </div>
        </div>
    </div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const sendBtn = document.getElementById("usertoadminchatSendButton");
    const input = document.getElementById("usertoadminchatInput");
    const messagesContainer = document.getElementById("usertoadminchatMessages");
    const fileInput = document.getElementById("usertoadminchatFileInput");
    const previewContainer = document.getElementById("usertoadminchatImagePreviewContainer");
    const previewImage = document.getElementById("usertoadminchatPreviewImage");
    const removePreviewBtn = document.getElementById("usertoadminchatRemovePreviewBtn");
    const badge = document.getElementById("usertoadminchatBadge");
    const chatWindow = document.getElementById("usertoadminchatWindow");

    const receiverId = 1; // Admin ID
    let chatOpen = false;
    let unreadCount = 0;

    // Load messages
    async function loadMessages(markRead = false) {
        try {
            const res = await fetch("{{ route('usertoadminchat.fetch') }}");
            const data = await res.json();

            messagesContainer.innerHTML = "";
            unreadCount = 0;

            data.forEach(msg => {
                const div = document.createElement("div");
                div.classList.add(
                    "usertoadminchat-message",
                    msg.sender_id === {{ auth()->id() }} ? "usertoadminchat-user" : "usertoadminchat-admin"
                );

                let html = `
                    <div class="usertoadminchat-message-avatar">
                        ${msg.sender_id === {{ auth()->id() }} ? '<i class="fas fa-user"></i>' : 'A'}
                    </div>
                    <div class="usertoadminchat-message-content">
                        ${msg.sender_id !== {{ auth()->id() }} ? '<div class="usertoadminchat-admin-badge">Admin</div>' : ''}
                        <div class="usertoadminchat-message-bubble">
                            ${msg.message || ''}
                            ${msg.image ? `<div class="usertoadminchat-message-image"><img src="/storage/${msg.image}" alt=""></div>` : ''}
                        </div>
                        <div class="usertoadminchat-message-time">${new Date(msg.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</div>
                    </div>
                `;
                div.innerHTML = html;
                messagesContainer.appendChild(div);

                if (!msg.is_read && msg.sender_id !== {{ auth()->id() }}) unreadCount++;
            });

            badge.textContent = unreadCount;
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            // Mark as read if chat open
            if (markRead && unreadCount > 0) {
                await fetch("{{ route('usertoadminchat.markread') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ sender_id: receiverId })
                });
                unreadCount = 0;
                badge.textContent = 0;
            }
        } catch (err) {
            console.error("Error fetching messages:", err);
        }
    }

    loadMessages();
    setInterval(loadMessages, 5000);

    // Preview image
    fileInput.addEventListener("change", function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                previewImage.src = e.target.result;
                previewContainer.style.display = "block";
            };
            reader.readAsDataURL(file);
        }
    });

    removePreviewBtn.addEventListener("click", function() {
        previewContainer.style.display = "none";
        fileInput.value = "";
    });

    // Send message
    sendBtn.addEventListener("click", async function() {
        const message = input.value.trim();
        const image = fileInput.files[0];
        if (!message && !image) return;

        const formData = new FormData();
        formData.append("message", message);
        formData.append("receiver_id", receiverId);
        if (image) formData.append("image", image);
        formData.append("_token", "{{ csrf_token() }}");

        await fetch("{{ route('usertoadminchat.send') }}", { method: "POST", body: formData });

        input.value = "";
        fileInput.value = "";
        previewContainer.style.display = "none";
        loadMessages();
    });

    // Chat window open marks messages read
    chatWindow.addEventListener("mouseenter", function() {
        if (!chatOpen) {
            chatOpen = true;
            loadMessages(true);
        }
    });
});
</script>


@endsection
