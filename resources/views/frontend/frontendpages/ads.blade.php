@extends('frontend.master')

@section('content')
<div class="container-content py-4 text-center">

    <!-- Banner Ad Top -->
    <div class="banner-ad mb-3">
        <div class="ad-badge">AD</div>
        <div class="ad-content">
            {!! $ads->banner_ad_1 ?? '<div class="startio-ad">Banner Advertisement Space</div>' !!}
        </div>
    </div>

    <!-- Task Box + Package Info -->
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
            <strong>Package:</strong> {{ $packageBuy->package->package_name }} <br>
            <strong>Daily Income:</strong> ${{ $packageBuy->daily_income }} <br>
            <strong>Daily Limit:</strong> {{ $packageBuy->daily_limit }} Coins
        </div>
        @else
        <div class="alert alert-warning mb-3">
            You have not purchased any package yet.
        </div>
        @endif

        <div class="task-counter h5 mb-3" id="taskCounter">1/2</div>

        <button class="btn btn-primary btn-lg" onclick="goToAddedPage()">
            <i class="fas fa-hand-pointer"></i> Click to Complete
        </button>
    </div>

    <!-- MREC Ad Center -->
    <div class="mrec-ad my-4">
        <div class="ad-badge">AD</div>
        <div class="ad-content">
            {!! $ads->code ?? '<div class="startio-ad">MREC Advertisement Space</div>' !!}
        </div>
    </div>

    <!-- Banner Ad Bottom -->
    <div class="banner-ad mt-3">
        <div class="ad-badge">AD</div>
        <div class="ad-content">
            {!! $ads->code ?? '<div class="startio-ad">Banner Advertisement Space</div>' !!}
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function goToAddedPage() {
    alert('Task Completed!'); // আপনার অরিজিনাল JS replace করতে পারেন
}
</script>
@endpush

@push('styles')
<style>
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
.package-info {
    font-weight: 500;
    color: #333;
}
.task-box {
    border-radius: 12px;
}
</style>
@endpush
