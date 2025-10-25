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
      <div class="chat-main card flex-grow-1 p-3">
        <h6 id="chatUserName" class="mb-3">Select a user</h6>

        <div id="chatBox" class="border rounded p-2 mb-3" style="height:450px; overflow-y:auto;">
          <p class="text-muted text-center mt-5">No chat selected</p>
        </div>

        <form id="chatForm" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="receiver_id" id="receiver_id">
          <div class="d-flex align-items-center gap-2">
            <input type="text" class="form-control" name="message" id="message" placeholder="Type a message...">
            <input type="file" name="image" id="chatImage" class="form-control" style="width:180px;">
            <button type="submit" class="btn btn-primary">Send</button>
          </div>
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

    // fetch unread counts and move recent users to top
    function fetchUnread() {
        fetch("{{ route('admin.user.unread') }}") // new route
        .then(res => res.json())
        .then(data => {
            Object.keys(data).forEach(userId => {
                const userEl = document.querySelector(`.user-item[data-id='${userId}']`);
                const badge = userEl.querySelector(".unread-count");
                const count = data[userId];

                if(count > 0){
                    badge.style.display = "inline-block";
                    badge.innerText = count;
                    // Move user to top
                    userEl.parentNode.prepend(userEl);
                    userEl.style.fontWeight = "bold";
                }else{
                    badge.style.display = "none";
                    userEl.style.fontWeight = "normal";
                }
            });
        });
    }

    // call initially
    fetchUnread();
    setInterval(fetchUnread, 2000);

    // User select
    document.querySelectorAll(".user-item").forEach(item => {
        item.addEventListener("click", function() {
            document.querySelectorAll(".user-item").forEach(u => u.classList.remove("bg-primary", "text-white"));
            this.classList.add("bg-primary", "text-white");

            selectedUser = this.dataset.id;
            receiverInput.value = selectedUser;
            chatUserName.innerText = this.querySelector("strong").innerText;

            // mark as read
            fetch(`/admin/to/chat/mark-read/${selectedUser}`, { method: 'POST', headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}'}})
            .then(()=> fetchUnread());

            lastMessageId = 0;
            chatBox.innerHTML = '';
            loadMessages();
        });
    });

    // load messages (append only new)
    function loadMessages() {
        if(!selectedUser) return;
        fetch(`/admin/to/chat/fetch/${selectedUser}?last_id=${lastMessageId}`)
        .then(res=>res.json())
        .then(data=>{
            data.forEach(msg => {
                if(msg.id > lastMessageId){
                    appendMessage(msg);
                    lastMessageId = msg.id;
                }
            });
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

    // send message
    chatForm.addEventListener("submit", function(e){
        e.preventDefault();
        if(!receiverInput.value) return alert("Please select a user first!");
        const formData = new FormData(chatForm);

        fetch("{{ route('admin.chat.send') }}", {
            method:"POST",
            body: formData
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                messageInput.value = '';
                document.getElementById("chatImage").value = '';
                appendMessage(data.chat);
            }
        });
    });

    // auto load messages
    setInterval(loadMessages, 2000);

});
</script>

@endsection
