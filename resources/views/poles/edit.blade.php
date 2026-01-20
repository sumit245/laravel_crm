@extends("layouts.main")

@section("content")
  <div class="pd-20 pd-xl-25 container">
    <div class="d-flex align-items-center justify-content-between mg-b-25">
      <h6 class="mg-b-0">Edit Pole Details</h6>
      <div class="d-flex">
        <a href="{{ route("poles.show", $pole->id) }}" class="btn btn-sm btn-white d-flex align-items-center">
          <span class="d-none d-sm-inline mg-l-5">Cancel</span>
        </a>
      </div>
    </div>

    @if (session("success"))
      <div class="alert alert-success">
        {{ session("success") }}
      </div>
    @endif

    @if (session("error"))
      <div class="alert alert-danger">
        {{ session("error") }}
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route("poles.update", $pole->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method("PUT")
      
      <div class="row">
        <!-- Non-editable fields -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Complete Pole Number</label>
          <p class="mg-b-0 text-muted">{{ $pole->complete_pole_number }}</p>
          <small class="text-muted">Cannot be edited</small>
        </div>

        <!-- Editable Location -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Latitude</label>
          <input type="number" step="any" name="lat" class="form-control" value="{{ old("lat", $pole->lat) }}">
        </div>

        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Longitude</label>
          <input type="number" step="any" name="lng" class="form-control" value="{{ old("lng", $pole->lng) }}">
        </div>

        <!-- Ward Name -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Ward Name</label>
          <input type="text" name="ward_name" class="form-control" value="{{ old("ward_name", $pole->ward_name) }}">
        </div>
      </div>

      <div class="row mt-3">
        <!-- Beneficiary -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Beneficiary</label>
          <input type="text" name="beneficiary" class="form-control" value="{{ old("beneficiary", $pole->beneficiary) }}">
        </div>

        <!-- Beneficiary Contact -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Beneficiary Contact</label>
          <input type="text" name="beneficiary_contact" class="form-control" value="{{ old("beneficiary_contact", $pole->beneficiary_contact) }}">
        </div>

        <!-- Survey Status -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Survey Status</label>
          <select name="isSurveyDone" class="form-control white-select">
            <option value="1" {{ old("isSurveyDone", $pole->isSurveyDone) ? "selected" : "" }}>Yes</option>
            <option value="0" {{ !old("isSurveyDone", $pole->isSurveyDone) ? "selected" : "" }}>No</option>
          </select>
        </div>

        <!-- Installation Status -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Installation Status</label>
          <select name="isInstallationDone" class="form-control white-select">
            <option value="1" {{ old("isInstallationDone", $pole->isInstallationDone) ? "selected" : "" }}>Yes</option>
            <option value="0" {{ !old("isInstallationDone", $pole->isInstallationDone) ? "selected" : "" }}>No</option>
          </select>
        </div>
      </div>

      <div class="row mt-3">
        <!-- Network Status -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Network Status</label>
          <select name="isNetworkAvailable" class="form-control white-select">
            <option value="1" {{ old("isNetworkAvailable", $pole->isNetworkAvailable) ? "selected" : "" }}>Yes</option>
            <option value="0" {{ !old("isNetworkAvailable", $pole->isNetworkAvailable) ? "selected" : "" }}>No</option>
          </select>
        </div>

        <!-- Non-editable Installer Name -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Installer Name</label>
          <p class="mg-b-0 text-muted">{{ $installer->name ?? "" }}</p>
          <small class="text-muted">Cannot be edited</small>
        </div>
      </div>

      <hr />

      <div class="row">
        <!-- QR Codes -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Luminary QR</label>
          <input type="text" name="luminary_qr" class="form-control" value="{{ old("luminary_qr", $pole->luminary_qr) }}">
          <small class="text-danger inventory-warning">Changing this will return old inventory</small>
        </div>

        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Sim Number</label>
          <input type="text" name="sim_number" class="form-control" value="{{ old("sim_number", $pole->sim_number) }}">
        </div>

        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Battery QR</label>
          <input type="text" name="battery_qr" class="form-control" value="{{ old("battery_qr", $pole->battery_qr) }}">
          <small class="text-danger inventory-warning">Changing this will return old inventory</small>
        </div>

        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Panel QR</label>
          <input type="text" name="panel_qr" class="form-control" value="{{ old("panel_qr", $pole->panel_qr) }}">
          <small class="text-danger inventory-warning">Changing this will return old inventory</small>
        </div>
      </div>

      <hr />

      <div class="row">
        <!-- Non-editable fields -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Engineer</label>
          <p class="mg-b-0 text-muted">{{ $siteEngineer->name ?? "" }}</p>
          <small class="text-muted">Cannot be edited</small>
        </div>

        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Project Manager</label>
          <p class="mg-b-0 text-muted">{{ $projectManager->name ?? "" }}</p>
          <small class="text-muted">Cannot be edited</small>
        </div>

        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Installation Date</label>
          <p class="mg-b-0 text-muted">{{ $pole->created_at }}</p>
          <small class="text-muted">Cannot be edited</small>
        </div>

        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Submitted at</label>
          <p class="mg-b-0 text-muted">{{ $pole->isInstallationDone == 1 ? $pole->updated_at : "" }}</p>
          <small class="text-muted">Cannot be edited</small>
        </div>
      </div>

      <hr />

      <div class="row">
        <!-- Remarks -->
        <div class="col-12">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Remarks</label>
          <textarea name="remarks" class="form-control" rows="3">{{ old("remarks", $pole->remarks) }}</textarea>
        </div>
      </div>

      <hr />

      <!-- Image Upload Section -->
      <div class="row">
        <!-- Survey Images Card -->
        <div class="col-lg-6">
          <div class="card mb-4">
            <div class="card-header bg-white">
              <h5 class="mb-0 fw-bold">
                <i class="mdi mdi-camera text-primary"></i> Survey Images
              </h5>
            </div>
            <div class="card-body">
              <!-- Existing Images -->
              @if (!empty($surveyImages) && count($surveyImages) > 0)
                <div class="image-gallery mb-3">
                  @foreach ($surveyImages as $index => $image)
                    <div class="image-item-wrapper mb-3 position-relative">
                      <div class="existing-image-container">
                        <a href="{{ $image }}" target="_blank" class="image-link">
                          <img src="{{ $image }}" alt="Survey Image {{ $index + 1 }}" class="img-fluid rounded shadow-sm existing-image">
                        </a>
                        <div class="image-overlay">
                          <button type="button" class="btn btn-icon btn-sm btn-danger delete-existing-image" data-image-url="{{ $image }}" data-image-type="survey" title="Delete Image">
                            <i class="mdi mdi-delete"></i>
                          </button>
                          <label class="btn btn-icon btn-sm btn-primary upload-replace-image" title="Replace Image">
                            <i class="mdi mdi-camera"></i>
                            <input type="file" name="replace_survey_image[{{ $index }}]" class="d-none" accept="image/*">
                          </label>
                        </div>
                        <input type="hidden" name="existing_survey_images[]" value="{{ $image }}" class="existing-image-input">
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif
              
              <!-- Drag and Drop Upload Area -->
              <div class="image-upload-area" id="surveyUploadArea">
                <input type="file" name="survey_image[]" id="surveyImageInput" class="d-none" multiple accept="image/*">
                <div class="drop-zone" id="surveyDropZone">
                  <i class="mdi mdi-camera mdi-48px text-muted mb-2"></i>
                  <p class="text-muted mb-0">Drag & drop images here or click to upload</p>
                  <small class="text-muted">You can select multiple images</small>
                </div>
                <div id="surveyPreviewContainer" class="preview-container mt-3"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Installation Images Card -->
        <div class="col-lg-6">
          <div class="card mb-4">
            <div class="card-header bg-white">
              <h5 class="mb-0 fw-bold">
                <i class="mdi mdi-camera text-primary"></i> Installation Images
              </h5>
            </div>
            <div class="card-body">
              <!-- Existing Images -->
              @if (!empty($submissionImages) && count($submissionImages) > 0)
                <div class="image-gallery mb-3">
                  @foreach ($submissionImages as $index => $image)
                    <div class="image-item-wrapper mb-3 position-relative">
                      <div class="existing-image-container">
                        <a href="{{ $image }}" target="_blank" class="image-link">
                          <img src="{{ $image }}" alt="Installation Image {{ $index + 1 }}" class="img-fluid rounded shadow-sm existing-image">
                        </a>
                        <div class="image-overlay">
                          <button type="button" class="btn btn-icon btn-sm btn-danger delete-existing-image" data-image-url="{{ $image }}" data-image-type="installation" title="Delete Image">
                            <i class="mdi mdi-delete"></i>
                          </button>
                          <label class="btn btn-icon btn-sm btn-primary upload-replace-image" title="Replace Image">
                            <i class="mdi mdi-camera"></i>
                            <input type="file" name="replace_submission_image[{{ $index }}]" class="d-none" accept="image/*">
                          </label>
                        </div>
                        <input type="hidden" name="existing_submission_images[]" value="{{ $image }}" class="existing-image-input">
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif
              
              <!-- Drag and Drop Upload Area -->
              <div class="image-upload-area" id="installationUploadArea">
                <input type="file" name="submission_image[]" id="installationImageInput" class="d-none" multiple accept="image/*">
                <div class="drop-zone" id="installationDropZone">
                  <i class="mdi mdi-camera mdi-48px text-muted mb-2"></i>
                  <p class="text-muted mb-0">Drag & drop images here or click to upload</p>
                  <small class="text-muted">You can select multiple images</small>
                </div>
                <div id="installationPreviewContainer" class="preview-container mt-3"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <hr />

      <div class="row">
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Update Pole Details</button>
          <a href="{{ route("poles.show", $pole->id) }}" class="btn btn-secondary ml-2">Cancel</a>
        </div>
      </div>
    </form>
  </div>
@endsection

@push("scripts")
  <script>
    $(document).ready(function() {
      // Setup drag and drop for Survey Images
      setupImageUpload('survey');
      
      // Setup drag and drop for Installation Images
      setupImageUpload('installation');
      
      function setupImageUpload(type) {
        const dropZone = document.getElementById(type + 'DropZone');
        const fileInput = document.getElementById(type + 'ImageInput');
        const previewContainer = document.getElementById(type + 'PreviewContainer');
        let previews = [];
        
        // Click to open file dialog
        dropZone.addEventListener('click', () => {
          fileInput.click();
        });
        
        // File input change
        fileInput.addEventListener('change', function(e) {
          handleFiles(e.target.files, type);
        });
        
        // Drag and drop handlers
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
          dropZone.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
          e.preventDefault();
          e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
          dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('drag-over');
          }, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
          dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('drag-over');
          }, false);
        });
        
        dropZone.addEventListener('drop', (e) => {
          const dt = e.dataTransfer;
          const files = dt.files;
          handleFiles(files, type);
        }, false);
        
        // Hover effect for camera icon
        dropZone.addEventListener('mouseenter', () => {
          dropZone.querySelector('i').classList.remove('text-muted');
          dropZone.querySelector('i').classList.add('text-primary');
        });
        
        dropZone.addEventListener('mouseleave', () => {
          if (!dropZone.classList.contains('drag-over')) {
            dropZone.querySelector('i').classList.remove('text-primary');
            dropZone.querySelector('i').classList.add('text-muted');
          }
        });
        
        function handleFiles(files, uploadType) {
          Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
              const reader = new FileReader();
              reader.onload = function(e) {
                const preview = createPreview(e.target.result, file.name);
                previews.push({ file: file, preview: preview });
                previewContainer.appendChild(preview);
              };
              reader.readAsDataURL(file);
            }
          });
        }
        
        function createPreview(src, fileName) {
          const div = document.createElement('div');
          div.className = 'preview-item mb-2';
          div.innerHTML = `
            <div class="position-relative d-inline-block">
              <img src="${src}" class="preview-img rounded" alt="Preview">
              <span class="preview-name small text-muted d-block mt-1">${fileName}</span>
            </div>
          `;
          return div;
        }
      }
      
      // Handle delete existing images
      $(document).on('click', '.delete-existing-image', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $wrapper = $(this).closest('.image-item-wrapper');
        const $input = $wrapper.find('.existing-image-input');
        const imageType = $(this).data('image-type');
        
        // Mark as deleted (add to delete list)
        if (!$input.hasClass('to-delete')) {
          $input.addClass('to-delete');
          $input.attr('name', `deleted_${imageType}_images[]`);
          $wrapper.addClass('deleted');
          
          // Visual feedback
          Swal.fire({
            icon: 'success',
            title: 'Image Marked for Deletion',
            text: 'This image will be removed when you save the form.',
            timer: 2000,
            showConfirmButton: false
          });
        }
      });
      
      // Handle replace image upload
      $(document).on('change', '.upload-replace-image input[type="file"]', function(e) {
        const file = this.files[0];
        if (file && file.type.startsWith('image/')) {
          const $wrapper = $(this).closest('.image-item-wrapper');
          const $img = $wrapper.find('.existing-image');
          const reader = new FileReader();
          
          reader.onload = function(e) {
            $img.attr('src', e.target.result);
            $wrapper.removeClass('deleted');
            $wrapper.find('.existing-image-input').removeClass('to-delete');
          };
          
          reader.readAsDataURL(file);
        }
      });
    });
  </script>
