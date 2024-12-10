@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Add Projects</h4>

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
        <form class="forms-sample" action="{{ route("projects.update", $project->id) }}" method="POST">
          @csrf
          @method("PUT")
          <div class="form-group">
            <label for="project_name" class="form-label">Project Name</label>
            <input type="text" name="project_name" class="form-control" id="project_name" placeholder="BREDA"
              value="{{ old("project_name", $project->project_name) }}">
          </div>
          <div class="form-group">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" id="start_date" placeholder="{{ date("Y-m-d") }}"
              value="{{ old("start_date", date("Y-m-d")) }}">
          </div>

          <div class="form-group">
            <label for="work_order_number" class="form-label">Work Order Number</label>
            <input type="text" name="work_order_number" class="form-control" id="work_order_number"
              placeholder="PO202412-01" value="{{ old("project_name", $project->work_order_number) }}">
          </div>
          <div class="form-group">
            <label for="rate" class="form-label">Rate</label>
            <input type="text" name="rate" class="form-control" id="rate" placeholder="50L"
              value="{{ old("project_name", $project->rate) }}">
          </div>

          <button type="submit" class="btn btn-primary">Add Item</button>
        </form>
      </div>
    </div>
  </div>
@endsection

{{-- @push
<script>
  // Set today's date as the placeholder and default value
  document.addEventListener("DOMContentLoaded", function() {
    const startDateInput = document.getElementById('start_date');
    const today = new Date().toISOString().split('T')[0]; // Format as YYYY-MM-DD
    startDateInput.placeholder = today;
    startDateInput.value = today; // Set as default value
  });
</script>
@endpush --}}
