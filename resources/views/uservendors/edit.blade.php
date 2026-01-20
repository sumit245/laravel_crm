@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">Edit Vendor</h4>
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
        <!-- Project & District Selection -->
        <form class="forms-sample" action="{{ route("uservendors.update", $vendor->id) }}" method="POST">
          @csrf
          @method("PUT")
          <!-- Project and Team Lead -->
           <div class="form-group mb-4">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="project_id" class="form-label">Select Project</label>
                <select name="project_id" class="form-select" id="project_id">
                  <option value="">-- Select Project --</option>
                  @foreach ($projects as $category)
                    <option value="{{ $category->id }}" {{ old('project_id', $vendor->project_id) == $category->id ? 'selected' : '' }}>{{ $category->project_name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="district_id" class="form-label">District (for selected project)</label>
                <select name="district_id" id="district_id" class="form-select">
                  <option value="">-- Select District --</option>
                  @foreach ($districts as $district)
                    <option value="{{ $district->id }}" {{ old('district_id', $primaryDistrictId) == $district->id ? 'selected' : '' }}>
                      {{ $district->name }}
                    </option>
                  @endforeach
                </select>
                <small class="text-muted d-block mt-1">
                  If a project is selected, choose the district where this vendor will primarily work for that project.
                </small>
                @error('district_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

           <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="manager" class="form-label">Team Lead</label>
                <select name="manager_id" class="form-select" id="manager">
                  <option value="">-- Select Project Manager --</option>
                  @foreach ($projectEngineers as $teamLead)
                    <option value="{{ $teamLead->id }}" {{ old('manager_id', $vendor->manager_id) == $teamLead->id ? 'selected' : '' }}>{{ $teamLead->firstName }} {{ $teamLead->lastName }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="d-none mb-3 hidden"> <!-- Mark it as hidden if needed -->
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="{{ old("username") }}">
          </div>

          <h6 class="card-subtitle text-bold text-info">Personal details</h6>
          <div class="form-group">
            <label for="name" class="form-label">Vendor Name</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Enter Vendor Name"
              value="{{ old("name", $vendor->name) }}">
            @error("name")
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="row">
            <div class="col-sm-6 col-md-6">
              <div class="form-group">
                <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" name="firstName" class="form-control @error('firstName') is-invalid @enderror" id="firstName" placeholder="Ravi"
                  value="{{ old("firstName", $vendor->firstName) }}" required>
                @error("firstName")
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-sm-6 col-md-6">
              <div class="form-group">
                <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="lastName" class="form-control @error('lastName') is-invalid @enderror" id="lastName" placeholder="Sharma"
                  value="{{ old("lastName", $vendor->lastName) }}" required>
                @error("lastName")
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-6 col-md-6">
              <div class="form-group">
                <label for="contactNo" class="form-label">Contact Number <span class="text-danger">*</span></label>
                <input type="phone" name="contactNo" class="form-control @error('contactNo') is-invalid @enderror" id="contactNo" placeholder="9649240944"
                  value="{{ old("contactNo", $vendor->contactNo) }}" required>
                @error("contactNo")
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-sm-6 col-md-6">
              <div class="form-group">
                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" id="address"
                  placeholder="Enter vendor address" value="{{ old("address", $vendor->address) }}" required>
                @error("address")
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <small>
              </div>
            </div>
          </div>

          <hr class="my-4" />

          <h6 class="card-subtitle text-bold text-info">Document details</h6>
          <div class="row">
            <div class="col-sm-4 col-md-4 col-lg-4">
              <div class="form-group">
                <label for="aadharNumber">Aadhar Number</label>
                <input type="text" id="aadharNumber" name="aadharNumber"
                  class="form-control @error('aadharNumber') is-invalid @enderror" value="{{ old("aadharNumber", $vendor->aadharNumber) }}">
                @error("aadharNumber")
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-sm-4 col-md-4 col-lg-4">
              <div class="form-group">
                <label for="gstNumber">GST Number</label>
                <input type="text" id="gstNumber" name="gstNumber" class="form-control @error('gstNumber') is-invalid @enderror"
                  value="{{ old("gstNumber", $vendor->gstNumber) }}">
                @error("gstNumber")
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-sm-4 col-md-4 col-lg-4">
              <div class="form-group">
                <label for="pan">PAN</label>
                <input type="text" id="pan" name="pan" class="form-control @error('pan') is-invalid @enderror"
                  value="{{ old("pan", $vendor->pan) }}">
                @error("pan")
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
          <hr class="my-4" />
          <h6 class="card-title text-bold text-info">Bank details</h6>
          <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6">
              <div class="form-group">
                <label for="accountName">Account Name</label>
                <input type="text" id="accountName" name="accountName" class="form-control @error('accountName') is-invalid @enderror"
                  value="{{ old("accountName", $vendor->accountName) }}">
                @error("accountName")
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6">
              <div class="form-group">
                <label for="accountNumber">Account Number</label>
                <input type="text" id="accountNumber" name="accountNumber" class="form-control @error('accountNumber') is-invalid @enderror"
                  value="{{ old("accountNumber", $vendor->accountNumber) }}">
                @error("accountNumber")
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-4 col-sm-4 col-md-4">
              <div class="form-group">
                <label for="ifsc">IFSC</label>
                <input type="text" id="ifsc" name="ifsc" class="form-control @error('ifsc') is-invalid @enderror"
                  value="{{ old("ifsc", $vendor->ifsc) }}">
                @error("ifsc")
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-lg-4 col-sm-4 col-md-4">
              <div class="form-group">
                <label for="bankName">Bank Name</label>
                <input type="text" id="bankName" name="bankName" class="form-control @error('bankName') is-invalid @enderror"
                  value="{{ old("bankName", $vendor->bankName) }}">
                @error("bankName")
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <hr class="my-4" />
          <h6 class="card-subtitle text-bold text-info">Login details</h6>
          <div class="form-group">
            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email"
              placeholder="info@dashandots.tech" value="{{ old("email", $vendor->email) }}" required>
            @error("email")
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-group mt-3">
            <label class="form-label d-block">Password</label>
            <a href="{{ route("staff.change-password", $vendor->id) }}" class="btn btn-outline-secondary btn-sm">
              Change Password
            </a>
          </div>

          <div class="mt-4 d-flex justify-content-end">
            <a href="{{ route('uservendors.index') }}" class="btn btn-light me-2">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Vendor</button>
          </div>
        </form>
      </div>
      </div>
    </div>
  </div>
@endsection

@push("scripts")
  <script>
    $(document).ready(function() {
      // Make district select searchable with Select2
      $('#district_id').select2({
        placeholder: 'Select District',
        allowClear: true,
        width: '100%'
      });
    });

    document.addEventListener("DOMContentLoaded", function() {
      const toggleButton = document.getElementById("togglePasswordSection");
      const passwordSection = document.getElementById("passwordSection");

      toggleButton.addEventListener("click", function() {
        passwordSection.classList.toggle("d-none"); // Toggle visibility
      });
    });

    document.querySelector('.forms-sample').addEventListener('submit', function(e) {
      const formData = new FormData(this);
      const data = {};
      formData.forEach((value, key) => {
        data[key] = value;
      });

      console.log("Submitting Vendor Form Data:");
      console.log(data);
    });

    // function togglePasswordVisibility(fieldId, iconId) {
    //   const passwordField = document.getElementById(fieldId);
    //   const toggleIcon = document.getElementById(iconId);

    //   if (passwordField.type === 'password') {
    //     passwordField.type = 'text';
    //     toggleIcon.classList.remove('mdi-eye');
    //     toggleIcon.classList.add('mdi-eye-off');
    //   } else {
    //     passwordField.type = 'password';
    //     toggleIcon.classList.remove('mdi-eye-off');
    //     toggleIcon.classList.add('mdi-eye');
    //   }
    // }
  </script>
@endpush

@push('styles')
  <style>
    /* Consistent card styling to match theme */
    .content-wrapper .card {
      border-radius: 4px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      border: 1px solid #e3e3e3;
    }
  </style>
@endpush
