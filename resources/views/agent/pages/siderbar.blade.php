<aside class="sidebar">
  <button type="button" class="sidebar-close-btn">
    <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
  </button>
  <div>
    <a href="index.html" class="sidebar-logo">
      <img src="assets/images/logo.png" alt="site logo" class="light-logo">
      <img src="assets/images/logo-light.png" alt="site logo" class="dark-logo">
      <img src="assets/images/logo-icon.png" alt="site logo" class="logo-icon">
    </a>
  </div>
  <div class="sidebar-menu-area">
    <ul class="sidebar-menu" id="sidebar-menu">
       <a href="{{route('agent.dashboard')}}">
          <span>Dashboard</span>
        </a>

      <li class="dropdown">
        <a href="javascript:void(0)">
          <iconify-icon icon="icon-park-outline:setting-two" class="menu-icon"></iconify-icon>
          <span>Live Chat</span>
        </a>
        <ul class="sidebar-submenu">
          <li>
            <a href="{{route('agent.user.toagent.chat')}}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Live Chat</a>
          </li>
           <li>
            <a href="{{route('agentforchat.index')}}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Live Chat for Admin</a>
          </li>

        </ul>
      </li>
      <li class="dropdown">
        <a href="javascript:void(0)">
          <iconify-icon icon="icon-park-outline:setting-two" class="menu-icon"></iconify-icon>
          <span>User Request</span>
        </a>
        <ul class="sidebar-submenu">
          <li>
            <a href="{{route('agent.friend.request.accept.view')}}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Agent Request</a>
          </li>
        </ul>
      </li>


    <li class="dropdown">
        <a href="javascript:void(0)">
            <iconify-icon icon="mdi:bank-transfer" class="menu-icon"></iconify-icon>
            <span>Deposite</span>
        </a>
        <ul class="sidebar-submenu">
            <!-- 🏦 Deposit -->
            <li>
                <a href="{{ route('agent.deposite') }}">
                    <iconify-icon icon="mdi:cash-plus" class="menu-icon text-success"></iconify-icon>
                    Deposite Request
                </a>
            </li>

            <!-- ✅ Approved List -->
            <li>
                <a href="{{ route('agent.deposite.approved.list') }}">
                    <iconify-icon icon="mdi:check-circle" class="menu-icon text-success"></iconify-icon>
                    Approved List
                </a>
            </li>

            <!-- ❌ Rejected List -->
            <li>
                <a href="{{ route('agent.deposite.reject.list') }}">
                    <iconify-icon icon="mdi:close-circle" class="menu-icon text-danger"></iconify-icon>
                    Rejected List
                </a>
            </li>
        </ul>
    </li>
    <li class="dropdown">
        <a href="javascript:void(0)">
            <!-- Iconify icon -->
            <iconify-icon icon="icon-park-outline:setting-two" class="menu-icon" width="20" height="20"></iconify-icon>
            <span>Agent Buy/Sell Post</span>
            <!-- Dropdown arrow -->
            <i class="ri-arrow-down-s-line float-end"></i>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('agentbuysellpost.index') }}">
                    <!-- Circle small icon for submenu -->
                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                    View Posts
                </a>
            </li>
            <li>
                <a href="{{ route('agentbuysellpost.create') }}">
                    <i class="ri-add-line circle-icon text-success-600 w-auto"></i>
                    Create Post
                </a>
            </li>
        </ul>
    </li>
    <li class="dropdown">
        <a href="javascript:void(0)">
            <!-- Iconify icon -->
            <iconify-icon icon="icon-park-outline:setting-two" class="menu-icon" width="20" height="20"></iconify-icon>
            <span>User Deposite Widhraw Request</span>
            <!-- Dropdown arrow -->
            <i class="ri-arrow-down-s-line float-end"></i>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('agent.deposit.requests') }}">
                    <!-- Circle small icon for submenu -->
                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                    User Deposite Widhraw Request
                </a>
            </li>
        </ul>
    </li>

    </ul>
  </div>
</aside>

