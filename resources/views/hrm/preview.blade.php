<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Your Application - Sugs Lloyd Ltd</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset("css/vertical-layout-light/style.css") }}">
    <link rel="stylesheet" href="{{ asset("vendors/select2/select2.min.css") }}">
    <link rel="shortcut icon" href="{{ asset("images/favicon.png") }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
      body {
        font-family: 'Roboto', sans-serif;
        background-color: #f0f0f0;
        color: #202124;
      }

      .container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 20px;
      }

      .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
        overflow: hidden;
      }

      .card-header {
        background-color: #673ab7;
        color: white;
        padding: 16px 24px;
        border-bottom: none;
        font-weight: 500;
      }

      .card-body {
        padding: 24px;
        background-color: white;
      }

      .card-footer {
        background-color: white;
        border-top: 1px solid #e0e0e0;
        padding: 16px 24px;
      }

      .btn-primary {
        background-color: #673ab7;
        border-color: #673ab7;
      }

      .btn-primary:hover {
        background-color: #5e35b1;
        border-color: #5e35b1;
      }

      .btn-success {
        background-color: #1a73e8;
        border-color: #1a73e8;
      }

      .btn-success:hover {
        background-color: #1765cc;
        border-color: #1765cc;
      }

      .edit-btn {
        display: flex;
        justify-content: start;
        margin-top: 15px;
      }

      .edit-btn a {
        text-decoration: none;
        color: #673ab7;
        padding: 5px 15px;
        border-radius: 5px;
        border: 1px solid #673ab7;
        transition: all 0.3s ease;
      }

      .edit-btn a:hover {
        background-color: #673ab7;
        color: white;
      }

      .logo-container {
        text-align: center;
        margin-bottom: 30px;
      }

      .logo-container img {
        max-height: 80px;
      }

      .section-title {
        font-weight: 600;
        color: #202124;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e0e0e0;
      }

      .data-row {
        margin-bottom: 8px;
      }

      .data-label {
        font-weight: 500;
        color: #5f6368;
      }

      .data-value {
        color: #202124;
      }

      .submit-container {
        position: sticky;
        bottom: 0;
        background-color: white;
        padding: 15px;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        border-top: 1px solid #e0e0e0;
        margin-top: 30px;
        text-align: right;
      }

      body {
        font-family: 'Roboto', sans-serif;
        background-color: #f0f0f0;
        color: #202124;
      }

      .container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 20px;
      }

      .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
        overflow: hidden;
      }

      .card-header {
        background-color: #673ab7;
        color: white;
        padding: 16px 24px;
        border-bottom: none;
        font-weight: 500;
      }

      .card-body {
        padding: 24px;
        background-color: white;
      }

      .card-footer {
        background-color: white;
        border-top: 1px solid #e0e0e0;
        padding: 16px 24px;
      }

      .btn-primary {
        background-color: #673ab7;
        border-color: #673ab7;
      }

      .btn-primary:hover {
        background-color: #5e35b1;
        border-color: #5e35b1;
      }

      .btn-success {
        background-color: #1a73e8;
        border-color: #1a73e8;
      }

      .btn-success:hover {
        background-color: #1765cc;
        border-color: #1765cc;
      }

      .edit-btn {
        display: flex;
        justify-content: start;
        margin-top: 15px;
      }

      .edit-btn a {
        text-decoration: none;
        color: #673ab7;
        padding: 5px 15px;
        border-radius: 5px;
        border: 1px solid #673ab7;
        transition: all 0.3s ease;
      }

      .edit-btn a:hover {
        background-color: #673ab7;
        color: white;
      }

      .logo-container {
        text-align: center;
        margin-bottom: 30px;
      }

      .logo-container img {
        max-height: 80px;
      }

      .section-title {
        font-weight: 600;
        color: #202124;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e0e0e0;
      }

      .data-row {
        margin-bottom: 8px;
      }

      .data-label {
        font-weight: 500;
        color: #5f6368;
      }

      .data-value {
        color: #202124;
      }

      .submit-container {
        position: sticky;
        bottom: 0;
        background-color: white;
        padding: 15px;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        border-top: 1px solid #e0e0e0;
        margin-top: 30px;
        text-align: right;
      }
    </style>
