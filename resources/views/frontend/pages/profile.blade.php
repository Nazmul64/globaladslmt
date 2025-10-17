    <!-- Top Header -->
    <div class="top-header">
        <div class="header-content">
            <i class="fas fa-bars menu-icon" onclick="toggleSidebar()"></i>
            <div class="app-title">Global money Ltd</div>
            <i class="fas fa-bell notification-icon" onclick="showNotification()"></i>
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
