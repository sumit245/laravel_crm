@extends("layouts.main") {{-- or the name of your base layout --}}

@section("content")
  <div class="content-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Vendors Information</h4>
            <div class="row">
              <!-- Personal Details -->
              <div class="col-md-6">
                <h6 class="text-muted">Personal Details</h6>
                <div class="mb-2">
                  <strong>Firstname:</strong> <span>{{ $vendor->firstName }}</span>
                </div>
                <div class="mb-2">
                  <strong>Lastname:</strong> <span>{{ $vendor->lastName }}</span>
                </div>
              </div>
              <!-- Contact Details -->
              <div class="col-md-6">
                <h6 class="text-muted">Contact Details</h6>
                <div class="mb-2">
                  <strong>Mobile Phone:</strong> <span>{{ $vendor->contactNo }}</span>
                </div>
                <div class="mb-2">
                  <strong>Email Address:</strong> <span>{{ $vendor->email }}</span>
                </div>
              </div>
            </div>
            <hr>
            <div class="row">
              <!-- Address Details -->
              <div class="col-md-6">
                <h6 class="text-muted">Address</h6>
                <p>{{ $vendor->address }}</p>
              </div>
              <div class="col-md-6">

              </div>
            </div>
            <!-- Edit & Delete Buttons -->
            <div class="d-flex justify-content-end mt-4">
              <!-- Edit Button -->
              <a href="{{ route("uservendors.edit", $vendor->id) }}" class="btn btn-icon btn-warning"
                data-toggle="tooltip" title="Edit Vendor">
                <i class="mdi mdi-pencil"> Edit</i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
