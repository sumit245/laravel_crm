@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
        <h4 class="card-title">Edit Vendor</h4>
        <!-- Project Selection Dropdown -->
      
        
        <form class="forms-sample" action="{{ route("uservendors.update", $vendor->id) }}" method="POST">
          @csrf
          @method("PUT")
          <!-- Project and Team Lead -->
           <div class="form-group mb-4">
          <div class="row">
            <div class="col-md-12">
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
            <input type="text" name="name" class="form-control" id="name" placeholder="Enter Vendor Name"
              value="{{ old("name", $vendor->name) }}">
            @error("name")
              <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>

          <div class="row">
            <div class="col-sm-6 col-md-6">
              <div class="form-group">
                <label for="firstName" class="form-label">First Name</label>
                <input type="text" name="firstName" class="form-control" id="firstName" placeholder="Ravi"
                  value="{{ old("firstName", $vendor->firstName) }}" required>
                @error("firstName")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
            <div class="col-sm-6 col-md-6">
              <div class="form-group">
                <label for="lastName" class="form-label">Last Name</label>
                <input type="text" name="lastName" class="form-control" id="lastName" placeholder="Sharma"
                  value="{{ old("lastName", $vendor->lastName) }}" required>
                @error("lastName")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-6 col-md-6">
              <div class="form-group">
                <label for="contactNo" class="form-label">Contact Number</label>
                <input type="phone" name="contactNo" class="form-control" id="contactNo" placeholder="9649240944"
                  value="{{ old("contactNo", $vendor->contactNo) }}" required>
                @error("contactNo")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
            <div class="col-sm-6 col-md-6">
              <div class="form-group">
                <label for="address" class="form-label">Address</label>
                <input type="text" name="address" class="form-control" id="address"
                  placeholder="Enter vendor address" value="{{ old("address", $vendor->address) }}" required>
                @error("address")
                  <small class="text-danger">{{ $message }}</small>
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
                  class="form-control"value="{{ old("aadharNumber", $vendor->aadharNumber) }}">
                @error("aadharNumber")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
            <div class="col-sm-4 col-md-4 col-lg-4">
              <div class="form-group">
                <label for="gstNumber">GST Number</label>
                <input type="text" id="gstNumber" name="gstNumber" class="form-control"
                  value="{{ old("gstNumber", $vendor->gstNumber) }}">
                @error("gstNumber")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
            <div class="col-sm-4 col-md-4 col-lg-4">
              <div class="form-group">
                <label for="pan">PAN</label>
                <input type="text" id="pan" name="pan" class="form-control"
                  value="{{ old("pan", $vendor->pan) }}">
                @error("pan")
                  <small class="text-danger">{{ $message }}</small>
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
                <input type="text" id="accountName" name="accountName" class="form-control"
                  value="{{ old("accountName", $vendor->accountName) }}">
                @error("accountName")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6">
              <div class="form-group">
                <label for="accountNumber">Account Number</label>
                <input type="text" id="accountNumber" name="accountNumber" class="form-control"
                  value="{{ old("accountNumber", $vendor->accountNumber) }}">
                @error("accountNumber")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-4 col-sm-4 col-md-4">
              <div class="form-group">
                <label for="ifsc">IFSC</label>
                <input type="text" id="ifsc" name="ifsc" class="form-control"
                  value="{{ old("ifsc", $vendor->ifsc) }}">
                @error("ifsc")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
            <div class="col-lg-4 col-sm-4 col-md-4">
              <div class="form-group">
                <label for="bankName">Bank Name</label>
                <input type="text" id="bankName" name="bankName" class="form-control"
                  value="{{ old("bankName", $vendor->bankName) }}">
                @error("bankName")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
          </div>

          <hr class="my-4" />
          <h6 class="card-subtitle text-bold text-info">Login details</h6>
          <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control" id="email"
              placeholder="info@dashandots.tech" value="{{ old("email", $vendor->email) }}" required>
            @error("email")
              <small class="text-danger">{{ $message }}</small>
            @enderror

            <div class="d-flex justify-content-between">
              {{-- <div class="col-md-7"></div> --}}
              <div></div>
              {{-- <div classs="col-md-5"> --}}
              <div class="d-flex">
                <button type="submit" class="btn btn-primary mx-2 mb-3">Update Vendor</button>
                <!-- Editting password for the vendor made a common method for all to change the password -->
                <a href="{{ route("staff.change-password", $vendor->id) }}" class="btn btn-secondary mx-2 mb-3">
                  Change Password
                </a>
                {{-- </div> --}}
                <div>
                </div>
              </div>
        </form>
      </div>
      </div>
    </div>
  </div>
@endsection

@push("scripts")
  <script>
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
