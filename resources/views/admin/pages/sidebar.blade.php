<aside class="sidebar">
  <button type="button" class="sidebar-close-btn">
    <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
  </button>
  <div>

@php
    use App\Models\Settinglogo;
    use App\Models\Logosetting;
    $logoRecord = Settinglogo::first() ?? Logosetting::first();
    $logoPath = ($logoRecord && $logoRecord->photo && file_exists(public_path('uploads/logo/'.$logoRecord->photo)))
        ? asset('uploads/logo/'.$logoRecord->photo)
        : (file_exists(public_path('admin/assets/images/logo.png')) ? asset('admin/assets/images/logo.png') : asset('logo.png'));
    $logoIconPath = ($logoRecord && $logoRecord->photo && file_exists(public_path('uploads/logo/'.$logoRecord->photo)))
        ? asset('uploads/logo/'.$logoRecord->photo)
        : (file_exists(public_path('admin/assets/images/logo-icon.png')) ? asset('admin/assets/images/logo-icon.png') : asset('logo.png'));
@endphp

<a href="{{ route('admin.dashboard') }}" class="sidebar-logo">
    <img src="{{ $logoPath }}" alt="Platform Logo" class="light-logo" style="max-height: 42px; object-fit: contain;">
    <img src="{{ $logoPath }}" alt="Platform Logo" class="dark-logo" style="max-height: 42px; object-fit: contain;">
    <img src="{{ $logoIconPath }}" alt="Platform Logo" class="logo-icon" style="max-height: 38px; object-fit: contain;">
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
        <i class="bi bi-currency-dollar menu-icon"></i>
        <span>Deposit Edit</span>
    </a>
    <ul class="sidebar-submenu">
        <li>
            <a href="{{ route('admin.depositeblanceadd') }}">
                <i class="bi bi-currency-dollar circle-icon"></i>
                Deposit Edit
            </a>
        </li>
    </ul>
</li>
 <li class="dropdown">
        <a href="javascript:void(0)">
            <iconify-icon icon="mdi:bank-transfer-out" class="menu-icon"></iconify-icon>
            <span>privacy policies</span>
        </a>

        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('privacy.index') }}">
                    <iconify-icon
                        icon="mdi:cash-minus"
                        class="circle-icon text-danger-600">
                    </iconify-icon>
                    privacy policies
                </a>
            </li>
        </ul>
</li>

 <li class="dropdown">
        <a href="javascript:void(0)">
            <iconify-icon icon="mdi:bank-transfer-out" class="menu-icon"></iconify-icon>
            <span>Child Safety Standards</span>
        </a>

        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('Childsafety.index') }}">
                    <iconify-icon
                        icon="mdi:cash-minus"
                        class="circle-icon text-danger-600">
                    </iconify-icon>
                    Child Safety Standards
                </a>
            </li>
        </ul>
