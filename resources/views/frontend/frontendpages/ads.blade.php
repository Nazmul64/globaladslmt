@extends('frontend.master')

@section('head')
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
@endsection

@section('content')
<div class="container-content py-4 text-center">

    {{-- ================= Banner Top ================= --}}
    @if($ads && $ads->show_banner_ads === 'enabled' && !empty($ads->code))
    <div class="banner-ad mb-3" id="top-banner-wrapper">
        <div class="ad-badge">AD</div>
        <div class="ad-container">
            <div id="startapp-banner-top"></div>
        </div>
    </div>
    @endif

    {{-- ================= Task Box ================= --}}
    <div class="task-box card shadow-lg p-4 border-0 mb-4">
        <div class="task-icon mb-3">
            <i class="fas fa-tasks fa-3x text-primary"></i>
        </div>
        <div class="task-title h4 mb-2">Complete Your Task</div>
        <div class="task-description mb-3 text-muted">
            Click below to complete your daily task and earn rewards!
        </div>

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
            <a href="{{ route('packages') }}" class="btn btn-sm btn-warning mt-2">Browse Packages</a>
        </div>
        @endif

        <div class="task-counter h5 mb-3">
            <span id="current-tasks">{{ $packageBuy->tasks_completed_today ?? 0 }}</span>/<span id="max-tasks">{{ $packageBuy ? $packageBuy->daily_limit : 0 }}</span>
        </div>

        <button class="btn btn-primary btn-lg pulse-button" id="taskButton"
                @if(!$packageBuy || ($packageBuy->tasks_completed_today ?? 0) >= $packageBuy->daily_limit) disabled @endif>
            <i class="fas fa-hand-pointer"></i> Click to Complete Task
        </button>

        <div class="mt-3" id="ad-status">
            @if($ads && !empty($ads->code))
                <i class="fas fa-circle-notch fa-spin"></i> <span id="status-text">Initializing ads...</span>
            @else
                <i class="fas fa-exclamation-triangle text-warning"></i> <span>Ads not configured</span>
            @endif
        </div>

        <div class="mt-2 small text-muted" id="earnings-today">
            Today's Earnings: $<span id="earnings-amount">{{ number_format(($packageBuy->tasks_completed_today ?? 0) * ($packageBuy->daily_income ?? 0), 2) }}</span>
        </div>
    </div>

    {{-- ================= Banner Bottom ================= --}}
    @if($ads && $ads->show_banner_ads === 'enabled' && !empty($ads->code))
    <div class="banner-ad mt-3" id="bottom-banner-wrapper">
        <div class="ad-badge">AD</div>
        <div class="ad-container">
            <div id="startapp-banner-bottom"></div>
        </div>
    </div>
    @endif

</div>

{{-- Start.io SDK --}}
@if($ads && !empty($ads->code))
<script src="https://cdn.startapp.com/sdk/inapp.js"></script>
<script>
// ==================== CONFIGURATION ====================
const APP_CONFIG = {
    maxTasks: {{ $packageBuy ? $packageBuy->daily_limit : 0 }},
    dailyIncome: {{ $packageBuy ? $packageBuy->daily_income : 0 }},
    tasksCompleted: {{ $packageBuy->tasks_completed_today ?? 0 }},
    packageBuyId: {{ $packageBuy->id ?? 'null' }},
    userId: {{ Auth::id() }},
    csrfToken: '{{ csrf_token() }}',
    taskCompleteUrl: '{{ route("task.complete") }}',
    ads: {
        appId: '{{ $ads->code }}',
        showBanner: {{ $ads->show_banner_ads === 'enabled' ? 'true' : 'false' }},
        showInterstitial: {{ $ads->show_mrce_ads === 'enabled' ? 'true' : 'false' }}
    }
};

// ==================== STATE MANAGEMENT ====================
const AppState = {
    taskCount: APP_CONFIG.tasksCompleted,
    totalEarnings: APP_CONFIG.tasksCompleted * APP_CONFIG.dailyIncome,
    sdk: {
        loaded: false,
        ready: false,
        interstitialLoaded: false
    },
    startAppSDK: null
};

console.log('🎯 App Config:', APP_CONFIG);
console.log('📊 Initial State:', AppState);

// ==================== START.IO AD MANAGER ====================
class StartIOAdManager {
    constructor() {
        this.sdk = null;
        this.interstitialReady = false;
        this.initializationPromise = null;
        this.adLoadInProgress = false;
    }