</head>
<body>
    <div class="container">
      <div class="logo-container">
        <img src="{{ asset("images/logo.png") }}" alt="Sugs Lloyd Ltd Logo" class="img-fluid">
        <h1 class="mt-3">Review & Confirm Your Details</h1>
        <p class="text-muted">Please review your information carefully before final submission.</p>
      </div>

      <form action="{{ route("hrm.submit") }}" method="POST">
        @csrf

        <!-- Personal Information -->
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">Personal Information</h2>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="data-row">
                  <span class="data-label">Full Name:</span>
                  <span class="data-value">{{ $data["name"] ?? "N/A" }}</span>
                </div>
                <div class="data-row">
                  <span class="data-label">Email:</span>
                  <span class="data-value">{{ $data["email"] ?? "N/A" }}</span>
                </div>
                <div class="data-row">
                  <span class="data-label">Phone:</span>
                  <span class="data-value">{{ $data["phone"] ?? "N/A" }}</span>
                </div>
                <div class="data-row">
                  <span class="data-label">Date of Birth:</span>
                  <span class="data-value">{{ $data["dob"] ?? "N/A" }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="data-row">
                  <span class="data-label">Gender:</span>
                  <span class="data-value">{{ $data["gender"] ?? "N/A" }}</span>
                </div>
                <div class="data-row">
                  <span class="data-label">Marital Status:</span>
                  <span class="data-value">{{ $data["marital_status"] ?? "N/A" }}</span>
                </div>
                <div class="data-row">
                  <span class="data-label">Nationality:</span>
                  <span class="data-value">{{ $data["nationality"] ?? "N/A" }}</span>
                </div>
                <div class="data-row">
                  <span class="data-label">Preferred Language:</span>
                  <span class="data-value">{{ $data["language"] ?? "N/A" }}</span>
                </div>
              </div>
            </div>
            <div class="edit-btn">
              <a href="{{ route("hrm.apply") }}#personal-info">
                <i class="fas fa-edit me-2"></i> Edit
              </a>
            </div>
          </div>
        </div>

        <!-- Contact Information -->
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">Contact Information</h2>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="data-row">
                  <span class="data-label">Permanent Address:</span>
                  <span class="data-value">{{ $data["permanent_address"] ?? "N/A" }}</span>
                </div>
                <div class="data-row">
                  <span class="data-label">Current Address:</span>
                  <span class="data-value">{{ $data["current_address"] ?? "N/A" }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="data-row">
                  <span class="data-label">Emergency Contact Name:</span>
                  <span class="data-value">{{ $data["emergency_contact_name"] ?? "N/A" }}</span>
                </div>
                <div class="data-row">
                  <span class="data-label">Emergency Contact Phone:</span>
                  <span class="data-value">{{ $data["emergency_contact_phone"] ?? "N/A" }}</span>
                </div>
              </div>
            </div>
            <div class="edit-btn">
              <a href="{{ route("hrm.apply") }}#contact-info">
                <i class="fas fa-edit me-2"></i> Edit
              </a>
            </div>
          </div>
        </div>

        <!-- Educational Background -->
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">Educational Background</h2>
          </div>
          <div class="card-body">
            @if (!empty($data["education"]))
              @foreach ($data["education"] as $index => $edu)
                <div class="border-bottom mb-4 pb-3">
                  <h5>Education #{{ $index + 1 }}</h5>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="data-row">
                        <span class="data-label">Qualification:</span>
                        <span class="data-value">{{ $edu["qualification"] ?? "N/A" }}</span>
                      </div>
                      <div class="data-row">
                        <span class="data-label">Institution:</span>
                        <span class="data-value">{{ $edu["institution"] ?? "N/A" }}</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="data-row">
                        <span class="data-label">Year of Graduation:</span>
                        <span class="data-value">{{ $edu["year"] ?? "N/A" }}</span>
                      </div>
                      <div class="data-row">
                        <span class="data-label">Specialization:</span>
                        <span class="data-value">{{ $edu["specialization"] ?? "N/A" }}</span>
                      </div>
                      <div class="data-row">
                        <span class="data-label">Certifications:</span>
                        <span class="data-value">{{ $edu["certifications"] ?? "N/A" }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            @else
              <p>No educational information provided.</p>
            @endif
            <div class="edit-btn">
              <a href="{{ route("hrm.apply") }}#education">
                <i class="fas fa-edit me-2"></i> Edit
              </a>
            </div>
          </div>
        </div>

        <!-- Employment Details -->
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">Employment Details</h2>
          </div>
          <div class="card-body">
            <!--  -->
            <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="data-row">
                  <span class="data-label">Position Applied For:</span>
                  <span class="data-value">{{ $data["position_applied_for"] ?? "N/A" }}</span>
                </div>
                <div class="data-row">
                  <span class="data-label">Department:</span>
                  <span class="data-value">{{ $data["department"] ?? "N/A" }}</span>
                </div>
                    <div class="data-row">
                      <span class="data-label">Experience:</span>
                      <span class="data-value">{{ $data["experience"] ?? 'N/A' }} years</span>
                    </div>
                    <div class="data-row">
                      <span class="data-label">Notice Period:</span>
                      <span class="data-value">{{ $data["notice_period"] ?? 'N/A' }}</span>
                    </div>
              </div>
            </div>
          </div>
          <hr>
            <!--  -->
          <h4>
            Previous Experience
          </h4>
            @if (!empty($data['employment']) && is_array($data['employment']))
              @foreach ($data['employment'] as $index => $emp)
                <div class="row mb-3 card-body">
                  <div class="col-md-6">
                    <div class="data-row">
                      <span class="data-label">Previous Employer:</span>
                      <span class="data-value">{{ $emp['previous_employer'] ?? 'N/A' }}</span>
                    </div>
                    <div class="data-row">
                      <span class="data-label">Department:</span>
                      <span class="data-value">{{ $emp['department'] ?? 'N/A' }}</span>
                    </div>
                    <div class="data-row">
                      <span class="data-label">Designation</span>
                      <span class="data-value">{{ $emp['designation'] ?? 'N/A' }}</span>
                    </div>
                    <div class="data-row">
                      <span class="data-label">Experience:</span>
                      <span class="data-value">{{ $emp['experience'] ?? 'N/A' }} years</span>
                    </div>
                    
                    <div class="data-row">
                      <span class="data-label">Date of Joining:</span>
                      <span class="data-value">{{ $emp['date_of_joining'] ?? 'N/A' }}</span>
                    </div>
                  </div>
                </div>
                <hr>
              @endforeach
              <div class="edit-btn mt-3">
                <a href="{{ route('hrm.apply') }}#employment">
                  <i class="fas fa-edit me-2"></i> Edit
                </a>
              </div>
            @else
              <p class="text-muted">No employment details provided.</p>
              <div class="edit-btn">
                <a href="{{ route('hrm.apply') }}#employment">
                  <i class="fas fa-edit me-2"></i> Add Employment Details
                </a>
              </div>
            @endif
          </div>
        </div>

      <!-- Uploaded Documents -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
          <h4 class="mb-0 fw-semibold">Uploaded Documents</h4>
        </div>
        <div class="card-body">
          @if (!empty($data["documents"]))
            <div class="row g-3">
              @foreach ($data["documents"] as $docName => $docPath)
                <div class="col-md-3 col-sm-4 col-6">
                  <div class="rounded-3 border hover-shadow overflow-hidden position-relative transition-all h-100">
                    <!-- File Thumbnail -->
                    <a href="{{ asset('storage/' . $docPath) }}" target="_blank" class="d-block">
                      <div class="bg-light position-relative" style="height: 160px;">
                        <img src="{{ asset('storage/thumbnails/' . pathinfo($docPath, PATHINFO_FILENAME) . '.jpg') }}"
                            onerror="this.onerror=null;this.src='{{ asset('images/default-pdf-thumb.jpg') }}';"
                            alt="{{ $docName }}"
                            class="img-fluid w-100 h-100"
                            style="object-fit: cover;">
                      </div>
                    </a>

                    <!-- File Name + Menu -->
                    <div class="bg-white p-2 d-flex justify-content-between align-items-start">
                      <div class="text-truncate small fw-medium" title="{{ $docName }}" style="max-width: 140px;">
                        {{ $docName }}
                      </div>
                      <div class="dropdown">
                        <a href="#" class="text-muted small" data-bs-toggle="dropdown">
                          <i class="fas fa-ellipsis-v"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                          <li>
                            <a class="dropdown-item" href="{{ asset('storage/' . $docPath) }}" target="_blank">
                              <i class="fas fa-eye me-2"></i> View
                            </a>
                          </li>
                          <!-- Add more options like Delete, Rename if needed -->
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <p class="text-muted">No documents uploaded.</p>
          @endif

          <div class="edit-btn mt-4">
            <a href="{{ route('hrm.apply') }}#documents" class="btn btn-outline-secondary">
              <i class="fas fa-edit me-2"></i> Edit Documents
            </a>
          </div>
        </div>
      </div>

      <!-- Additional Information -->
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">Additional Information</h2>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="data-row">
                  <span class="data-label">Do you have any disabilities?</span>
                  <span class="data-value">{{ $data["disabilities"] ?? "N/A" }}</span>
                </div>
                <div class="data-row">
                  <span class="data-label">Are you currently employed?</span>
                  <span class="data-value">{{ $data["currently_employed"] ?? "N/A" }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="data-row">
                  <span class="data-label">Reason for leaving current employment:</span>
                  <span class="data-value">{{ $data["reason_for_leaving"] ?? "N/A" }}</span>
                </div>
                <div class="data-row">
                  <span class="data-label">Other Information:</span>
                  <span class="data-value">{{ $data["other_info"] ?? "N/A" }}</span>
                </div>
              </div>
            </div>
            <div class="edit-btn">
              <a href="{{ route("hrm.apply") }}#additional-info">
                <i class="fas fa-edit me-2"></i> Edit
              </a>
            </div>
          </div>
        </div>

       <!-- Passport Size Photo -->
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">Passport Size Photo</h2>
          </div>
          <div class="card-body">
            <div class="text-center">
              @if (!empty($data["photo"]) && file_exists(storage_path('app/public/' . $data["photo"])))
                <img src="{{ asset('storage/' . $data["photo"]) }}" alt="Passport Photo" class="img-thumbnail rounded"
                    style="max-height: 200px; max-width: 200px; object-fit: cover;">
              @else
                <div class="alert alert-warning d-inline-block">No photo uploaded.</div>
              @endif
            </div>

            <div class="edit-btn mt-3 text-center">
              <a href="{{ route('hrm.apply') }}#photo" class="btn btn-outline-primary">
                <i class="fas fa-edit me-2"></i> Edit Photo
              </a>
            </div>
          </div>
        </div>


        <!-- Declaration -->
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">Declaration</h2>
          </div>
          <div class="card-body">
            <div class="alert alert-info">
              <p>I hereby declare that the information provided above is true to the best of my knowledge and
                belief. I
                understand that any false information may lead to disqualification from the recruitment process.
              </p>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="data-row">
                  <span class="data-label">Signature:</span>
                  <span class="data-value">{{ $data["signature"] ?? "N/A" }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="data-row">
                  <span class="data-label">Date:</span>
                  <span class="data-value">{{ $data["date"] ?? "N/A" }}</span>
                </div>
              </div>
            </div>
            <div class="edit-btn">
              <a href="{{ route("hrm.apply") }}#declaration">
                <i class="fas fa-edit me-2"></i> Edit
              </a>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="submit-container">
          <button type="submit" class="btn btn-success btn-lg">
            <i class="fas fa-check-circle me-2"></i> Confirm & Submit Application
          </button>
        </div>
      </form>
    </div>
    
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
        <strong>Error:</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Optional: Display validation errors --}}
@if ($errors->any())
    <div class="alert alert-warning">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


  </body>

  </body>

</html>