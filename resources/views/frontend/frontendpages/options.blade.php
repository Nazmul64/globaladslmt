@extends('frontend.master')

@section('content')



    <!-- Container -->
    <div class="container">
        <!-- Add Balance Card -->
        <div class="option-card">
            <div class="icon-circle">
                <i class="fas fa-plus"></i>
            </div>
            <div class="option-title"><a href="{{route('frontend.adblance')}}">Add Balance</a></div>
        </div>

        <!-- Buy Membership Card -->
        <div class="option-card">
            <div class="icon-circle">
                <i class="fas fa-shopping-basket"></i>
            </div>
            <div class="option-title"><a href="package.html">Buy Membership</a></div>
        </div>

        <!-- Info Card -->
        <div class="info-card">
            <div class="info-question">
                1) How to Buy Membership in Global Money Company?
            </div>
            <div class="info-answer">
                <strong>Ans:</strong> After clicking on <strong>Add Balance</strong> you will see many deposit balances. You can deposit in any account. Click on your profile after the deposit is complete. You can see if your deposit balance has been added. After the deposit balance is added. Click on <strong>Buy Membership</strong> to purchase the membership of your choice. Then click on <strong>Subscribe Now</strong> on the membership of your choice. Then confirm your membership purchase by clicking on <strong>Confirm</strong>. •º*"˜˜"*º•
            </div>
        </div>
    </div>
<div class="bottom-nav">
    <div class="nav-container">

        <div class="nav-item active">
            <a href="profile.html">
                <i class="fas fa-user"></i>
                <div class="nav-label">Profile</div>
            </a>
        </div>

        <div class="nav-item">
            <a href="widraw.html">
                <i class="fas fa-wallet"></i>
                <div class="nav-label">Withdraw</div>
            </a>
        </div>

        <div class="nav-item">
            <a href="index.html">
                <i class="fas fa-dollar-sign"></i>
                <div class="nav-label">Home</div>
            </a>
        </div>

        <div class="nav-item">
            <a href="userchat.html">
                <i class="fab fa-telegram"></i>
                <div class="nav-label">Live Chat</div>
            </a>
        </div>

        <div class="nav-item">
            <a href="agentlist.html">
                <i class="fas fa-users"></i>
                <div class="nav-label">Agents</div>
            </a>
        </div>

    </div>
</div>
@endsection
