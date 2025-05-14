@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card p-2">
      <form id="jicrForm" action="{{ route("import.device") }}" method="POST" enctype="multipart/form-data">
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
        <div class="row p-4">
          <div class="col-sm-6">
            <div class="mb-3">
              <label for="wardSelect" class="form-label">Select Ward</label>
              <select id="wardSelect" class="form-select" name="ward_name" style="width: 100%;" disabled>
                <option value="">Select Ward</option>
              </select>
            </div>
          </div>

          <div class="col-sm-6">
            <div class="mb-6">
              <label for="wardSelect" class="form-label">
                Select a file to import
                <small>or <a href=""download>Download Sample</a>
                  <small>
              </label>
              {{-- <form action="{{ route("import.device") }}" method="POST" enctype="multipart/form-data"> --}}
              {{-- @csrf --}}
              <div class="input-group">
                <input type="file" name="file" class="form-control form-control-sm" style="height: 2.4rem; background-color: transparent" required>
                <button type="submit" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Import Devices">
                  <i class="mdi mdi-upload"></i> Import
                </button>
              </div>
              {{-- </form> --}}
            </div>
          </div>

        </div>
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">Import Device</button>
        </div>

    </div>
    </form>
    @if (session("success"))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session("success") }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if (session("error"))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session("error") }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

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