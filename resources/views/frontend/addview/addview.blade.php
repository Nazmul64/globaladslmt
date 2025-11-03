@extends('frontend.master')

@section('content')
<div class="ad-view-container">

    {{-- ================= Ad Viewing Area ================= --}}
    <div class="ad-viewing-box">

        {{-- Header --}}
        <div class="ad-header">
            <div class="ad-header-content">
                <i class="fas fa-tv fa-2x"></i>
                <h3>Viewing Advertisement</h3>
                <p>Please watch the complete advertisement</p>
            </div>
        </div>

        {{-- Timer Display --}}
        <div class="ad-timer-box">
            <div class="timer-circle">
                <svg class="timer-svg" viewBox="0 0 100 100">
                    <circle class="timer-circle-bg" cx="50" cy="50" r="45"></circle>
                    <circle class="timer-circle-progress" id="timerCircle" cx="50" cy="50" r="45"></circle>
                </svg>
                <div class="timer-content">
                    <div class="timer-number" id="timerNumber">{{ $adDuration ?? 30 }}</div>
                    <div class="timer-label">seconds</div>
                </div>
            </div>
            <p class="timer-message">Please wait while ad is loading...</p>
        </div>

        {{-- Main Ad Container --}}
        <div class="main-ad-container" id="mainAdContainer" data-ad-id="{{ $ads->interstitial_ad ?? '' }}">
            <div class="ad-content-wrapper">

                {{-- Banner Ad Top --}}
                @if($ads->show_banner_ads === 'enabled' && !empty($ads->banner_ad_top))
                <div class="banner-ad-slot top" id="bannerAdTop" data-ad-id="{{ $ads->banner_ad_top }}">
                    <div class="ad-label">Advertisement</div>
                    <div class="ad-placeholder">
                        <i class="fas fa-image fa-3x"></i>
                        <p>Banner Ad Space</p>
                    </div>
                </div>
                @endif

                {{-- Main Interstitial Ad --}}
                <div class="interstitial-ad-slot" id="interstitialAd">
                    <div class="ad-label">Sponsored Content</div>
                    <div class="ad-placeholder main">
                        <i class="fas fa-film fa-4x mb-3"></i>
                        <h4>Advertisement Loading...</h4>
                        <p>Please wait while we load the advertisement</p>
                        <div class="spinner-border text-primary mt-3" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>

                {{-- Native Ad --}}
                @if(!empty($ads->native_ad))
                <div class="native-ad-slot" id="nativeAd" data-ad-id="{{ $ads->native_ad }}">
                    <div class="ad-label">Recommended</div>
                    <div class="ad-placeholder">
                        <i class="fas fa-star fa-2x"></i>
                        <p>Native Ad Space</p>
                    </div>
                </div>
                @endif

                {{-- Banner Ad Bottom --}}
                @if($ads->show_banner_ads === 'enabled' && !empty($ads->banner_ad_bottom))
                <div class="banner-ad-slot bottom" id="bannerAdBottom" data-ad-id="{{ $ads->banner_ad_bottom }}">
                    <div class="ad-label">Advertisement</div>
                    <div class="ad-placeholder">
                        <i class="fas fa-image fa-3x"></i>
                        <p>Banner Ad Space</p>
                    </div>
                </div>
                @endif

            </div>
        </div>

        {{-- Close/Back Button (Initially Hidden) --}}
        <div class="ad-action-buttons" id="actionButtons" style="display: none;">
            <button class="btn btn-success btn-lg btn-block" id="continueButton" onclick="returnToTaskPage()">
                <i class="fas fa-check-circle"></i> Continue & Complete Task
            </button>
            <p class="text-muted mt-2 small">
                <i class="fas fa-info-circle"></i> You can now continue to complete your task
            </p>
        </div>

    </div>

    {{-- Ad Progress Info --}}
    <div class="ad-progress-info mt-3">
        <div class="progress" style="height: 25px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                 id="adProgress" role="progressbar" style="width: 0%">
                <span id="progressText">0%</span>
            </div>
        </div>
    </div>

</div>

<script>
// Configuration
const AD_DURATION = {{ $adDuration ?? 30 }}; // seconds
const taskId = {{ request()->get('task_id', 0) }};
let timeRemaining = AD_DURATION;
let timerInterval = null;
let isFlutterWebView = false;

// Initialize on page load
window.addEventListener('load', function() {
    console.log('📺 Ad view page loaded');

    // Detect Flutter WebView
    if (typeof FlutterAdChannel !== 'undefined') {
        isFlutterWebView = true;
        console.log('✅ Running in Flutter WebView');

        // Request Flutter to show interstitial ad
        showFlutterAd();
    } else {
        console.log('ℹ️ Running in web browser');
    }

    // Start ad viewing timer
    startAdTimer();

    // Load banner and native ads
    loadBannerAds();
});

