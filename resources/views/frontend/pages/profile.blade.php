    <!-- Top Header -->
    <div class="top-header">
        <div class="header-content">
            <i class="fas fa-bars menu-icon" onclick="toggleSidebar()"></i>
            <div class="app-title">Global money Ltd</div>
            <i class="fas fa-bell nazmulNotifications-icon" id="nazmulNotificationsIcon"></i>
        </div>
    </div>


<!-- ============ CSS CODE ============ -->

<!-- ============ HTML CODE ============ -->
<div class="profile-container">
    <div class="profile-content">
        <!-- Profile Info Left -->
        <div class="profile-info">
            <div class="profile-role">Owner</div>

            <div class="info-row">
                <span class="info-label">Refer Code</span>
                <div class="info-value refer-code" onclick="copyReferCode(event)" title="Click to copy">
                    <span id="referCodeText"style="color:white;"style="color:white;">01811111111</span>
                    <i class="fas fa-copy copy-icon"></i>
                </div>
            </div>

            <div class="tap-to-view" onclick="toggleBalance()">
                Tap to view balance
                <i class="fas fa-chevron-down tap-arrow" id="tapArrow"style="color:white;"></i>
            </div>

            <!-- Hidden Balance -->
            <div class="profile-details" id="balanceDetails">
                <div class="info-row">
                    <span class="info-label">Balance</span>
                    <div class="info-value">
                        <i class="fas fa-wallet coin-icon"></i>
                        <span id="balanceAmount"style="color:white;">142 Coin</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Avatar Right -->
        <div class="profile-avatar">
            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Felix" alt="Profile">
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast" id="toast">
    <i class="fas fa-check-circle"></i>
    <span>Refer code copied!</span>
</div>


<style>
/* 🔔 Notification Icon */
.nazmulNotifications-icon {
    font-size: 35px;
    color: #ff5436;
    cursor: pointer;
    transition: transform 0.2s;
}
.nazmulNotifications-icon:hover {
    transform: scale(1.15);
}

/* 🌫 Modal Overlay */
.nazmulNotifications-modal-overlay {
    display: none; /* hidden by default */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.45);
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

/* 📦 Modal Box */
.nazmulNotifications-modal-box {
    background: #fff;
    border-radius: 20px;
    padding: 25px 30px;
    width: 350px;
    text-align: left;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    animation: nazmulNotifications-zoom 0.3s ease;
}

/* 💫 Animation */
@keyframes nazmulNotifications-zoom {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

/* ❌ Close Button */
.nazmulNotifications-close-btn {
    float: right;
    font-size: 20px;
    color: #666;
    cursor: pointer;
}
.nazmulNotifications-close-btn:hover {
    color: #ff5436;
}

/* 📝 Modal Header */
.nazmulNotifications-modal-box h2 {
    margin-bottom: 15px;
    color: #ff5436;
    font-size: 20px;
    border-bottom: 2px solid #ff5436;
    padding-bottom: 8px;
}

/* 📬 Notification List */
.nazmulNotifications-modal-box ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.nazmulNotifications-modal-box ul li {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
    font-size: 15px;
    color: #333;
}
</style>


<!-- 🔔 Notification Icon -->

@php
    use App\Models\Notice;
    $notices = Notice::get();
@endphp

<!-- 💬 Modal Overlay -->
<div class="nazmulNotifications-modal-overlay" id="nazmulNotificationsModal">
  <div class="nazmulNotifications-modal-box">
    <span class="nazmulNotifications-close-btn" id="nazmulNotificationsCloseBtn">&times;</span>
    <h2>Notifications</h2>
    <ul>
      @forelse($notices as $notice)
          <li>
            <strong>{{ $notice->title }}</strong><br>
            {{ $notice->description }}
          </li>
     @empty
          <li>No Notices Available</li>
      @endforelse
    </ul>
  </div>
</div>


<script>
// Open modal on icon click
document.getElementById("nazmulNotificationsIcon").addEventListener("click", function() {
    const modal = document.getElementById("nazmulNotificationsModal");
    modal.style.display = modal.style.display === "flex" ? "none" : "flex";
});

// Close modal on close button click
document.getElementById("nazmulNotificationsCloseBtn").addEventListener("click", function() {
    document.getElementById("nazmulNotificationsModal").style.display = "none";
});

// Close modal on clicking outside
window.addEventListener("click", function(e) {
    const modal = document.getElementById("nazmulNotificationsModal");
    if (e.target === modal) {
        modal.style.display = "none";
    }
});
</script>
