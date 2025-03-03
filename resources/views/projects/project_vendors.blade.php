<div class="row">
  <div class="col-md-4">
    <h5>Assigned Vendor</h5>
    <ul>
      @foreach ($assignedVendors as $engineer)
        <li>{{ $engineer->name }}</li>
      @endforeach
    </ul>
  </div>
  <div class="col-md-8">
    <h5>Assign Vendor</h5>
    <form method="POST" action="{{ route("projects.assignStaff", $project->id) }}">
      @csrf
      <table class="table">
        <thead>
          <tr>
            <th>Select</th>
            <th>Name</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($availableVendors as $vendor)
            <tr>
              <td><input type="checkbox" name="user_ids[]" value="{{ $vendor->id }}"></td>
              <td>{{ $vendor->name }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
      <button type="submit" class="btn btn-primary">Assign Selected</button>
    </form>
  </div>
</div>
