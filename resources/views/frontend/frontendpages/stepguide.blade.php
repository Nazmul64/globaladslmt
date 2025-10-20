@extends('frontend.master')
@section('content')
 <div class="container">

        <!-- Steps Section -->
        <div class="steps-section">
            <h2 class="section-title">
                <i class="fas fa-list-ol"></i>
                Step by Step Guide
            </h2>
           @foreach ($step_guides  as   $item)
            <div class="step-card">
                <div class="step-header">
                    <div class="step-number">{{$item->serial_number ?? ''}}</div>
                    <div class="step-title">{{$item->title ?? ''}}</div>
                </div>
                <p class="step-description">
                    {{$item->description ?? ''}}
                </p>
                <i class=" {{$item->icon ?? ''}}"></i>
            </div>
            @endforeach
        </div>

        <!-- Features Grid -->
        <h2 class="section-title">
            <i class="fas fa-star"></i>
            Why Choose Us
        </h2>
        <div class="features-grid-container">
             @foreach ( $why_choose_us_items as  $item)
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="{{$item->icon ?? ''}}"></i>
                        </div>
                        <div class="feature-title">{{$item->title ?? ''}}</div>
                        <p class="feature-description">
                            {{$item->description ?? ''}}
                        </p>
                    </div>
                </div>
                @endforeach

        </div>
    </div>
@endsection