    async waitForSDK() {
        return new Promise((resolve, reject) => {
            let attempts = 0;
            const maxAttempts = 40; // Increased attempts

            const checkSDK = () => {
                attempts++;
                console.log(`🔍 SDK Check (${attempts}/${maxAttempts})...`);

                if (typeof StartApp !== 'undefined') {
                    console.log('✅ StartApp SDK Available');
                    resolve(true);
                } else if (attempts >= maxAttempts) {
                    console.error('❌ SDK Load Timeout');
                    reject(new Error('SDK timeout'));
                } else {
                    setTimeout(checkSDK, 500);
                }
            };

            checkSDK();
        });
    }

    async initialize() {
        if (this.initializationPromise) {
            return this.initializationPromise;
        }

        this.initializationPromise = (async () => {
            if (!APP_CONFIG.ads.appId) {
                console.warn('⚠️ No Start.io App ID');
                this.updateStatus('No ad configuration', 'warning');
                return false;
            }

            try {
                console.log('🚀 Initializing Start.io SDK...');
                console.log('📱 App ID:', APP_CONFIG.ads.appId);
                this.updateStatus('Loading ads...', 'info');

                // Wait for SDK to load
                await this.waitForSDK();
                console.log('✅ SDK Script Loaded');

                // Wait for SDK to be ready
                await new Promise(resolve => setTimeout(resolve, 2000));

                // Initialize StartApp instance
                console.log('🎬 Creating StartApp Instance...');
                this.sdk = new StartApp(APP_CONFIG.ads.appId);

                if (!this.sdk) {
                    throw new Error('Failed to create StartApp instance');
                }

                // Additional stabilization time
                await new Promise(resolve => setTimeout(resolve, 2000));

                AppState.sdk.loaded = true;
                AppState.sdk.ready = true;
                AppState.startAppSDK = this.sdk;

                console.log('✅ SDK Initialized Successfully');
                console.log('📦 SDK Instance:', this.sdk);

                this.updateStatus('Ads ready', 'success');
                return true;

            } catch (error) {
                console.error('❌ SDK Init Failed:', error);
                this.updateStatus('Ads unavailable', 'error');
                return false;
            }
        })();

        return this.initializationPromise;
    }

    loadBannerAds() {
        if (!this.sdk) {
            console.log('⚠️ SDK not ready for banners');
            return;
        }

        if (!APP_CONFIG.ads.showBanner) {
            console.log('ℹ️ Banner ads disabled');
            return;
        }

        try {
            console.log('📢 Loading Banner Ads...');

            // Top Banner
            setTimeout(() => {
                const topBanner = document.getElementById('startapp-banner-top');
                if (topBanner) {
                    try {
                        this.sdk.showBanner({
                            container: 'startapp-banner-top',
                            size: 'LARGE'
                        });
                        console.log('✅ Top Banner Loaded');
                    } catch (error) {
                        console.error('❌ Top Banner Error:', error);
                    }
                }
            }, 500);

            // Bottom Banner
            setTimeout(() => {
                const bottomBanner = document.getElementById('startapp-banner-bottom');
                if (bottomBanner) {
                    try {
                        this.sdk.showBanner({
                            container: 'startapp-banner-bottom',
                            size: 'LARGE'
                        });
                        console.log('✅ Bottom Banner Loaded');
                    } catch (error) {
                        console.error('❌ Bottom Banner Error:', error);
                    }
                }
            }, 1000);

        } catch (error) {
            console.error('❌ Banner Load Error:', error);
        }
    }

    async loadInterstitial() {
        if (!this.sdk) {
            console.log('⚠️ SDK not initialized');
            return false;
        }

        if (!APP_CONFIG.ads.showInterstitial) {
            console.log('ℹ️ Interstitial ads disabled');
            return false;
        }

        if (this.adLoadInProgress) {
            console.log('⚠️ Ad load already in progress');
            return false;
        }

        try {
            this.adLoadInProgress = true;
            console.log('📺 Loading Interstitial Ad...');

            return new Promise((resolve) => {
                const timeout = setTimeout(() => {
                    console.warn('⏱️ Interstitial Load Timeout');
                    this.adLoadInProgress = false;
                    this.interstitialReady = false;
                    resolve(false);
                }, 20000); // 20 second timeout

                try {
                    this.sdk.loadAd('INTERSTITIAL', {
                        adTag: 'task_' + Date.now()
                    }, (loaded) => {
                        clearTimeout(timeout);
                        this.adLoadInProgress = false;

                        if (loaded) {
                            this.interstitialReady = true;
                            AppState.sdk.interstitialLoaded = true;
                            console.log('✅ Interstitial Loaded Successfully');
                            resolve(true);
                        } else {
                            this.interstitialReady = false;
                            console.warn('⚠️ Interstitial Load Failed (No Fill)');
                            resolve(false);
                        }
                    });
                } catch (error) {
                    clearTimeout(timeout);
                    this.adLoadInProgress = false;
                    console.error('❌ Interstitial Load Exception:', error);
                    resolve(false);
                }
            });
        } catch (error) {
            this.adLoadInProgress = false;
            console.error('❌ Interstitial Load Error:', error);
            return false;
        }
    }

