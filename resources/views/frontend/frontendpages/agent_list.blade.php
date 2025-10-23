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
                <img src="{{ $agent->photo ? asset('uploads/agents/' . $agent->photo) : asset('uploads/logo.png') }}"
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
                @php
                    // Current logged-in user + agent check
                    $friendRequest = \App\Models\ChatRequest::where('sender_id', auth()->id())
                                        ->where('receiver_id', $agent->id)
                                        ->first();
                @endphp

                @if($friendRequest)
                    @if($friendRequest->status == 'accepted')
                        {{-- শুধুমাত্র এই user এর জন্য --}}
                        <button class="btn btn-success" disabled>
                            <i class="fas fa-check"></i> Friend / Confirmed
                        </button>
                    @elseif($friendRequest->status == 'pending')
                        <button class="btn btn-warning" disabled>
                            <i class="fas fa-hourglass-half"></i> Request Sent
                        </button>
                    @endif
                @else
                    {{-- এই user এখনও request পাঠায়নি --}}
                    <form action="{{ route('agentss.user.friend.request') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $agent->id }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-phone"></i> Agent Request
                        </button>
                    </form>
                @endif

                {{-- Live Chat --}}
                <a href="{{ route('frontend.user.toagent.chat') }}" target="_blank" class="action-btn chat-btn">
                    <i class="fas fa-comments"></i> Live Chat
                </a>
            </div>

        </div>
    @endforeach

</div>
@endsection
