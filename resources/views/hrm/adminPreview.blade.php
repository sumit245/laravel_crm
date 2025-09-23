@extends("layouts.main")

@section("content")
<<<<<<< HEAD
<style>
  body {
    font-family: 'Roboto', sans-serif;
    background-color: #f8f9fa;
    color: #202124;
  }

  .container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 20px;
  }

  .card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 24px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
  }

  .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 24px;
    border-bottom: none;
    font-weight: 600;
    font-size: 1.1rem;
  }

  .card-body {
    padding: 28px;
    background-color: white;
  }

  .logo-container {
    text-align: center;
    margin-bottom: 40px;
    padding: 30px;
    background-color: white;
    border-radius: 15px;
  }

  .logo-container img {
    max-height: 80px;
    margin-bottom: 20px;
  }

  .admin-badge {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    color: white;
    padding: 8px 20px;
    border-radius: 25px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 20px;
    display: inline-block;
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
  }

  .data-row {
    margin-bottom: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #f1f3f4;
  }

  .data-label {
    font-weight: 600;
    color: #5f6368;
    display: inline-block;
    min-width: 180px;
  }

  .data-value {
    color: #202124;
    font-weight: 500;
  }

  .admin-actions {
    position: sticky;
    bottom: 0;
    background-color: white;
    padding: 20px;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    border-top: 1px solid #e9ecef;
    margin-top: 30px;
    text-align: center;
  }

  .btn-approve {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
    padding: 12px 30px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 8px;
    margin-right: 15px;
    transition: all 0.3s ease;
  }

  .btn-approve:hover {
    background-color: #218838;
    border-color: #1e7e34;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
  }

  .btn-reject {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
    padding: 12px 30px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
  }

  .btn-reject:hover {
    background-color: #c82333;
    border-color: #bd2130;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
  }

  .document-thumbnail {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    margin-top: 15px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    transition: all 0.3s ease;
    cursor: pointer;
  }

  .document-thumbnail:hover {
    border-color: #667eea;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
    transform: translateY(-2px);
  }

  .document-thumbnail img {
    max-width: 200px;
    max-height: 150px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .file-thumbnail {
    width: 120px;
    height: 150px;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 2px solid #dee2e6;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .file-thumbnail:hover {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
  }

  .file-thumbnail.pdf-thumbnail {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    color: white;
    border-color: #ff6b6b;
  }

  .file-thumbnail.word-thumbnail {
    background: linear-gradient(135deg, #2b5ce6 0%, #1a47cc 100%);
    color: white;
    border-color: #2b5ce6;
  }

  .file-thumbnail.excel-thumbnail {
    background: linear-gradient(135deg, #217346 0%, #0f5132 100%);
    color: white;
    border-color: #217346;
  }

  .file-icon {
    font-size: 2.5rem;
    margin-bottom: 8px;
  }

  .file-extension {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .passport-photo-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    margin: 20px 0;
  }

  .passport-photo-container img {
    max-height: 300px;
    max-width: 250px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border: 4px solid white;
  }

  .document-item {
    border: none;
    border-radius: 12px !important;
    margin-bottom: 20px;
    background: #ffffff;
    transition: all 0.3s ease;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  }

  .document-item:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
  }

  .btn-outline-primary {
    border-radius: 20px;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .alert-info {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    border: none;
    color: white;
    border-radius: 10px;
    font-weight: 500;
  }

  .document-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f1f3f4;
    display: flex;
    align-items: center;
  }

  .document-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-top: 15px;
  }
</style>

<div class="container">
  <div class="logo-container">
    <h1 class="mt-3" style="color: #2d3436; font-weight: 700;">Application Review & Decision</h1>
    <p class="text-muted" style="font-size: 1.1rem;">Review the applicant's information and make your decision.</p>
  </div>

  @php
    $data = session("submittedData");
    $documents = [
        "Resume" => "bills/sample.pdf",
        "Profile Picture" => "bills/sample.pdf",
        "Contract" => "bills/sample.pdf",
    ];
  @endphp

  <!-- Personal Information -->
  <div class="card">
    <div class="card-header">
      <i class="fas fa-user me-2"></i>Personal Information
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="data-row">
            <span class="data-label">Full Name:</span>
            <span class="data-value">{{ $candidate->name ?? "N/A" }}</span>
          </div>
          <div class="data-row">
            <span class="data-label">Email:</span>
            <span class="data-value">{{ $candidate->email ?? "N/A" }}</span>
          </div>
          <div class="data-row">
            <span class="data-label">Phone:</span>
            <span class="data-value">{{ $candidate->phone ?? "N/A" }}</span>
          </div>
          <div class="data-row">
            <span class="data-label">Date of Offer:</span>
            <span class="data-value">{{ $candidate->date_of_offer ?? "N/A" }}</span>
          </div>
        </div>
        <div class="col-md-6">
          <div class="data-row">
            <span class="data-label">Gender:</span>
            <span class="data-value">{{ $candidate->gender ?? "N/A" }}</span>
          </div>
          <div class="data-row">
            <span class="data-label">Marital Status:</span>
            <span class="data-value">{{ $candidate->marital_status ?? "N/A" }}</span>
          </div>
          <div class="data-row">
            <span class="data-label">Nationality:</span>
            <span class="data-value">{{ $candidate->nationality ?? "N/A" }}</span>
          </div>
          <div class="data-row">
            <span class="data-label">Preferred Language:</span>
            <span class="data-value">{{ $candidate->language ?? "N/A" }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Contact Information -->
  <div class="card">
    <div class="card-header">
      <i class="fas fa-address-book me-2"></i>Contact Information
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="data-row">
            <span class="data-label">Permanent Address:</span>
            <span class="data-value">
              {{ $candidate->permanent_address ?? "N/A" }}
            </span>
          </div>
          <div class="data-row">
            <span class="data-label">Current Address:</span>
            <span class="data-value">
              {{ $candidate->address ?? "N/A" }}
            </span>
          </div>
        </div>
        <div class="col-md-6">
          <div class="data-row">
            <span class="data-label">Emergency Contact:</span>
            <span class="data-value">{{ $candidate->emergency_contact_name ?? "N/A"}} - {{  $candidate->emergency_contact_phone ?? "N/A" }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Educational Background -->
  <div class="card">
    <div class="card-header">
      <i class="fas fa-graduation-cap me-2"></i>Educational Background
    </div>
    <div class="card-body">
      @php
          $educationData = is_string($candidate->education)
              ? json_decode($candidate->education, true)
              : $candidate->education;
      @endphp

    @forelse ($educationData ?? [] as $index => $edu)
      <div class="border-bottom mb-4 pb-3">
        <h5 style="color: #667eea; font-weight: 600;">Education #{{ $index }}</h5>
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
              <span class="data-label">Year:</span>
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
    @empty
      <p class="text-muted">No educational information provided.</p>
    @endforelse
  </div>

  </div>

  <!-- Employment Details -->
   <div class="card">
  <div class="card-header">
    <i class="fas fa-briefcase me-2"></i>Employment Details
  </div>
  <div class="card-body">
    @php
      $employmentData = is_string($candidate->previous_employment)
          ? json_decode($candidate->previous_employment, true)
          : $candidate->previous_employment;
    @endphp

    {{-- Primary Employment Info --}}
    <div class="mb-4 pb-2 border-bottom">
      <h5 style="color: #667eea; font-weight: 600;">Current Employment</h5>
      <div class="row">
        <div class="col-md-6">
          <div class="data-row">
            <span class="data-label">Position Applied For:</span>
            <span class="data-value">{{ $candidate->designation ?? 'N/A' }}</span>
          </div>
          <div class="data-row">
            <span class="data-label">Department:</span>
            <span class="data-value">{{ $candidate->department ?? 'N/A' }}</span>
          </div>
        </div>
        <div class="col-md-6">
          <div class="data-row">
            <span class="data-label">Total Experience (Years):</span>
            <span class="data-value">{{ $candidate->experience ?? 'N/A' }}</span>
          </div>
          <div class="data-row">
            <span class="data-label">Notice Period:</span>
            <span class="data-value">{{ $candidate->notice_period ?? 'N/A' }}</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Previous Employment Loop --}}
    @forelse ($employmentData ?? [] as $index => $job)
      <div class="border-bottom mb-4 pb-3">
        <h5 style="color: #667eea; font-weight: 600;">Previous Employment #{{ $index + 1 }}</h5>
        <div class="row">
          <div class="col-md-6">
            <div class="data-row">
              <span class="data-label">Previous Employer:</span>
              <span class="data-value">{{ $job["previous_employer"] ?? "N/A" }}</span>
            </div>
            <div class="data-row">
              <span class="data-label">Designation:</span>
              <span class="data-value">{{ $job["designation"] ?? "N/A" }}</span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="data-row">
              <span class="data-label">Department:</span>
              <span class="data-value">{{ $job["department"] ?? "N/A" }}</span>
            </div>
            <div class="data-row">
              <span class="data-label">Date of Joining:</span>
              <span class="data-value">{{ \Carbon\Carbon::parse($job["date_of_joining"] ?? null)->format('d-m-Y') ?? "N/A" }}</span>
            </div>
            <div class="data-row">
              <span class="data-label">Experience:</span>
              <span class="data-value">{{ $job["experience"] ?? "N/A" }} years</span>
            </div>
          </div>
        </div>
      </div>
    @empty
      <p class="text-muted">No previous employment information provided.</p>
    @endforelse
  </div>
</div>

  <!-- Documents with Google Drive Style Thumbnails -->
<div class="card">
  <div class="card-header">
    <i class="fas fa-file-alt me-2"></i>Uploaded Documents
  </div>
  <div class="card-body">
      @php
          $paths1 = is_string($candidate->document_paths) ? json_decode($candidate->document_paths, true) : ($candidate->document_paths ?? []);
          $paths2 = is_string($candidate->document_path) ? json_decode($candidate->document_path, true) : ($candidate->document_path ?? []);
          $allDocuments = array_merge($paths1 ?: [], $paths2 ?: []);
      @endphp

    @if (!empty($allDocuments))
      <div class="row">
        @foreach ($allDocuments as $docName => $docPath)
  @php
    // If key is numeric, name it as Document #index
    $label = is_numeric($docName) ? "Document #" . ($loop->iteration) : ucfirst(str_replace("_", " ", $docName));
  @endphp

  <div class="col-md-4 mb-4">
    <div class="document-item">
      <div class="document-title">
        <i class="fas fa-file me-2"></i>{{ $label }}
      </div>

      @if (!empty($docPath))
        @php $fileExtension = pathinfo($docPath, PATHINFO_EXTENSION); @endphp

        <div class="document-thumbnail text-center">
          @if (in_array(strtolower($fileExtension), ["jpg", "jpeg", "png", "gif", "bmp", "webp"]))
            <img src="{{ $docPath }}" alt="{{ $label }}" class="img-fluid">
          @elseif (strtolower($fileExtension) === "pdf")
            <div class="file-thumbnail pdf-thumbnail">
              <i class="fas fa-file-pdf file-icon"></i>
              <div class="file-extension">PDF</div>
            </div>
          @elseif (in_array(strtolower($fileExtension), ["doc", "docx"]))
            <div class="file-thumbnail word-thumbnail">
              <i class="fas fa-file-word file-icon"></i>
              <div class="file-extension">{{ strtoupper($fileExtension) }}</div>
            </div>
          @elseif (in_array(strtolower($fileExtension), ["xls", "xlsx"]))
            <div class="file-thumbnail excel-thumbnail">
              <i class="fas fa-file-excel file-icon"></i>
              <div class="file-extension">{{ strtoupper($fileExtension) }}</div>
            </div>
          @else
            <div class="file-thumbnail">
              <i class="fas fa-file file-icon text-secondary"></i>
              <div class="file-extension text-secondary">{{ strtoupper($fileExtension) }}</div>
            </div>
          @endif
        </div>

        <div class="document-actions mt-2">
          <a href="{{ $docPath }}" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-eye me-1"></i> View
          </a>
          <a href="{{ $docPath }}" download class="btn btn-sm btn-outline-primary">
            <i class="fas fa-download me-1"></i> Download
          </a>
        </div>
      @else
        <div class="text-center text-muted py-4">
          <i class="fas fa-file-slash" style="font-size: 2rem;"></i>
          <p class="mt-2">Not uploaded</p>
        </div>
      @endif
    </div>
  </div>
@endforeach

      </div>
    @else
      <p class="text-muted">No documents uploaded.</p>
    @endif
  </div>
</div>


  <!-- Additional Information -->
  <div class="card">
    <div class="card-header">
      <i class="fas fa-info-circle me-2"></i>Additional Information
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="data-row">
            <span class="data-label">Disabilities:</span>
            <span class="data-value">{{ $candidate->disabilities ?? "N/A" }}</span>
          </div>
          <div class="data-row">
            <span class="data-label">Currently Employed:</span>
            <span class="data-value">{{ $candidate->currently_employed ?? "N/A" }}</span>
          </div>
        </div>
        <div class="col-md-6">
          <div class="data-row">
            <span class="data-label">Reason for Leaving:</span>
            <span class="data-value">{{ $candidate->reason_for_leaving ?? "N/A" }}</span>
          </div>
          <div class="data-row">
            <span class="data-label">Other Info:</span>
            <span class="data-value">{{ $candidate->other_info ?? "N/A" }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Passport Photo with Enhanced Preview -->
  <div class="card">
    <div class="card-header">
      <i class="fas fa-camera me-2"></i>Passport Size Photo
    </div>
    <div class="card-body">
      @if (!empty($candidate->photo_s3_path))
        <div class="passport-photo-container">
          <img src="{{ asset($candidate->photo_s3_path) }}" alt="Passport Photo">
        </div>
        <div class="text-center mt-3">
          <a href="{{ asset($candidate->photo_s3_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-expand me-1"></i> View Full Size
          </a>
        </div>
      @else
        <div class="text-center text-muted py-5">
          <i class="fas fa-user-circle" style="font-size: 4rem;"></i>
          <p class="mt-3">No passport photo uploaded</p>
        </div>
      @endif
    </div>
  </div>
</div>

=======
<div class="container mx-auto p-6 max-w-5xl bg-white shadow-lg rounded-lg relative">

    {{-- Logo + Heading --}}
    <div class="flex flex-col items-center justify-center text-center mb-6">
        <img src="{{ asset('images/logo.png') }}" alt="Company Logo" class="h-20 w-auto mb-2">
        <h1 class="text-xl font-semibold text-emerald-700">Application Details</h1>
        <p class="mt-2 text-gray-600 max-w-2xl">
            Below are the details submitted by the applicant.
        </p>
    </div>

    @php
        $data = session('submittedData');
        $documents = [
            'Resume' => 'bills/sample.pdf',
            'Profile Picture' => 'bills/sample.pdf',
            'Contract' => 'bills/sample.pdf',
        ];
    @endphp

    {{-- Personal Information --}}
    <div class="bg-gray-50 p-5 rounded-lg shadow-md mb-6 border border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-800">Personal Information</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
            <p><strong>Full Name:</strong> {{ $data['personalInfo']['name'] ?? 'N/A' }}</p>
            <p><strong>Email:</strong> {{ $data['personalInfo']['email'] ?? 'N/A' }}</p>
            <p><strong>Phone:</strong> {{ $data['personalInfo']['phone'] ?? 'N/A' }}</p>
            <p><strong>DOB:</strong> {{ $data['personalInfo']['dob'] ?? 'N/A' }}</p>
            <p><strong>Gender:</strong> {{ $data['personalInfo']['gender'] ?? 'N/A' }}</p>
            <p><strong>Marital Status:</strong> {{ $data['personalInfo']['maritalStatus'] ?? 'N/A' }}</p>
            <p><strong>Nationality:</strong> {{ $data['personalInfo']['nationality'] ?? 'N/A' }}</p>
            <p><strong>Language:</strong> {{ $data['personalInfo']['language'] ?? 'N/A' }}</p>
        </div>
    </div>

    {{-- Contact Information --}}
    <div class="bg-gray-50 p-5 rounded-lg shadow-md mb-6 border border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-800">Contact Information</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
            <p><strong>Permanent Address:</strong> {{ isset($data['contactInfo']['permanentAddress']) ? implode(', ', array_filter($data['contactInfo']['permanentAddress'])) : 'N/A' }}</p>
            <p><strong>Current Address:</strong> {{ isset($data['contactInfo']['currentAddress']) ? implode(', ', array_filter($data['contactInfo']['currentAddress'])) : 'N/A' }}</p>
            <p><strong>Emergency Contact:</strong> {{ $data['contactInfo']['emergencyContact']['name'] ?? 'N/A' }} ({{ $data['contactInfo']['emergencyContact']['phone'] ?? 'N/A' }})</p>
        </div>
    </div>

    {{-- Education --}}
    <div class="bg-gray-50 p-5 rounded-lg shadow-md mb-6 border border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-800">Educational Background</h2>
        </div>
        @forelse ($data['education'] ?? [] as $edu)
            <div class="mb-4 text-gray-700">
                <p><strong>Qualification:</strong> {{ $edu['qualification'] ?? 'N/A' }}</p>
                <p><strong>Institution:</strong> {{ $edu['institution'] ?? 'N/A' }} ({{ $edu['year'] ?? 'N/A' }})</p>
                <p><strong>Specialization:</strong> {{ $edu['specialization'] ?? 'N/A' }}</p>
                <p><strong>Certifications:</strong> {{ $edu['certifications'] ?? 'N/A' }}</p>
                <hr class="my-2">
            </div>
        @empty
            <p class="text-gray-500">No education records provided.</p>
        @endforelse
    </div>

    {{-- Documents --}}
    <div class="bg-gray-50 p-5 rounded-lg shadow-md mb-6 border border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-800">Uploaded Documents</h2>
        </div>
        @if (!empty($documents))
            <ul class="list-disc list-inside space-y-2 text-gray-700">
                @foreach ($documents as $docName => $docPath)
                    <li>
                        <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $docName)) }}:</span>
                        @if ($docPath)
                            <div class="mt-2">
                                @php $fileExtension = pathinfo($docPath, PATHINFO_EXTENSION); @endphp

                                @if (in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']))
                                    <img src="{{ asset($docPath) }}" alt="{{ $docName }}" class="w-32 h-32 object-cover rounded shadow-md">
                                @elseif (strtolower($fileExtension) === 'pdf')
                                    <a href="{{ asset($docPath) }}" target="_blank" class="text-emerald-600 hover:underline ml-2">
                                        <i class="fas fa-file-pdf"></i> View PDF
                                    </a>
                                @elseif (in_array(strtolower($fileExtension), ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']))
                                    <a href="{{ asset($docPath) }}" target="_blank" class="text-emerald-600 hover:underline ml-2">
                                        <i class="fas fa-file-word"></i> View Document
                                    </a>
                                @else
                                    <a href="{{ asset($docPath) }}" target="_blank" class="text-emerald-600 hover:underline ml-2">
                                        <i class="fas fa-file"></i> View Document
                                    </a>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-500">Not uploaded</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-500">No documents uploaded.</p>
        @endif
    </div>

    {{-- Additional Information --}}
    <div class="bg-gray-50 p-5 rounded-lg shadow-md mb-6 border border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-800">Additional Information</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
            <p><strong>Disabilities:</strong> {{ $data['additionalInfo']['disabilities'] ?? 'N/A' }}</p>
            <p><strong>Currently Employed:</strong> {{ $data['additionalInfo']['currentlyEmployed'] ?? 'N/A' }}</p>
            <p><strong>Reason for Leaving:</strong> {{ $data['additionalInfo']['reasonForLeaving'] ?? 'N/A' }}</p>
            <p><strong>Other Info:</strong> {{ $data['additionalInfo']['otherInfo'] ?? 'N/A' }}</p>
        </div>
    </div>

    {{-- Passport Size Photo --}}
    <div class="bg-gray-50 p-5 rounded-lg shadow-md mb-6 border border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-800">Passport Size Photo</h2>
        </div>
        @if (!empty($data['photo']))
            <img src="{{ asset($data['photo']) }}" alt="Passport Photo" class="w-32 h-40 object-cover border rounded shadow-md">
        @else
            <p class="text-gray-500">No photo uploaded.</p>
        @endif
    </div>

    {{-- Declaration --}}
    <div class="bg-gray-50 p-5 rounded-lg shadow-md mb-6 border border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-800">Declaration</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
            <p>I declare the above information is true.</p>
            <p><strong>Signature:</strong> {{ $data['declaration']['signature'] ?? 'N/A' }}</p>
            <p><strong>Date:</strong> {{ $data['declaration']['date'] ?? 'N/A' }}</p>
        </div>
    </div>

    {{-- Approve and Reject Buttons --}}
    <div class="sticky bottom-0 left-0 bg-white py-4 mt-6 border-t border-gray-200 shadow-inner">
        <div class="flex justify-end gap-4 px-6">
            {{-- Approve Form --}}
                @csrf
                <button type="submit"
                class="px-6 py-2 border border-green-500 badge bg-danger text-green-800 font-semibold rounded-md hover:bg-green-200 hover:border-green-600 transition duration-200 shadow-md">
                Approve
                </button>
            </form>

            {{-- Reject Form --}}
                @csrf
                <button type="submit"
                    class="px-6 py-2 badge bg-success  border">
                    Reject
                </button>
            </form>
        </div>
    </div>
</div>
>>>>>>> 5fd7e494199d3ae2af4600437e3169f144087b5c
@endsection