@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">Edit Staff</h4>
          <a href="{{ route('staff.index') }}" class="btn btn-light">
            <i class="mdi mdi-arrow-left me-2"></i>Back to Staff
          </a>
        </div>

        {{-- Validation errors --}}
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

        <form action="{{ route('staff.update', $staff->id) }}" method="POST" class="forms-sample">
          @csrf
          @method('PUT')

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="project_id" class="form-label">Project</label>
                <select name="project_id" id="project_id" class="form-select">
                  <option value="">-- No Project --</option>
                  @foreach ($projects as $project)
                    <option value="{{ $project->id }}"
                      {{ (string) old('project_id', $staff->project_id) === (string) $project->id ? 'selected' : '' }}>
                      {{ $project->project_name }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="manager_id" class="form-label">Team Lead</label>
                <select name="manager_id" id="manager_id" class="form-select" required>
                  <option value="">-- Select Project Manager --</option>
                  @foreach ($projectEngineers as $teamLead)
                    <option value="{{ $teamLead->id }}"
                      {{ (string) old('manager_id', $staff->manager_id) === (string) $teamLead->id ? 'selected' : '' }}>
                      {{ $teamLead->firstName }} {{ $teamLead->lastName }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text"
                       id="firstName"
                       name="firstName"
                       class="form-control @error('firstName') is-invalid @enderror"
                       value="{{ old('firstName', $staff->firstName) }}"
                       required>
                @error('firstName')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text"
                       id="lastName"
                       name="lastName"
                       class="form-control @error('lastName') is-invalid @enderror"
                       value="{{ old('lastName', $staff->lastName) }}"
                       required>
                @error('lastName')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="name" class="form-label">Display Name</label>
                <input type="text"
                       id="name"
                       name="name"
                       class="form-control"
                       value="{{ old('name', $staff->name) }}"
                       required>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $staff->email) }}"
                       required>
                @error('email')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="contactNo" class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="text"
                       id="contactNo"
                       name="contactNo"
                       class="form-control @error('contactNo') is-invalid @enderror"
                       value="{{ old('contactNo', $staff->contactNo) }}"
                       required>
                @error('contactNo')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="role" class="form-label">Role</label>
                <select name="role" id="role" class="form-select">
                  <option value="">-- Select Role --</option>
                  <option value="2" {{ (string) old('role', $staff->role) === '2' ? 'selected' : '' }}>Project Manager</option>
                  <option value="1" {{ (string) old('role', $staff->role) === '1' ? 'selected' : '' }}>Site Engineer</option>
                  <option value="4" {{ (string) old('role', $staff->role) === '4' ? 'selected' : '' }}>Store Incharge</option>
                  <option value="5" {{ (string) old('role', $staff->role) === '5' ? 'selected' : '' }}>Coordinator</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="category" class="form-label">User Category (Department)</label>
                <select id="category" name="category" class="form-select">
                  <option value="">-- Select Category --</option>
                  @foreach ($usercategory as $category)
                    <option value="{{ $category->id }}"
                      {{ (string) old('category', $staff->category) === (string) $category->id ? 'selected' : '' }}>
                      {{ $category->category_code }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="department" class="form-label">Department (Free Text)</label>
                <input type="text"
                       id="department"
                       name="department"
                       class="form-control"
                       value="{{ old('department', $staff->department) }}"
                       placeholder="e.g. Operations, HR, Finance">
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
            <textarea id="address"
                      name="address"
                      class="form-control @error('address') is-invalid @enderror"
                      rows="2"
                      required>{{ old('address', $staff->address) }}</textarea>
            @error('address')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-group mt-3">
            <label class="form-label d-block">Password</label>
            <a href="{{ route('staff.change-password', $staff->id) }}" class="btn btn-outline-secondary btn-sm">
              Change Password
            </a>
          </div>

          <h5 class="mt-4 mb-3">Financial / KYC Details</h5>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="accountName" class="form-label">Account Name</label>
                <input type="text" name="accountName" id="accountName" class="form-control"
                       value="{{ old('accountName', $staff->accountName) }}" placeholder="Name as per bank account">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="accountNumber" class="form-label">Account Number</label>
                <input type="text" name="accountNumber" id="accountNumber" class="form-control"
                       value="{{ old('accountNumber', $staff->accountNumber) }}">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="ifsc" class="form-label">IFSC</label>
                <input type="text" name="ifsc" id="ifsc" class="form-control"
                       value="{{ old('ifsc', $staff->ifsc) }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="bankName" class="form-label">Bank Name</label>
                <input type="text" name="bankName" id="bankName" class="form-control"
                       value="{{ old('bankName', $staff->bankName) }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="branch" class="form-label">Branch</label>
                <input type="text" name="branch" id="branch" class="form-control"
                       value="{{ old('branch', $staff->branch) }}">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="gstNumber" class="form-label">GST Number</label>
                <input type="text" name="gstNumber" id="gstNumber" class="form-control"
                       value="{{ old('gstNumber', $staff->gstNumber) }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="pan" class="form-label">PAN</label>
                <input type="text" name="pan" id="pan" class="form-control"
                       value="{{ old('pan', $staff->pan) }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="aadharNumber" class="form-label">Aadhar Number</label>
                <input type="text" name="aadharNumber" id="aadharNumber" class="form-control"
                       value="{{ old('aadharNumber', $staff->aadharNumber) }}">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="status" class="form-label">Status</label>
                <input type="text" name="status" id="status" class="form-control"
                       value="{{ old('status', $staff->status) }}" placeholder="Active / Inactive">
              </div>
            </div>
            <div class="col-md-4 d-flex align-items-center">
              <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" value="1" id="disableLogin" name="disableLogin"
                       {{ old('disableLogin', $staff->disableLogin) ? 'checked' : '' }}>
                <label class="form-check-label" for="disableLogin">
                  Disable Login
                </label>
              </div>
            </div>
          </div>

          <div class="mt-4 d-flex justify-content-end">
            <a href="{{ route('staff.index') }}" class="btn btn-light me-2">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Staff</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

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
