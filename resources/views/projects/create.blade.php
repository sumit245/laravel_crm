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

          <!-- Project Type -->
          <div class="form-group">
            <label for="project_type" class="form-label">Select Project Type</label>
            <select name="project_type" class="form-select" id="project_type" required>
              <option value="" disabled selected>-- Select Project Type --</option>
              <option value="0">Rooftop Installation</option>
              <option value="1">Streetlight Installation</option>
            </select>
          </div>

          <!-- Agreement Fields (Only for Streetlight) -->
          <div id="agreement_fields" style="display: none;">
            <div class="form-group">
              <label for="agreement_number" class="form-label">Agreement Number</label>
              <input type="text" name="agreement_number" class="form-control" id="agreement_number"
                placeholder="AG123456">
            </div>

           <div class="form-group">
  <label for="agreement_date" class="form-label">Agreement Date</label>
  <div style="width: 100%;" onclick="document.getElementById('agreement_date').showPicker()">
    <input type="date" name="agreement_date" class="form-control" id="agreement_date"
      style="pointer-events: none; background-color: white;"
      value="{{ old('agreement_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
  </div>
</div>
          </div>

          <!-- Other Fields -->
          <div class="form-group">
            <label for="project_name" class="form-label">Project Name</label>
            <input type="text" name="project_name" class="form-control" id="project_name" placeholder="BREDA"
              value="{{ old("project_name") }}">
          </div>

          <div class="form-group">
            <label for="state" class="form-label">Select State</label>
            <select name="project_in_state" class="form-select" id="state">
              <option value="" disabled selected>-- Select State --</option>
              @foreach ($states as $state)
                <option value="{{ $state->id }}" {{ old("state") == $state->id ? "selected" : "" }}>
                  {{ $state->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
  <label for="start_date" class="form-label">Start Date</label>
  <div onclick="document.getElementById('start_date').showPicker()" style="width: 100%;">
    <input type="date" name="start_date" class="form-control" id="start_date"
      value="{{ old('date', date('Y-m-d')) }}">
  </div>
</div>


        <div class="form-group" onclick="document.getElementById('end_date').showPicker()" style="cursor: pointer;">
  <label for="end_date" class="form-label">End Date</label>
  <input type="date" name="end_date" class="form-control" id="end_date"
    value="{{ old('end_date', date('Y-m-d')) }}">
</div>

          <div class="form-group">
            <label for="work_order_number" class="form-label">Work Order Number</label>
            <input type="text" name="work_order_number" class="form-control" id="work_order_number"
              placeholder="PO202412-01" value="{{ old("work_order_number") }}">
          </div>

          <div class="form-group">
            <label for="rate" class="form-label">Rate (Excluding all taxes and duties in rupees)</label>
            <input type="number" step="0.01" name="rate" class="form-control" id="rate" placeholder="50L"
              value="{{ old("rate") }}">
          </div>

          <div class="form-group">
            <label for="project_capacity" class="form-label">Project Capacity</label>
            <input type="number" step="0.01" name="project_capacity" class="form-control" id="project_capacity"
              placeholder="50kW" value="{{ old("project_capacity") }}">
          </div>

          <div class="form-group">
            <label for="total" class="form-label">Total (INR)</label>
            <input type="number" step="0.01" max="999999999999.99" name="total" class="form-control" id="total"
              placeholder="5000.82" value="{{ old("total") }}" readonly>
          </div>

          <div class="form-group">
            <label for="description" class="form-label">Scope of Project</label>
            <textarea class="form-control" id="description" name="description" style="height:100px;"
              placeholder="Briefly describe your project here">{{ old("description") }}</textarea>
          </div>

          <button type="submit" class="btn btn-primary">Create Project</button>
        </form>
      </div>
    </div>
  </div>
@endsection

@push('styles')
  <style>
    .select2-container--default .select2-selection--single:read-only {
      padding: 0px;
      display: flex;
    }
  </style>
@endpush

@push("scripts")
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const projectTypeSelect = document.getElementById("project_type");
      const agreementFields = document.getElementById("agreement_fields");
      const startDateInput = document.getElementById('start_date');
      const endDateInput = document.getElementById('end_date');
      const rateInput = document.getElementById('rate');
      const projectCapacityInput = document.getElementById('project_capacity');
      const totalInput = document.getElementById('total');

      // Set today's date as default for start date and end date
      const today = new Date().toISOString().split('T')[0];
      startDateInput.placeholder = today;
      startDateInput.value = today;
      endDateInput.placeholder = today;
      endDateInput.value = today;

      // Set min date for end date to be equal to or greater than start date
      startDateInput.addEventListener('change', function() {
        const startDate = startDateInput.value;
        endDateInput.min = startDate; // Update end date's min value
      });

      // Calculate total when rate or project capacity changes
      function calculateTotal() {
        const rate = parseFloat(rateInput.value) || 0;
        const capacity = parseFloat(projectCapacityInput.value) || 0;
        const total = rate * capacity;
        totalInput.value = total.toFixed(2); // Set total with 2 decimal places
      }

      rateInput.addEventListener('input', calculateTotal);
      projectCapacityInput.addEventListener('input', calculateTotal);

      // Initialize end date's min value on load
      endDateInput.min = startDateInput.value;

      projectTypeSelect.addEventListener("change", function() {
        if (this.value === "1") {
          agreementFields.style.display = "block";
        } else {
          agreementFields.style.display = "none";
        }
      });

      // Set up default visibility
      if (projectTypeSelect.value === "1") {
        agreementFields.style.display = "block";
      }
    });
    $(document).ready(function() {
      $('#state').select2({
        placeholder: '-- Select State --',
        allowClear: true
      });
    });
  </script>
@endpush