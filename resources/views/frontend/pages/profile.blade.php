<div class="profile-container">
    <div class="profile-content">

        <!-- AUTH USER CHECK -->
        @php
            use App\Models\Kyc;

            $user = auth()->user();
            $kyc = $user ? Kyc::where('user_id', $user->id)->first() : null;
        @endphp

        <div class="profile-info">

            <!-- USER NAME + VERIFIED BADGE -->
            <div class="profile-role">
                {{ $user->name ?? 'Guest User' }}

                @if($user && Kyc::where('user_id', $user->id)->where('status','approved')->exists())
                    <span class="badge bg-success ms-2">
                        <i class="fas fa-check-circle me-1"></i> Verified
                    </span>
                @else
                    <span class="badge bg-danger ms-2">
                        <i class="fas fa-times-circle me-1"></i> Unverified
                    </span>
                @endif
            </div>

            <!-- REFERRAL CODE COPY -->
            @if($user)
                <div class="info-row">
                    <div class="info-value refer-code"
                         onclick="copyReferCode()" title="Click to copy"
                         style="cursor:pointer; padding:5px; display:inline-block;">
                        <span id="referCodeText" style="color:white;">
                            {{ $user->ref_code }}
                        </span>
                        <i class="fas fa-copy copy-icon" style="color:white;"></i>
                    </div>
                </div>
            @endif

            <!-- TOGGLE BALANCE -->
            <div class="tap-to-view" onclick="toggleBalance()">
                Tap to view balance
                <i class="fas fa-chevron-down tap-arrow" id="tapArrow" style="color:white;"></i>
            </div>

            @php
                // FIXED: logged-in user's balance ONLY
                $user_balance = $user ? $user->balance : 0;
            @endphp

            <!-- BALANCE DETAILS -->
            <div class="profile-details" id="balanceDetails" style="display:none;">
                <div class="info-row">
                    <span class="info-label">Balance</span>
                    <div class="info-value">
                        <i class="fas fa-wallet coin-icon"></i>
                        <span id="balanceAmount" style="color:white;">
                            {{ round($user_balance) }} BDT
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- AVATAR -->
        <div class="profile-avatar">
            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Felix" alt="Profile">
        </div>

    </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast">
    <i class="fas fa-check-circle"></i>
    <span>Refer code copied!</span>
</div>

<!-- JS -->
<script>
    function toggleBalance() {
        let details = document.getElementById('balanceDetails');
        let arrow = document.getElementById('tapArrow');

        if (details.style.display === "none") {
            details.style.display = "block";
            arrow.style.transform = "rotate(180deg)";
        } else {
            details.style.display = "none";
            arrow.style.transform = "rotate(0deg)";
        }
    }

    function copyReferCode() {
        let code = document.getElementById('referCodeText').innerText;
        navigator.clipboard.writeText(code);

        let toast = document.getElementById('toast');
        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
        }, 1500);
    }
</script>
