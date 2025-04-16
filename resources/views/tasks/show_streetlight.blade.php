@extends("layouts.main")

@section("content")
  {{--  
  <pre>{{ $streetlightTask }}</pre>
  <pre>{{ $manager }}</pre>
  <pre>{{ $engineer }}</pre>
  <pre>{{ $vendor }}</pre>
  <pre>{{ $streetlight }}</pre>
  <pre>{{ $surveyedPoles }}</pre>
  <pre>{{ $installedPoles }}</pre>
--}}

  <div class="container mt-4">
  <div class="row">
    <div class="col-md-4 mb-3">
      <strong>District</strong><p>{{ $streetlightTask->site->district }}</p> 
    </div>
    <div class="col-md-4 mb-3">
      <strong>Block</strong><p> {{ $streetlightTask->site->block }}</p>
    </div>
    <div class="col-md-4 mb-3">
      <strong>Panchayat</strong> <p>{{ $streetlightTask->site->panchayat }}</p>
    </div>
    <div class="col-md-4 mb-3">
      <strong>Alloted Wards</strong><p> {{ $streetlightTask->site->ward }}</p>
    </div>
    <div class="col-md-4 mb-3">
      <strong>Mukhiya Contact</strong> <p>{{ $streetlightTask->site->mukhiya_contact }}</p>
    </div>
     <div class="col-md-4 mb-3">
      <strong>Surveyed Poles</strong><p> {{ $streetlightTask->site->number_of_surveyed_poles }}</p>
    </div>
    <div class="col-md-4 mb-3">
      <strong>Installed Poles</strong><p> {{ $streetlightTask->site->number_of_installed_poles }}</p>
    </div>
    <div class="col-md-4 mb-3">
      <strong>Total Poles</strong><p> {{ $streetlightTask->site->total_poles }}</p>
    </div>
  </div>
</div>
 <hr class="my-1" />

    
    <div class="container">
        <!-- Cards -->
        <div class="row">

<!-- Manager Card -->
<div class="col-md-4 mb-4">
  <div class="card d-flex flex-row align-items-center p-3 shadow-sm">
    <img src="{{ $streetlightTask->manager->image }}" alt="{{ $streetlightTask->manager->firstName }}" class="rounded-circle" width="60" height="60">
    <div class="ms-3">
      <h5 class="mb-0">{{ $streetlightTask->manager->firstName }} {{ $streetlightTask->manager->lastName }}</h5>
      <small class="text-muted">Manager</small>
    </div>
  </div>
</div>

<!-- Vendor Card -->
<div class="col-md-4 mb-4">
  <div class="card d-flex flex-row align-items-center p-3 shadow-sm">
    <img src="{{ $vendor->image }}" alt="{{ $vendor->name }}" class="rounded-circle" width="60" height="60">
    <div class="ms-3">
      <h5 class="mb-0">{{ $vendor->name }}</h5>
      <small class="text-muted">Vendor</small>
    </div>
  </div>
</div>

<!-- Engineer Card -->
<div class="col-md-4 mb-4">
  <div class="card d-flex flex-row align-items-center p-3 shadow-sm">
    <img src="{{ $streetlightTask->engineer->image }}" alt="{{ $streetlightTask->engineer->firstName }}" class="rounded-circle" width="60" height="60">
    <div class="ms-3">
      <h5 class="mb-0">{{ $streetlightTask->engineer->firstName }} {{ $streetlightTask->engineer->lastName }}</h5>
      <small class="text-muted">Engineer</small>
    </div>
  </div>
</div>

</div>

<div class="tab-content mt-1" id="poleTabsContent">
  <!-- Nav Tabs -->
  <ul class="nav nav-tabs fixed-navbar-project" id="poleTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="installed-tab" data-bs-toggle="tab" data-bs-target="#installed" type="button"
        role="tab" aria-controls="installed" aria-selected="true">
        Installed Poles
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="surveyed-tab" data-bs-toggle="tab" data-bs-target="#surveyed" type="button"
        role="tab" aria-controls="surveyed" aria-selected="false">
        Surveyed Poles
      </button>
    </li>
  </ul>

  <!-- Tab Content -->
  <div class="tab-pane fade show active" id="installed" role="tabpanel" aria-labelledby="installed-tab">
    @if(request()->get('project_type') == 1)
      @includeIf('staff.installedPoles')
    @endif
  </div>
  <div class="tab-pane fade" id="surveyed" role="tabpanel" aria-labelledby="surveyed-tab">
    @if(request()->get('project_type') == 1)
      @includeIf('staff.surveyedPoles')
    @endif
  </div>
</div>

    </div>


    
@endsection

@push("styles")
<style>
        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            /* padding: 30px; */
            margin-top: 15px;
        }
        .progress {
            height: 8px;
        }
        .table th {
            color: #6c757d;
            font-weight: 500;
            border-top: none;
        }
        .table td {
            vertical-align: middle;
            padding: 20px;
        }
        .profile-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .customer-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .name {
            font-weight: 600;
            margin-bottom: 0;
        }
        .role {
            color: #6c757d;
            margin-bottom: 0;
        }
        .badge-in-progress {
            background-color: #ffefc3;
            color: #b88100;
        }
        .badge-pending {
            background-color: #ffebeb;
            color: #e53935;
        }
        .badge-completed {
            background-color: #d1f7ea;
            color: #00a67e;
        }
        .progress-green {
            background-color: #4caf50;
        }
        .progress-yellow {
            background-color: #ffc107;
        }
        .progress-red {
            background-color: #f44336;
        }
        .add-btn {
            background-color: #304ffe;
            border-color: #304ffe;
        }
        .add-btn:hover {
            background-color: #1e40ff;
            border-color: #1e40ff;
        }
        
        /* Vendor Modal Styles */
        .vendor-name {
            color: #304ffe;
            text-decoration: underline;
            transition: color 0.2s;
        }
        .vendor-name:hover {
            color: #1e40ff;
        }
        .vendor-modal-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f0f0f0;
        }
        .vendor-details {
            margin-top: 20px;
        }
        .detail-item {
            display: flex;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-item:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            width: 100px;
            color: #6c757d;
        }
        .detail-value {
            flex: 1;
        }
    </style>
@endpush

@push("scripts")
<script>
    // You can add any additional JavaScript functionality here if needed
    document.addEventListener('DOMContentLoaded', function() {
        // Example: Add click event listener to vendor name
        const vendorName = document.querySelector('.vendor-name');
        vendorName.addEventListener('click', function() {
            // The modal is already triggered by Bootstrap data attributes
            // This is just an example if you need additional functionality
            console.log('Vendor modal opened');
        });
    });
</script>
@endpush