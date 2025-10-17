<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global money Ltd</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="{{asset('frontend')}}/assets/css/custom.css">
</head>
<body>

   @include('frontend.pages.header')
   @include('frontend.pages.profile')


<!-- ============ JAVASCRIPT CODE ============ -->


    <!-- Notice Bar -->
    <!-- <div class="notice-bar">
        <div class="notice-content">
            <i class="fas fa-bell notice-icon"></i>
            <div class="notice-text">
                🎉 Welcome! Complete tasks & earn coins • Refer friends for bonus • Min withdrawal 100 coins • New offers soon! 🚀
            </div>
            <i class="fas fa-bell notice-icon"></i>
            <div class="notice-text">
                🎉 Welcome! Complete tasks & earn coins • Refer friends for bonus • Min withdrawal 100 coins • New offers soon! 🚀
            </div>
        </div>
    </div> -->

    <!-- Slider Section -->
    <!-- <div class="slider-section">
        <div class="slider-container">
            <div class="slider-wrapper">
                <div class="slider-track" id="sliderTrack">
                    <div class="slide">
                        <img src="https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=800&h=400&fit=crop" alt="Slide 1">
                        <div class="slide-overlay">
                            <div class="slide-title">Earn Money Daily</div>
                            <div class="slide-description">Complete tasks and get instant rewards</div>
                        </div>
                    </div>
                    <div class="slide">
                        <img src="https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?w=800&h=400&fit=crop" alt="Slide 2">
                        <div class="slide-overlay">
                            <div class="slide-title">Refer & Earn</div>
                            <div class="slide-description">Invite friends and earn bonus coins</div>
                        </div>
                    </div>
                    <div class="slide">
                        <img src="https://images.unsplash.com/photo-1633158829585-23ba8f7c8caf?w=800&h=400&fit=crop" alt="Slide 3">
                        <div class="slide-overlay">
                            <div class="slide-title">Fast Withdrawal</div>
                            <div class="slide-description">Quick and secure payment process</div>
                        </div>
                    </div>
                </div>
            </div>
            <button class="slider-nav prev" onclick="moveSlide(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="slider-nav next" onclick="moveSlide(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
            <div class="slider-dots" id="sliderDots"></div>
        </div>
    </div> -->

    <!-- Dashboard Grid -->
   <!-- Font Awesome CDN (for icons) -->


<!-- Dashboard Menu Section -->
<div class="dashboard-grid">
    @yield('content')
</div>



<!-- Bottom Navigation -->
@include('frontend.pages.footer')
<!-- Modal HTML - এটা আপনার body তে যোগ করুন -->
<div id="startTaskModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6);">
    <div style="position: relative; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; padding: 25px; border-radius: 10px; max-width: 450px; width: 90%; box-shadow: 0 5px 30px rgba(0,0,0,0.4);">

        <!-- Header -->
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="margin: 0; font-size: 22px; color: #333; font-weight: 600;">
                😍 Welcome to our company 😍
            </h2>
        </div>

        <!-- Content -->
        <div style="margin-bottom: 25px; line-height: 1.6;">
            <p style="margin: 0 0 10px 0; font-size: 16px; color: #333; font-weight: 600;">
                "Do not click invalid"
            </p>
            <p style="margin: 0 0 15px 0; font-size: 15px; color: #555;">
                Account will be blocked after 5 invalid clicks.
            </p>

            <!-- Bangladesh Flag -->
            <div style="background-color: #f0f0f0; padding: 12px; border-radius: 8px; margin-bottom: 15px; text-align: center;">
                <p style="margin: 0; font-size: 16px; color: #333; font-weight: 500;">
                    🇧🇩 বাংলা ভাষীদের জন্য 🇧🇩
                </p>
            </div>

            <div style="margin-bottom: 15px;">
                <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #333; font-weight: 600;">
                    😍 আমাদের কোম্পানিতে স্বাগতম 😍
                </h3>
            </div>

            <p style="margin: 0 0 8px 0; font-size: 15px; color: #555; font-weight: 600;">
                "ইনভ্যালিড ক্লিক করবেন না"
            </p>
            <p style="margin: 0; font-size: 15px; color: #555;">
                ৫ টি ইনভ্যালিড ক্লিকের পরে আপনার অ্যাকাউন্ট ব্লক করা হবে।
            </p>
        </div>

        <!-- OK Button -->
        <div style="text-align: right;">
            <button onclick="goToAds()" style="padding: 10px 35px; border: none; background-color: transparent; color: #E91E63; font-size: 16px; font-weight: 700; cursor: pointer; text-transform: uppercase;">
                OK
            </button>
        </div>
    </div>
</div>

<script>
// Start Task Modal Functions
function openTaskModal(event) {
    event.preventDefault();
    document.getElementById('startTaskModal').style.display = 'block';
}

function closeTaskModal() {
    document.getElementById('startTaskModal').style.display = 'none';
}

function goToAds() {
    window.location.href = 'ads.html';
}

// পেজ লোড হলে Start Task লিংকে ইভেন্ট যোগ করবে
document.addEventListener('DOMContentLoaded', function() {
    const startTaskLink = document.querySelector('a[href="ads.html"]');
    if (startTaskLink) {
        startTaskLink.addEventListener('click', openTaskModal);
    }
});

// মডালের বাইরে ক্লিক করলে বন্ধ হবে
window.addEventListener('click', function(event) {
    if (event.target.id === 'startTaskModal') {
        closeTaskModal();
    }
});
</script>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{asset('frontend')}}/assets/js/customs.js"></script>
<script src="{{asset('frontend')}}/assets/js/custom.js"></script>
</body>
</html>
