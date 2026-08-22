  <!-- Copy Toast -->
    <div class="copy-toast" id="copyToast">
        <i class="fas fa-check-circle"></i>
        <span>Refer code copied!</span>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="sidebar-title">Global money Ltd</div>
        </div>
        <div class="sidebar-menu">
            <a href="#" class="sidebar-item">
                <i class="fas fa-user"></i>
                <span class="sidebar-item-text">Profile</span>
            </a>
             <a href="{{ route('privacy.terms') }}" class="sidebar-item">
                <i class="fas fa-user"></i>
                <span class="sidebar-item-text">Privacy Policy</span>
            </a>
              <a href="{{ route('child.safety.policy') }}" class="sidebar-item">
                <i class="fas fa-user"></i>
                <span class="sidebar-item-text">Child Safety Policy</span>
            </a>

            <a href="#" class="sidebar-item">
                <i class="fas fa-wallet"></i>
                <span class="sidebar-item-text">Widraw</span>
            </a>
            <a href="{{route('posts.index')}}" class="sidebar-item" >
                <i class="fas fa-dollar-sign"></i>
                <span class="sidebar-item-text">Post Create</span>
            </a>
            <a href="#" class="sidebar-item">
                <i class="fas fa-bullhorn"></i>
                <span class="sidebar-item-text">Support</span>
            </a>
            <a href="{{route('agent.register')}}" class="sidebar-item" >
                <i class="fas fa-users"></i>
                <span class="sidebar-item-text">Agent Register</span>
            </a>
           <a href="javascript:void(0);" class="sidebar-item" onclick="document.getElementById('logoutForm').submit();">
                <i class="fas fa-shield-alt"></i>
                <span class="sidebar-item-text">Logout</span>
            </a>

            <form id="logoutForm" action="{{ route('user.logout') }}" method="POST" style="display:none;">
                @csrf
           </form>

        </div>
    </div>
