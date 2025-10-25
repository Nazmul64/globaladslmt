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
        @php
            use App\Models\Wornotice;
            $Wornotices  = Wornotice::all();
        @endphp

        @foreach ($Wornotices as $Wornotice)
            <!-- Header -->
            <div style="text-align: center; margin-bottom: 20px;">
                <h2 style="margin: 0; font-size: 22px; color: #333; font-weight: 600;">
                    😍 {{ $Wornotice->title ?? '' }} 😍
                </h2>
            </div>

            <!-- Content -->
            <div style="margin-bottom: 25px; line-height: 1.6;">
                <p style="margin: 0 0 10px 0; font-size: 16px; color: #333; font-weight: 600;">
                    {{ $Wornotice->description ?? '' }}
                </p>
            </div>
        @endforeach



        <!-- OK Button -->
        <div style="text-align: right;">
            <button onclick="goToAds()" style="padding: 10px 35px; border: none; background-color: transparent; color: #E91E63; font-size: 16px; font-weight: 700; cursor: pointer; text-transform: uppercase;">
                OK
            </button>
        </div>
    </div>
</div>

@include('frontend.pages.footerlink')
