@extends("layouts.main")

@section("content")
  <div class="content-wrapper bg-white p-3">
    <div class="d-flex justify-content-between align-items-center">
      <h6>Upload Profile Photo</h6>
      <span class="close">×</span>
    </div>
    <div class="body">
      <div class="name">
        <h6 class="text-uppercase">{{ $user->firstName }} {{ $user->lastName }}</h6>
      </div>
      <form id="upload-form" action="{{ route('staff.updateProfilePicture') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
          <div class="col-md-4">
            <div class="photo">
              <img src="{{ $user->image }}" alt="Profile Photo" class="custom-image" id="current-photo">
              <div class="trash" id="remove-image" style="display: none;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="red" stroke-width="2">
                  <polyline points="3 6 5 6 21 6"></polyline>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="upload" id="uploadTrigger" style="cursor: pointer;">
              <h2>Upload new photo</h2>
              <div class="area drop-area" id="drop-area">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#ccc" id="upload-icon">
                  <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                  <circle cx="8.5" cy="8.5" r="1.5"></circle>
                  <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                <p class="file" id="upload-text">
                  <span class="blue">Upload</span> or drag and drop your file here
                </p>
                <div id="preview-container" style="display: none; width: 100%; text-align: center;">
                  <img id="image-preview" src="/placeholder.svg" alt="Preview" style="max-width: 200px; max-height: 200px; margin: 10px auto;">
                  <p id="file-info" class="mt-2" style="font-size: 14px; color: #666;"></p>
                </div>
              </div>
              <p class="info">Accepted file types: .jpg, .jpeg, .png, .gif, .heic, .heif</p>
              <p class="info">Maximum file size: 2MB</p>

              <!-- Hidden file input -->
              <input type="file" name="profile_picture" id="photoInput" accept="image/*" style="display: none;">
            </div>
          </div>
        </div>

        <!-- Error/Success messages -->
        <div id="message-container" class="mt-3" style="display: none;">
          <div id="message" class="alert"></div>
        </div>

        <div class="d-flex justify-content-end align-items-center mx-2 mt-4">
          <div>
            <button type="button" class="btn btn-outline btn-danger mx-2" id="cancel-button">Cancel</button>
          </div>
          <div>
            <button type="submit" class="btn save" id="submit-button">Save</button>
          </div>
        </div>
      </form>

      <hr class="my-4">

      <div class="row mt-3">
        <div class="col-md-6">
          <h6>Mobile Number</h6>
          <p class="mb-1">
            <strong>Current:</strong> {{ $user->contactNo ?? 'Not set' }}
          </p>

          {{-- Step 1: Request OTP --}}
          <form method="POST" action="{{ route('staff.mobile.send-otp') }}" class="mt-2">
            @csrf
            <div class="mb-2">
              <label for="new_mobile" class="form-label">New Mobile Number</label>
              <input type="text"
                     class="form-control"
                     id="new_mobile"
                     name="new_mobile"
                     placeholder="10-digit mobile number"
                     maxlength="10"
                     pattern="[0-9]{10}"
                     required>
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Send OTP on WhatsApp</button>
          </form>

          {{-- Step 2: Verify OTP --}}
          <form method="POST" action="{{ route('staff.mobile.verify-otp') }}" class="mt-3">
            @csrf
            <div class="mb-2">
              <label for="otp" class="form-label">Enter OTP</label>
              <input type="text"
                     class="form-control"
                     id="otp"
                     name="otp"
                     placeholder="6-digit OTP"
                     maxlength="6"
                     pattern="[0-9]{6}"
                     required>
            </div>
            <button type="submit" class="btn btn-sm btn-success">Verify & Update Mobile</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Elements
      const form = document.getElementById('upload-form');
      const photoInput = document.getElementById('photoInput');
      const uploadTrigger = document.getElementById('uploadTrigger');
      const dropArea = document.getElementById('drop-area');
      const previewContainer = document.getElementById('preview-container');
      const imagePreview = document.getElementById('image-preview');
      const fileInfo = document.getElementById('file-info');
      const uploadIcon = document.getElementById('upload-icon');
      const uploadText = document.getElementById('upload-text');
      const currentPhoto = document.getElementById('current-photo');
      const removeImage = document.getElementById('remove-image');
      const messageContainer = document.getElementById('message-container');
      const message = document.getElementById('message');
      const submitButton = document.getElementById('submit-button');
      const cancelButton = document.getElementById('cancel-button');

      let hasFile = false;
      
      // Click-to-open file dialog
      uploadTrigger.addEventListener('click', () => {
        photoInput.click();
      });

      // File input change handler
      photoInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
          handleFiles(this.files);
        }
      });

      // Drag & Drop handlers
      ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, e => {
          e.preventDefault();
          e.stopPropagation();
          dropArea.classList.add('highlight');
        }, false);
      });

      ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, e => {
          e.preventDefault();
          e.stopPropagation();
          dropArea.classList.remove('highlight');
        }, false);
      });

      dropArea.addEventListener('drop', e => {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files && files.length > 0) {
          photoInput.files = files; // Set dropped files into input
          handleFiles(files);
        }
      });

      // Remove selected image
      removeImage.addEventListener('click', function() {
        resetUploadArea();
      });

      // Cancel button
      cancelButton.addEventListener('click', function() {
        resetUploadArea();
      });

      // Form submission
      form.addEventListener('submit', function(e) {
        if (!hasFile) {
          e.preventDefault();
          showMessage('Please select an image to upload.', 'error');
          return false;
        }

        // Disable submit button to prevent multiple submissions
        submitButton.disabled = true;
        submitButton.textContent = 'Saving...';
      });

      // Handle selected files
      function handleFiles(files) {
        const file = files[0];
        
        // Validate file type
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/heic', 'image/heif'];
        if (!validTypes.includes(file.type)) {
          showMessage('Please select a valid image file (.jpg, .jpeg, .png, .gif, .heic, .heif).', 'error');
          return;
        }
        
        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
          showMessage('File size exceeds 2MB limit. Please select a smaller file.', 'error');
          return;
        }

        // Create and display preview
        const reader = new FileReader();
        reader.onload = function(e) {
          // Update preview image
          imagePreview.src = e.target.result;
          
          // Show file info
          const fileSize = (file.size / 1024 / 1024).toFixed(2);
          fileInfo.textContent = `${file.name} (${fileSize}MB)`;
          
          // Hide upload icon and text, show preview
          uploadIcon.style.display = 'none';
          uploadText.style.display = 'none';
          previewContainer.style.display = 'block';
          
          // Show remove button
          removeImage.style.display = 'flex';
          
          // Update current photo preview
          currentPhoto.src = e.target.result;
          
          // Clear any error messages
          hideMessage();
          
          // Set file flag
          hasFile = true;
        };
        reader.readAsDataURL(file);
      }

      // Reset upload area
      function resetUploadArea() {
        // Clear file input
        photoInput.value = '';
        
        // Reset preview
        imagePreview.src = '';
        previewContainer.style.display = 'none';
        
        // Show upload icon and text
        uploadIcon.style.display = 'block';
        uploadText.style.display = 'block';
        
        // Hide remove button
        removeImage.style.display = 'none';
        
        // Reset current photo
        currentPhoto.src = "{{ $user->image }}";
        
        // Clear file flag
        hasFile = false;
        
        // Hide any messages
        hideMessage();
        
        // Reset submit button
        submitButton.disabled = false;
        submitButton.textContent = 'Save';
      }

      // Show message
      function showMessage(text, type) {
        message.textContent = text;
        message.className = 'alert';
        
        if (type === 'error') {
          message.classList.add('alert-danger');
        } else if (type === 'success') {
          message.classList.add('alert-success');
        }
        
        messageContainer.style.display = 'block';
      }

      // Hide message
      function hideMessage() {
        messageContainer.style.display = 'none';
      }

      // Check for flash messages from the server
      @if(session('success'))
        showMessage("{{ session('success') }}", 'success');
      @endif

      @if(session('error'))
        showMessage("{{ session('error') }}", 'error');
      @endif
    });
  </script>
