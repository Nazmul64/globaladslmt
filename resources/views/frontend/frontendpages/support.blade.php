@extends('frontend.master')
@section('content')
      <div class="support-container">
        <!-- Support Header -->
        <div class="support-header">
            <h1>Need Help?</h1>
            <p>We're here to assist you 24/7</p>
        </div>

        <!-- Contact Methods -->
        <div class="contact-methods">
             @foreach ($supports  as $item )
                <a href="{{ $item->url_link ?? '#' }}" class="contact-card whatsapp" target="_blank">
                    <i class="{{$item->icon ?? ''}}"></i>
                    <div class="contact-label">{{$item->name ?? ''}}</div>
                </a>
            @endforeach
        </div>
    </div>
@endsection
