@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Add Vendor</h4>
        <form class="forms-sample" action="{{ route("uservendors.store") }}" method="POST">
          @csrf
          <div class="d-none mb-3 hidden"> <!-- Mark it as hidden if needed -->
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="{{ old("username") }}">
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="team_lead" class="form-label">Team Lead</label>
                <select name="team_lead_id" class="form-select" id="team_lead">
                  <option value="">-- Select Site Engineer --</option>
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
            <input type="text" name="name" class="form-control" id="name" placeholder="Enter Vendor Name"
              value="{{ old("name") }}">
            @error("name")
              <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>

          <div class="row">
            <div class="col-sm-6 col-md-6">
              <div class="form-group">
                <label for="firstName" class="form-label">First Name</label>
                <input type="text" name="firstName" class="form-control" id="firstName" placeholder="Ravi"
                  value="{{ old("firstName") }}" required>
                @error("firstName")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
            <div class="col-sm-6 col-md-6">
              <div class="form-group">
                <label for="lastName" class="form-label">Last Name</label>
                <input type="text" name="lastName" class="form-control" id="lastName" placeholder="Sharma"
                  value="{{ old("lastName") }}" required>
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
                  value="{{ old("contactNo") }}" required>
                @error("contactNo")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
            <div class="col-sm-6 col-md-6">
              <div class="form-group">
                <label for="address" class="form-label">Address</label>
                <input type="text" name="address" class="form-control" id="address"
                  placeholder="Enter vendor address" value="{{ old("address") }}" required>
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
                  class="form-control"value="{{ old("aadharNumber") }}">
                @error("aadharNumber")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
              <div class="form-group">
                <label for="aadharImage" class="form-label">Upload Document</label>
                <div class="custom-file">
                  <input type="file" id="aadharImage" name="aadharImage" class="custom-file-input">
                  <label class="custom-file-label" for="aadharImage">Choose file</label>
                </div>
                @error("aadharNumberImage")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
            <div class="col-sm-4 col-md-4 col-lg-4">
              <div class="form-group">
                <label for="gstNumber">GST Number</label>
                <input type="text" id="gstNumber" name="gstNumber" class="form-control"
                  value="{{ old("gstNumber") }}">
                @error("gstNumber")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
              <div class="form-group">
                <label for="aadharNumberImage" class="form-label">Upload Document</label>
                <input type="file" id="aadharNumberImage" name="aadharNumber" class="form-control">
                @error("aadharNumberImage")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
            <div class="col-sm-4 col-md-4 col-lg-4">
              <div class="form-group">
                <label for="panNumber">PAN</label>
                <input type="text" id="panNumber" name="pan" class="form-control"
                  value="{{ old("panNumber") }}">
                @error("panNumber")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
              <div class="form-group">
                <label for="aadharNumberImage" class="form-label">Upload Document</label>
                <input type="file" id="aadharNumberImage" name="aadharNumber" class="form-control">
                @error("aadharNumberImage")
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
                  value="{{ old("accountName") }}">
                @error("accountName")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6">
              <div class="form-group">
                <label for="accountNumber">Account Number</label>
                <input type="text" id="accountNumber" name="accountNumber" class="form-control"
                  value="{{ old("accountNumber") }}">
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
                  value="{{ old("ifsc") }}">
                @error("ifsc")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
            <div class="col-lg-4 col-sm-4 col-md-4">
              <div class="form-group">
                <label for="bankName">Bank Name</label>
                <input type="text" id="bankName" name="bankName" class="form-control"
                  value="{{ old("bankName") }}">
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
              placeholder="info@dashandots.tech" value="{{ old("email") }}" required>
            @error("email")
              <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>

          <div class="row">
            <div class="col-sm-6 col-md-6">
              <div class="form-group position-relative">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control form-control-lg" id="password"
                  placeholder="Password" autocomplete="current-password" required>
                <span class="position-absolute translate-middle-y end-0 me-3" style="cursor: pointer; top:3.6rem;"
                  onclick="togglePasswordVisibility('password','password-toggle-icon')">
                  <i id="password-toggle-icon" class="mdi mdi-eye" style="font-size:1.4rem;"></i>
                </span>
                @error("password")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
            <div class="col-sm-6 col-md-6">
              <div class="form-group position-relative">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control form-control-lg"
                  id="password_confirmation" placeholder="Password" autocomplete="current-password" required>
                <span class="position-absolute translate-middle-y end-0 me-3" style="cursor: pointer; top:3.6rem;"
                  onclick="togglePasswordVisibility('password_confirmation','password-toggle-icon-2')">
                  <i id="password-toggle-icon-2" class="mdi mdi-eye" style="font-size:1.4rem;"></i>
                </span>
                @error("confirmed")
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Add Vendor</button>
        </form>
      </div>
    </div>
  </div>
@endsection

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
