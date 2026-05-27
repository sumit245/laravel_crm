@extends("layouts.main")

@section("content")
  @php
    $profilePhoto = $user->image ?: asset("images/faces/face8.jpg");
    $pendingMobile = session("mobile_change_mobile");
    $hasPendingMobileOtp = (bool) session("mobile_change_otp") && $pendingMobile;
  @endphp

  <div class="content-wrapper bg-white p-3 staff-profile-page">
    <div class="profile-page-header">
      <h1>Profile Settings</h1>
      <button type="button" class="profile-close-button" id="profile-close-button" aria-label="Close profile settings">
        <span aria-hidden="true">×</span>
      </button>
    </div>
    <div class="body">
      <div class="name">
        <h6 class="text-uppercase">{{ $user->firstName }} {{ $user->lastName }}</h6>
      </div>

      <form method="POST" action="{{ route('staff.updateProfileDetails') }}" class="profile-details-form">
        @csrf
        <div class="row">
          <div class="col-md-4 mb-3">
            <label for="firstName" class="form-label">First Name</label>
            <input type="text"
                   class="form-control @error('firstName') is-invalid @enderror"
                   id="firstName"
                   name="firstName"
                   value="{{ old('firstName', $user->firstName) }}"
                   maxlength="25"
                   required>
            @error('firstName')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-4 mb-3">
            <label for="lastName" class="form-label">Last Name</label>
            <input type="text"
                   class="form-control @error('lastName') is-invalid @enderror"
                   id="lastName"
                   name="lastName"
                   value="{{ old('lastName', $user->lastName) }}"
                   maxlength="25"
                   required>
            @error('lastName')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-4 mb-3 profile-details-actions">
            <button type="submit" class="btn save">Update Name</button>
          </div>
        </div>
      </form>

      <hr class="my-4">

      <form id="upload-form" action="{{ route('staff.updateProfilePicture') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
          <div class="col-md-4">
            <div class="photo">
              <img src="{{ $profilePhoto }}" alt="Profile photo for {{ $user->firstName }} {{ $user->lastName }}" class="custom-image" id="current-photo">
              <button type="button" class="trash" id="remove-image" style="display: none;" aria-label="Remove selected profile photo">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <polyline points="3 6 5 6 21 6"></polyline>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
              </button>
            </div>
          </div>

          <div class="col-md-6">
            <div class="upload">
              <h2>Upload new photo</h2>
              <label class="area drop-area" id="drop-area" for="photoInput" role="button" tabindex="0" aria-label="Choose a new profile photo" aria-describedby="upload-help">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" id="upload-icon" aria-hidden="true">
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
              </label>
              <div id="upload-help">
                <p class="info">Accepted file types: .jpg, .jpeg, .png, .gif, .heic, .heif</p>
                <p class="info">Maximum file size: 2MB</p>
              </div>

              <input type="file" name="profile_picture" id="photoInput" accept="image/*" class="visually-hidden-file" tabindex="-1" aria-hidden="true">
            </div>
          </div>
        </div>

        <!-- Error/Success messages -->
        <div id="message-container" class="mt-3" style="display: none;">
          <div id="message" class="alert" role="alert" aria-live="polite" tabindex="-1"></div>
        </div>

        <div class="d-flex justify-content-end align-items-center mx-2 mt-4">
          <div>
            <button type="button" class="btn profile-cancel-button mx-2" id="cancel-button">Cancel</button>
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
            <button type="submit" class="btn btn-sm btn-primary">{{ $hasPendingMobileOtp ? 'Send New OTP' : 'Send OTP on WhatsApp' }}</button>
          </form>

          @if($hasPendingMobileOtp)
            <div class="otp-status" role="status">
              OTP sent to <strong>{{ $pendingMobile }}</strong>. It expires in 10 minutes.
            </div>

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
                       inputmode="numeric"
                       autocomplete="one-time-code"
                       required>
              </div>
              <button type="submit" class="btn btn-sm btn-success">Verify & Update Mobile</button>
            </form>
          @else
            <p class="otp-help mt-3">Verification appears after an OTP is sent.</p>
          @endif
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Elements
      const form = document.getElementById('upload-form');
      const photoInput = document.getElementById('photoInput');
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
      const closeButton = document.getElementById('profile-close-button');

      let hasFile = false;
      
      closeButton.addEventListener('click', function() {
        if (window.history.length > 1) {
          window.history.back();
          return;
        }

        window.location.href = "{{ route('dashboard') }}";
      });

      dropArea.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          photoInput.click();
        }
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
          photoInput.value = '';
          hasFile = false;
          showMessage('Please select a valid image file (.jpg, .jpeg, .png, .gif, .heic, .heif).', 'error');
          return;
        }
        
        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
          photoInput.value = '';
          hasFile = false;
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
        currentPhoto.src = "{{ $profilePhoto }}";
        
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
        message.focus();
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
    .staff-profile-page {
      color: #1f2528;
    }

    .staff-profile-page .profile-page-header {
      align-items: center;
      display: flex;
      justify-content: space-between;
      gap: 16px;
    }

    .staff-profile-page .profile-page-header h1 {
      font-size: 18px;
      font-weight: 600;
      margin: 0;
    }

    .staff-profile-page .profile-close-button {
      align-items: center;
      background: transparent;
      border: 1px solid transparent;
      border-radius: 4px;
      color: #4c555a;
      cursor: pointer;
      display: inline-flex;
      font-size: 28px;
      height: 44px;
      justify-content: center;
      line-height: 1;
      width: 44px;
    }

    .staff-profile-page .profile-close-button:hover,
    .staff-profile-page .profile-close-button:focus {
      background: #eef4f6;
      border-color: #cbd8dd;
      outline: none;
    }

    .staff-profile-page .body {
      padding: 20px
    }

    .staff-profile-page .name {
      font-size: 32px;
      margin-bottom: 20px
    }

    .staff-profile-page .profile-details-form {
      max-width: 820px;
    }

    .staff-profile-page .profile-details-actions {
      align-items: flex-end;
      display: flex;
    }

    .staff-profile-page .photo {
      border: 1px solid #ddd;
      border-radius: 50%;
      position: relative;
      overflow: hidden;
      aspect-ratio: 1/1;
    }

    .staff-profile-page .photo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 50%;
      display: block
    }

    .staff-profile-page .trash {
      position: absolute;
      top: 20px;
      right: 20px;
      background: white;
      border: 1px solid #ddd;
      border-radius: 20px;
      color: #b42318;
      width: 40px;
      height: 40px;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .staff-profile-page .trash:hover,
    .staff-profile-page .trash:focus {
      background: #f8f8f8;
      outline: 2px solid #8dd3e7;
      outline-offset: 2px;
    }

    .staff-profile-page .upload {
      flex: 1
    }

    .staff-profile-page .upload h2 {
      font-size: 24px;
      margin-bottom: 15px;
      font-weight: normal
    }

    .staff-profile-page .area {
      border: 2px dashed #ccc;
      color: #8d989e;
      cursor: pointer;
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

    .staff-profile-page .area.highlight,
    .staff-profile-page .area:focus {
      border-color: #8dd3e7;
      background-color: rgba(141, 211, 231, 0.1);
      outline: none;
    }

    .staff-profile-page .area svg {
      margin-bottom: 15px;
      opacity: .5;
      transition: opacity 0.3s ease;
    }

    .staff-profile-page .area:hover svg,
    .staff-profile-page .area:focus svg {
      opacity: .8;
    }

    .staff-profile-page .text {
      font-size: 18px;
      color: #666
    }

    .staff-profile-page .blue {
      color: #1155cc
    }

    .staff-profile-page .info {
      color: #666;
      margin-bottom: 10px;
      font-size: 16px
    }

    .staff-profile-page .footer {
      padding: 20px;
      display: flex;
      justify-content: space-between;
      border-top: 1px solid #eee
    }

    .staff-profile-page .btn {
      padding: 10px 25px;
      font-size: 18px;
      border-radius: 4px;
      cursor: pointer;
      border: none;
      transition: all 0.3s ease;
    }

    .staff-profile-page .btn:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }

    .staff-profile-page .save {
      background: #137b91;
      color: #f8fbfc
    }

    .staff-profile-page .save:hover:not(:disabled),
    .staff-profile-page .save:focus {
      background: #0d6578;
      outline: 2px solid #8dd3e7;
      outline-offset: 2px;
    }

    .staff-profile-page .profile-cancel-button {
      color: #dc3545;
      border: 1px solid #dc3545;
      background: #fffafa;
    }

    .staff-profile-page .profile-cancel-button:hover,
    .staff-profile-page .profile-cancel-button:focus {
      background-color: #dc3545;
      color: white;
      outline: 2px solid #f1aeb5;
      outline-offset: 2px;
    }

    .staff-profile-page .alert {
      padding: 10px 15px;
      border-radius: 4px;
      margin-bottom: 15px;
    }

    .staff-profile-page .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .staff-profile-page .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .staff-profile-page #image-preview {
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .staff-profile-page .visually-hidden-file {
      display: none;
    }

    .staff-profile-page .otp-status {
      background: #eef8fb;
      border: 1px solid #b9dfe8;
      border-radius: 4px;
      color: #194d5a;
      margin-top: 14px;
      padding: 10px 12px;
    }

    .staff-profile-page .otp-help {
      color: #66727a;
      font-size: 14px;
      margin-bottom: 0;
    }

    @media (max-width: 767.98px) {
      .staff-profile-page .body {
        padding: 12px 0;
      }

      .staff-profile-page .photo {
        margin: 0 auto 24px;
        max-width: 220px;
      }

      .staff-profile-page .upload h2 {
        font-size: 20px;
      }

      .staff-profile-page .btn {
        width: 100%;
      }

      .staff-profile-page .profile-details-actions {
        align-items: stretch;
      }

      .staff-profile-page .d-flex.justify-content-end {
        align-items: stretch !important;
        flex-direction: column-reverse;
        gap: 10px;
        margin-left: 0 !important;
        margin-right: 0 !important;
      }

      .staff-profile-page .d-flex.justify-content-end > div {
        width: 100%;
      }

      .staff-profile-page .profile-cancel-button {
        margin-left: 0 !important;
        margin-right: 0 !important;
      }
    }
  </style>
@endpush
