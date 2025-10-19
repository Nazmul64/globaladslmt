 @include('frontend.pages.headerlink')
@include('frontend.pages.sidebar')
@include('frontend.pages.navbar')


<!-- ============ CSS CODE ============ -->

<!-- ============ HTML CODE ============ -->
@include('frontend.pages.profile')
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
@include('frontend.pages.footerlink')