// Show Flutter interstitial ad
function showFlutterAd() {
    const mainAd = document.getElementById('mainAdContainer');
    if (mainAd) {
        const adId = mainAd.getAttribute('data-ad-id');

        if (adId) {
            FlutterAdChannel.postMessage(JSON.stringify({
                type: 'show_fullscreen_interstitial',
                adId: adId,
                duration: AD_DURATION
            }));

            console.log('✅ Fullscreen interstitial ad request sent to Flutter');
        }
    }
}

// Load banner and native ads
function loadBannerAds() {
    if (!isFlutterWebView) return;

    // Load top banner
    const topBanner = document.getElementById('bannerAdTop');
    if (topBanner) {
        const adId = topBanner.getAttribute('data-ad-id');
        FlutterAdChannel.postMessage(JSON.stringify({
            type: 'load_banner',
            position: 'top',
            adId: adId
        }));
    }

    // Load bottom banner
    const bottomBanner = document.getElementById('bannerAdBottom');
    if (bottomBanner) {
        const adId = bottomBanner.getAttribute('data-ad-id');
        FlutterAdChannel.postMessage(JSON.stringify({
            type: 'load_banner',
            position: 'bottom',
            adId: adId
        }));
    }

    // Load native ad
    const nativeAd = document.getElementById('nativeAd');
    if (nativeAd) {
        const adId = nativeAd.getAttribute('data-ad-id');
        FlutterAdChannel.postMessage(JSON.stringify({
            type: 'load_native',
            adId: adId
        }));
    }
}

// Start ad viewing timer
function startAdTimer() {
    const timerNumber = document.getElementById('timerNumber');
    const timerCircle = document.getElementById('timerCircle');
    const timerMessage = document.querySelector('.timer-message');
    const actionButtons = document.getElementById('actionButtons');
    const adProgress = document.getElementById('adProgress');
    const progressText = document.getElementById('progressText');

    // Calculate circle circumference
    const radius = 45;
    const circumference = 2 * Math.PI * radius;
    timerCircle.style.strokeDasharray = `${circumference} ${circumference}`;
    timerCircle.style.strokeDashoffset = circumference;

    timerMessage.textContent = 'Viewing advertisement...';

    // Update timer every second
    timerInterval = setInterval(() => {
        timeRemaining--;

        // Update timer display
        timerNumber.textContent = timeRemaining;

        // Update circle progress
        const progress = 1 - (timeRemaining / AD_DURATION);
        const offset = circumference - (progress * circumference);
        timerCircle.style.strokeDashoffset = offset;

        // Update progress bar
        const percentage = Math.round(progress * 100);
        adProgress.style.width = percentage + '%';
        progressText.textContent = percentage + '%';

        // Timer completed
        if (timeRemaining <= 0) {
            clearInterval(timerInterval);
            onAdViewCompleted();
        }
    }, 1000);
}

// Ad viewing completed
function onAdViewCompleted() {
    console.log('✅ Ad viewing completed!');

    const timerNumber = document.getElementById('timerNumber');
    const timerMessage = document.querySelector('.timer-message');
    const actionButtons = document.getElementById('actionButtons');
    const adProgress = document.getElementById('adProgress');
    const progressText = document.getElementById('progressText');

    // Update UI
    timerNumber.innerHTML = '<i class="fas fa-check"></i>';
    timerMessage.innerHTML = '<span style="color: #28a745; font-weight: bold;"><i class="fas fa-check-circle"></i> Advertisement Completed!</span>';

    adProgress.classList.remove('progress-bar-animated');
    adProgress.classList.add('bg-success');
    progressText.textContent = '100% Complete';

    // Show continue button
    actionButtons.style.display = 'block';
    actionButtons.style.animation = 'fadeIn 0.5s ease-in';

    // Notify Flutter (if in WebView)
    if (isFlutterWebView) {
        FlutterAdChannel.postMessage(JSON.stringify({
            type: 'ad_view_completed',
            taskId: taskId
        }));
    }

    // Play success sound (optional)
    playSuccessSound();
}

// Return to task page
function returnToTaskPage() {
    console.log('🔙 Returning to task page');

    // Add loading state to button
    const btn = document.getElementById('continueButton');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Returning...';

    // Redirect back to task page with completion flag
    setTimeout(() => {
        window.location.href = "{{ route('task.index') }}?ad_viewed=1&task_id=" + taskId;
    }, 500);
}

