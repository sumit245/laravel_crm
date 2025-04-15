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
      <strong>District:</strong><p>{{ $streetlightTask->site->district }}</p> 
    </div>
    <div class="col-md-4 mb-3">
      <strong>Block:</strong><p> {{ $streetlightTask->site->block }}</p>
    </div>
    <div class="col-md-4 mb-3">
      <strong>Panchayat:</strong> <p>{{ $streetlightTask->site->panchayat }}</p>
    </div>
    <div class="col-md-4 mb-3">
      <strong>Alloted:</strong><p> {{ $streetlightTask->site->ward }}</p>
    </div>
    <div class="col-md-4 mb-3">
      <strong>Mukhiya Contact:</strong> <p>{{ $streetlightTask->site->mukhiya_contact }}</p>
    </div>
    <!-- <div class="col-md-4 mb-3">
      <strong>Surveyed Poles:</strong><p> {{ $streetlightTask->site->number_of_surveyed_poles }}</p>
    </div>
    <div class="col-md-4 mb-3">
      <strong>Installed Poles:</strong><p> {{ $streetlightTask->site->number_of_installed_poles }}</p>
    </div> -->
    <div class="col-md-4 mb-3">
      <strong>Total Poles:</strong><p> {{ $streetlightTask->site->total_poles }}</p>
    </div>
  </div>
</div>


    
    <div class="container">
        <div class="row mb-4">
            <!-- <div class="col-md-6">
                <h2 class="fw-bold">Pending Requests</h2>
                <p class="text-muted">You have 50+ new requests</p>
            </div> -->
            <!-- <div class="col-md-6 text-end">
                <button class="btn btn-primary add-btn px-4 py-2">
                    <i class="fas fa-user-plus me-2"></i> Add new member
                </button>
            </div> -->
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" width="5%">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                        <th scope="col" width="20%">Name</th>
                        <th scope="col" width="20%">Designation</th>
                        <!-- <th scope="col" width="25%">PROGRESS</th> -->
                        <th scope="col" width="20%">Surveyed Poles</th>
                        <th scope="col" width="20%">Installed Poles</th>
                        <th scope="col" width="20%">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Row 1 -->
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox">
                            </div>
                        </td>
                        <td>
                            <div class="customer-info">
                            <img src="{{ $manager->image }}" alt="{{ $engineer->name }}" class="profile-img">
                                <div>
                                    <p class="name">{{ $manager->firstName }} {{ $manager->lastName }}</p>
                                    
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="mb-0 fw-medium">Manager</p>
                        </td>
                        <td>
                          <p>{{ $streetlightTask->site->number_of_surveyed_poles }}</p>
                        </td>
                        <td>
                          <p><p> {{ $streetlightTask->site->number_of_installed_poles }}</p></p>
                        </td>
                        <!-- <td>
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>79%</span>
                                    <span>85/162</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar progress-green" role="progressbar" style="width: 79%" aria-valuenow="79" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </td> -->
                        <td>
                            <span class="badge badge-in-progress px-3 py-2 rounded-pill">In progress</span>
                        </td>
                    </tr>

                    <!-- Row 2 -->
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox">
                            </div>
                        </td>
                        <td>
                            <div class="customer-info">
                            <img src="{{ $vendor->image }}" alt="{{ $engineer->name }}" class="profile-img">
                                <div>
                                    <p class="name">{{$vendor->name}}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="mb-0 fw-medium">Vendor</p>
                        </td>
                        <td>
                          <p>{{ $streetlightTask->site->number_of_surveyed_poles }}</p>
                        </td>
                        <td>
                          <p><p> {{ $streetlightTask->site->number_of_installed_poles }}</p></p>
                        </td>
                        <!-- <td>
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>65%</span>
                                    <span>85/162</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar progress-green" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </td> -->
                        <td>
                            <span class="badge badge-in-progress px-3 py-2 rounded-pill">In progress</span>
                        </td>
                    </tr>

                    <!-- Row 3 -->
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox">
                            </div>
                        </td>
                        <td>
                            <div class="customer-info">
                            <img src="{{ $engineer->image }}" alt="{{ $engineer->name }}" class="profile-img">

                                <div>
                                    <p class="name">{{$engineer->firstName}} {{ $engineer->lastName }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="mb-0 fw-medium">Engineer</p>
                        </td>
                        <td>
                          <p>{{ $streetlightTask->site->number_of_surveyed_poles }}</p>
                        </td>
                        <td>
                          <p><p> {{ $streetlightTask->site->number_of_installed_poles }}</p></p>
                        </td>
                        <!-- <td>
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>65%</span>
                                    <span>85/162</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar progress-yellow" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </td> -->
                        <td>
                            <span class="badge badge-in-progress px-3 py-2 rounded-pill">In progress</span>
                        </td>
                    </tr>
                </tbody>
            </table>
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