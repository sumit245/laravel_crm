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

      /* Document Thumbnail Styles */
      .document-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
        background: white;
        height: 100%;
        cursor: pointer;
      }

      .document-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
        border-color: #673ab7;
      }

      .document-thumbnail {
        height: 160px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
      }

      .document-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      .document-icon {
        font-size: 48px;
        color: #5f6368;
      }

      .document-info {
        padding: 12px;
        border-top: 1px solid #e0e0e0;
      }

      .document-name {
        font-size: 14px;
        font-weight: 500;
        color: #202124;
        margin-bottom: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.3;
        min-height: 34px;
      }

      .document-size {
        font-size: 12px;
        color: #5f6368;
      }

      .document-actions {
        position: absolute;
        top: 8px;
        right: 8px;
        opacity: 0;
        transition: opacity 0.3s ease;
      }

      .document-card:hover .document-actions {
        opacity: 1;
      }

      .action-btn {
        background: rgba(255, 255, 255, 0.9);
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 4px;
        transition: all 0.3s ease;
        color: #5f6368;
      }

      .action-btn:hover {
        background: white;
        color: #673ab7;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
      }

      /* File type specific colors */
      .pdf-icon {
        color: #d32f2f;
      }

      .doc-icon {
        color: #1976d2;
      }

      .image-icon {
        color: #388e3c;
      }

      .default-icon {
        color: #5f6368;
      }

      /* Document grid */
      .documents-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 16px;
        margin-top: 16px;
      }

      @media (max-width: 768px) {
        .documents-grid {
          grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
          gap: 12px;
        }

        .document-thumbnail {
          height: 120px;
        }

        .document-icon {
          font-size: 36px;
        }
      }

      /* Modal styles for document preview */
      .document-modal .modal-dialog {
        max-width: 90vw;
        max-height: 90vh;
      }

      .document-modal .modal-content {
        height: 85vh;
      }

      .document-modal .modal-body {
        padding: 0;
        height: calc(85vh - 120px);
        overflow: hidden;
      }

      .document-preview {
        width: 100%;
        height: 100%;
        border: none;
      }

      .document-preview img {
        width: 100%;
        height: 100%;
        object-fit: contain;
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
              <a href="{{ route("apply-now", ["id" => $id]) }}#personal-info">
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
              <a href="{{ route("apply-now", ["id" => $id]) }}#contact-info">
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
              <a href="{{ route("apply-now", ["id" => $id]) }}#education">
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
                  <span class="data-value">{{ $data["experience"] ?? "N/A" }} years</span>
                </div>
                <div class="data-row">
                  <span class="data-label">Notice Period:</span>
                  <span class="data-value">{{ $data["notice_period"] ?? "N/A" }}</span>
                </div>
              </div>
            </div>

            @if (!empty($data["employment"]) && is_array($data["employment"]))
              <hr>
              <h4>Previous Experience</h4>
              @foreach ($data["employment"] as $index => $emp)
                <div class="row bg-light mb-3 rounded p-3">
                  <div class="col-md-6">
                    <div class="data-row">
                      <span class="data-label">Previous Employer:</span>
                      <span class="data-value">{{ $emp["previous_employer"] ?? "N/A" }}</span>
                    </div>
                    <div class="data-row">
                      <span class="data-label">Department:</span>
                      <span class="data-value">{{ $emp["department"] ?? "N/A" }}</span>
                    </div>
                    <div class="data-row">
                      <span class="data-label">Designation:</span>
                      <span class="data-value">{{ $emp["designation"] ?? "N/A" }}</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="data-row">
                      <span class="data-label">Experience:</span>
                      <span class="data-value">{{ $emp["experience"] ?? "N/A" }} years</span>
                    </div>
                    <div class="data-row">
                      <span class="data-label">Date of Joining:</span>
                      <span class="data-value">{{ $emp["date_of_joining"] ?? "N/A" }}</span>
                    </div>
                  </div>
                </div>
              @endforeach
            @endif

            <div class="edit-btn">
              <a href="{{ route("hrm.apply") }}#employment">
                <i class="fas fa-edit me-2"></i> Edit
              </a>
            </div>
          </div>
        </div>

        <!-- Uploaded Documents -->
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">
              <i class="fas fa-folder-open me-2"></i>
              Uploaded Documents
            </h2>
          </div>
          <div class="card-body">
            @if (!empty($data["documents"]))
              <div class="documents-grid">
                @foreach ($data["documents"] as $docName => $docPath)
                  @php
                    $fullPath = storage_path("app/public/" . $docPath);
                    $fileSize = file_exists($fullPath) ? formatBytes(filesize($fullPath)) : "Unknown";
                    $extension = strtolower(pathinfo($docPath, PATHINFO_EXTENSION));
                    $isImage = in_array($extension, ["jpg", "jpeg", "png", "gif", "webp"]);
                    $isPdf = $extension === "pdf";
                    $isDoc = in_array($extension, ["doc", "docx"]);
                  @endphp

                  <div class="document-card"
                    onclick="previewDocument('{{ asset("storage/" . $docPath) }}', '{{ $docName }}', '{{ $extension }}')">
                    <div class="document-thumbnail">
                      @if ($isImage)
                        <img src="{{ asset("storage/" . $docPath) }}" alt="{{ $docName }}" loading="lazy">
                      @else
                        <i
                          class="fas @if ($isPdf) fa-file-pdf pdf-icon
                          @elseif($isDoc) fa-file-word doc-icon
                          @elseif(in_array($extension, ["xls", "xlsx"])) fa-file-excel
                          @elseif(in_array($extension, ["ppt", "pptx"])) fa-file-powerpoint
                          @elseif(in_array($extension, ["txt"])) fa-file-alt
                          @else fa-file default-icon @endif document-icon"></i>
                      @endif

                      <div class="document-actions">
                        <button type="button" class="action-btn"
                          onclick="event.stopPropagation(); downloadDocument('{{ asset("storage/" . $docPath) }}', '{{ $docName }}')"
                          title="Download">
                          <i class="fas fa-download"></i>
                        </button>
                        <button type="button" class="action-btn"
                          onclick="event.stopPropagation(); previewDocument('{{ asset("storage/" . $docPath) }}', '{{ $docName }}', '{{ $extension }}')"
                          title="Preview">
                          <i class="fas fa-eye"></i>
                        </button>
                      </div>
                    </div>

                    <div class="document-info">
                      <div class="document-name" title="{{ $docName }}">{{ $docName }}</div>
                      <div class="document-size">{{ $fileSize }} • {{ strtoupper($extension) }}</div>
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <div class="py-5 text-center">
                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                <p class="text-muted">No documents uploaded.</p>
              </div>
            @endif

            <div class="edit-btn">
              <a href="{{ route("hrm.apply") }}#documents" class="btn btn-outline-primary">
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
              <a href="{{ route("apply-now", ["id" => $id]) }}#additional-info">
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
              @if (!empty($data["photo"]) && file_exists(storage_path("app/public/" . $data["photo"])))
                <img src="{{ asset("storage/" . $data["photo"]) }}" alt="Passport Photo"
                  class="img-thumbnail rounded" style="max-height: 200px; max-width: 200px; object-fit: cover;">
              @else
                <div class="alert alert-warning d-inline-block">No photo uploaded.</div>
              @endif
            </div>
            <div class="edit-btn mt-3 text-center">
              <a href="{{ route("hrm.apply") }}#photo" class="btn btn-outline-primary">
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
                belief. I understand that any false information may lead to disqualification from the recruitment
                process.
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
              <a href="{{ route("apply-now", ["id" => $id]) }}#declaration">
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

    <!-- Document Preview Modal -->
    <div class="modal fade document-modal" id="documentPreviewModal" tabindex="-1"
      aria-labelledby="documentPreviewModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="documentPreviewModalLabel">Document Preview</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="documentPreviewContent" class="document-preview">
              <!-- Content will be loaded here -->
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="downloadBtn">
              <i class="fas fa-download me-2"></i> Download
            </button>
          </div>
        </div>
      </div>
    </div>

    @if (session("error"))
      <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
        <strong>Error:</strong> {{ session("error") }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

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

    <script>
      // Document preview functionality
      function previewDocument(url, name, extension) {
        const modal = new bootstrap.Modal(document.getElementById('documentPreviewModal'));
        const modalTitle = document.getElementById('documentPreviewModalLabel');
        const previewContent = document.getElementById('documentPreviewContent');
        const downloadBtn = document.getElementById('downloadBtn');

        modalTitle.textContent = name;

        // Clear previous content
        previewContent.innerHTML = '';

        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension.toLowerCase())) {
          // Image preview
          previewContent.innerHTML = `<img src="${url}" alt="${name}" class="img-fluid">`;
        } else if (extension.toLowerCase() === 'pdf') {
          // PDF preview
          previewContent.innerHTML = `
            <iframe src="${url}" width="100%" height="100%" style="border: none;">
              <p>Your browser does not support PDFs. <a href="${url}" target="_blank">Download the PDF</a>.</p>
            </iframe>
          `;
        } else {
          // Other file types
          previewContent.innerHTML = `
            <div class="text-center py-5">
              <i class="fas fa-file fa-5x text-muted mb-3"></i>
              <h4>${name}</h4>
              <p class="text-muted">Preview not available for this file type.</p>
              <a href="${url}" target="_blank" class="btn btn-primary">
                <i class="fas fa-external-link-alt me-2"></i> Open in New Tab
              </a>
            </div>
          `;
        }

        // Set download button
        downloadBtn.onclick = () => downloadDocument(url, name);

        modal.show();
      }

      // Download document
      function downloadDocument(url, name) {
        const link = document.createElement('a');
        link.href = url;
        link.download = name;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }


      function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
      }
    </script>

    @php
      function formatBytes($size, $precision = 2)
      {
          $base = log($size, 1024);
          $suffixes = ["B", "KB", "MB", "GB", "TB"];
          return round(pow(1024, $base - floor($base)), $precision) . " " . $suffixes[floor($base)];
      }
    @endphp
  </body>

</html>