    async showInterstitial() {
        console.log('📺 Show Interstitial Called');
        console.log('🔍 SDK Ready:', !!this.sdk);
        console.log('🔍 Interstitial Ready:', this.interstitialReady);

        if (!this.sdk) {
            console.error('❌ SDK Not Initialized');
            return false;
        }

        // If not ready, try to load it
        if (!this.interstitialReady) {
            console.log('⚠️ Interstitial Not Ready, Loading Now...');
            UIManager.showNotification('Loading ad...', 'info');

            const loaded = await this.loadInterstitial();
            if (!loaded) {
                console.error('❌ Failed to Load Ad');
                return false;
            }

            // Wait a bit after loading
            await new Promise(r => setTimeout(r, 1500));
        }

        try {
            console.log('📺 Displaying Interstitial Ad...');

            return new Promise((resolve) => {
                const timeout = setTimeout(() => {
                    console.warn('⏱️ Interstitial Show Timeout');
                    resolve(false);
                }, 20000); // 20 second timeout

                try {
                    this.sdk.showAd((shown) => {
                        clearTimeout(timeout);

                        if (shown) {
                            console.log('✅ Interstitial Displayed Successfully');
                            this.interstitialReady = false;
                            AppState.sdk.interstitialLoaded = false;

                            // Preload next ad
                            setTimeout(() => {
                                console.log('🔄 Preloading Next Ad...');
                                this.loadInterstitial();
                            }, 3000);

                            resolve(true);
                        } else {
                            console.warn('⚠️ Interstitial Not Shown (Blocked/No Fill)');
                            this.interstitialReady = false;

                            // Try to reload
                            setTimeout(() => {
                                this.loadInterstitial();
                            }, 2000);

                            resolve(false);
                        }
                    });
                } catch (error) {
                    clearTimeout(timeout);
                    console.error('❌ Show Ad Exception:', error);
                    resolve(false);
                }
            });

        } catch (error) {
            console.error('❌ Interstitial Show Error:', error);
            return false;
        }
    }

    updateStatus(message, type) {
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

        const statusEl = document.getElementById('ad-status');
        if (statusEl) {
            statusEl.className = `mt-3 text-${colors[type]}`;
            statusEl.innerHTML = `<i class="fas fa-${icons[type]}"></i> <span>${message}</span>`;
        }
    }
}

// ==================== UI MANAGER ====================
class UIManager {
    static updateTaskCount() {
        const elements = {
            current: document.getElementById('current-tasks'),
            completed: document.getElementById('completed-today'),
            earnings: document.getElementById('earnings-amount'),
            button: document.getElementById('taskButton')
        };

        if (elements.current) elements.current.textContent = AppState.taskCount;
        if (elements.completed) elements.completed.textContent = AppState.taskCount;
        if (elements.earnings) elements.earnings.textContent = AppState.totalEarnings.toFixed(2);

        if (AppState.taskCount >= APP_CONFIG.maxTasks && elements.button) {
            elements.button.disabled = true;
            elements.button.innerHTML = '<i class="fas fa-check-circle"></i> All Tasks Completed!';
            elements.button.classList.remove('pulse-button');
            elements.button.style.background = '#28a745';
        }
    }

    static showNotification(message, type = 'info') {
        console.log(`🔔 ${message}`);

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
}

// ==================== TASK HANDLER ====================
class TaskHandler {
    constructor(adManager) {
        this.adManager = adManager;
        this.processing = false;
    }

