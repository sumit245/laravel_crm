@extends("layouts.main")

@section("content")
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

@endsection