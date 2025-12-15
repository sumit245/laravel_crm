@php
    use App\Models\Project;

    $selectedProject = null;
    $selectedProjectId = 0;
    if (session()->has('project_id')) {
        $selectedProject = Project::find(session('project_id'));
        if ($selectedProject) {
            $selectedProjectId = $selectedProject->id;
        }
    }
    if (!$selectedProject) {
        $selectedProject = Project::first();
        if ($selectedProject) {
            $selectedProjectId = $selectedProject->id;
        }
    }
    $projectType = $selectedProject ? $selectedProject->project_type : null;

    // Check if user has restricted access (only review-meetings)
    // Site Engineer (1), Store Incharge (4), and Review Meeting Only (11) are restricted
    $userRole = (int) auth()->user()->role;
    $restrictedRoles = [1, 4, 11];
    $isRestrictedUser = in_array($userRole, $restrictedRoles, true);
@endphp

<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav" style="max-height: 80%;">
        @if (!$isRestrictedUser)
            <li class="nav-item nav-category">Project</li>

            <li class="nav-item">
                <a class="nav-link" href="{{ url('/dashboard') }}">
                    <i class="mdi mdi-grid-large menu-icon"></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('projects.index') }}">
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
            {{-- @if ($projectType == 1) --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('jicr.index', ['project_id' => $selectedProjectId]) }}">
                    <i class="menu-icon mdi mdi-chart-pie"></i>
                    <span class="menu-title">Generate JICR</span>
                </a>
            </li>
            {{-- @if (auth()->user()->role == 0) --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('device.index', ['project_id' => $selectedProjectId]) }}">
                    <i class="menu-icon mdi mdi-file-excel"></i>
                    <span class="menu-title">Import Devices</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('rms.index', ['project_id' => $selectedProjectId]) }}">
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
                    <a class="nav-link" href="{{ route('staff.index') }}">
                        <i class="menu-icon mdi mdi-account-multiple-outline"></i>
                        <span class="menu-title">Staffs Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('candidates.index') }}">
                        <i class="menu-icon mdi mdi-account-plus-outline"></i>
                        <span class="menu-title">New Hirings</span>
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a class="nav-link" href="{{ route('uservendors.index') }}">
                    <i class="menu-icon mdi mdi-account-multiple-outline"></i>
                    <span class="menu-title">Vendors Management</span>
                </a>
            </li>

            <!-- Billing management -->
            <li class="nav-item nav-category">Billing Management</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('billing.tada') }}">
                    <i class="menu-icon mdi mdi-store"></i>
                    <span class="menu-title">TA & DA</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('billing.convenience') }}">
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
                <a class="nav-link" href="{{ route('billing.settings') }}">
                    <i class="menu-icon mdi mdi-store"></i>
                    <span class="menu-title">Settings</span>
                </a>
            </li>
            <!-- Billing management ends -->

            <li class="nav-item nav-category">Inventory</li>
            <li class="nav-item">
                <a class="nav-link disabled" href="{{ route('inventory.index') }}">
                    <i class="menu-icon mdi mdi-store"></i>
                    <span class="menu-title">Inventory Management</span>
                </a>
            </li>
            <hr />
        @endif

        <li class="nav-item nav-category">My Meetings</li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('meets.dashboard') }}">
                <i class="menu-icon bi bi-view-stacked"></i>
                <span class="menu-title">Meeting Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('meets.index') }}">
                <i class="menu-icon mdi mdi-cogs"></i>
                <span class="menu-title">Meetings</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('meets.create') }}">
                <i class="menu-icon bi bi-plus-circle"></i>
                <span class="menu-title">Create Meeting</span>
            </a>
        </li>

        @if (!$isRestrictedUser)
            <hr />
            <li class="nav-item">
                <a class="nav-link" href="">
                    <i class="menu-icon mdi mdi-cogs"></i>
                    <span class="menu-title">Setting</span>
                </a>
            </li>
            @if (auth()->user()->role == 0)
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('backup.index') }}">
                        <i class="menu-icon mdi mdi-backup-restore"></i>
                        <span class="menu-title">Backup</span>
                    </a>
                </li>
            @endif
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