    async complete() {
        if (this.processing) {
            console.log('⚠️ Already Processing');
            UIManager.showNotification('Please wait...', 'warning');
            return;
        }

        console.log('');
        console.log('🎯 ========== TASK COMPLETION STARTED ==========');

        // Validations
        if (AppState.taskCount >= APP_CONFIG.maxTasks) {
            UIManager.showNotification('Daily limit reached!', 'warning');
            console.log('❌ Daily Limit Reached');
            return;
        }

        if (!APP_CONFIG.packageBuyId) {
            UIManager.showNotification('No package purchased!', 'error');
            console.log('❌ No Package');
            return;
        }

        this.processing = true;
        const button = document.getElementById('taskButton');
        const originalHTML = button.innerHTML;
        button.disabled = true;

        try {
            // STEP 1: Show Ad FIRST
            if (APP_CONFIG.ads.showInterstitial) {
                console.log('📺 STEP 1: Showing Interstitial Ad...');
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading Ad...';

                const adShown = await this.adManager.showInterstitial();

                if (adShown) {
                    console.log('✅ Ad Displayed Successfully');
                    UIManager.showNotification('Ad watched!', 'success');
                    // Wait for user to see the ad
                    await new Promise(r => setTimeout(r, 2000));
                } else {
                    console.warn('⚠️ Ad Not Shown (Continuing Anyway)');
                    UIManager.showNotification('Ad not available', 'warning');
                    await new Promise(r => setTimeout(r, 1000));
                }
            } else {
                console.log('ℹ️ Interstitial Ads Disabled');
            }

            // STEP 2: Complete Task
            console.log('💾 STEP 2: Submitting Task Completion...');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Completing Task...';

            const response = await fetch(APP_CONFIG.taskCompleteUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': APP_CONFIG.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    package_buy_id: APP_CONFIG.packageBuyId
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('📥 Server Response:', data);

            if (data.success) {
                AppState.taskCount++;
                AppState.totalEarnings = AppState.taskCount * APP_CONFIG.dailyIncome;
                UIManager.updateTaskCount();

                UIManager.showNotification(
                    `✅ Task Completed! Earned $${APP_CONFIG.dailyIncome.toFixed(2)}`,
                    'success'
                );

                console.log('✅ Task Completed Successfully');
                console.log('📊 Tasks:', AppState.taskCount, '/', APP_CONFIG.maxTasks);
                console.log('💰 Total Earnings: $', AppState.totalEarnings.toFixed(2));
            } else {
                throw new Error(data.message || 'Task completion failed');
            }

        } catch (error) {
            console.error('❌ Task Error:', error);
            UIManager.showNotification(
                error.message || 'Failed to complete task',
                'error'
            );
        } finally {
            this.processing = false;

            // Update button state
            if (AppState.taskCount >= APP_CONFIG.maxTasks) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-check-circle"></i> All Tasks Completed!';
                button.classList.remove('pulse-button');
                button.style.background = '#28a745';
                console.log('🎉 All Tasks Completed!');
            } else {
                button.disabled = false;
                button.innerHTML = originalHTML;
            }

            console.log('🎯 ========== TASK COMPLETION FINISHED ==========');
            console.log('');
        }
    }
}

// ==================== APP INITIALIZATION ====================
class App {
    constructor() {
        this.adManager = new StartIOAdManager();
        this.taskHandler = null;
    }

    async init() {
        console.log('');
        console.log('🚀 ========== APP INITIALIZATION ==========');
        console.log('🌐 URL:', window.location.href);
        console.log('📱 User Agent:', navigator.userAgent);

        // Update UI
        UIManager.updateTaskCount();

        // Initialize SDK
        const sdkReady = await this.adManager.initialize();

        if (sdkReady) {
            console.log('✅ SDK Ready');

            // Load Banner Ads
            setTimeout(() => {
                console.log('📢 Loading Banner Ads...');
                this.adManager.loadBannerAds();
            }, 1500);

            // Preload FIRST Interstitial (CRITICAL!)
            setTimeout(async () => {
                console.log('🎬 Preloading First Interstitial...');
                const loaded = await this.adManager.loadInterstitial();

                if (loaded) {
                    console.log('✅ First Interstitial Ready');
                    UIManager.showNotification('Ready to earn!', 'success');
                } else {
                    console.warn('⚠️ First Interstitial Failed');
                }
            }, 3000);

            // Initialize Task Handler
            this.taskHandler = new TaskHandler(this.adManager);
            this.setupTaskButton();

            console.log('✅ Task Handler Ready');
        } else {
            console.error('❌ SDK Initialization Failed');
            UIManager.showNotification('Ad system unavailable', 'error');
        }

        console.log('✅ ========== INITIALIZATION COMPLETE ==========');
        console.log('');
    }

