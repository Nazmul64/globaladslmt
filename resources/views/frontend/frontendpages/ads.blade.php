@extends('frontend.master')

@section('head')
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
@endsection

@section('content')
<div class="container-content py-4">

    {{-- Task Card --}}
    <div class="task-box card shadow-lg p-4 border-0 mb-4">
        <div class="task-icon mb-3 text-center">
            <i class="fas fa-tasks fa-3x text-primary"></i>
        </div>

        <h4 class="task-title text-center mb-2">Complete Your Task</h4>
        <p class="task-description text-center text-muted mb-3">
            Click below to complete your daily task and earn rewards!
        </p>

        @if($packageBuy)
        <div class="package-info mb-3">
            <div class="info-row">
                <span class="label">Package:</span>
                <span class="value">{{ $packageBuy->package->package_name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Daily Income:</span>
                <span class="value">${{ number_format($packageBuy->daily_income, 2) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Daily Limit:</span>
                <span class="value">{{ $packageBuy->daily_limit }} Tasks</span>
            </div>
            <div class="info-row">
                <span class="label">Completed Today:</span>
                <span class="value" id="completed-today">{{ $packageBuy->tasks_completed_today ?? 0 }}</span>
            </div>
        </div>
        @else
        <div class="alert alert-warning mb-3">
            <i class="fas fa-exclamation-triangle"></i> You have not purchased any package yet.
        </div>
        @endif

        <div class="task-counter text-center mb-3">
            <span id="current-tasks">{{ $packageBuy->tasks_completed_today ?? 0 }}</span>/<span id="max-tasks">{{ $packageBuy ? $packageBuy->daily_limit : 0 }}</span>
        </div>

        <div class="text-center">
            <button class="btn btn-primary btn-lg pulse-button" id="taskButton"
                    @if(!$packageBuy || ($packageBuy->tasks_completed_today ?? 0) >= $packageBuy->daily_limit) disabled @endif>
                <i class="fas fa-hand-pointer"></i> Click to Complete Task
            </button>
        </div>

        <div class="text-center mt-3" id="ad-status">
            <i class="fas fa-circle-notch fa-spin"></i> <span id="status-text">Checking platform...</span>
        </div>

        <div class="text-center mt-2 small text-muted" id="earnings-today">
            Today's Earnings: $<span id="earnings-amount">{{ number_format(($packageBuy->tasks_completed_today ?? 0) * ($packageBuy->daily_income ?? 0), 2) }}</span>
        </div>
    </div>

</div>

<script>
(function() {
    'use strict';

    const CONFIG = {
        MAX_TASKS: {{ $packageBuy ? $packageBuy->daily_limit : 0 }},
        DAILY_INCOME: {{ $packageBuy ? $packageBuy->daily_income : 0 }},
        TASKS_COMPLETED: {{ $packageBuy->tasks_completed_today ?? 0 }},
        PACKAGE_BUY_ID: {{ $packageBuy->id ?? 'null' }},
        CSRF_TOKEN: '{{ csrf_token() }}',
        TASK_URL: '{{ route("task.complete") }}',

        // Ads Configuration
        ADS: {
            APP_ID: '{{ $ads->code ?? "" }}',
            INTERSTITIAL_ID: '{{ $ads->interstitial ?? "" }}',
            REWARDED_VIDEO_ID: '{{ $ads->rewarded_video ?? "" }}',
            BANNER_AD_1: '{{ $ads->banner_ad_1 ?? "" }}',
            BANNER_AD_2: '{{ $ads->banner_ad_2 ?? "" }}',
            NATIVE_ID: '{{ $ads->native ?? "" }}',
            SHOW_MRCE_ADS: '{{ $ads->show_mrce_ads ?? "disabled" }}' === 'enabled',
            SHOW_BUTTON_TIMER: '{{ $ads->show_button_timer_ads ?? "disabled" }}' === 'enabled',
            SHOW_BANNER: '{{ $ads->show_banner_ads ?? "disabled" }}' === 'enabled'
        }
    };

    const State = {
        isWebView: false,
        isProcessing: false,
        taskCount: CONFIG.TASKS_COMPLETED,
        totalEarnings: CONFIG.TASKS_COMPLETED * CONFIG.DAILY_INCOME,
        adsConfigured: false
    };

    function detectWebView() {
        const ua = navigator.userAgent.toLowerCase();
        const isFlutterWebView = window.FlutterBridge !== undefined;
        const isAndroidWebView = ua.includes('wv') || (ua.includes('android') && !ua.includes('chrome'));
        const isIOSWebView = ua.includes('iphone') && !ua.includes('safari');
        return isFlutterWebView || isAndroidWebView || isIOSWebView;
    }

    function checkAdsConfiguration() {
        const hasAppId = CONFIG.ADS.APP_ID && CONFIG.ADS.APP_ID.trim() !== '';
        const hasInterstitial = CONFIG.ADS.INTERSTITIAL_ID && CONFIG.ADS.INTERSTITIAL_ID.trim() !== '';
        State.adsConfigured = hasAppId && hasInterstitial;

        console.log('📊 Ads Configuration:', {
            appId: CONFIG.ADS.APP_ID,
            interstitial: CONFIG.ADS.INTERSTITIAL_ID,
            configured: State.adsConfigured,
            mrceEnabled: CONFIG.ADS.SHOW_MRCE_ADS,
            bannerEnabled: CONFIG.ADS.SHOW_BANNER
        });

        return State.adsConfigured;
    }

    function init() {
        console.log('🚀 Initializing Task Page...');
        console.log('📦 Package Buy ID:', CONFIG.PACKAGE_BUY_ID);
        console.log('🎯 Max Tasks:', CONFIG.MAX_TASKS);
        console.log('💰 Daily Income:', CONFIG.DAILY_INCOME);

        State.isWebView = detectWebView();
        checkAdsConfiguration();

        console.log('🔍 Platform:', State.isWebView ? 'Flutter WebView' : 'Mobile Browser');
        console.log('📱 User Agent:', navigator.userAgent);
        console.log('🔗 Flutter Bridge:', window.FlutterBridge ? 'Available' : 'Not Available');
        console.log('🎯 Ads Configured:', State.adsConfigured);

        if (State.isWebView && State.adsConfigured) {
            updateStatus('WebView & Ads Ready!', 'success');
            sendAdsConfigToFlutter();
        } else if (State.isWebView && !State.adsConfigured) {
            updateStatus('WebView Detected - Ads Not Configured', 'warning');
        } else {
            updateStatus('Mobile Browser - Ads Not Available', 'warning');
        }

        const btn = document.getElementById('taskButton');
        if (btn) {
            console.log('✅ Button found, disabled:', btn.disabled);
            if (!btn.disabled) {
                btn.addEventListener('click', handleTaskClick);
                console.log('✅ Click listener added');
            } else {
                console.log('⚠️ Button is disabled');
            }
        } else {
            console.error('❌ Button not found!');
        }
    }

    function sendAdsConfigToFlutter() {
        if (window.FlutterBridge && window.FlutterBridge.postMessage) {
            try {
                const adsConfig = {
                    action: 'configureAds',
                    data: {
                        appId: CONFIG.ADS.APP_ID,
                        interstitialId: CONFIG.ADS.INTERSTITIAL_ID,
                        rewardedVideoId: CONFIG.ADS.REWARDED_VIDEO_ID,
                        bannerId1: CONFIG.ADS.BANNER_AD_1,
                        bannerId2: CONFIG.ADS.BANNER_AD_2,
                        nativeId: CONFIG.ADS.NATIVE_ID,
                        showMrceAds: CONFIG.ADS.SHOW_MRCE_ADS,
                        showButtonTimer: CONFIG.ADS.SHOW_BUTTON_TIMER,
                        showBanner: CONFIG.ADS.SHOW_BANNER
                    }
                };

                window.FlutterBridge.postMessage(JSON.stringify(adsConfig));
                console.log('✅ Ads config sent to Flutter:', adsConfig);
            } catch (err) {
                console.error('❌ Error sending ads config:', err);
            }
        }
    }

    async function handleTaskClick(e) {
        console.log('🖱️ Button clicked!');
        e.preventDefault();

        if (State.isProcessing) {
            console.log('⚠️ Already processing...');
            notify('Please wait...', 'warning');
            return;
        }

        if (State.taskCount >= CONFIG.MAX_TASKS) {
            console.log('⚠️ Daily limit reached');
            notify('Daily limit reached!', 'warning');
            return;
        }

        if (!CONFIG.PACKAGE_BUY_ID) {
            console.log('❌ No package purchased');
            notify('No package purchased!', 'error');
            return;
        }

        State.isProcessing = true;
        const btn = document.getElementById('taskButton');
        const originalHTML = btn.innerHTML;
        btn.disabled = true;

        try {
            console.log('🎯 Task Started');

            if (State.isWebView && State.adsConfigured) {
                console.log('📺 Showing ad...');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading Ad...';
                const adShown = await showWebViewAd();

                if (adShown) {
                    console.log('✅ Ad shown successfully');
                    notify('Ad completed successfully!', 'success');
                    await sleep(2000);
                } else {
                    console.log('⚠️ Ad not shown, continuing...');
                    notify('Ad unavailable, proceeding...', 'warning');
                    await sleep(1000);
                }
            } else {
                console.log('⏭️ Skipping ad (browser mode)');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                await sleep(2000);
            }

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Completing Task...';
            console.log('🌐 Sending request to:', CONFIG.TASK_URL);

            const response = await fetch(CONFIG.TASK_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CONFIG.CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    package_buy_id: CONFIG.PACKAGE_BUY_ID,
                    ad_shown: State.isWebView && State.adsConfigured
                })
            });

            console.log('📡 Response status:', response.status);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('❌ Response not OK:', errorText);
                throw new Error(`Server error: ${response.status}`);
            }

            const data = await response.json();
            console.log('📦 Response data:', data);

            if (data.success) {
                State.taskCount++;
                State.totalEarnings = State.taskCount * CONFIG.DAILY_INCOME;
                updateUI();
                console.log('✅ Task completed successfully!');
                notify(`✅ Task Complete! Earned $${CONFIG.DAILY_INCOME.toFixed(2)}`, 'success');
            } else {
                throw new Error(data.message || 'Task failed');
            }

        } catch (error) {
            console.error('❌ Error:', error);
            console.error('Error stack:', error.stack);
            notify(error.message || 'Task failed. Please try again.', 'error');

        } finally {
            State.isProcessing = false;
            console.log('🏁 Task process finished');

            if (State.taskCount >= CONFIG.MAX_TASKS) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> All Tasks Completed!';
                btn.style.background = '#28a745';
            } else {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        }
    }

    function showWebViewAd() {
        return new Promise((resolve) => {
            console.log('📺 Requesting Ad from Flutter App...');

            const timeout = setTimeout(() => {
                console.warn('⏱️ Ad Timeout (30s)');
                resolve(false);
            }, 30000);

            if (window.FlutterBridge && window.FlutterBridge.postMessage) {
                try {
                    const adRequest = {
                        action: 'showInterstitialAd',
                        data: {
                            adUnitId: CONFIG.ADS.INTERSTITIAL_ID
                        }
                    };

                    window.FlutterBridge.postMessage(JSON.stringify(adRequest));

                    window.onAdCompleted = (success) => {
                        clearTimeout(timeout);
                        console.log(success ? '✅ Ad Completed' : '⚠️ Ad Failed');
                        resolve(success === 'true' || success === true);
                    };

                    window.onAdFailed = (error) => {
                        clearTimeout(timeout);
                        console.error('❌ Ad Failed:', error);
                        resolve(false);
                    };

                } catch (err) {
                    clearTimeout(timeout);
                    console.error('❌ Flutter Bridge Error:', err);
                    resolve(false);
                }
            } else {
                clearTimeout(timeout);
                console.warn('⚠️ No Flutter Bridge Found');
                resolve(false);
            }
        });
    }

    function updateUI() {
        const elements = {
            current: document.getElementById('current-tasks'),
            completed: document.getElementById('completed-today'),
            earnings: document.getElementById('earnings-amount')
        };

        if (elements.current) elements.current.textContent = State.taskCount;
        if (elements.completed) elements.completed.textContent = State.taskCount;
        if (elements.earnings) elements.earnings.textContent = State.totalEarnings.toFixed(2);

        console.log('✅ UI Updated:', {
            taskCount: State.taskCount,
            earnings: State.totalEarnings
        });
    }

    function updateStatus(message, type) {
        const icons = {
            info: 'circle-notch fa-spin',
            success: 'check-circle',
            warning: 'exclamation-triangle',
            error: 'times-circle'
        };

        const colors = {
            info: 'info',
            success: 'success',
            warning: 'warning',
            error: 'danger'
        };

        const el = document.getElementById('ad-status');
        if (el) {
            el.className = `text-center mt-3 text-${colors[type]}`;
            el.innerHTML = `<i class="fas fa-${icons[type]}"></i> <span>${message}</span>`;
        }
    }

    function notify(message, type = 'info') {
        const notif = document.createElement('div');
        notif.className = `notification notification-${type}`;

        const icons = {
            success: 'check-circle',
            error: 'times-circle',
            warning: 'exclamation-circle',
            info: 'info-circle'
        };

        notif.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${icons[type]}"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notif);
        setTimeout(() => notif.classList.add('show'), 100);

        setTimeout(() => {
            notif.classList.remove('show');
            setTimeout(() => notif.remove(), 300);
        }, 4000);
    }

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.debugApp = () => {
        console.log('=== APP DEBUG INFO ===');
        console.log('Config:', CONFIG);
        console.log('State:', State);
        console.log('Is WebView:', State.isWebView);
        console.log('Ads Configured:', State.adsConfigured);
        console.log('Flutter Bridge:', window.FlutterBridge);
        console.log('Ads Config:', CONFIG.ADS);
        console.log('Button Element:', document.getElementById('taskButton'));
        console.log('Button Disabled:', document.getElementById('taskButton')?.disabled);
    };

    // Test button manually
    window.testButton = () => {
        console.log('🧪 Testing button click manually...');
        const btn = document.getElementById('taskButton');
        if (btn) {
            btn.click();
        } else {
            console.error('❌ Button not found!');
        }
    };

})();
</script>

