@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card p-2">
      <div class="row">
        <div class="col-sm-3">
          <div class="mb-3">
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
          <div class="mb-3">
            <label for="blockSelect" class="form-label">Block</label>
            <select id="blockSelect" class="form-select" style="width: 100%;" disabled>
              <option value="">Select a Block</option>
            </select>
          </div>
        </div>
        <div class="col-sm-3">
          <div class="mb-3">
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

  </div>
@endsection

@push("scripts")
  <script>
    console.log('Script section loaded');
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
      console.log('Select2 initialized');
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
@endpush
