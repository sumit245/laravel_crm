<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item">
      <a class="nav-link" href="{{ url("/") }}">
        <i class="mdi mdi-grid-large menu-icon"></i>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>

    <li class="nav-item nav-category">Project</li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route("projects.index") }}">
        <i class="menu-icon mdi mdi-chart-pie"></i>
        <span class="menu-title">Projects Overview</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route("sites.index") }}">
        <i class="menu-icon mdi mdi-map-marker-outline"></i>
        <span class="menu-title">Sites Management</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route("tasks.index") }}">
        <i class="menu-icon mdi mdi-checkbox-marked"></i>
        <span class="menu-title">Tasks Management</span>
      </a>
    </li>
    <li class="nav-item nav-category">Inventory</li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route("inventory.index") }}">
        <i class="menu-icon mdi mdi-store"></i>
        <span class="menu-title">Inventory Management</span>
      </a>
    </li>

    <li class="nav-item nav-category">Users</li>
    @if (auth()->user()->role == 0)
      <li class="nav-item">
        <a class="nav-link" href="{{ route("staff.index") }}">
          <i class="menu-icon mdi mdi-account-multiple-outline"></i>
          <span class="menu-title">Staffs Management</span>
        </a>
      </li>
    @endif
    <li class="nav-item">
      <a class="nav-link" href="{{ route("uservendors.index") }}">
        <i class="menu-icon mdi mdi-account-multiple-outline"></i>
        <span class="menu-title">Vendors Management</span>
      </a>
    </li>
    <hr />
    <li class="nav-item">
      <a class="nav-link" href="">
        <i class="menu-icon mdi mdi-cogs"></i>
        <span class="menu-title">Setting</span>
      </a>
    </li>
    <!-- <li class="nav-item nav-category">Help</li> -->
  </ul>
</nav>