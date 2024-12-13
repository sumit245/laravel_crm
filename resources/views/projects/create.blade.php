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
        <form class="forms-sample" action="{{ route("projects.store") }}" method="POST">
          @csrf
          <div class="form-group">
            <label for="project_name" class="form-label">Project Name</label>
            <input type="text" name="project_name" class="form-control" id="project_name" placeholder="BREDA">
          </div>
          <div class="form-group">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="date" class="form-control" id="start_date" placeholder="{{ date("Y-m-d") }}"
              value="{{ old("date", date("Y-m-d")) }}">
          </div>
          <div class="form-group">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="date" class="form-control" id="end_date" placeholder="{{ date("Y-m-d") }}"
              value="{{ old("date", date("Y-m-d")) }}">
          </div>

          <div class="form-group">
            <label for="work_order_number" class="form-label">Work Order Number</label>
            <input type="text" name="work_order_number" class="form-control" id="work_order_number"
              placeholder="PO202412-01">
          </div>
          <div class="form-group">
            <label for="rate" class="form-label">Rate</label>
            <input type="text" name="rate" class="form-control" id="rate" placeholder="50L">
          </div>

          <div class="form-group">
            <label for="description" class="form-label text-capitalize">description</label>
            <textarea class="form-control" style="height:80px;" id="description" placeholder="Briefly describe" name="description"
              rows="16" cols="50">{{ old("description") }}</textarea>
          </div>

          <button type="submit" class="btn btn-primary">Create Project</button>
        </form>
      </div>
    </div>
  </div>
@endsection

@push("scripts")
  <script>
    // Set today's date as the placeholder and default value
    document.addEventListener("DOMContentLoaded", function() {
      const startDateInput = document.getElementById('start_date');
      const today = new Date().toISOString().split('T')[0]; // Format as YYYY-MM-DD
      startDateInput.placeholder = today;
      startDateInput.value = today; // Set as default value
    });
  </script>
@endpush
