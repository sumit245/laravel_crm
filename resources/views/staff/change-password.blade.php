@extends("layouts.main")

@section("content")
  <div class="container p-4">
    <h4>Change Password for {{ $staff->name }}</h4>
    <form action="{{ route("staff.update-password", $staff->id) }}" method="POST">
      @csrf
      <div class="form-group">
        <label for="password">New Password</label>
        <input type="password" id="password" name="password" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="password_confirmation">Confirm Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-primary">Update Password</button>
        <a href="{{ route("staff.index") }}" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
@endsection
