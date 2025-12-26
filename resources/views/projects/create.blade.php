@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">Add Projects</h4>
          <a href="{{ route('projects.index') }}" class="btn btn-light">
            <i class="mdi mdi-arrow-left me-2"></i>Back to Projects
          </a>
        </div>

        <!-- Display validation errors -->
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

        <form class="forms-sample" action="{{ route("projects.store") }}" method="POST">
          @csrf

          <!-- Project Type -->
          <div class="form-group">
            <label for="project_type" class="form-label">Select Project Type <span class="text-danger">*</span></label>
            <select name="project_type" class="form-select @error('project_type') is-invalid @enderror" id="project_type" required>
              <option value="" disabled selected>-- Select Project Type --</option>
              <option value="0" {{ old('project_type') == '0' ? 'selected' : '' }}>Rooftop Installation</option>
              <option value="1" {{ old('project_type') == '1' ? 'selected' : '' }}>Streetlight Installation</option>
            </select>
            @error('project_type')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
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
                  value="{{ old("agreement_date", date("Y-m-d")) }}" max="{{ date("Y-m-d") }}">
              </div>
            </div>
          </div>

          <!-- Other Fields -->
          <div class="form-group">
            <label for="project_name" class="form-label">Project Name <span class="text-danger">*</span></label>
            <input type="text" name="project_name" class="form-control @error('project_name') is-invalid @enderror" id="project_name" placeholder="BREDA"
              value="{{ old("project_name") }}" required>
            @error('project_name')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-group">
            <label for="state" class="form-label">Select State <span class="text-danger">*</span></label>
            <select name="project_in_state" class="form-select @error('project_in_state') is-invalid @enderror" id="state" required>
              <option value="" disabled selected>-- Select State --</option>
              @foreach ($states as $state)
                <option value="{{ $state->id }}" {{ old("project_in_state") == $state->id ? "selected" : "" }}>
                  {{ $state->name }}
                </option>
              @endforeach
            </select>
            @error('project_in_state')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-group">
            <label for="start_date" class="form-label">Start Date</label>
            <div onclick="document.getElementById('start_date').showPicker()" style="width: 100%;">
              <input type="date" name="start_date" class="form-control" id="start_date"
                value="{{ old("date", date("Y-m-d")) }}">
            </div>
          </div>

          <div class="form-group" onclick="document.getElementById('end_date').showPicker()" style="cursor: pointer;">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" id="end_date"
              value="{{ old("end_date", date("Y-m-d")) }}">
          </div>

          <div class="form-group">
            <label for="work_order_number" class="form-label">Work Order Number</label>
            <input type="text" name="work_order_number" class="form-control @error('work_order_number') is-invalid @enderror" id="work_order_number"
              placeholder="PO202412-01" value="{{ old("work_order_number") }}">
            @error('work_order_number')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-group">
            <label for="rate" class="form-label">Rate (Excluding all taxes and duties in rupees)</label>
            <input type="number" step="0.01" name="rate" class="form-control @error('rate') is-invalid @enderror" id="rate" placeholder="50000"
              value="{{ old("rate") }}">
            @error('rate')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-group">
            <label for="project_capacity" class="form-label">Project Capacity (kW)</label>
            <input type="number" step="0.01" name="project_capacity" class="form-control @error('project_capacity') is-invalid @enderror" id="project_capacity"
              placeholder="50" value="{{ old("project_capacity") }}">
            @error('project_capacity')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-group">
            <label for="total" class="form-label">Total (INR)</label>
            <input type="number" step="0.01" max="999999999999.99" name="total" class="form-control"
              id="total" placeholder="5000.82" value="{{ old("total") }}" readonly>
          </div>

          <div class="form-group">
            <label for="description" class="form-label">Scope of Project</label>
            <textarea class="form-control" id="description" name="description" style="height:100px;"
              placeholder="Briefly describe your project here">{{ old("description") }}</textarea>
            @error('description')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="mt-4 d-flex justify-content-end">
            <a href="{{ route('projects.index') }}" class="btn btn-light me-2">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Project</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push("styles")
  <style>
    .select2-container--default .select2-selection--single:read-only {
      padding: 0px;
      display: flex;
    }
    
    /* Consistent card styling to match theme */
    .content-wrapper .card {
      border-radius: 4px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      border: 1px solid #e3e3e3;
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