</li>










   <li class="dropdown">
        <a href="javascript:void(0)">
            <i class="bi bi-people-fill menu-icon"></i>
            <span>  User Social Posts</span>
        </a>
        <ul class="sidebar-submenu">
            <!-- Dollar Signed -->
            <li>
                <a href="{{ route('admin.usersocialposts') }}">
                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                    User Social Posts
                </a>
            </li>

        </ul>
    </li>






       <li class="dropdown">
        <a href="javascript:void(0)">
            <i class="bi bi-people-fill menu-icon"></i>
            <span>Manual Deposit</span>
        </a>
        <ul class="sidebar-submenu">
            <!-- Dollar Signed -->
            <li>
                <a href="{{ route('admin.balance.index') }}">
                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                    Manual Deposit
                </a>
            </li>

        </ul>
    </li>
    <li class="dropdown">
        <a href="javascript:void(0)">
            <i class="bi bi-people-fill menu-icon"></i>
            <span>Taka & Dollar Signed</span>
        </a>
        <ul class="sidebar-submenu">
            <!-- Dollar Signed -->
            <li>
                <a href="{{ route('dollarsiged.index') }}">
                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                    Dollar Signed List
                </a>
            </li>

        </ul>
    </li>

        <li class="dropdown">
            <a href="javascript:void(0)">
                <i class="bi bi-people-fill menu-icon"></i>
                <span>Agent System</span>
                <span class="agent-system-badge">MODULE</span>
            </a>
            <ul class="sidebar-submenu">
                <!-- Agents Management -->
                <li>
                    <a href="{{ route('agentcreate.create') }}">
                        <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                        Agents Create
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.agent.pending') }}">
                        <i class="ri-circle-fill circle-icon text-warning w-auto"></i>
                        Pending Agents
                    </a>
                </li>
                <li>
                    <a href="{{ route('agentapprovedlist') }}">
                        <i class="ri-circle-fill circle-icon text-success w-auto"></i>
                        Approved Agents
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.agent.rejectlist') }}">
                        <i class="ri-circle-fill circle-icon text-danger w-auto"></i>
                        Rejected Agents
                    </a>
                </li>

                <!-- Agent Posts -->
                <li>
                    <a href="{{ route('agent.agentposts') }}">
                        <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                        Agent Posts
                    </a>
                </li>

                <!-- Agent Deposit Edit -->
                <li>
                    <a href="{{ route('admin.agent.balance.index') }}">
                        <i class="bi bi-currency-dollar circle-icon"></i>
                        Agent Deposit Edit
                    </a>
                </li>

                <!-- Agent Deposit Pending & Lists -->
                <li>
                    <a href="{{ route('admin.agent.deposite.pending') }}">
                        <i class="fa-solid fa-list me-2"></i>
                        Agent Deposit Pending
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.agent.deposite.approved.list') }}">
                        <i class="ri-circle-fill circle-icon text-success w-auto"></i>
                        Agent Deposit Approved Lists
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.agent.deposite.reject.list') }}">
                        <i class="ri-circle-fill circle-icon text-danger w-auto"></i>
                        Agent Deposit Rejected Lists
                    </a>
                </li>

                <!-- Agent KYC List -->
                <li>
                    <a href="{{ route('agent.kyc.list') }}">
                        <i class="fa-solid fa-hourglass-half me-2 text-warning"></i>
                        Pending / All Agent KYC List
                    </a>
                </li>
                <li>
                    <a href="{{ route('agent.approved.kyc.list') }}">
                        <i class="fa-solid fa-circle-check me-2 text-success"></i>
                        Approved Agent KYC List
                    </a>
                </li>
                <li>
                    <a href="{{ route('agent.kyc.reject.list') }}">
                        <i class="fa-solid fa-circle-xmark me-2 text-danger"></i>
                        Rejected Agent KYC List
                    </a>
                </li>

                <!-- Agent Withdraw Commission -->
                <li>
                    <a href="{{ route('agentwidthrawcommission.index') }}">
                        <iconify-icon icon="mdi:cash-minus" class="circle-icon text-danger-600"></iconify-icon>
                        Agent Withdraw Commission
                    </a>
                </li>

                <!-- Agent Withdraw -->
                <li>
                    <a href="{{ route('widthraw.list') }}">
                        <iconify-icon icon="mdi:cash-minus" class="circle-icon text-danger-600"></iconify-icon>
                        Agent Withdraw Requests
                    </a>
                </li>
                <li>
                    <a href="{{ route('agent.widthraw.approved.list') }}">
                        <iconify-icon icon="mdi:check-circle-outline" class="circle-icon text-success-600"></iconify-icon>
                        Agent Withdraw Approved
                    </a>
                </li>
                <li>
                    <a href="{{ route('agent.widthraw.reject.list') }}">
                        <iconify-icon icon="mdi:close-circle-outline" class="circle-icon text-danger-600"></iconify-icon>
                        Agent Withdraw Rejected
                    </a>
                </li>

                <!-- Dollar Lock System -->
                <li>
                    <a href="{{ route('locksystem.index') }}">
                        <i class="bi bi-cash-coin me-2"></i>
                        Global Dollar Lock Setting
                    </a>
                </li>
                <li>
                    <a href="{{ route('locksystem.manage-agents') }}">
                        <i class="bi bi-person-lock me-2"></i>
                        Manage Individual Agent Locks
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
            <!-- Payment icon -->
            <i class="ri-wallet-line text-xl me-14 d-flex w-auto"></i>
            <span>logosetting</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('logosetting.index') }}">
                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                    logosetting
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
                   Package Buy Notice
                </a>
            </li>
            <li>
                <a href="{{ route('worknotice.index') }}">
                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                    WorkNotice Setup
                </a>
            </li>
            <li>
                <a href="{{ route('agentnotices.index') }}">
                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                    Agent Notices
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
      <li class="dropdown">
        <a href="javascript:void(0)">
          <iconify-icon icon="icon-park-outline:setting-two" class="menu-icon"></iconify-icon>
          <span>App Setting</span>
        </a>
        <ul class="sidebar-submenu">
          <li>
            <a href="{{route('appsetting.index')}}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> App Setting (StarApp & AdMob)</a>
          </li>
          <li>
            <a href="{{route('admin.homecards.index')}}"><i class="ri-circle-fill circle-icon text-success-600 w-auto"></i> Home Cards & Colors</a>
          </li>
        </ul>
      </li>
      <li class="dropdown">
        <a href="javascript:void(0)">
          <iconify-icon icon="ri-mail-settings-line" class="menu-icon"></iconify-icon>
          <span>Mail Configuration</span>
        </a>
        <ul class="sidebar-submenu">
          <li>
            <a href="{{route('mailsetting.index')}}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Mail Configuration</a>
          </li>
        </ul>
      </li>
      <li class="dropdown">
        <a href="javascript:void(0)">
          <iconify-icon icon="ri-google-line" class="menu-icon"></iconify-icon>
          <span>Google Ads Approval</span>
        </a>
        <ul class="sidebar-submenu">
          <li>
            <a href="{{route('googleadsapproval.index')}}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Google Ads Approval</a>
          </li>
        </ul>
      </li>
    <li class="dropdown">
        <a href="javascript:void(0)">
            <iconify-icon icon="mdi:theme-light-dark" class="menu-icon"></iconify-icon>
            <span>Themechange</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
            <a href="{{route('themechange.index')}}">
                <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Themechange
            </a>
            </li>
        </ul>
    </li>
   <li class="dropdown">
        <a href="javascript:void(0)">
            <iconify-icon icon="mdi:theme-light-dark" class="menu-icon"></iconify-icon>
            <span>Deposit Instructions</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('depositeinstructions.index') }}">
                    <!-- Add deposit icon here -->
                    <iconify-icon icon="mdi:bank-transfer" class="circle-icon text-primary-600"></iconify-icon>
                    Deposit Instructions
                </a>
            </li>
        </ul>
    </li>

    <li class="dropdown">
        <a href="javascript:void(0)">
            <iconify-icon icon="mdi:theme-light-dark" class="menu-icon"></iconify-icon>
            <span>Widthraw Instructions</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('widthrawInstruction.index') }}">
                    <!-- Add deposit icon here -->
                    <iconify-icon icon="mdi:bank-transfer" class="circle-icon text-primary-600"></iconify-icon>
                    Widthraw Instructions
                </a>
            </li>
        </ul>
    </li>