// Play success sound
function playSuccessSound() {
    try {
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSOIzvTTfS0GHm/A7+OZSA0PVq3m77BdGAg+leHwwG4hBSWAy/HYgDIHI3fH8N2QQAoUXrTp66hVFApGn+DyvmwhBSOIzvTTfS0GHm/A7+OZSA0PVq3m77BdGAg+leHwwG4hBSWAy/HYgDIHI3fH8N2QQAoUXrTp66hVFApGn+DyvmwhBSOIzvTTfS0GHm/A7+OZSA0PVq3m77BdGAg+leHwwG4hBSWAy/HYgDIHI3fH8N2QQAoUXrTp66hVFApGn+DyvmwhBSOIzvTTfS0GHm/A7+OZSA0PVq3m77BdGAg+leHwwG4hBSWAy/HYgDIHI3fH8N2QQAoUXrTp66hVFA==');
        audio.play().catch(e => console.log('Sound play failed:', e));
    } catch (error) {
        console.log('Sound not available');
    }
}

// Handle back button press
window.addEventListener('popstate', function(event) {
    if (timeRemaining > 0) {
        event.preventDefault();
        if (confirm('Ad viewing is not complete. Are you sure you want to go back?')) {
            window.location.href = "{{ route('task.index') }}";
        } else {
            window.history.pushState(null, '', window.location.href);
        }
    }
});

// Prevent accidental page close
window.addEventListener('beforeunload', function(event) {
    if (timeRemaining > 0) {
        event.preventDefault();
        event.returnValue = '';
        return '';
    }
});

// Handle messages from Flutter
window.addEventListener('message', function(event) {
    try {
        const data = JSON.parse(event.data);

        switch(data.type) {
            case 'ad_loaded':
                console.log('✅ Ad loaded:', data.adType);
                break;
            case 'ad_failed':
                console.log('❌ Ad failed:', data.adType);
                break;
            case 'fullscreen_ad_closed':
                console.log('🚪 Fullscreen ad closed by user');
                // Continue with timer
                break;
        }
    } catch (error) {
        console.error('Error parsing message:', error);
    }
});

// Push state to prevent back button during ad viewing
window.history.pushState(null, '', window.location.href);
</script>

<style>
/* Main Container */
.ad-view-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* Ad Viewing Box */
.ad-viewing-box {
    background: white;
    border-radius: 20px;
    padding: 30px;
    max-width: 600px;
    width: 100%;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}

/* Header */
.ad-header {
    text-align: center;
    margin-bottom: 30px;
}

.ad-header-content i {
    color: #667eea;
    margin-bottom: 10px;
}

.ad-header-content h3 {
    font-weight: 700;
    color: #333;
    margin: 10px 0;
}

.ad-header-content p {
    color: #666;
    font-size: 14px;
}

/* Timer Box */
.ad-timer-box {
    text-align: center;
    margin-bottom: 30px;
}

.timer-circle {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto 20px;
}

.timer-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.timer-circle-bg {
    fill: none;
    stroke: #e0e0e0;
    stroke-width: 8;
}

.timer-circle-progress {
    fill: none;
    stroke: #667eea;
    stroke-width: 8;
    stroke-linecap: round;
    transition: stroke-dashoffset 1s linear;
}

.timer-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.timer-number {
    font-size: 48px;
    font-weight: 700;
    color: #667eea;
}

.timer-label {
    font-size: 14px;
    color: #666;
    margin-top: 5px;
}

.timer-message {
    color: #666;
    font-size: 14px;
    font-weight: 500;
}

/* Main Ad Container */
.main-ad-container {
    margin-bottom: 20px;
}

.ad-content-wrapper {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Ad Slots */
.banner-ad-slot, .interstitial-ad-slot, .native-ad-slot {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    position: relative;
}

.ad-label {
    font-size: 10px;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
    font-weight: 600;
}

.ad-placeholder {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    color: #666;
}

.ad-placeholder.main {
    min-height: 250px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.ad-placeholder i {
    color: #999;
    opacity: 0.5;
}

.ad-placeholder h4 {
    margin: 10px 0;
    color: #555;
}

.ad-placeholder p {
    margin: 0;
    font-size: 14px;
}

.banner-ad-slot .ad-placeholder {
    min-height: 80px;
    padding: 20px;
}

.native-ad-slot .ad-placeholder {
    min-height: 120px;
}

/* Action Buttons */
.ad-action-buttons {
    text-align: center;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Progress Info */
.ad-progress-info {
    max-width: 600px;
    width: 100%;
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar span {
    font-weight: 600;
    font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
    .ad-view-container {
        padding: 15px;
    }

    .ad-viewing-box {
        padding: 20px;
    }

    .timer-circle {
        width: 120px;
        height: 120px;
    }

    .timer-number {
        font-size: 36px;
    }

    .ad-placeholder.main {
        min-height: 200px;
        padding: 20px;
    }
}

/* Loading Animation */
.spinner-border {
    width: 3rem;
    height: 3rem;
}
</style>
@endsection
