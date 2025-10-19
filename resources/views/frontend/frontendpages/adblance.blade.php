@extends('frontend.master')

@section('content')
<!-- Payment Method Selection Container -->
<div class="payment-methods-wrapper">
    <div class="payment-methods-grid">

        <!-- Bkash Payment Card -->
        <div class="payment-card-wrapper">
            <div class="payment-method-card" onclick="window.location.href='{{route('frontend.deposite')}}'">
                <div class="payment-logo-container">
                    <img src="{{ asset('frontend/assets/images/assets/bkash.jpg') }}"
                         class="payment-logo-img"
                         alt="Bkash Payment Gateway">
                </div>
                <div class="payment-label-overlay">
                    <h5 class="payment-title">
                        <i class="fas fa-wallet"></i> Bkash
                    </h5>
                </div>
            </div>
        </div>

        <!-- Nagad Payment Card -->
        <div class="payment-card-wrapper">
            <div class="payment-method-card" onclick="window.location.href='{{route('frontend.deposite')}}'">
                <div class="payment-logo-container">
                    <img src="{{ asset('frontend/assets/images/assets/nogad.jpg') }}"
                         class="payment-logo-img"
                         alt="Nagad Payment Gateway">
                </div>
                <div class="payment-label-overlay">
                    <h5 class="payment-title">
                        <i class="fas fa-mobile-alt"></i> Nagad
                    </h5>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Responsive Styles -->
<style>
    /* Main Wrapper */
    .payment-methods-wrapper {
        width: 100%;
        padding: 20px 15px;
        box-sizing: border-box;
    }

    /* Grid Layout */
    .payment-methods-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Card Wrapper */
    .payment-card-wrapper {
        width: 100%;
    }

    /* Payment Method Card */
    .payment-method-card {
        background: #ffffff;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        height: 100%;
        min-height: 250px;
    }

    /* Logo Container */
    .payment-logo-container {
        width: 100%;
        height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;

        padding: 30px;
        box-sizing: border-box;
    }

    .payment-logo-img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        display: block;
    }

    /* Label Overlay */
    .payment-label-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 20px;

        text-align: center;
    }

    .payment-title {
        color: #FF7E5E;
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6);
        letter-spacing: 0.5px;
    }

    .payment-title i {
        margin-right: 8px;
    }

    /* Hover Effect */
    .payment-method-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2);
    }

    .payment-method-card:active {
        transform: translateY(-4px);
    }

    /* Mobile Responsive - Single Column */
    @media (max-width: 576px) {
        .payment-methods-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .payment-method-card {
            min-height: 280px;
        }

        .payment-logo-container {
            height: 280px;
            padding: 40px;
        }

        .payment-title {
            font-size: 1.4rem;
        }

        .payment-label-overlay {
            padding: 25px;
        }

        .payment-methods-wrapper {
            padding: 25px 15px;
        }
    }

    /* Tablet View */
    @media (min-width: 577px) and (max-width: 768px) {
        .payment-logo-container {
            height: 230px;
        }

        .payment-title {
            font-size: 1.2rem;
        }
    }

    /* Prevent Body Overflow */
    body {
        overflow-x: hidden;
    }
</style>
@endsection
