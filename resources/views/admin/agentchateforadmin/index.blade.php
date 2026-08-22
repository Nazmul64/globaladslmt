@extends('admin.master')

@section('content')

<style>
.agent-chat-wrapper {
  display: flex;
  gap: 1.5rem;
  width: 100%;
}

.agent-chat-sidebar {
  width: 320px;
  min-width: 320px;
  max-height: calc(100vh - 180px);
  overflow-y: auto;
}

.agent-chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 550px;
}

.chat-sidebar-single {
  cursor: pointer;
  transition: all 0.2s ease;
}

.chat-sidebar-single:hover {
  background-color: #f8f9fa !important;
  transform: translateX(3px);
}

.chat-sidebar-single.active {
  background-color: #0d6efd !important;
  color: #fff !important;
}

.chat-sidebar-single.active small,
.chat-sidebar-single.active .last-msg-preview {
  color: rgba(255, 255, 255, 0.8) !important;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
  .agent-chat-wrapper {
    flex-direction: column;
    gap: 1rem;
  }

  .agent-chat-sidebar {
    width: 100% !important;
    min-width: 100% !important;
    max-height: 220px;
  }

  .agent-chat-main {
    min-height: 450px;
  }
}
</style>

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h6 class="fw-semibold mb-0">Agent Chats</h6>
    <small class="text-muted">Real-time messaging with agents</small>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-primary">
      <i class="fas fa-home"></i> Dashboard
    </a>
  </div>
</div>

<div class="agent-chat-wrapper">

  {{-- Sidebar --}}
  <div class="agent-chat-sidebar card p-3" id="agentListContainer">
    <h6 class="mb-3 d-flex justify-content-between align-items-center">
      <span><i class="fas fa-user-shield me-1"></i> Agents</span>
      <span class="badge bg-primary" id="agentCountBadge">{{ count($agents) }}</span>
    </h6>

    <div id="agentList">
      @forelse($agents as $agent)
        @php
            $agentAvatarSrc = (!empty($agent->photo) && file_exists(public_path('uploads/profile/' . $agent->photo)))
                ? asset('uploads/profile/' . $agent->photo)
                : asset('uploads/profile/avator.jpg');
        @endphp
        <div class="chat-sidebar-single p-2 mb-2 border rounded d-flex justify-content-between align-items-center"
             data-id="{{ $agent->id }}"
             data-name="{{ $agent->name }}"
             data-last-time="{{ $agent->latest_message_time ?? 0 }}"
             data-last-id="{{ $agent->latest_message_id ?? 0 }}">
          <div class="d-flex align-items-center overflow-hidden me-2">
            <img src="{{ $agentAvatarSrc }}"
                 onerror="this.onerror=null;this.src='{{ asset('uploads/profile/avator.jpg') }}';"
                 class="rounded-circle me-2 flex-shrink-0"
                 width="35" height="35" style="object-fit: cover;">
            <div class="overflow-hidden">
              <strong class="d-block text-truncate" style="font-size: 14px;">{{ $agent->name }}</strong>
              <small class="text-muted d-block text-truncate last-msg-preview" style="font-size: 12px;">{{ $agent->last_message_text ?: $agent->email }}</small>
            </div>
          </div>
          <span class="badge bg-danger rounded-pill unread-badge"
                id="agent-unread-{{ $agent->id }}"
                style="{{ ($agent->unread_count ?? 0) > 0 ? 'display:inline-block;' : 'display:none;' }}">{{ $agent->unread_count ?? 0 }}</span>
        </div>
      @empty
        <div class="text-center text-muted py-3">
          <p class="mb-0">No approved agents found</p>
        </div>
      @endforelse
    </div>
  </div>

  {{-- Chat Box --}}
  <div class="agent-chat-main card p-3">
    <div id="chatHeader" class="mb-2 d-flex justify-content-between align-items-center border-bottom pb-2">
      <strong id="chatPartnerName" class="fs-6">Select an agent to chat</strong>
      <button id="markReadBtn" class="btn btn-sm btn-outline-secondary d-none">Mark Read</button>
    </div>

    <div id="chatMessages" class="flex-grow-1 mb-3" style="height:400px; overflow-y:auto; border:1px solid #eee; padding:12px; background:#fafafa; border-radius:8px;">
      <div class="text-center text-muted py-5">
        <i class="fas fa-comments fa-3x mb-2"></i>
        <p>Select an agent from the left sidebar to start messaging</p>
      </div>
    </div>

    <form id="chatForm" class="d-flex gap-2 align-items-center" enctype="multipart/form-data">
      @csrf
      <input type="hidden" id="receiver_id" name="receiver_id">
      <input type="text" id="message" name="message" class="form-control" placeholder="Type a message..." disabled>
      <input type="file" id="image" name="image" class="d-none">
      <button type="button" id="attachBtn" class="btn btn-light border" disabled>📎</button>
      <button type="button" id="emojiBtn" class="btn btn-light border" disabled>😊</button>
      <button type="submit" id="sendBtn" class="btn btn-primary" disabled>Send</button>
    </form>

    <div id="emojiPickerContainer" class="mt-2 d-none">
      <emoji-picker style="max-width:300px;"></emoji-picker>
    </div>
  </div>

</div>

<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

<script>
const myId = {{ auth()->id() }};
let currentReceiver = null;
let refreshInterval = null;
let lastMsgId = 0;

