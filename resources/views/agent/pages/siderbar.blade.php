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
       <a href="{{route('admin.dashboard')}}">
          <span>Dashboard</span>
        </a>

      <li>
        <a href="maintenance.html">
          <i class="ri-hammer-line text-xl me-14 d-flex w-auto"></i>
          <span>Maintenance</span>
        </a>
      </li>
      <li class="dropdown">
        <a href="javascript:void(0)">
          <iconify-icon icon="icon-park-outline:setting-two" class="menu-icon"></iconify-icon>
          <span>Settings</span>
        </a>
        <ul class="sidebar-submenu">
          <li>
            <a href="{{route('appsetting.index')}}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Settings</a>
          </li>
          <li>
            <a href="notification.html"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i>
              Notification</a>
          </li>

          <li>
            <a href="theme.html"><i class="ri-circle-fill circle-icon text-danger-main w-auto"></i> Theme</a>
          </li>
          <li>
            <a href="currencies.html"><i class="ri-circle-fill circle-icon text-danger-main w-auto"></i> Currencies</a>
          </li>
          <li>
            <a href="language.html"><i class="ri-circle-fill circle-icon text-danger-main w-auto"></i> Languages</a>
          </li>
          <li>
            <a href="payment-gateway.html"><i class="ri-circle-fill circle-icon text-danger-main w-auto"></i> Payment
              Gateway</a>
          </li>
        </ul>
      </li>
    </ul>
  </div>
</aside>
