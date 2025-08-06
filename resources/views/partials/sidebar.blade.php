@php
  use App\Models\Project;

  $selectedProject = null;
  $selectedProjectId = 0;
  if (session()->has("project_id")) {
      $selectedProject = Project::find(session("project_id"));
      $selectedProjectId = $selectedProject->id;
  }
  if (!$selectedProject) {
      $selectedProject = Project::first();
      $selectedProjectId = $selectedProject->id;
  }
  $projectType = $selectedProject ? $selectedProject->project_type : null;
@endphp

<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav" style="max-height: 80%;">
    <li class="nav-item nav-category">Project</li>

    <li class="nav-item">
      <a class="nav-link" href="{{ url("/dashboard") }}">
        <i class="mdi mdi-grid-large menu-icon"></i>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>
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
<<<<<<< HEAD
      <li class="nav-item">
        <a class="nav-link" href="{{ route("jicr.index", ["project_id" => $selectedProjectId]) }}">
          <i class="menu-icon mdi mdi-chart-pie"></i>
          <span class="menu-title">Generate JICR</span>
        </a>
      </li>
      @if (auth()->user()->role == 0)
        <li class="nav-item">
          <a class="nav-link" href="{{ route("device.index", ["project_id" => $selectedProjectId]) }}">
            <i class="menu-icon mdi mdi-file-excel"></i>
            <span class="menu-title">Import Devices</span>
          </a>
        </li>
      @endif


=======
    {{-- @if ($projectType == 1) --}}
>>>>>>> f825c9f0b561ec0ffb351e6bd76c219dd5835433
    <li class="nav-item">
      <a class="nav-link" href="{{ route("jicr.index", ["project_id" => $selectedProjectId]) }}">
        <i class="menu-icon mdi mdi-chart-pie"></i>
        <span class="menu-title">Generate JICR</span>
      </a>
    </li>
    {{-- @if (auth()->user()->role == 0) --}}
    <li class="nav-item">
      <a class="nav-link" href="{{ route("device.index", ["project_id" => $selectedProjectId]) }}">
        <i class="menu-icon mdi mdi-file-excel"></i>
        <span class="menu-title">Import Devices</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route("rms.index", ["project_id" => $selectedProjectId]) }}">
        <i class="menu-icon mdi mdi-file-excel"></i>
        <span class="menu-title">Push to RMS</span>
      </a>
    </li>
    {{-- @endif --}}

    {{-- @endif --}}

    {{-- <li class="nav-item">
      <a class="nav-link disabled" href="{{ route("sites.index") }}">
        <i class="menu-icon mdi mdi-map-marker-outline"></i>
        <span class="menu-title">Sites Management</span>
      </a>
    </li> --}}
    {{-- <li class="nav-item">
      <a class="nav-link disabled" href="{{ route("tasks.index") }}">
        <i class="menu-icon mdi mdi-checkbox-marked"></i>
        <span class="menu-title">Target Management</span>
      </a>
    </li> --}}

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

    <!-- Billing management -->
    <li class="nav-item nav-category">Billing Management</li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route("billing.tada") }}">
        <i class="menu-icon mdi mdi-store"></i>
        <span class="menu-title">TA & DA</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route("billing.convenience") }}">
        <i class="menu-icon mdi mdi-store"></i>
        <span class="menu-title">Conveyance</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link disabled" href="#">
        <i class="menu-icon mdi mdi-store"></i>
        <span class="menu-title">Users</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route("billing.settings") }}">
        <i class="menu-icon mdi mdi-store"></i>
        <span class="menu-title">Settings</span>
      </a>
    </li>
    <!-- Billing management ends -->

    <li class="nav-item nav-category">Inventory</li>
    <li class="nav-item">
      <a class="nav-link disabled" href="{{ route("inventory.index") }}">
        <i class="menu-icon mdi mdi-store"></i>
        <span class="menu-title">Inventory Management</span>
      </a>
    </li>

    <hr />
    <li class="nav-item nav-category">My Meetings</li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route("meets.index") }}">
        <i class="menu-icon mdi mdi-cogs"></i>
        <span class="menu-title">Create Meeting Links</span>
      </a>
    </li>

    <hr />
    <li class="nav-item">
      <a class="nav-link" href="">
        <i class="menu-icon mdi mdi-cogs"></i>
        <span class="menu-title">Setting</span>
      </a>
    </li>
    @if (auth()->user()->role == 0)
      <li class="nav-item">
        <a class="nav-link" href="{{ route("backup.index") }}">
          <i class="menu-icon mdi mdi-backup-restore"></i>
          <span class="menu-title">Backup</span>
        </a>
      </li>
    @endif
    <!-- <li class="nav-item nav-category">Help</li> -->
  </ul>
</nav>

<style>
  .sidebar .nav {
    overflow: hidden;
  }

  .sidebar:hover .nav {
    overflow: auto;
  }
</style>