    setupTaskButton() {
        const button = document.getElementById('taskButton');
        if (button && !button.disabled) {
            button.addEventListener('click', async () => {
                console.log('🖱️ Button Clicked');
                if (this.taskHandler) {
                    await this.taskHandler.complete();
                }
            });
            console.log('✅ Button Listener Attached');
        }
    }
}

// ==================== START APPLICATION ====================
let appInstance = null;

async function startApplication() {
    console.log('🎬 Starting Application...');

    if (document.readyState === 'loading') {
        console.log('⏳ Waiting for DOM...');
        await new Promise(resolve => {
            document.addEventListener('DOMContentLoaded', resolve);
        });
    }

    console.log('✅ DOM Ready');
    appInstance = new App();
    await appInstance.init();
}

// Start App
startApplication().catch(error => {
    console.error('💥 Startup Error:', error);
});

// ==================== DEBUG HELPERS ====================
window.debugApp = () => {
    console.log('');
    console.log('🔍 ========== DEBUG INFO ==========');
    console.log('📋 Config:', APP_CONFIG);
    console.log('📊 State:', AppState);
    console.log('🔌 StartApp SDK:', typeof StartApp !== 'undefined' ? '✅ Loaded' : '❌ Not Loaded');
    console.log('🎯 App Instance:', appInstance);
    console.log('📺 Ad Manager:', appInstance?.adManager);
    console.log('🎬 SDK Instance:', appInstance?.adManager?.sdk);
    console.log('✅ Interstitial Ready:', appInstance?.adManager?.interstitialReady);
    console.log('🔄 Load In Progress:', appInstance?.adManager?.adLoadInProgress);
    console.log('========================================');
    console.log('');
};

window.testLoadAd = async () => {
    console.log('🧪 Testing Ad Load...');
    if (appInstance?.adManager) {
        const result = await appInstance.adManager.loadInterstitial();
        console.log('Result:', result ? '✅ Success' : '❌ Failed');
        return result;
    }
    console.error('❌ App Not Ready');
    return false;
};

window.testShowAd = async () => {
    console.log('🧪 Testing Ad Show...');
    if (appInstance?.adManager) {
        const result = await appInstance.adManager.showInterstitial();
        console.log('Result:', result ? '✅ Success' : '❌ Failed');
        return result;
    }
    console.error('❌ App Not Ready');
    return false;
};

window.testCompleteTask = async () => {
    console.log('🧪 Testing Task Completion...');
    if (appInstance?.taskHandler) {
        await appInstance.taskHandler.complete();
    } else {
        console.error('❌ Task Handler Not Ready');
    }
};
</script>
@endif

<style>
/* Container */
.container-content {
    max-width: 600px;
    margin: 0 auto;
}

/* Ad containers */
.banner-ad {
    position: relative;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 15px;
    min-height: 70px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 15px;
}

.ad-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(255,255,255,0.9);
    color: #333;
    font-size: 9px;
    font-weight: 700;
    padding: 4px 8px;
    border-radius: 10px;
    letter-spacing: 0.5px;
    z-index: 10;
}

.ad-container {
    background: white;
    border-radius: 8px;
    min-height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.ad-container > div {
    width: 100%;
    min-height: 50px;
}

/* Task box */
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

/* Package info */
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

/* Button */
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

.pulse-button {
    animation: buttonPulse 2s infinite;
}

@keyframes buttonPulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7); }
    50% { box-shadow: 0 0 0 15px rgba(102, 126, 234, 0); }
}

/* Task counter */
.task-counter {
    font-weight: 700;
    color: #667eea;
    font-size: 1.8rem;
}

/* Notification */
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

/* Status colors */
.text-success { color: #28a745 !important; }
.text-danger { color: #dc3545 !important; }
.text-error { color: #dc3545 !important; }
.text-warning { color: #ffc107 !important; }
.text-info { color: #17a2b8 !important; }

/* Responsive */
@media (max-width: 768px) {
    .container-content {
        padding: 10px !important;
    }

    .notification {
        right: 10px;
        left: 10px;
        max-width: none;
    }
}

/* WebView optimizations */
body {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -webkit-tap-highlight-color: transparent;
}

* {
    -webkit-overflow-scrolling: touch;
}
</style>
@endsection
