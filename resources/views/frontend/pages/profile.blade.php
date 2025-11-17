<div class="profile-container">
    <div class="profile-content">
        <!-- Profile Info Left -->
        @php
             $usercopy = auth()->user();
         @endphp
        @php
            use App\Models\Kyc;
            $user = auth()->user();
            $kyc = Kyc::where('user_id', $user->id)->first();
        @endphp
        <div class="profile-info">
          <div class="profile-role">
            {{ $usercopy->name ?? '' }}
            @if(App\Models\Kyc::where('user_id', auth()->id())->where('status','approved')->exists()) <span class="badge bg-success ms-2">
                <i class="fas fa-check-circle me-1"></i>
                Verified</span>
                @else
                 <span class="badge bg-danger ms-2"><i class="fas fa-times-circle me-1">
                    </i> Unverified</span>
                    @endif
            </div>

                @if($usercopy)
                <div class="info-row">
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
            @php
                $user = auth()->user();
                $user_balance = App\Models\User::sum('balance');
            @endphp

            <!-- User Balance -->
            <div class="profile-details" id="balanceDetails">
                <div class="info-row">
                    <span class="info-label">Balance</span>
                    <div class="info-value">
                        <i class="fas fa-wallet coin-icon"></i>
                        <span id="balanceAmount" style="color:white;">
                            {{ round($user_balance ?? 0) }} BDT
                        </span>
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
