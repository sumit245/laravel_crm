<nav class="navbar default-layout col-lg-12 col-12 fixed-top d-flex align-items-top flex-row p-0">
  <div class="navbar-brand-wrapper d-flex align-items-center justify-content-start text-center">
    <div class="me-3">
      <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
        <span class="icon-menu"></span>
      </button>
    </div>
    <div>
      <a class="navbar-brand brand-logo" href="{{ url("/") }}">
        <img src="https://sugs-assets.s3.ap-south-1.amazonaws.com/logo.png" alt="logo" />
      </a>
      <a class="navbar-brand brand-logo-mini" href="{{ url("/") }}">
        <img src="https://sugs-assets.s3.ap-south-1.amazonaws.com/logo.png" alt="logo" />
      </a>
    </div>
  </div>
  <div class="navbar-menu-wrapper d-flex align-items-top">
    <ul class="navbar-nav">
      <li class="nav-item font-weight-semibold d-none d-lg-block ms-0">
        <h1 class="welcome-text">Good Morning, <span
            class="fw-bold text-black">{{ Auth::user()->name ?? "Guest" }}</span></h1>
        <h3 class="welcome-sub-text">Your performance summary this week</h3>
      </li>
    </ul>
    <ul class="navbar-nav ms-auto">
      <li class="nav-item dropdown d-none d-lg-block">
        <a class="nav-link dropdown-bordered dropdown-toggle dropdown-toggle-split" id="messageDropdown" href="#"
          data-bs-toggle="dropdown" aria-expanded="false">
          <span id="selectedState">Select State</span>
        </a>
        {{-- <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0"
          aria-labelledby="messageDropdown" style="max-height: 400px; overflow-y: auto;">
          <a class="dropdown-item py-3">
            <p class="font-weight-medium float-left mb-0">Select State</p>
          </a>
          <div class="dropdown-divider"></div>
          @foreach ($states as $index => $category)
            <a class="dropdown-item preview-item state-item" data-state-name="{{ $category->name }}"
              data-state-id="{{ $category->id }}">
              <div class="preview-item-content flex-grow py-2">
                <p class="preview-subject ellipsis font-weight-medium text-dark">{{ $category->name }}</p>
              </div>
            </a>
          @endforeach
        </div> --}}
      </li>

      <li class="nav-item d-none d-lg-block">
        <div id="datepicker-popup" class="input-group date datepicker navbar-date-picker">
          <span class="input-group-addon input-group-prepend border-right">
            <span class="icon-calendar input-group-text calendar-icon"></span>
          </span>
          <input type="text" class="form-control">
        </div>
      </li>

      <li class="nav-item dropdown">
        <a class="nav-link count-indicator" id="notificationDropdown" href="#" data-bs-toggle="dropdown"
          aria-expanded="false">
          <i class="icon-bell icon-lg"></i>
          <span class="count"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0"
          aria-labelledby="notificationDropdown">
          <a class="dropdown-item border-bottom py-3">
            <p class="font-weight-medium float-left mb-0">You have 0 new notifications</p>
            <span class="badge badge-pill badge-primary float-right">View all</span>
          </a>
        </div>
      </li>
      <li class="nav-item dropdown d-none d-lg-block user-dropdown">
        <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
          <img class="img-xs rounded-circle" src="{{ asset("images/faces/face8.jpg") }}" alt="Profile image">
        </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
          <div class="dropdown-header text-center">
            <img class="img-md rounded-circle" src="{{ asset("images/faces/face8.jpg") }}" alt="Profile image">
            <p class="font-weight-semibold mb-1 mt-3">{{ Auth::user()->name ?? "Guest" }}</p>
            <p class="fw-light text-muted mb-0">{{ Auth::user()->email ?? "" }}</p>
          </div>
          <a class="dropdown-item" href="#">
            <i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> My Profile
            <span class="badge badge-pill badge-danger">1</span>
          </a>
          <a class="dropdown-item" href="#">
            <i class="dropdown-item-icon mdi mdi-message-text-outline text-primary me-2"></i> Messages
          </a>
          <a class="dropdown-item" href="#">
            <i class="dropdown-item-icon mdi mdi-calendar-check-outline text-primary me-2"></i> Activity
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
  });
</script>
