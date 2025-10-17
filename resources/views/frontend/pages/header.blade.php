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
            <a href="profile.html" class="sidebar-item">
                <i class="fas fa-user"></i>
                <span class="sidebar-item-text">Profile</span>
            </a>
            <a href="widraw.html" class="sidebar-item">
                <i class="fas fa-wallet"></i>
                <span class="sidebar-item-text">Widraw</span>
            </a>
            <a href="paymenthistory.html" class="sidebar-item" >
                <i class="fas fa-dollar-sign"></i>
                <span class="sidebar-item-text">Payment History</span>
            </a>
            <a href="support.html" class="sidebar-item">
                <i class="fas fa-bullhorn"></i>
                <span class="sidebar-item-text">Support</span>
            </a>
            <a href="taskhistory.html" class="sidebar-item" >
                <i class="fas fa-users"></i>
                <span class="sidebar-item-text">Taskhistory</span>
            </a>
            <a href="{{ route('user.logout') }}" class="sidebar-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-right-from-bracket"></i>
                <span class="sidebar-item-text">Logout</span>
            </a>
             <form id="logout-form" action="{{ route('user.logout') }}" method="POST" class="d-none">
                    @csrf
             </form>



        </div>
    </div>