@endpush

@push("styles")
  <style>
    /* White background for select dropdowns */
    .white-select {
      background-color: #ffffff !important;
      color: #333333 !important;
    }
    
    .white-select option {
      background-color: #ffffff !important;
      color: #333333 !important;
    }
    
    /* Smaller red text for inventory warnings */
    .inventory-warning {
      font-size: 0.75rem !important;
      color: #dc3545 !important;
      font-weight: 500;
    }
    
    /* Image upload and display styles matching show page */
    .card {
      border: none;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      border-radius: 8px;
    }
    
    .card-header {
      border-bottom: 1px solid #e9ecef;
      padding: 1rem 1.25rem;
    }
    
    .card-header h5 {
      color: #495057;
      font-size: 1.1rem;
    }
    
    .card-body {
      padding: 1.25rem;
    }
    
    .image-gallery {
      display: flex;
      flex-direction: column;
    }
    
    .image-item-wrapper {
      position: relative;
    }
    
    .existing-image-container {
      position: relative;
      display: inline-block;
      width: 100%;
    }
    
    .existing-image-container .existing-image {
      width: 100%;
      height: auto;
      max-height: 300px;
      object-fit: cover;
      border: 1px solid #dee2e6;
      transition: opacity 0.3s ease;
    }
    
    .image-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.6);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      opacity: 0;
      transition: opacity 0.3s ease;
      border-radius: 4px;
    }
    
    .existing-image-container:hover .image-overlay {
      opacity: 1;
    }
    
    .existing-image-container:hover .existing-image {
      opacity: 0.8;
    }
    
    .btn-icon {
      width: 40px;
      height: 40px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      color: white;
      border: none;
    }
    
    .btn-icon:hover {
      transform: scale(1.1);
    }
    
    .btn-icon.btn-danger {
      background-color: #dc3545;
    }
    
    .btn-icon.btn-danger:hover {
      background-color: #c82333;
    }
    
    .btn-icon.btn-primary {
      background-color: #007bff;
    }
    
    .btn-icon.btn-primary:hover {
      background-color: #0056b3;
    }
    
    .upload-replace-image {
      cursor: pointer;
      margin: 0;
    }
    
    .image-item.deleted {
      opacity: 0.5;
    }
    
    .image-item.deleted .existing-image {
      filter: grayscale(100%);
    }
    
    .image-link {
      display: block;
      cursor: pointer;
    }
    
    /* Drag and drop upload area */
    .image-upload-area {
      margin-top: 1rem;
    }
    
    .drop-zone {
      border: 2px dashed #dee2e6;
      border-radius: 8px;
      padding: 2rem;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
      background-color: #f8f9fa;
    }
    
    .drop-zone:hover {
      border-color: #007bff;
      background-color: #e7f3ff;
    }
    
    .drop-zone.drag-over {
      border-color: #007bff;
      background-color: #e7f3ff;
      transform: scale(1.02);
    }
    
    .drop-zone i {
      transition: all 0.3s ease;
      display: block;
      margin: 0 auto 0.5rem;
    }
    
    .drop-zone:hover i,
    .drop-zone.drag-over i {
      transform: scale(1.1);
    }
    
    .preview-container {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    
    .preview-item {
      display: inline-block;
    }
    
    .preview-img {
      max-width: 150px;
      max-height: 150px;
      object-fit: cover;
      border: 1px solid #dee2e6;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .preview-name {
      max-width: 150px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  </style>
@endpush
