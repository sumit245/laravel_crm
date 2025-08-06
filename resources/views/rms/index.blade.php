@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    {{-- Display Success/Error Alerts --}}
    @if (session("success") || session("error"))
      <div class="alert alert-{{ session("success") ? "success" : "danger" }} alert-dismissible fade show" role="alert">
        {{ session("success", session("error")) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="card p-2">
      <form id="rmsForm" action="{{ route("rms.push") }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row p-4">
          <div class="col-sm-4">
            <div class="mb-3">
              <label for="districtSelect" class="form-label">District</label>
              <select id="districtSelect" class="form-select select2" name="district" style="width: 100%;">
                <option value="">Select a District</option>
                @foreach ($districts as $district)
                  <option value="{{ $district->district }}">{{ $district->district }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="mb-3">
              <label for="blockSelect" class="form-label">Block</label>
              <select id="blockSelect" class="form-select" name="block" style="width: 100%;" disabled>
                <option value="">Select a Block</option>
              </select>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="mb-3">
              <label for="panchayatSelect" class="form-label">Panchayat</label>
              <select id="panchayatSelect" class="form-select" name="panchayat" style="width: 100%;" disabled>
                <option value="">Select a Panchayat</option>
              </select>
            </div>
          </div>
        </div>

        <div class="mt-3">
          <button type="submit" class="btn btn-primary">Push To RMS</button>
        </div>

    </div>
    </form>
  </div>
  </div>
@endsection

@push("scripts")
  <script>
    $(document).ready(function() {
      $('#districtSelect').select2({
        placeholder: "Select a District",
        allowClear: true
      });

      $('#blockSelect').select2({
        placeholder: "Select a Block",
        allowClear: true
      });

      $('#panchayatSelect').select2({
        placeholder: "Select a Panchayat",
        allowClear: true
      });
      $('#wardSelect').select2({
        placeholder: "Select a Ward",
        multiple: true,
        allowClear: true
      });

      $('#districtSelect').on('change', function() {
        var district = $(this).val();
        $('#blockSelect').prop('disabled', false).empty().append('<option value="">Select a Block</option>');
        $('#panchayatSelect').prop('disabled', true).empty().append(
          '<option value="">Select a Panchayat</option>');

        if (district) {
          $.ajax({
            url: '/jicr/blocks/' + district,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
              $.each(data, function(index, block) {
                $('#blockSelect').append('<option value="' + block.block + '">' + block.block +
                  '</option>');
              });
            },
            error: function(xhr, status, error) {
              console.error("AJAX Error:", status, error);
              console.log("Response:", xhr.responseText);
            }
          });
        }
      });

      $('#blockSelect').on('change', function() {
        var block = $(this).val();
        $('#panchayatSelect').prop('disabled', false).empty().append(
          '<option value="">Select a Panchayat</option>');

        if (block) { // You're checking 'district' instead of 'block'
          $.ajax({
            url: '/jicr/panchayats/' + block,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
              $.each(data, function(index, block) {
                $('#panchayatSelect').append('<option value="' + block.panchayat + '">' + block
                  .panchayat +
                  '</option>');
              });
            },
            error: function(xhr, status, error) {
              console.error("AJAX Error:", status, error);
              console.log("Response:", xhr.responseText);
            }
          });
        }
      });

      $('#panchayatSelect').on('change', function() {
        var block = $(this).val();
        $('#wardSelect').prop('disabled', false).empty().append(
          '<option value="">Select a Ward</option>');

        if (block) { // You're checking 'district' instead of 'block'
          $.ajax({
            url: '/jicr/ward/' + block,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
              $.each(data, function(index, block) {
                $('#wardSelect').append('<option value="Ward ' + block.ward + '">Ward ' + block.ward +
                  '</option>');

              });
            },
            error: function(xhr, status, error) {
              console.error("AJAX Error:", status, error);
              console.log("Response:", xhr.responseText);
            }
          });
        }
      });
    });
  </script>
@endpush

@push("styles")
  <style>
    /* Optional: style the dropdown container */
    .select2-container--default .select2-selection--single {
      height: 38px;
      padding: 6px 12px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
  </style>
@endpush
