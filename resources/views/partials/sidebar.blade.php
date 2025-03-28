@php
  $selectedProjectId = session("project_id");
@endphp
<style>
  .sidebar .nav {
    overflow: hidden; 
  }

  .sidebar:hover .nav {
    overflow: auto; 
  }

  
</style>
<nav class="sidebar sidebar-offcanvas" id="sidebar" >
  <ul class="nav" style="max-height: 80%;">
    <li class="nav-item nav-category">Project</li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route("projects.index") }}">
        <i class="menu-icon mdi mdi-chart-pie"></i>
        <span class="menu-title">Projects Overview</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="https://ssl.slldm.com/Views/index.php" target="_parent">
        <i class="menu-icon mdi mdi-chart-pie"></i>
        <span class="menu-title">RMS Portal</span>
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
      <li class="nav-item">
        <a class="nav-link" href="{{ route("hiring.index") }}">
          <i class="menu-icon mdi mdi-account-plus-outline"></i>
          <span class="menu-title">New Hirings</span>
        </a>
      </li>
    @endif
    <li class="nav-item">
      <a class="nav-link" href="{{ route("uservendors.index") }}">
        <i class="menu-icon mdi mdi-account-multiple-outline"></i>
        <span class="menu-title">Vendors Management</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ url("/dashboard?project_id=" . $selectedProjectId) }}">
        <i class="mdi mdi-grid-large menu-icon"></i>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>
    <hr />
    <li class="nav-item">
      <a class="nav-link disabled" href="{{ route("sites.index") }}">
        <i class="menu-icon mdi mdi-map-marker-outline"></i>
        <span class="menu-title">Sites Management</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link disabled" href="{{ route("tasks.index") }}">
        <i class="menu-icon mdi mdi-checkbox-marked"></i>
        <span class="menu-title">Target Management</span>
      </a>
    </li>
    <li class="nav-item nav-category">Inventory</li>
    <li class="nav-item">
      <a class="nav-link disabled" href="{{ route("inventory.index") }}">
        <i class="menu-icon mdi mdi-store"></i>
        <span class="menu-title">Inventory Management</span>
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
