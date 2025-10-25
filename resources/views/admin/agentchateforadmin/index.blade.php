@extends('admin.master')

@section('content')
<main class="dashboard-main">
  <div class="dashboard-main-body d-flex gap-3">

    {{-- Sidebar --}}
    <div class="chat-sidebar card p-3" style="width:280px;">
      <h6 class="mb-3">Agents</h6>
      @foreach($agents as $agent)
        @php
            $unreadCount = \App\Models\Adminchatforagent::where('sender_id', $agent->id)
                            ->where('receiver_id', auth()->id())
                            ->where('is_read', 0)
                            ->count();
        @endphp
        <div class="chat-sidebar-single p-2 mb-2 border rounded d-flex justify-content-between align-items-center"
             data-id="{{ $agent->id }}"
             data-name="{{ $agent->name }}"
             style="cursor:pointer;">
          <div>
            <strong>{{ $agent->name }}</strong><br>
            <small>{{ $agent->email }}</small>
          </div>
          @if($unreadCount > 0)
            <span class="badge bg-danger">{{ $unreadCount }}</span>
          @endif
        </div>
      @endforeach
    </div>

    {{-- Chat Box --}}
    <div class="chat-main card flex-fill p-3">
      <div id="chatHeader" class="mb-2 d-flex justify-content-between align-items-center">
        <strong id="chatPartnerName">Select an agent to chat</strong>
        <button id="markReadBtn" class="btn btn-sm btn-outline-secondary d-none">Mark Read</button>
      </div>

      <div id="chatMessages" style="height:420px; overflow-y:auto; border:1px solid #eee; padding:12px; background:#fafafa;"></div>

      <form id="chatForm" class="d-flex mt-3 gap-2" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="receiver_id" name="receiver_id">
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
let currentReceiver = null;
let refreshInterval = null;

// Select agent
$(document).on('click', '.chat-sidebar-single', function() {
    $('.chat-sidebar-single').removeClass('bg-primary text-white');
    $(this).addClass('bg-primary text-white');

    currentReceiver = $(this).data('id');
    $('#receiver_id').val(currentReceiver);
    $('#chatPartnerName').text($(this).data('name'));
    $('#markReadBtn').removeClass('d-none');

    loadMessages();
    markRead(); // mark unread as read

    if (refreshInterval) clearInterval(refreshInterval);
    refreshInterval = setInterval(loadMessages, 2000);
});

// Attach button
$('#attachBtn').click(() => $('#image').click());

// Emoji picker
$('#emojiBtn').click(() => $('#emojiPickerContainer').toggleClass('d-none'));
document.querySelector('emoji-picker').addEventListener('emoji-click', e => {
  $('#message').val($('#message').val() + e.detail.unicode);
});

// Send message
$('#chatForm').on('submit', function(e) {
    e.preventDefault();
    if (!currentReceiver) return alert('Select an agent first.');

    let formData = new FormData(this);
    $.ajax({
        url: "{{ route('admin.agent.chat.send') }}",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function() {
            $('#message').val('');
            $('#image').val('');
            loadMessages();
            updateUnreadCount();
        }
    });
});

// Load messages
function loadMessages() {
    if (!currentReceiver) return;
    $.get(`/admin/agent/chat/fetch/${currentReceiver}`, function(data) {
        let html = '';
        data.forEach(msg => {
            const isMe = msg.sender_id === myId;
            const bg = isMe ? '#007bff' : '#f1f1f1';
            const color = isMe ? '#fff' : '#000';
            const align = isMe ? 'text-end' : 'text-start';
            const img = msg.image ? `<div><img src="/${msg.image}" style="max-width:150px;border-radius:6px;margin-top:5px;"></div>` : '';

            html += `
              <div class="mb-2 ${align}">
                <div style="display:inline-block;padding:8px 12px;border-radius:10px;background:${bg};color:${color};max-width:70%;">
                  ${msg.message ?? ''} ${img}
                </div>
                <small class="text-muted d-block">${new Date(msg.created_at).toLocaleTimeString()}</small>
              </div>
            `;
        });
        $('#chatMessages').html(html).scrollTop($('#chatMessages')[0].scrollHeight);
    });
}

// Mark as read
function markRead() {
    if (!currentReceiver) return;
    $.post(`/admin/agent/chat/mark-read/${currentReceiver}`, {_token: '{{ csrf_token() }}'}, function(){
        updateUnreadCount();
    });
}

// Update unread badge
function updateUnreadCount() {
    $('.chat-sidebar-single').each(function() {
        const agentId = $(this).data('id');
        $.get(`/admin/agent/unread-count/${agentId}`, function(count) {
            if(count > 0){
                if($(this).find('.badge').length){
                    $(this).find('.badge').text(count);
                } else {
                    $(this).append(`<span class="badge bg-danger">${count}</span>`);
                }
            } else {
                $(this).find('.badge').remove();
            }
        }.bind(this));
    });
}

setInterval(updateUnreadCount, 5000);

</script>
@endsection
