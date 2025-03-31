@extends("layouts.main")

@section("content")

  <div class="container mt-5">
    <div class="row">

      <div class="col-sm-3">
        <div class="form-group">
          <label for="districtSelect" class="form-label">District</label>
          <select id="districtSelect" class="form-select" style="width: 100%;">
            <option value="">Select a District</option>
            @foreach ($districts as $district)
              <option value="{{ $district->district }}">{{ $district->district }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="form-group">
          <label for="blockSelect" class="form-label">Block</label>
          <select id="blockSelect" class="form-select" style="width: 100%;" disabled>
            <option value="">Select a Block</option>
          </select>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="form-group">
          <label for="panchayatSelect" class="form-label">Panchayat</label>
          <select id="panchayatSelect" class="form-select" style="width: 100%;" disabled>
            <option value="">Select a Panchayat</option>
          </select>
        </div>
      </div>
    </div>
    <div class="mt-3">
      <a href="{{ route("jicr.generate") }}" id="submitButton" class="btn btn-primary">Create JICR</a>
    </div>
  </div>

@section("css")
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
  <style>
    /* Add any page-specific CSS here */
    .select2-container {
      width: 100% !important;
      /* Ensure the dropdown takes full width */
    }
  </style>
@endsection

@section("js")
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
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

      $('#districtSelect').on('change', function() {
        console.log('District change event fired');
        var district = $(this).val();
        console.log(`Selected District is ${district}`)
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
        var district = $('#districtSelect').val(); // This line is missing
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
            }
          });
        }
      });

    });
  </script>
@endsection
@endsection
