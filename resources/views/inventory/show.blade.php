@extends("layouts.main") {{-- or the name of your base layout --}}

@section("content")
  <div class="content-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Contact Information</h4>
            <div class="row">
              <!-- Personal Details -->
              <div class="col-md-6">
                <h6 class="text-muted">Personal Details</h6>
                <div class="mb-2">
                  <strong>Firstname:</strong> <span>{{ $item->firstName }}</span>
                </div>
                <div class="mb-2">
                  <strong>Lastname:</strong> <span>{{ $item->lastName }}</span>
                </div>
              </div>
              <!-- Contact Details -->
              <div class="col-md-6">
                <h6 class="text-muted">Contact Details</h6>
                <div class="mb-2">
                  <strong>Mobile Phone:</strong> <span>{{ $item->contactNo }}</span>
                </div>
                <div class="mb-2">
                  <strong>Email Address:</strong> <span>{{ $item->email }}</span>
                </div>
              </div>
            </div>
            <hr>
            <div class="row">
              <!-- Address Details -->
              <div class="col-md-6">
                <h6 class="text-muted">Address</h6>
                <p>{{ $item->address }}</p>
              </div>
              <div class="col-md-6">
                <h6 class="text-muted">Role</h6>
                <p>{{ $item->role == 1 ? "Coordinator" : "Project Manager" }}</p>
              </div>
            </div>
            <!-- Edit & Delete Buttons -->
            <div class="d-flex justify-content-end mt-4">
              <!-- Edit Button -->
              <a href="{{ route("staff.edit", $item->id) }}" class="btn btn-icon btn-warning" data-toggle="tooltip"
                title="Edit Staff">
                <i class="mdi mdi-pencil"> Edit</i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