<!-- Notification System Dropdown Menu -->
<li class="sidebar-dropdown">
    <a href="#" class="dropdown-toggle">
        <iconify-icon icon="mdi:bell-badge" class="menu-icon"></iconify-icon>
        <span>Notification System</span>
    </a>

    <ul class="sidebar-submenu">
        <li>
            <a href="{{ route('firebase.index') }}"
               class="{{ request()->routeIs('notification.firebase.*') ? 'active-page' : '' }}">
                Firebase Apps
            </a>
        </li>

        <li>
            <a href="{{ route('send') }}"
               class="{{ request()->routeIs('notification.send.*') ? 'active-page' : '' }}">
                Send Notification
            </a>
        </li>

        <li>
            <a href="{{ route('history') }}"
               class="{{ request()->routeIs('notification.history') ? 'active-page' : '' }}">
                Notification History
            </a>
        </li>

        <li>
            <a href="{{ route('users.index') }}"
               class="{{ request()->routeIs('notification.users.*') ? 'active-page' : '' }}">
                App Users
            </a>
        </li>
    </ul>
</li>

    <li class="dropdown">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-server fa-xl me-2 text-warning"></i>
            <span>Server Mode Setting</span>
        </a>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('admin.server.setting') }}">
                    <i class="fa-solid fa-network-wired fa-lg me-2 text-primary"></i>
                    Local / Live Server Switch
                </a>
            </li>
        </ul>
    </li>


<script>
$(document).ready(function () {

    $('.dropdown-toggle').on('click', function (e) {
        e.preventDefault();

        let parent = $(this).closest('.sidebar-dropdown');
        let submenu = parent.find('.sidebar-submenu');

        // Close others
        $('.sidebar-dropdown').not(parent).removeClass('active')
            .find('.sidebar-submenu').slideUp(300);

        // Toggle current
        parent.toggleClass('active');
        submenu.slideToggle(300);
    });

    // Auto open if active page exists
    $('.sidebar-submenu .active-page').each(function () {
        $(this).closest('.sidebar-submenu').show();
        $(this).closest('.sidebar-dropdown').addClass('active');
    });

});
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>






    </ul>
  </div>
</aside>
