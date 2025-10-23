@extends('frontend.master')

@section('content')
<div class="container">

    <!-- Search Bar -->
    <div class="search-container">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input
                type="text"
                class="search-input"
                id="searchInput"
                placeholder="Search agents by name, ID or phone..."
                oninput="searchAgents()"
            >
            <i class="fas fa-times clear-search" id="clearSearch" onclick="clearSearch()"></i>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="info-banner">
        <div class="info-icon">
            <i class="fas fa-headset"></i>
        </div>
        <div class="info-text">
            <div class="info-title">24/7 Support Available</div>
            <div class="info-desc">Our agents are here to help you with any questions or issues</div>
        </div>
    </div>

    <!-- Section Title -->
    <div class="section-title">
        <i class="fas fa-users"></i>
        Available Agents ({{ $agents->count() }})
    </div>

    @php
        use App\Models\Agentkyc;
        $currentUser = auth()->user();
    @endphp

    <!-- Agent List -->
    @foreach($agents as $agent)
        @php
            // Fetch KYC info for this agent if needed
            $kyc = Agentkyc::where('user_id', $agent->id)->first();
        @endphp

        <div class="agent-card">
            <div class="agent-header">
                <img src="{{ $agent->photo ? asset('uploads/agents/' . $agent->photo) : asset('uploads/avator.jpg') }}"
                     class="agent-photo"
                     alt="{{ $agent->name }}">
                <div class="agent-info">
                   <div class="agent-name">
                  {{ $agent->name }}

                       @php
                         $kyc = App\Models\Agentkyc::where('user_id', $agent->id)->first();
                        @endphp
                        @if($kyc && $kyc->status === 'approved')
                            <i class="fas fa-check-circle verified-badge text-success" title="Verified">Verified</i>
                        @else
                            <i class="fas fa-times-circle unverified-badge text-danger" title="Unverified">Unverified</i>
                        @endif
                    </div>

                    <div class="agent-id">Agent ID: #AG{{ str_pad($agent->id, 3, '0', STR_PAD_LEFT) }}</div>
                    <span class="agent-status">● {{ $agent->is_online ? 'Online' : 'Offline' }}</span>
                </div>
            </div>
            <div class="agent-actions">
                <a href="tel:{{ $agent->mobile }}" class="action-btn call-btn">
                    <i class="fas fa-phone"></i> Call Now
                </a>
                <a href="{{route('frontend.user.toagent.chat')}}" target="_blank" class="action-btn chat-btn">
                    <i class="fas fa-comments"></i> Live Chat
                </a>
            </div>
        </div>
    @endforeach

</div>
@endsection
