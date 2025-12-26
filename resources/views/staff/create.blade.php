@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">Add Staff</h4>
          <a href="{{ route('staff.index') }}" class="btn btn-light">
            <i class="mdi mdi-arrow-left me-2"></i>Back to Staff
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

        <form class="forms-sample" action="{{ route("staff.store") }}" method="POST">
          @csrf
          <div class="d-none mb-3 hidden"> <!-- Mark it as hidden if needed -->
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="{{ old("username") }}">
          </div>

          <div class="row">
            <!-- Project Selection Dropdown -->
            <div class="col-md-6">
              <div class="form-group">
                <label for="select_project" class="form-label">Select Project</label>
                <select name="project_id" class="form-select" id="select_project" required>
                  <option value="">-- Select Project --</option>
                  @foreach ($projects as $project)
                    <option value="{{ $project->id }}" {{ (string) old('project_id') === (string) $project->id ? 'selected' : '' }}>
                      {{ $project->project_name }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="team_lead" class="form-label">Team Lead</label>
                <select name="manager_id" class="form-select" id="team_lead">
                  <option value="">-- Select Team Lead --</option>
                  @foreach ($teamLeads as $teamLead)
                    <option value="{{ $teamLead->id }}" {{ (string) old('manager_id') === (string) $teamLead->id ? 'selected' : '' }}>
                      {{ $teamLead->firstName }} {{ $teamLead->lastName }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

        <div class="row">
          <div class="col-sm-6 col-md-6">
            <div class="form-group">
              <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
              <input type="text" name="firstName" class="form-control @error('firstName') is-invalid @enderror" id="firstName" placeholder="Ravi"
                value="{{ old("firstName") }}" required>
              @error("firstName")
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="col-sm-6 col-md-6">
            <div class="form-group">
              <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
              <input type="text" name="lastName" class="form-control @error('lastName') is-invalid @enderror" id="lastName" placeholder="Sharma"
                value="{{ old("lastName") }}" required>
              @error("lastName")
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-6 col-md-6">
            <div class="form-group">
              <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email"
                placeholder="info@dashandots.tech" value="{{ old("email") }}" required>
              @error("email")
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="col-sm-6 col-md-6">
            <div class="form-group">
              <label for="contactNo" class="form-label">Contact Number <span class="text-danger">*</span></label>
              <input type="text" name="contactNo" class="form-control @error('contactNo') is-invalid @enderror" id="contactNo" placeholder="9649240944"
                value="{{ old("contactNo") }}" required>
              @error("contactNo")
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-12 col-md-12">
            <div class="form-group">
              <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
              <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" id="address" placeholder="Enter staff address"
                value="{{ old("address") }}" required>
              @error("address")
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="role" class="form-label">Role</label>
                <select name="role" class="form-select" id="role" required>
                  <option value="">-- Select Role --</option>
                  <option value="2" {{ old('role') == 2 ? 'selected' : '' }}>Project Manager</option>
                  <option value="1" {{ old('role') == 1 ? 'selected' : '' }}>Site Engineer</option>
                  <option value="4" {{ old('role') == 4 ? 'selected' : '' }}>Store Incharge</option>
                  <option value="5" {{ old('role') == 5 ? 'selected' : '' }}>Coordinator</option>
                  <option value="11" {{ old('role') == 11 ? 'selected' : '' }}>Guest (Review Meetings Only)</option>
                </select>
              </div>
            </div>
          </div>
          <!-- User category -->
           <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="category" class="form-label">User Category</label>
                <select name="category" class="form-select" id="category">
                  <option value="">-- Select Category --</option>
                  @foreach ($usercategories as $category)
                    <option value="{{ $category->id }}" {{ (string) old('category') === (string) $category->id ? 'selected' : '' }}>
                      {{ $category->category_code }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="department" class="form-label">Department (Free Text)</label>
                <input type="text"
                       name="department"
                       id="department"
                       class="form-control"
                       placeholder="e.g. Operations, HR, Finance"
                       value="{{ old('department') }}">
              </div>
            </div>
          </div>

          <h5 class="mt-4 mb-3">Financial / KYC Details</h5>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="accountName" class="form-label">Account Name</label>
                <input type="text" name="accountName" id="accountName" class="form-control"
                       value="{{ old('accountName') }}" placeholder="Name as per bank account">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="accountNumber" class="form-label">Account Number</label>
                <input type="text" name="accountNumber" id="accountNumber" class="form-control"
                       value="{{ old('accountNumber') }}">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="ifsc" class="form-label">IFSC</label>
                <input type="text" name="ifsc" id="ifsc" class="form-control"
                       value="{{ old('ifsc') }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="bankName" class="form-label">Bank Name</label>
                <input type="text" name="bankName" id="bankName" class="form-control"
                       value="{{ old('bankName') }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="branch" class="form-label">Branch</label>
                <input type="text" name="branch" id="branch" class="form-control"
                       value="{{ old('branch') }}">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="gstNumber" class="form-label">GST Number</label>
                <input type="text" name="gstNumber" id="gstNumber" class="form-control"
                       value="{{ old('gstNumber') }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="pan" class="form-label">PAN</label>
                <input type="text" name="pan" id="pan" class="form-control"
                       value="{{ old('pan') }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="aadharNumber" class="form-label">Aadhar Number</label>
                <input type="text" name="aadharNumber" id="aadharNumber" class="form-control"
                       value="{{ old('aadharNumber') }}">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="status" class="form-label">Status</label>
                <input type="text" name="status" id="status" class="form-control"
                       value="{{ old('status') }}" placeholder="Active / Inactive">
              </div>
            </div>
            <div class="col-md-4 d-flex align-items-center">
              <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" value="1" id="disableLogin" name="disableLogin"
                       {{ old('disableLogin') ? 'checked' : '' }}>
                <label class="form-check-label" for="disableLogin">
                  Disable Login
                </label>
              </div>
            </div>
          </div>


        <!-- Password Fields with eye toggle -->
        <div class="row">
          <div class="col-sm-6 col-md-6">
            <div class="form-group position-relative">
              <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Password"
                required>
              <span class="position-absolute translate-middle-y end-0 me-3" style="cursor:pointer; top:3rem;"
                onclick="togglePasswordVisibility('password', 'icon-password')">
                <i id="icon-password" class="mdi mdi-eye" style="font-size:1.4rem;"></i>
              </span>
              @error("password")
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
            <div class="col-sm-6 col-md-6">
            <div class="form-group position-relative">
              <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
              <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation"
                placeholder="Confirm Password" required>
              <span class="position-absolute translate-middle-y end-0 me-3" style="cursor:pointer; top:3rem;"
                onclick="togglePasswordVisibility('password_confirmation', 'icon-confirm-password')">
                <i id="icon-confirm-password" class="mdi mdi-eye" style="font-size:1.4rem;"></i>
              </span>
              @error("password_confirmation")
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
          <a href="{{ route('staff.index') }}" class="btn btn-light me-2">Cancel</a>
          <button type="submit" class="btn btn-primary">Add Staff</button>
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
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(iconId);

    if (field.type === "password") {
      field.type = "text";
      icon.classList.remove("mdi-eye");
      icon.classList.add("mdi-eye-off");
    } else {
      field.type = "password";
      icon.classList.remove("mdi-eye-off");
      icon.classList.add("mdi-eye");
    }
  }
</script>
@endpush
