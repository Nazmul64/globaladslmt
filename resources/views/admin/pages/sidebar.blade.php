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

      <li class="dropdown">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-id-card-clip fa-xl me-2"></i>
            <span>Live Chat</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('admin.userchat') }}">
                    <i class="fa-solid fa-hourglass-half fa-lg me-2 text-warning"></i>
                    Live Chat User
                </a>
            </li>
            <li>
                <a href="{{ route('admin.agent.chat') }}">
                    <i class="fa-solid fa-hourglass-half fa-lg me-2 text-warning"></i>
                    Live Chat Fro Agent
                </a>
            </li>
        </ul>
    </li>
        <li class="dropdown">
            <a href="javascript:void(0)">
                <i class="bi bi-people-fill menu-icon"></i>
                <span>Agents</span>
            </a>
            <ul class="sidebar-submenu">
                <!-- Pending Agents -->
                 <li>
                    <a href="{{ route('agentcreate.create') }}">
                        <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                         Agents  Create
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.agent.pending') }}">
                        <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                        Pending Agents
                    </a>
                </li>

                <!-- Approved Agents -->
                <li>
                    <a href="{{ route('agentapprovedlist') }}">
                        <i class="ri-circle-fill circle-icon text-success w-auto"></i>
                        Approved Agents
                    </a>
                </li>

                <!-- Rejected Agents -->
                <li>
                    <a href="{{ route('admin.agent.rejectlist') }}">
                        <i class="ri-circle-fill circle-icon text-danger w-auto"></i>
                        Rejected Agents
                    </a>
                </li>
            </ul>
        </li>

     <li class="dropdown">
        <a href="javascript:void(0)">
            <!-- Payment icon -->
            <i class="ri-wallet-line text-xl me-14 d-flex w-auto"></i>
            <span>Payment Method</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('paymentmethod.index') }}">
                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                    Payment Method
                </a>
            </li>
        </ul>
    </li>

    <li class="dropdown">
        <a href="javascript:void(0)">
            <!-- Payment icon -->
            <i class="ri-wallet-line text-xl me-14 d-flex w-auto"></i>
            <span>Commission Setup</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('reffercommission.index') }}">
                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                    Reffercommission Setup
                </a>
            </li>
            <li>
                <a href="{{ route('agentcommission.index') }}">
                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                    Agent Commission Setup
                </a>
            </li>
        </ul>
    </li>
    <li class="dropdown">
        <a href="javascript:void(0)">
            <!-- Notice icon -->
            <i class="ri-megaphone-line text-xl me-14 d-flex w-auto"></i>
            <span>Notice Setup</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('notice.index') }}">
                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                    Notice Setup
                </a>
            </li>
              <li>
                <a href="{{ route('worknotice.index') }}">
                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                    WorkNotice Setup
                </a>
            </li>

        </ul>
    </li>
    <li class="dropdown">
        <a href="javascript:void(0)">
            <!-- Notice icon -->
             <i class="fa-solid fa-box text-xl me-14 d-flex w-auto"></i>
            <span>Package Setup</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('package.index') }}">
                    <i class="fa-solid fa-box text-xl me-14 d-flex w-auto"></i>
                    Package Setup
                </a>
            </li>
        </ul>
    </li>
   <li class="dropdown">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-box fa-xl me-2"></i>
            <span>User Package List</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('admin.buy.package.list') }}">
                    <i class="fa-solid fa-box fa-lg me-2"></i>
                    User Package List
                </a>
            </li>
        </ul>
    </li>
       <li class="dropdown">
            <a href="javascript:void(0)">
                <i class="fa-solid fa-hand-holding-dollar text-xl me-2"></i>
                <span>Pending Deposits</span>
            </a>
            <ul class="sidebar-submenu">
                <li>
                    <a href="{{ route('admin.deposite.pending') }}">
                        <i class="fa-solid fa-hourglass-half text-xl me-2"></i>
                        Pending Deposits
                    </a>
                </li>
                 <li>
                    <a href="{{ route('admin.deposite.approved.list') }}">
                        <i class="fa-solid fa-hourglass-half text-xl me-2"></i>
                        Approved  Deposits List
                    </a>
                </li>
                 <li>
                    <a href="{{ route('admin.deposite.reject.list') }}">
                        <i class="fa-solid fa-hourglass-half text-xl me-2"></i>
                        Reject  Deposits List
                    </a>
                </li>
            </ul>
        </li>
    <li class="dropdown">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-box fa-xl me-2"></i>
            <span>Support Setup</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('support.index') }}">
                    <i class="fa-solid fa-box fa-lg me-2"></i>
                    Support SetupList
                </a>
            </li>
        </ul>
    </li>
    <li class="dropdown">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-list-check fa-xl me-2"></i>
            <span>Stepguide Setup</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('stepguide.index') }}">
                    <i class="fa-solid fa-list-check fa-lg me-2"></i>
                    Stepguide Setup List
                </a>
            </li>
             <li>
                <a href="{{ route('whychooseu.index') }}">
                    <i class="fa-solid fa-list-check fa-lg me-2"></i>
                    Whychooseus  List
                </a>
            </li>
        </ul>
    </li>
    <li class="dropdown">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-list-check fa-xl me-2"></i>
            <span>User KYC List</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('kyc.list') }}">
                    <i class="fa-solid fa-file-shield fa-lg me-2"></i>
                    KYC List
                </a>
            </li>
             <li>
                <a href="{{ route('frontend.kyc.approved.list') }}">
                    <i class="fa-solid fa-check-circle me-2"></i> Approved KYC List
                </a>
            </li>
             <li>
                <a href="{{ route('frontend.kyc.reject.list') }}">
                   <i class="fa-solid fa-xmark-circle me-2"></i> Rejected KYC List
                </a>
            </li>
        </ul>
    </li>
    <li class="dropdown">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-id-card-clip fa-xl me-2"></i>
            <span>Agent KYC List</span>
        </a>
        <ul class="sidebar-submenu">

            <!-- Pending/All KYC List -->
            <li>
                <a href="{{ route('agent.kyc.list') }}">
                    <i class="fa-solid fa-hourglass-half fa-lg me-2 text-warning"></i>
                    Pending / All KYC List
                </a>
            </li>

            <!-- Approved KYC List -->
            <li>
                <a href="{{ route('agent.approved.kyc.list') }}">
                    <i class="fa-solid fa-circle-check fa-lg me-2 text-success"></i>
                    Approved Agent KYC List
                </a>
            </li>

            <!-- Rejected KYC List -->
            <li>
                <a href="{{ route('agent.kyc.reject.list') }}">
                    <i class="fa-solid fa-circle-xmark fa-lg me-2 text-danger"></i>
                    Rejected Agent KYC List
                </a>
            </li>

        </ul>
    </li>
    <li class="dropdown">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-box fa-xl me-2"></i>
            <span>Ads Setup</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('ads.index') }}">
                    <i class="fa-solid fa-box fa-lg me-2"></i>
                    Ads SetupList
                </a>
            </li>
        </ul>
    </li>
    <li class="dropdown">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-coins fa-xl me-2"></i>
            <span>Deposit Limit Setup</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('depositelimit.index') }}">
                    <i class="fa-solid fa-list fa-lg me-2"></i>
                    Deposit Limit List
                </a>
            </li>
            <li>
                <a href="{{ route('widthrawlimit.index') }}">
                    <i class="fa-solid fa-list fa-lg me-2"></i>
                    Widthraw Limit List
                </a>
            </li>
        </ul>
    </li>
    <li class="dropdown">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-coins fa-xl me-2"></i>
            <span>Agent Deposite Pending</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('admin.agent.deposite.pending') }}">
                    <i class="fa-solid fa-list fa-lg me-2"></i>
                    Agent Deposite Pending
                </a>
            </li>
            <li>
            <a href="{{route('admin.agent.deposite.approved.list')}}">
                <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                Approved  Lists
            </a>
            </li>
            <li>
            <a href="{{route('admin.agent.deposite.reject.list')}}">
                <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                Rejected Lists
            </a>
            </li>
        </ul>
    </li>

    <li class="dropdown">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-tags fa-xl me-2"></i>
            <span> Category</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('category.index') }}">
                    <i class="fa-solid fa-list fa-lg me-2"></i>
                    Category
                </a>
            </li>
        </ul>
    </li>
   <li class="dropdown">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-money-bill-transfer fa-xl me-2"></i>
            <span>User Withdraw</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('admin.widtharw.approved.index') }}">
                    <i class="fa-solid fa-list fa-lg me-2"></i>
                    User Withdraw approved
                </a>
            </li>
              <li>
                <a href="{{ route('approved.list') }}">
                    <i class="fa-solid fa-list fa-lg me-2"></i>
                    Approved List
                </a>
            </li>
            <li>
                <a href="{{ route('rejected.list') }}">
                    <i class="fa-solid fa-list fa-lg me-2"></i>
                    Rejected List
                </a>
            </li>
        </ul>
    </li>
      <li class="dropdown">
        <a href="javascript:void(0)">
          <iconify-icon icon="icon-park-outline:setting-two" class="menu-icon"></iconify-icon>
          <span>Deposite Instructions</span>
        </a>
        <ul class="sidebar-submenu">
          <li>
            <a href="{{route('depositeinstruction.index')}}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Deposite Instructions</a>
          </li>
          <li>
            <a href="notification.html"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i>
              Notification</a>
          </li>
        </ul>
      </li>
    </ul>
  </div>
</aside>
