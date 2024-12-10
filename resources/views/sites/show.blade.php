@extends("layouts.main") {{-- or the name of your base layout --}}

@section("content")
  <div class="content-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <!-- Personal Details -->
              <div class="col-md-6">
                <div class="mb-2">
                  <strong>Site name</strong> <span>{{ $site->site_name }}</span>
                </div>
                <div class="mb-2">
                  {{-- <strong>Work Order Number:</strong> <span>{{ $site->work_order_number }}</span> --}}
                </div>
              </div>
              <!-- Contact Details -->
              <div class="col-md-6">
                <h6 class="text-muted">Contact Details</h6>
                <div class="mb-2">
                  {{-- <strong>Mobile Phone:</strong> <span>{{ $site->contactNo }}</span> --}}
                </div>
                <div class="mb-2">
                  {{-- <strong>Email Address:</strong> <span>{{ $site->email }}</span> --}}
                </div>
              </div>
            </div>
            <hr>
            <div class="row">
              <!-- Address Details -->
              <div class="col-md-6">
                <h6 class="text-muted">Address</h6>
                {{-- <p>{{ $site->address }}</p> --}}
              </div>
              <div class="col-md-6">
                <h6 class="text-muted">Role</h6>

              </div>
            </div>
            <!-- Edit & Delete Buttons -->
            <div class="d-flex justify-content-end mt-4">
              <!-- Edit Button -->
              <a href="{{ route("sites.edit", $site->id) }}" class="btn btn-icon btn-warning" data-toggle="tooltip"
                title="Edit Site">
                <i class="mdi mdi-pencil"> Edit</i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
