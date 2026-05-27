<nav class="navbar default-layout col-lg-12 col-12 fixed-top d-flex align-items-top flex-row p-0">
  <div class="navbar-brand-wrapper d-flex align-items-center justify-content-start text-center">
    <div class="me-3">
      <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
        <span class="icon-menu"></span>
      </button>
    </div>
    @php
      $brandLogo = $organizationBranding?->logo_url ?: 'https://www.sugslloyds.com/sugs-assets/logo.png';
      $brandLogoAlt = $organizationBranding?->company_name ? $organizationBranding->company_name . ' logo' : 'Company logo';
    @endphp
    <div>
      <a class="navbar-brand brand-logo" href="{{ url("/") }}">
        <img src="{{ $brandLogo }}" alt="{{ $brandLogoAlt }}" />
      </a>
      <a class="navbar-brand brand-logo-mini" href="{{ url("/") }}">
        <img src="{{ $brandLogo }}" alt="{{ $brandLogoAlt }}" />
      </a>
    </div>
  </div>
  <div class="navbar-menu-wrapper d-flex align-items-top">
    <ul class="navbar-nav">
      <li class="nav-item font-weight-semibold d-none d-lg-block ms-0">
        @php
          $hour = now()->hour;
          $timeGreeting = match (true) {
              $hour < 12 => 'Good Morning',
              $hour < 17 => 'Good Afternoon',
              default => 'Good Evening',
          };
        @endphp
        <h1 class="welcome-text">{{ $timeGreeting }}, <span
            class="fw-bold text-black">{{ Auth::user()->name ?? "Guest" }}</span></h1>
        @php
          $currentUser = Auth::user();
          $userRole = \App\Enums\UserRole::tryFrom((int) ($currentUser->role ?? -1));
          $dashboardSummaryText = match ($userRole) {
              \App\Enums\UserRole::ADMIN => 'Organization summary',
              \App\Enums\UserRole::PROJECT_MANAGER => 'Your project summary',
              \App\Enums\UserRole::SITE_ENGINEER, \App\Enums\UserRole::VENDOR => 'Your tasks today',
              default => 'Your dashboard summary',
          };
          $headerSubtitle = $pageHeaderSubtitle ?? $dashboardSummaryText;
        @endphp
        <h3 class="welcome-sub-text">{{ $headerSubtitle }}</h3>
      </li>
    </ul>
    <ul class="navbar-nav ms-auto">
      {{-- <li class="nav-item dropdown d-none d-lg-block">
        <a class="nav-link dropdown-bordered dropdown-toggle dropdown-toggle-split" id="messageDropdown" href="#"
          data-bs-toggle="dropdown" aria-expanded="false">
          <span id="selectedState">Select State</span>
        </a>
      </li>

      <li class="nav-item d-none d-lg-block">
        <div id="datepicker-popup" class="input-group date datepicker navbar-date-picker">
          <span class="input-group-addon input-group-prepend border-right">
            <span class="icon-calendar input-group-text calendar-icon"></span>
          </span>
          <input type="text" class="form-control">
        </div>
      </li> --}}

      <li class="nav-item dropdown">
        <a class="nav-link count-indicator" id="notificationDropdown" href="#" data-bs-toggle="dropdown"
          aria-expanded="false">
          <i class="icon-bell icon-lg"></i>
          <span class="count" id="notificationCountBadge" style="display: none;">0</span>
        </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0"
          aria-labelledby="notificationDropdown">
          <div class="dropdown-item border-bottom py-3 d-flex justify-content-between align-items-center">
            <p class="font-weight-medium mb-0" id="notificationSummaryText">You have 0 new notifications</p>
            <button type="button" class="badge badge-pill badge-primary border-0" id="markAllNotificationsReadButton">
              Mark all read
            </button>
          </div>
          <div id="notificationList">
            <div class="dropdown-item py-3 text-muted">No notifications yet.</div>
          </div>
        </div>
      </li>
      <li class="nav-item dropdown d-none d-lg-block user-dropdown">
        <a class="nav-link d-flex align-items-center gap-1" id="UserDropdown" href="#" data-bs-toggle="dropdown"
          aria-expanded="false" aria-label="Open profile menu">
          <img class="img-xs rounded-circle" src="{{ Auth::user()->image ?? asset("images/faces/face8.jpg") }}"
            alt="Profile image">
          <i id="userDropdownChevron" class="mdi mdi-chevron-down" aria-hidden="true"></i>
          <span class="visually-hidden">Profile menu</span>
        </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
          <div class="dropdown-header text-center">
            <img class="img-md rounded-circle" src="{{ asset("images/faces/face8.jpg") }}" alt="Profile image">
            <p class="font-weight-semibold mb-1 mt-3">{{ Auth::user()->name ?? "Guest" }}</p>
            <p class="fw-light text-muted mb-0">{{ Auth::user()->email ?? "" }}</p>
          </div>
          <a class="dropdown-item" href="{{ route("staff.profile", Auth::id()) }}">
            <i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> My Profile
            <span class="badge badge-pill badge-danger">1</span>
          </a>

          <span class="dropdown-item disabled" aria-disabled="true">
            <i class="dropdown-item-icon mdi mdi-message-text-outline text-primary me-2"></i> Messages
          </span>
          <span class="dropdown-item disabled" aria-disabled="true">
            <i class="dropdown-item-icon mdi mdi-calendar-check-outline text-primary me-2"></i> Activity
          </span>
          <a class="dropdown-item" href={{ route("staff.change-password", Auth::id()) }}>
            <i class="dropdown-item-icon mdi mdi-calendar-check-outline text-primary me-2"></i> Change Password
          </a>
          <form id="logout-form" action="{{ route("logout") }}" method="POST" style="display: none;">
            @csrf
          </form>
          <a href="#" class="dropdown-item"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Sign Out
          </a>

          <!-- <a  href="{{ route("logout") }}">
                      <i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Sign Out
                  </a> -->
        </div>
      </li>
    </ul>
    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
      data-bs-toggle="offcanvas">
      <span class="mdi mdi-menu"></span>
    </button>
  </div>
</nav>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const stateItems = document.querySelectorAll(".state-item");
    const selectedStateSpan = document.getElementById('selectedState');

    stateItems.forEach((item) => {
      item.addEventListener("click", function(event) {
        event.preventDefault(); // Prevent default anchor behavior

        const stateId = this.getAttribute("data-state-id");
        const stateName = this.getAttribute('data-state-name');
        selectedStateSpan.textContent = stateName; // Update the dropdown toggle text

        // Example: Send stateId to server via AJAX
        fetch('/update-selected-state', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              state_id: stateId
            })
          })
          .then(response => response.json())
          .then(data => {
            console.log('Server Response:', data);
          })
          .catch(error => console.error('Error:', error));
      });
    });

    const userDropdownTrigger = document.getElementById("UserDropdown");
    const userDropdownChevron = document.getElementById("userDropdownChevron");

    if (userDropdownTrigger && userDropdownChevron) {
      userDropdownTrigger.addEventListener("shown.bs.dropdown", function() {
        userDropdownChevron.classList.remove("mdi-chevron-down");
        userDropdownChevron.classList.add("mdi-chevron-up");
      });

      userDropdownTrigger.addEventListener("hidden.bs.dropdown", function() {
        userDropdownChevron.classList.remove("mdi-chevron-up");
        userDropdownChevron.classList.add("mdi-chevron-down");
      });
    }
  });
</script>
