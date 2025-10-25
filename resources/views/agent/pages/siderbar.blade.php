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
          <span>Agent Request</span>
        </a>
        <ul class="sidebar-submenu">
          <li>
            <a href="{{route('agent.friend.request.accept.view')}}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Agent Request</a>
          </li>
        </ul>
      </li>
    </ul>
  </div>
</aside>