// Select agent
$(document).on('click', '.chat-sidebar-single', function() {
    $('.chat-sidebar-single').removeClass('active');
    $(this).addClass('active');

    currentReceiver = $(this).data('id');
    $('#receiver_id').val(currentReceiver);
    $('#chatPartnerName').html('<i class="fas fa-circle text-success me-1" style="font-size:10px;"></i> ' + $(this).data('name'));
    $('#markReadBtn').removeClass('d-none');

    // Enable inputs
    $('#message, #attachBtn, #emojiBtn, #sendBtn').prop('disabled', false);

    // Reset lastMsgId and clear messages box
    lastMsgId = 0;
    $('#chatMessages').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');

    loadMessages();
    markRead();

    if (refreshInterval) clearInterval(refreshInterval);
    refreshInterval = setInterval(loadMessages, 2500);
});

// Attach button
$('#attachBtn').click(() => $('#image').click());

// Emoji picker
$('#emojiBtn').click(() => $('#emojiPickerContainer').toggleClass('d-none'));
document.querySelector('emoji-picker')?.addEventListener('emoji-click', e => {
  $('#message').val($('#message').val() + e.detail.unicode);
});

// Send message
$('#chatForm').on('submit', function(e) {
    e.preventDefault();
    if (!currentReceiver) return alert('Select an agent first.');

    const msgText = $('#message').val().trim();
    const file = $('#image')[0].files[0];
    if (!msgText && !file) return;

    let formData = new FormData(this);
    $.ajax({
        url: "{{ route('admin.agent.chat.send') }}",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            $('#message').val('');
            $('#image').val('');

            if (res.chat) {
                appendSingleMessage(res.chat);
                lastMsgId = Math.max(lastMsgId, res.chat.id);
            }

            // Move current agent to top of list instantly
            const agentItem = $(`.chat-sidebar-single[data-id="${currentReceiver}"]`);
            if (agentItem.length) {
                const nowTs = Math.floor(Date.now() / 1000);
                agentItem.attr('data-last-time', nowTs);
                const txt = res.chat?.message ? (res.chat.message.length > 30 ? res.chat.message.substring(0, 30) + '...' : res.chat.message) : '📷 Image';
                agentItem.find('.last-msg-preview').text(txt);
                $('#agentList').prepend(agentItem);
            }
        }
    });
});

// Load messages
function loadMessages() {
    if (!currentReceiver) return;
    $.get(`/admin/agent/chat/fetch/${currentReceiver}`, function(data) {
        if (!data || data.length === 0) {
            if (lastMsgId === 0) {
                $('#chatMessages').html('<div class="text-center text-muted py-5"><p>No messages yet. Say hello!</p></div>');
            }
            return;
        }

        if (lastMsgId === 0) {
            $('#chatMessages').html('');
        }

        data.forEach(msg => {
            appendSingleMessage(msg);
            lastMsgId = Math.max(lastMsgId, msg.id);
        });
    });
}

function appendSingleMessage(msg) {
    if (!msg) return;
    if (msg.id && document.getElementById(`agent-msg-${msg.id}`)) {
        return; // Prevent duplicates
    }

    const isMe = msg.sender_id === myId;
    const bg = isMe ? '#0d6efd' : '#e9ecef';
    const color = isMe ? '#fff' : '#212529';
    const align = isMe ? 'text-end' : 'text-start';
    const img = msg.image ? `<div><img src="/${msg.image}" style="max-width:200px;border-radius:6px;margin-top:5px;"></div>` : '';

    const timeStr = msg.created_at ? new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '';

    const html = `
      <div id="agent-msg-${msg.id}" class="mb-3 ${align}">
        <div style="display:inline-block;padding:10px 14px;border-radius:12px;background:${bg};color:${color};max-width:75%;word-break:break-word;box-shadow:0 1px 2px rgba(0,0,0,0.05);">
          ${msg.message ? escapeHtml(msg.message) : ''} ${img}
        </div>
        <small class="text-muted d-block mt-1" style="font-size:11px;">${timeStr}</small>
      </div>
    `;

    $('#chatMessages').append(html);
    $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight);
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

// Mark as read
function markRead() {
    if (!currentReceiver) return;
    $.post(`/admin/agent/chat/mark-read/${currentReceiver}`, {_token: '{{ csrf_token() }}'}, function(){
        $(`#agent-unread-${currentReceiver}`).hide().text('0');
    });
}

// Update unread badges and auto-sort agents by last active time
function updateUnreadCount() {
    $.get("{{ route('admin.agent.unread.all') }}", function(data) {
        const unreadData = data.unread || {};
        const latestData = data.latest || {};

        const agentItems = $('.chat-sidebar-single').toArray();

        agentItems.forEach(item => {
            const $item = $(item);
            const agentId = $item.data('id');

            if (latestData[agentId]) {
                $item.attr('data-last-time', latestData[agentId].last_time || 0);
                $item.attr('data-last-id', latestData[agentId].last_id || 0);
                if (latestData[agentId].last_message) {
                    $item.find('.last-msg-preview').text(latestData[agentId].last_message);
                }
            }

            const count = unreadData[agentId] || 0;
            const $badge = $item.find('.unread-badge');
            if (count > 0) {
                $badge.show().text(count);
            } else if (currentReceiver != agentId) {
                $badge.hide().text('0');
            }
        });

        // Sort items by last_time descending
        agentItems.sort((a, b) => {
            const timeA = parseInt($(a).attr('data-last-time')) || 0;
            const timeB = parseInt($(b).attr('data-last-time')) || 0;
            if (timeB !== timeA) return timeB - timeA;
            const countA = unreadData[$(a).data('id')] || 0;
            const countB = unreadData[$(b).data('id')] || 0;
            return countB - countA;
        });

        // Re-append sorted elements
        agentItems.forEach(item => {
            $('#agentList').append(item);
        });
    });
}

updateUnreadCount();
setInterval(updateUnreadCount, 3000);

</script>
@endsection
