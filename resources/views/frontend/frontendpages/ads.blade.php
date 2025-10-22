@extends('frontend.master')

@section('content')
<!-- Task Page -->
<div id="taskPage" class="page active">
    <div class="container-content text-center py-4">

        <!-- Banner Ad 1 - Top -->
        <div class="banner-ad mb-3" id="bannerAd1">
            <div class="ad-badge">AD</div>
            <div class="ad-content">
                {!! $ads->banner_ad_1 ?? '<div class="startio-ad">Banner Advertisement Space</div>' !!}
            </div>
        </div>

        <!-- Task Box -->
        <div class="task-box card shadow-lg p-4 border-0">
            <div class="task-icon mb-3">
                <i class="fas fa-tasks fa-3x text-primary"></i>
            </div>
            <div class="task-title h4 mb-2">Complete Your Task</div>
            <div class="task-description mb-3 text-muted">
                Click the button below to complete your daily task and earn amazing rewards!
            </div>

            <div class="task-counter h5 mb-3" id="taskCounter">1/2</div>

            <button class="btn btn-primary btn-lg" onclick="goToAddedPage()">
                <i class="fas fa-hand-pointer"></i> Click to Complete
            </button>
        </div>

        <!-- MREC Ad 1 -->
        <div class="mrec-ad my-4" id="mrecAd1">
            <div class="ad-badge">AD</div>
            <div class="ad-content">
                {!! $ads->code ?? '<div class="startio-ad">MREC Advertisement Space</div>' !!}
            </div>
        </div>

        <!-- Banner Ad 2 - Bottom -->
        <div class="banner-ad mt-3" id="bannerAd2">
            <div class="ad-badge">AD</div>
            <div class="ad-content">
                {!! $ads->code ?? '<div class="startio-ad">Banner Advertisement Space</div>' !!}
            </div>
        </div>
    </div>
</div>

<!-- Added/Success Page -->
<div id="addedPage" class="page d-none">
    <div class="top-header d-flex align-items-center mb-3">
        <button class="btn btn-outline-secondary me-2" onclick="backToTask()">
            <i class="fas fa-arrow-left"></i>
        </button>
        <div class="header-title h5 mb-0">Task Completed</div>
    </div>

    <div class="container-content text-center">
        <!-- Banner Ad - Top -->
        <div class="banner-ad mb-3" id="bannerAd3">
            <div class="ad-badge">AD</div>
            <div class="ad-content">
                {!! $ads->code ?? '<div class="startio-ad">Banner Advertisement Space</div>' !!}
            </div>
        </div>

        <!-- MREC Ad - Center -->
        <div class="mrec-ad my-3" id="mrecAd2">
            <div class="ad-badge">AD</div>
            <div class="ad-content">
                {!! $ads->code ?? '<div class="startio-ad">MREC Advertisement Space</div>' !!}
            </div>
        </div>

        <!-- Success Box -->
        <div class="task-box card shadow-lg p-4 border-0">
            <div class="success-icon text-success mb-3">
                <i class="fas fa-check fa-3x"></i>
            </div>
            <div class="success-title h4 mb-2">Task Added Successfully!</div>
            <div class="success-message mb-4">
                <span class="coin-animation">🎉</span> Congratulations! Your task has been completed and <strong>+10 coins</strong> have been added to your account.
            </div>

            <div class="info-cards d-flex justify-content-center gap-4">
                <div class="info-card text-center">
                    <div class="info-card-value h5">+10 <i class="fas fa-coins text-warning"></i></div>
                    <div class="info-card-label text-muted">Coins Earned</div>
                </div>
                <div class="info-card text-center">
                    <div class="info-card-value h5">2/2</div>
                    <div class="info-card-label text-muted">Tasks Complete</div>
                </div>
            </div>

            <button class="btn btn-outline-primary mt-4" onclick="backToTask()">
                <i class="fas fa-arrow-left"></i> Back to Tasks
            </button>
        </div>

        <!-- Banner Ad - Bottom -->
        <div class="banner-ad mt-4" id="bannerAd4">
            <div class="ad-badge">AD</div>
            <div class="ad-content">
                {!! $ads->code ?? '<div class="startio-ad">Banner Advertisement Space</div>' !!}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function goToAddedPage() {
        document.getElementById("taskPage").classList.add("d-none");
        document.getElementById("addedPage").classList.remove("d-none");
    }

    function backToTask() {
        document.getElementById("addedPage").classList.add("d-none");
        document.getElementById("taskPage").classList.remove("d-none");
    }
</script>
@endpush

@push('styles')
<style>
    .page {
        transition: all 0.4s ease-in-out;
    }
    .banner-ad, .mrec-ad {
        position: relative;
        background: #f5f5f5;
        border: 1px dashed #ccc;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
    }
    .ad-badge {
        position: absolute;
        top: -10px;
        left: 10px;
        background: #ff9800;
        color: white;
        font-size: 12px;
        padding: 3px 6px;
        border-radius: 5px;
    }
    .startio-ad {
        font-weight: 500;
        color: #777;
    }
    .task-box {
        background: white;
        border-radius: 12px;
    }
    .coin-animation {
        animation: bounce 1s infinite;
    }
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
</style>
@endpush
