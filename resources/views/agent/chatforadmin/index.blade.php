@extends('agent.master')

@section('content')
<main class="dashboard-main">
  <div class="dashboard-main-body">

    <div class="chat-main card p-3">
      <div id="chatHeader" class="mb-2 d-flex justify-content-between align-items-center">
        <strong>Chat with Admin ({{ $admin->name ?? 'No Admin Found' }})</strong>
        <button id="markReadBtn" class="btn btn-sm btn-outline-secondary">Mark Read</button>
      </div>

      <div id="chatMessages" style="height:400px; overflow-y:auto; border:1px solid #eee; padding:12px; background:#fafafa;"></div>

      <form id="chatForm" class="d-flex mt-3 gap-2" enctype="multipart/form-data">
        @csrf
        <input type="text" id="message" name="message" class="form-control" placeholder="Type a message...">
        <input type="file" id="image" name="image" class="d-none">
        <button type="button" id="attachBtn" class="btn btn-light">📎</button>
        <button type="button" id="emojiBtn" class="btn btn-light">😊</button>
        <button type="submit" class="btn btn-primary">Send</button>
      </form>

      <div id="emojiPickerContainer" class="mt-2 d-none">
        <emoji-picker style="max-width:300px;"></emoji-picker>
      </div>
    </div>

  </div>
</main>

<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<script>
const myId = {{ auth()->id() }};
const adminId = {{ $admin->id ?? 'null' }};
if (!adminId) {
  alert('No admin available.');
}

function loadMessages() {
    if (!adminId) return;
    $.get("{{ route('agentchatadmin.fetch') }}", function(data) {
        let html = '';
        data.forEach(msg => {
            const isMe = msg.sender_id === myId;
            const bg = isMe ? '#0d6efd' : '#e9ecef';
            const color = isMe ? '#fff' : '#000';
            const img = msg.image ? `<div><img src="/${msg.image}" style="max-width:150px;border-radius:6px;margin-top:5px;"></div>` : '';
            html += `
              <div class="mb-2 ${isMe ? 'text-end' : 'text-start'}">
                <div style="display:inline-block;padding:8px 12px;border-radius:10px;background:${bg};color:${color}">
                  ${msg.message ?? ''} ${img}
                </div>
                <small class="text-muted d-block">${new Date(msg.created_at).toLocaleTimeString()}</small>
              </div>
            `;
        });
        $('#chatMessages').html(html).scrollTop($('#chatMessages')[0].scrollHeight);
    });
}

// Emoji toggle
$('#emojiBtn').on('click', () => $('#emojiPickerContainer').toggleClass('d-none'));
document.querySelector('emoji-picker').addEventListener('emoji-click', e => {
    $('#message').val($('#message').val() + e.detail.unicode);
});

// Attach button
$('#attachBtn').on('click', () => $('#image').click());

// Send message
$('#chatForm').on('submit', function(e) {
    e.preventDefault();
    if (!adminId) return;

    let formData = new FormData(this);
    $.ajax({
        url: "{{ route('agentchatforagent.send') }}",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function() {
            $('#message').val('');
            $('#image').val('');
            loadMessages();
        }
    });
});

// Mark read
$('#markReadBtn').on('click', function() {
    $.post("{{ route('agentadmin.markread') }}", {
        _token: '{{ csrf_token() }}'
    });
});

// Auto refresh
setInterval(loadMessages, 2000);
loadMessages();
</script>
@endsection
