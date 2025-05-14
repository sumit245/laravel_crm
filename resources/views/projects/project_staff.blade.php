@php
  $roles = [
      1 => "Site Engineer",
      2 => "Project Manager",
      3 => "Vendor",
      4 => "Store Incharge",
      5 => "Coordinator",
  ];
@endphp
<div class="row">
  <div class="col-md-4">
    <h5>Assigned Staff</h5>
    <ul>
      @if ($assignedEngineersMessage)
        <p>{{ $assignedEngineersMessage }}</p>
      @else
        @foreach ($assignedEngineers as $engineer)
          <li>{{ $engineer->firstName }} {{ $engineer->lastName }}</li>
        @endforeach
      @endif
    </ul>
  </div>
  <div class="col-md-8">
    <h5>Assign Staff</h5>
    <form method="POST" action="{{ route("projects.assignStaff", $project->id) }}">
      @csrf
      <table class="table">
        <thead>
          <tr>
            <th>Select</th>
            <th>Name</th>
            <th>Role</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($availableEngineers as $staff)
            <tr>
              <td><input type="checkbox" name="user_ids[]" value="{{ $staff->id }}"></td>
              <td>{{ $staff->firstName }} {{ $staff->lastName }}</td>
              <td>{{ $roles[$staff->role] ?? "Unknown" }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
      <button type="submit" class="btn btn-primary">Assign Selected</button>
    </form>
  </div>
</div>
