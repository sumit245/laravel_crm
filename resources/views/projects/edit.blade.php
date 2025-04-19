@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Update Projects</h4>

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
            <label for="state" class="form-label" style="padding: 0px;">Select State</label>
            <select name="project_in_state" class="form-select select2" id="state" style="width: 100%;">
              <option value="" disabled {{ old("project_in_state", $project->project_in_state) ? '' : 'selected' }}>
                -- Select State --
              </option>
              @foreach ($states as $state)
                <option value="{{ $state->id }}"
                  {{ old("project_in_state", $project->project_in_state) == $state->id ? "selected" : "" }}>
                  {{ $state->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" id="start_date" placeholder="{{ date("Y-m-d") }}"
              value="{{ old("start_date", $project->start_date) }}">
          </div>

          <div class="form-group">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" id="end_date" placeholder="{{ date("Y-m-d") }}"
              value="{{ old("end_date", $project->end_date) }}">
          </div>

          <div class="form-group">
            <label for="work_order_number" class="form-label">Work Order Number</label>
            <input type="text" name="work_order_number" class="form-control" id="work_order_number"
              placeholder="PO202412-01" value="{{ old("work_order_number", $project->work_order_number) }}">
          </div>

          <div class="form-group">
            <label for="rate" class="form-label">Rate (per kW including all taxes and duties in rupees)</label>
            <input type="number" step="0.01" name="rate" class="form-control" id="rate" placeholder="50L"
              value="{{ old("rate", $project->rate) }}">
          </div>

          <div class="form-group">
            <label for="project_capacity" class="form-label">Project Capacity (kW)</label>
            <input type="number" step="0.01" name="project_capacity" class="form-control" id="project_capacity"
              placeholder="50kW" value="{{ old("project_capacity", $project->project_capacity) }}">
          </div>

          <div class="form-group">
            <label for="total" class="form-label">Total</label>
            <input type="number" step="0.01" name="total" class="form-control" id="total" placeholder="5000.82"
              value="{{ old("total", $project->total) }}" readonly>
          </div>

          <div class="form-group">
            <label for="description" class="form-label text-capitalize">Description</label>
            <textarea class="form-control" style="height:100px;" id="description" placeholder="Briefly describe"
              name="description" rows="20" cols="50">{{ old("description", $project->description) }}</textarea>
          </div>

          <button type="submit" class="btn btn-primary">Update Project</button>
        </form>
      </div>
    </div>
  </div>
@endsection

@push("styles")
  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <style>
    /* Custom padding fix for Select2 input span */
    .select2-container--default .select2-selection--single {
      padding: 0px;
      height: 38px;
      display: flex;
      align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear{
    cursor: pointer;
    display: none;
    }
  </style>
@endpush

@push("scripts")
  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const startDateInput = document.getElementById('start_date');
      const endDateInput = document.getElementById('end_date');
      const rateInput = document.getElementById('rate');
      const projectCapacityInput = document.getElementById('project_capacity');
      const totalInput = document.getElementById('total');

      const today = new Date().toISOString().split('T')[0];
      startDateInput.placeholder = today;
      endDateInput.placeholder = today;

      startDateInput.addEventListener('change', function () {
        endDateInput.min = startDateInput.value;
      });

      function calculateTotal() {
        const rate = parseFloat(rateInput.value) || 0;
        const capacity = parseFloat(projectCapacityInput.value) || 0;
        totalInput.value = (rate * capacity).toFixed(2);
      }

      rateInput.addEventListener('input', calculateTotal);
      projectCapacityInput.addEventListener('input', calculateTotal);

      endDateInput.min = startDateInput.value;
    });

    $(document).ready(function () {
      $('#state').select2({
        placeholder: '-- Select State --',
        allowClear: true
      });
    });
  </script>
@endpush