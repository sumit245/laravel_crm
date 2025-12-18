@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-4">Edit Staff</h4>

        {{-- Validation errors --}}
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
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
                <label for="firstName" class="form-label">First Name</label>
                <input type="text"
                       id="firstName"
                       name="firstName"
                       class="form-control"
                       value="{{ old('firstName', $staff->firstName) }}"
                       required>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="lastName" class="form-label">Last Name</label>
                <input type="text"
                       id="lastName"
                       name="lastName"
                       class="form-control"
                       value="{{ old('lastName', $staff->lastName) }}"
                       required>
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
                <label for="email" class="form-label">Email</label>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control"
                       value="{{ old('email', $staff->email) }}"
                       required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="contactNo" class="form-label">Phone</label>
                <input type="text"
                       id="contactNo"
                       name="contactNo"
                       class="form-control"
                       value="{{ old('contactNo', $staff->contactNo) }}"
                       required>
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
            <label for="address" class="form-label">Address</label>
            <textarea id="address"
                      name="address"
                      class="form-control"
                      rows="2"
                      required>{{ old('address', $staff->address) }}</textarea>
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
