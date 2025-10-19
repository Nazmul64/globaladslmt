<div class="profile-container">
    <div class="profile-content">
        <!-- Profile Info Left -->


        <div class="profile-info">
            <div class="profile-role">Owner</div>

                @php
                    $usercopy = auth()->user();
                @endphp

                @if($usercopy)
                <div class="info-row">
                    <span class="info-label">Refer Code</span>
                    <div class="info-value refer-code" onclick="copyReferCode()" title="Click to copy" style="cursor:pointer; padding:5px;  display:inline-block;">
                        <span id="referCodeText" style="color:white;">{{ $usercopy->ref_code }}</span>
                        <i class="fas fa-copy copy-icon" style="color:white;"></i>
                    </div>
                </div>
                @endif

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
