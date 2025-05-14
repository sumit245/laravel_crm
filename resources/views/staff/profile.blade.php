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
      <div class="row">
        <div class="col-md-4">
          <div class="photo">
            <img src={{ $user->image }} alt="Profile Photo" class="custom-image">
            <div class="trash">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="red" stroke-width="2">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              </svg>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="upload" id="upload-box" style="cursor: pointer;">
            <input type="file" id="file" name="profile_picture" class="inputfile" hidden />
            <h2>Upload new photo</h2>
            <div class="area">
              <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#ccc">
                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <polyline points="21 15 16 10 5 21"></polyline>
              </svg>
              <p class="text">
                <span class="blue">Upload</span> or drop your file here
              </p>
            </div>
            <p class="info">Accepted file types: .jpg, .jpeg, .png, .gif, .heic, .heif</p>
          </div>
        </div>
      </div>
    </div>
    <div class="d-flex justify-content-end align-items-center mx-2 mt-4">
      <div>
        <button class="btn btn-outline btn-danger mx-2">Cancel</button>
      </div>
      <div>
        <form id="upload-form" action="{{ route("staff.updateProfilePicture") }}" method="POST"
          enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="profile_picture" id="hidden-profile-picture">
          <button type="submit" class="btn save">Save</button>
        </form>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const fileInput = document.getElementById("file");
      const uploadBox = document.getElementById("upload-box");
      const profilePreview = document.getElementById("profile-preview");

      // Open file selector when clicking on the upload div
      uploadBox.addEventListener("click", () => fileInput.click());

      // Preview selected image
      fileInput.addEventListener("change", function(event) {
        const file = event.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            profilePreview.src = e.target.result;
          };
          reader.readAsDataURL(file);
        }
      });
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
    }

    .photo img {
      width: 100%;
      border-radius: 50%;
      display: block
    }

    .trash {
      position: absolute;
      top: 20px;
      right: 60px;
      background: white;
      border: 1px solid #ddd;
      border-radius: 20px;
      width: 40px;
      height: 40px;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer
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
      margin-bottom: 15px
    }

    .area svg {
      margin-bottom: 15px;
      opacity: .5
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
      border: none
    }

    .save {
      background: #8dd3e7;
      color: white
    }
  </style>
@endpush