@endsection

@push("styles")
  <style>
    .header {
      background: #e0e0e0;
      padding: 15px;
      position: relative;
      text-align: center
    }

    .header h1 {
      font-size: 32px;
      font-weight: normal
    }

    .close {
      position: absolute;
      right: 20px;
      top: 15px;
      font-size: 28px;
      cursor: pointer
    }

    .body {
      padding: 20px
    }

    .name {
      font-size: 32px;
      margin-bottom: 20px
    }

    .photo {
      border: 1px solid #ddd;
      border-radius: 50%;
      position: relative;
      overflow: hidden;
      aspect-ratio: 1/1;
    }

    .photo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 50%;
      display: block
    }

    .trash {
      position: absolute;
      top: 20px;
      right: 20px;
      background: white;
      border: 1px solid #ddd;
      border-radius: 20px;
      width: 40px;
      height: 40px;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .trash:hover {
      background: #f8f8f8;
    }

    .upload {
      flex: 1
    }

    .upload h2 {
      font-size: 24px;
      margin-bottom: 15px;
      font-weight: normal
    }

    .area {
      border: 2px dashed #ccc;
      padding: 20px;
      text-align: center;
      min-height: 200px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      margin-bottom: 15px;
      transition: all 0.3s ease;
    }

    .area.highlight {
      border-color: #8dd3e7;
      background-color: rgba(141, 211, 231, 0.1);
    }

    .area svg {
      margin-bottom: 15px;
      opacity: .5;
      transition: opacity 0.3s ease;
    }

    .area:hover svg {
      opacity: .8;
    }

    .text {
      font-size: 18px;
      color: #666
    }

    .blue {
      color: #2196F3
    }

    .info {
      color: #666;
      margin-bottom: 10px;
      font-size: 16px
    }

    .footer {
      padding: 20px;
      display: flex;
      justify-content: space-between;
      border-top: 1px solid #eee
    }

    .btn {
      padding: 10px 25px;
      font-size: 18px;
      border-radius: 4px;
      cursor: pointer;
      border: none;
      transition: all 0.3s ease;
    }

    .btn:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }

    .save {
      background: #8dd3e7;
      color: white
    }

    .save:hover:not(:disabled) {
      background: #7bc0d4;
    }

    .btn-danger {
      color: #dc3545;
      border: 1px solid #dc3545;
    }

    .btn-danger:hover {
      background-color: #dc3545;
      color: white;
    }

    .alert {
      padding: 10px 15px;
      border-radius: 4px;
      margin-bottom: 15px;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    #image-preview {
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
  </style>
@endpush