<style>
.container-content {
    max-width: 600px;
    margin: 0 auto;
    padding: 15px;
}

.task-box {
    border-radius: 16px;
    background: white;
    position: relative;
    overflow: hidden;
}

.task-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2);
}

.task-icon i {
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.package-info {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid #667eea;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #e0e0e0;
}

.info-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.info-row .label {
    font-weight: 600;
    color: #555;
}

.info-row .value {
    font-weight: 700;
    color: #667eea;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
    padding: 12px 30px;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    background: #6c757d;
    cursor: not-allowed;
    opacity: 0.6;
}

.pulse-button {
    animation: buttonPulse 2s infinite;
}

@keyframes buttonPulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7); }
    50% { box-shadow: 0 0 0 15px rgba(102, 126, 234, 0); }
}

.task-counter {
    font-weight: 700;
    color: #667eea;
    font-size: 1.8rem;
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateX(400px);
    transition: transform 0.3s ease;
    z-index: 9999;
    max-width: 300px;
}

.notification.show {
    transform: translateX(0);
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.notification-success { border-left: 4px solid #28a745; }
.notification-error { border-left: 4px solid #dc3545; }
.notification-warning { border-left: 4px solid #ffc107; }
.notification-info { border-left: 4px solid #17a2b8; }

.notification-success i { color: #28a745; }
.notification-error i { color: #dc3545; }
.notification-warning i { color: #ffc107; }
.notification-info i { color: #17a2b8; }

.text-success { color: #28a745 !important; }
.text-danger { color: #dc3545 !important; }
.text-warning { color: #ffc107 !important; }
.text-info { color: #17a2b8 !important; }

@media (max-width: 768px) {
    .container-content {
        padding: 10px;
    }

    .notification {
        right: 10px;
        left: 10px;
        max-width: none;
    }

    .btn-primary {
        padding: 10px 20px;
        font-size: 14px;
    }
}
</style>
@endsection
