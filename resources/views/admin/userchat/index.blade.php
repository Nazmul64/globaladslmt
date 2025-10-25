@extends('admin.master')
@section('content')

<main class="dashboard-main">
  <div class="dashboard-main-body">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h6 class="fw-semibold mb-0">User Chats</h6>
      <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-primary">Dashboard</a>
    </div>

    <div class="chat-wrapper d-flex gap-3">
      {{-- Sidebar --}}
      <div class="chat-sidebar card p-3" style="width:300px;">
        <h6 class="mb-3">Users</h6>
        <div id="userList">
          @foreach($users as $user)
          <div class="user-item p-2 border rounded mb-2 d-flex justify-content-between align-items-center"
               data-id="{{ $user->id }}" style="cursor:pointer;">
            <div>
              <strong>{{ $user->name }}</strong><br>
              <small>{{ $user->email }}</small>
            </div>
            <span class="badge bg-success text-white unread-count" style="display:none;">0</span>
          </div>
          @endforeach
        </div>
      </div>

      {{-- Chat Area --}}
      <div class="chat-main card flex-grow-1 p-3 d-flex flex-column">
        <h6 id="chatUserName" class="mb-3">Select a user</h6>

        <div id="chatBox" class="border rounded p-2 mb-3 flex-grow-1 overflow-auto">
          <p class="text-muted text-center mt-5">No chat selected</p>
        </div>

        <form id="chatForm" enctype="multipart/form-data" class="d-flex gap-2">
          @csrf
          <input type="hidden" name="receiver_id" id="receiver_id">
          <input type="text" class="form-control" name="message" id="message" placeholder="Type a message...">
          <input type="file" name="image" id="chatImage" class="form-control" style="width:180px;">
          <button type="submit" class="btn btn-primary">Send</button>
        </form>
      </div>
    </div>
  </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const chatBox = document.getElementById("chatBox");
    const chatForm = document.getElementById("chatForm");
    const receiverInput = document.getElementById("receiver_id");
    const messageInput = document.getElementById("message");
    const chatUserName = document.getElementById("chatUserName");
    let selectedUser = null;
    let lastMessageId = 0;

    // Fetch unread counts and move recent users to top
    async function fetchUnread() {
        const res = await fetch("{{ route('admin.user.unread') }}");
        const data = await res.json();
        Object.keys(data).forEach(userId => {
            const userEl = document.querySelector(`.user-item[data-id='${userId}']`);
            const badge = userEl.querySelector(".unread-count");
            const count = data[userId];

            if(count > 0){
                badge.style.display = "inline-block";
                badge.innerText = count;
                userEl.parentNode.prepend(userEl); // move to top
                userEl.style.fontWeight = "bold";
            } else {
                badge.style.display = "none";
                userEl.style.fontWeight = "normal";
            }
        });
    }

    // Initial fetch and interval
    fetchUnread();
    setInterval(fetchUnread, 2000);

    // Select user
    document.querySelectorAll(".user-item").forEach(item => {
        item.addEventListener("click", async function() {
            document.querySelectorAll(".user-item").forEach(u => u.classList.remove("bg-primary","text-white"));
            this.classList.add("bg-primary","text-white");

            selectedUser = this.dataset.id;
            receiverInput.value = selectedUser;
            chatUserName.innerText = this.querySelector("strong").innerText;

            // Mark as read
            await fetch(`/admin/to/chat/mark-read/${selectedUser}`, {
                method: 'POST',
                headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}'}
            });

            lastMessageId = 0;
            chatBox.innerHTML = '';
            loadMessages();
        });
    });

    // Load new messages
    async function loadMessages() {
        if(!selectedUser) return;
        const res = await fetch(`/admin/to/chat/fetch/${selectedUser}?last_id=${lastMessageId}`);
        const data = await res.json();

        data.forEach(msg => {
            if(msg.id > lastMessageId){
                appendMessage(msg);
                lastMessageId = msg.id;
            }
        });
    }

    function appendMessage(msg){
        const isAdmin = msg.sender_id === {{ Auth::id() }};
        const side = isAdmin ? 'text-end' : 'text-start';
        let content = `<div class="${side} mb-2 ${!isAdmin && !msg.is_read ? 'bg-success text-white p-1 rounded' : ''}">`;

        if(msg.message) content += `<div class="p-2 rounded bg-light d-inline-block">${msg.message}</div>`;
        if(msg.image) content += `<br><img src="/storage/${msg.image}" width="120" class="rounded mt-1">`;
        content += `</div>`;

        chatBox.innerHTML += content;
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Send message
    chatForm.addEventListener("submit", async function(e){
        e.preventDefault();
        if(!receiverInput.value) return alert("Please select a user first!");
        const formData = new FormData(chatForm);

        const res = await fetch("{{ route('admin.chat.send') }}", {
            method: "POST",
            body: formData
        });
        const data = await res.json();

        if(data.success){
            messageInput.value = '';
            document.getElementById("chatImage").value = '';
            appendMessage(data.chat);
        }
    });

    // Auto load messages every 2 seconds
    setInterval(loadMessages, 2000);
});
</script>

@endsection
