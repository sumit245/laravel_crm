@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">Add Vendor</h4>
          <a href="{{ route('uservendors.index') }}" class="btn btn-light">
            <i class="mdi mdi-arrow-left me-2"></i>Back to Vendors
          </a>
        </div>

        <!-- Display validation errors -->
        @if ($errors->any())
          <script>
            const validationErrors = {!! json_encode($errors->all()) !!};
            Swal.fire({
              title: 'Validation errors',
              icon: 'error',
              html: validationErrors.join('<br>'),
              confirmButtonText: 'OK',
              width: '600px'
            });
          </script>
        @endif

        @if (session('success'))
          <script>
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: {!! json_encode(session('success')) !!},
              showConfirmButton: false,
              timer: 4000,
              timerProgressBar: true
            });
          </script>
        @endif

        @if (session('error'))
          <script>
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'error',
              title: {!! json_encode(session('error')) !!},
              showConfirmButton: false,
              timer: 5000,
              timerProgressBar: true
            });
          </script>
        @endif

        <form class="forms-sample" action="{{ route('uservendors.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

          <!-- Project Selection Dropdown -->
          <div class="form-group mb-4">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="select_project" class="form-label">Select Project</label>
                  <select name="project_id" class="form-select" id="select_project">
                    <option value="">-- Select Project --</option>
                    @foreach ($projects as $category)
                      <option value="{{ $category->id }}">{{ $category->project_name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>


          <div class="d-none mb-3 hidden">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="{{ old('username') }}">
          </div>

          <!-- Team Lead Selection -->
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="team_lead" class="form-label">Team Lead</label>
                <select name="manager_id" class="form-select" id="team_lead">
                  <option value="">-- Select Project Manager --</option>
                  @foreach ($siteEngineers as $teamLead)
                    <option value="{{ $teamLead->id }}">{{ $teamLead->firstName }} {{ $teamLead->lastName }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <h6 class="card-subtitle text-bold text-info">Personal details</h6>
          <div class="form-group">
            <label for="name" class="form-label">Vendor Name</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Enter Vendor Name" value="{{ old('name') }}">
            @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>

          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" name="firstName" class="form-control @error('firstName') is-invalid @enderror" id="firstName" placeholder="Ravi" value="{{ old('firstName') }}" required>
                @error('firstName') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="lastName" class="form-control @error('lastName') is-invalid @enderror" id="lastName" placeholder="Sharma" value="{{ old('lastName') }}" required>
                @error('lastName') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="contactNo" class="form-label">Contact Number <span class="text-danger">*</span></label>
                <input type="text" name="contactNo" class="form-control @error('contactNo') is-invalid @enderror" id="contactNo" placeholder="9649240944" value="{{ old('contactNo') }}" required>
                @error('contactNo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" id="address" placeholder="Enter vendor address" value="{{ old('address') }}" required>
                @error('address') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <hr class="my-4" />

          <h6 class="card-subtitle text-bold text-info">Document details</h6>
          <div class="row">
            <!-- Aadhar Section -->
            <div class="col-md-4">
              <div class="form-group mb-3">
                <label for="aadharNum">Aadhar Number</label>
                <input type="text" id="aadharNum" name="aadharNumber" class="form-control @error('aadharNumber') is-invalid @enderror" value="{{ old('aadharNumber') }}">
                @error('aadharNumber') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
              <div class="form-group mb-3">
                <label for="aadharImage">Upload Aadhar Document</label>
                <input type="file" id="aadharImage" name="aadhar_document" class="form-control @error('aadhar_document') is-invalid @enderror" style="padding: 0.4rem; background-color: transparent;">
                @error('aadhar_document') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>

            <!-- GST Section -->
            <div class="col-md-4">
              <div class="form-group mb-3">
                <label for="gstNumber">GST Number</label>
                <input type="text" id="gstNumber" name="gstNumber" class="form-control @error('gstNumber') is-invalid @enderror" value="{{ old('gstNumber') }}">
                @error('gstNumber') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
              <div class="form-group mb-3">
                <label for="gstproof">Upload GST Document</label>
                <input type="file" id="gstproof" name="gst_document" class="form-control @error('gst_document') is-invalid @enderror" style="padding: 0.4rem; background-color: transparent;">
                @error('gst_document') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>

            <!-- PAN Section -->
            <div class="col-md-4">
              <div class="form-group mb-3">
                <label for="panNumber">PAN</label>
                <input type="text" id="panNumber" name="pan" class="form-control @error('pan') is-invalid @enderror" value="{{ old('pan') }}">
                @error('pan') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
              <div class="form-group mb-3">
                <label for="pan_document">Upload PAN Document</label>
                <input type="file" id="pan_document" name="pan_document" class="form-control @error('pan_document') is-invalid @enderror" style="padding: 0.4rem; background-color: transparent;">
                @error('pan_document') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <hr class="my-4" />

          <h6 class="card-title text-bold text-info">Bank details</h6>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="accountName">Account Name</label>
                <input type="text" id="accountName" name="accountName" class="form-control @error('accountName') is-invalid @enderror" value="{{ old('accountName') }}">
                @error('accountName') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="accountNumber">Account Number</label>
                <input type="text" id="accountNumber" name="accountNumber" class="form-control @error('accountNumber') is-invalid @enderror" value="{{ old('accountNumber') }}">
                @error('accountNumber') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="ifsc">IFSC</label>
                <input type="text" id="ifsc" name="ifsc" class="form-control @error('ifsc') is-invalid @enderror" value="{{ old('ifsc') }}">
                @error('ifsc') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="bankName">Bank Name</label>
                <input type="text" id="bankName" name="bankName" class="form-control @error('bankName') is-invalid @enderror" value="{{ old('bankName') }}">
                @error('bankName') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <hr class="my-4" />

          <h6 class="card-subtitle text-bold text-info">Login details</h6>
          <div class="form-group">
            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" placeholder="info@dashandots.tech" value="{{ old('email') }}" required>
            @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>

          <div class="row">
            <div class="col-sm-6">
              <div class="form-group position-relative">
                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control form-control-lg @error('password') is-invalid @enderror" id="password" placeholder="Password" autocomplete="current-password" required>
                <span class="position-absolute translate-middle-y end-0 me-3" style="cursor: pointer; top:3.6rem;" onclick="togglePasswordVisibility('password','password-toggle-icon')">
                  <i id="password-toggle-icon" class="mdi mdi-eye" style="font-size:1.4rem;"></i>
                </span>
                @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group position-relative">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control form-control-lg" id="password_confirmation" placeholder="Confirm Password" autocomplete="current-password" required>
                <span class="position-absolute translate-middle-y end-0 me-3" style="cursor: pointer; top:3.6rem;" onclick="togglePasswordVisibility('password_confirmation','password-toggle-icon-2')">
                  <i id="password-toggle-icon-2" class="mdi mdi-eye" style="font-size:1.4rem;"></i>
                </span>
              </div>
            </div>
          </div>

          <div class="mt-4 d-flex justify-content-end">
            <a href="{{ route('uservendors.index') }}" class="btn btn-light me-2">Cancel</a>
            <button type="submit" class="btn btn-primary">Add Vendor</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push("styles")
  <style>
    /* Consistent card styling to match theme */
    .content-wrapper .card {
      border-radius: 4px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      border: 1px solid #e3e3e3;
    }
  </style>
@endpush

@push("scripts")
  <script>
    function togglePasswordVisibility(fieldId, iconId) {
      const passwordField = document.getElementById(fieldId);
      const toggleIcon = document.getElementById(iconId);

      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('mdi-eye');
        toggleIcon.classList.add('mdi-eye-off');
      } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('mdi-eye-off');
        toggleIcon.classList.add('mdi-eye');
      }
    }
  </script>
@endpush
