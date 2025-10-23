@extends('frontend.master')

@section('content')

 <div class="row">

        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('frontend.ads')}}"><i class="fas fa-bookmark"style="color:white;"></i></a>
            </div>
            <div class="menu-label">Start Task</div>
        </div>

        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('frontend.profile')}}"><i class="fas fa-user"style="color:white;"></i></a>
            </div>
            <div class="menu-label">Profile</div>
        </div>

        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('frontend.refer.list')}}"><i class="fas fa-shopping-basket"style="color:white;"></i></a>
            </div>
            <div class="menu-label">Refer</div>
        </div>

        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('frontend.options')}}"><i class="fas fa-user-plus"style="color:white;"></i></a>
            </div>
            <div class="menu-label">Options</div>
        </div>

        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="widthra.html"><i class="fas fa-money-bill-wave"style="color:white;"></i></a>
            </div>
            <div class="menu-label">Withdraw</div>
        </div>
        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('frontend.payment.history')}}"><i class="fas fa-bell"style="color:white;"></i></a>
            </div>
            <div class="menu-label">Payment History</div>
        </div>

        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('user.accept.view')}}"><i class="fas fa-user-check"style="color:white;"></i></a>
            </div>
            <div class="menu-label">Friend Request</div>
        </div>

        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('frontend.stepguide')}}"><i class="fas fa-box"style="color:white;"></i></a>
            </div>
            <div class="menu-label">How To Work</div>
        </div>

        <div class="menu-card">
            <div class="menu-icon-circle">
                <a href="{{route('frontend.support')}}"><i class="fas fa-coins"style="color:white;"></i></a>
            </div>
            <div class="menu-label">Support</div>
        </div>

    </div>
@endsection
