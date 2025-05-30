@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Add Staff</h4>
        <!-- Display validation errors -->
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
        <form class="forms-sample" action="{{ route("staff.store") }}" method="POST">
          @csrf
          <div class="d-none mb-3 hidden"> <!-- Mark it as hidden if needed -->
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="{{ old("username") }}">
          </div>

          <div class="row">
            <!-- Project Selection Dropdown -->
            {{-- <div class="form-group mb-4"> --}}
            <div class="col-md-12">
              <div class="form-group">
                <label for="select_project" class="form-label">Select Project</label>
                <select name="project_id" class="form-select" id="select_project">
                  <option value="">-- Select Project --</option>
                  @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="team_lead" class="form-label">Team Lead</label>
                <select name="manager_id" class="form-select" id="team_lead">
                  <option value="">-- Select Team Lead --</option>
                  @foreach ($teamLeads as $teamLead)
                    <option value="{{ $teamLead->id }}">{{ $teamLead->firstName }} {{ $teamLead->lastName }}</option>
                  @endforeach
                </select>
              </div>
            </div>
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
              <label for="email" class="form-label">Email</label>
              <input type="email" name="email" class="form-control" id="email"
                placeholder="info@dashandots.tech" value="{{ old("email") }}" required>
              @error("email")
              <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
          </div>
          <div class="col-sm-6 col-md-6">
            <div class="form-group">
              <label for="contactNo" class="form-label">Contact Number</label>
              <input type="text" name="contactNo" class="form-control" id="contactNo" placeholder="9649240944"
                value="{{ old("contactNo") }}" required>
              @error("contactNo")
              <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-12 col-md-12">
            <div class="form-group">
              <label for="address" class="form-label">Address</label>
              <input type="text" name="address" class="form-control" id="address" placeholder="Enter staff address"
                value="{{ old("address") }}" required>
              @error("address")
              <small class="text-danger">{{ $message }}</small>
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
                  <option value="2">Project Manager</option>
                  <option value="1">Site Engineer</option>
                  <option value="4">Store Incharge</option>
                  <option value="5">Coordinator</option>
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
                    <option value="{{ $category->id }}">{{ $category->category_code }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>


        <!-- Password Fields with eye toggle -->
        <div class="row">
          <div class="col-sm-6 col-md-6">
            <div class="form-group position-relative">
              <label for="password" class="form-label">Password</label>
              <input type="password" name="password" class="form-control" id="password" placeholder="Password"
                required>
              <span class="position-absolute translate-middle-y end-0 me-3" style="cursor:pointer; top:3rem;"
                onclick="togglePasswordVisibility('password', 'icon-password')">
                <i id="icon-password" class="mdi mdi-eye" style="font-size:1.4rem;"></i>
              </span>
              @error("password")
              <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
          </div>
          <div class="col-sm-6 col-md-6">
            <div class="form-group position-relative">
              <label for="password_confirmation" class="form-label">Confirm Password</label>
              <input type="password" name="password_confirmation" class="form-control" id="password_confirmation"
                placeholder="Confirm Password" required>
              <span class="position-absolute translate-middle-y end-0 me-3" style="cursor:pointer; top:3rem;"
                onclick="togglePasswordVisibility('password_confirmation', 'icon-confirm-password')">
                <i id="icon-confirm-password" class="mdi mdi-eye" style="font-size:1.4rem;"></i>
              </span>
              @error("confirm_password")
              <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Add Staff</button>
      </form>
    </div>
  </div>
</div>
@endsection

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
