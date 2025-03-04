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
    <form action="{{ route("staff.update", $staff->id) }}" method="POST">
      @csrf
      @method("PUT")
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
