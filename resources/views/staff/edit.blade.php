@extends("layouts.main")

@section("content")
  <div class="container p-4">
    <h4>Edit Staff</h4>
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

    <!-- Project Selection Dropdown -->
    <!-- <div class="form-group mb-4">
      <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="team_lead" class="form-label"> </label>
                <select name="team_lead_id" class="form-select" id="team_lead">
                  <option value="">-- Select Project --</option>
                  @foreach ($projects as $category)
                    <option value="{{ $category->project_name }}">{{ $category->project_name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
      </div> 
    </div> -->

    <form action="{{ route("staff.update", $staff->id) }}" method="POST">
      @csrf
      @method("PUT")
      
      <div class="form-group mb-4">
        <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="project_id" class="form-label"> </label>
                <select name="project_id" class="form-select" id="project_id"> --
                  <option value="">-- Select Project --</option>
                  @foreach ($projects as $category)
                    <option value="{{ $category->id }}">{{ $category->project_name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
       </div> 
    </div>

      <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" class="form-control" value="{{ old("name", $staff->name) }}"
          required>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control" value="{{ old("email", $staff->email) }}"
          required>
      </div>
      <div class="form-group">
        <label for="firstName">First Name</label>
        <input type="text" id="firstName" name="firstName" class="form-control"
          value="{{ old("firstName", $staff->firstName) }}" required>
      </div>
      <div class="form-group">
        <label for="lastName">Last Name</label>
        <input type="text" id="lastName" name="lastName" class="form-control"
          value="{{ old("lastName", $staff->lastName) }}" required>
      </div>
      <div class="form-group">
        <label for="category">User Category</label>
        <select id="category" name="category" class="form-control">
            @foreach ($usercategory as $category)
                <option value="{{ $category->category_code }}" 
                    {{ old('category', $staff->category) == $category->category_code ? 'selected' : '' }}>
                    {{ $category->category_code }}
                </option>
            @endforeach
        </select>
      </div>
      <div class= "form-group">
        <label for="address">Address</label>
        <textarea id="address" name="address" class="form-control" required>{{ old("address", $staff->address) }}</textarea>
      </div>
      <div class="form-group">
        <label for="contactNo">Phone</label>
        <input type="text" id="contactNo" name="contactNo" class="form-control"
          value="{{ old("contactNo", $staff->contactNo) }}" required>
      </div>

      <!-- Change Password Button -->
      <div class="form-group">
        <label>Password</label>
        <div>
          <a href="{{ route("staff.change-password", $staff->id) }}" class="btn btn-icon btn-secondary">
            Change Password
          </a>
        </div>
      </div>

      <div class="form-group">
        <button type="submit" class="btn btn-primary">Update Staff</button>
        <a href="{{ route("staff.index") }}" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
@endsection

@push("scripts")
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const projectItems = document.querySelectorAll(".project-item");
      const selectedProjectSpan = document.getElementById('selectedProject');
      const projectIdInput = document.getElementById('project_id');

      projectItems.forEach((item) => {
        item.addEventListener("click", function(event) {
          event.preventDefault();
          const projectId = this.getAttribute("data-project-id");
          const projectName = this.getAttribute('data-project-name');
          selectedProjectSpan.textContent = projectName;
          projectIdInput.value = projectId;
          sessionStorage.setItem("project_name", projectName);
          sessionStorage.setItem("project_id", projectId);
        })
      });
      
      // Load stored project if available
      let storedProjectName = sessionStorage.getItem("project_name");
      let storedProjectId = sessionStorage.getItem("project_id");
      if (storedProjectName) {
        selectedProjectSpan.textContent = storedProjectName;
      }
      if (storedProjectId && !projectIdInput.value) {
        projectIdInput.value = storedProjectId;
      }
    });
  </script>
@endpush