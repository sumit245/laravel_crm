@extends("layouts.main")



@section("content")
  <div class="content-wrapper p-2">
    <div class="card p-2">
    <div class="row m-2">
    <div class="col-sm-3">
        <div class="mb-3">
            <label for="districtSelect" class="form-label">District</label>
            <select id="districtSelect" class="form-select fix-select2" style="width: 100%;">
                <option value="">Select a District</option>
                @foreach ($districts as $district)
                    <option value="{{ $district->district }}">{{ $district->district }}</option>
                @endforeach
            </select>
        </div>
    </div>

  <!-- Testing select 2 -->
  
  <!-- Ending testing -->

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

      <div class="mt-5 mb-3 mr-2">
        <a href="{{ route("jicr.generate") }}" id="submitButton" class="btn btn-primary">Create JICR</a>
      </div>
      @include("jicr.show")
    </div>
  </div>
@endsection

@push("styles")
<style>
/* Prevent shifting caused by Select2 */
.fix-select2-container {
    width: 100% !important;
}

.fix-select2-container .select2-selection--single {
    height: auto !important;  
    padding: 6px 10px !important; 
    border: 1px solid #ced4da;  /* Bootstrap default */
    border-radius: 4px;
}

/* Ensures Select2 aligns properly */
.fix-select2-container .select2-selection__rendered {
    line-height: normal !important;
    padding-left: 10px !important;
}

/* Prevents select box from pushing down */
.fix-select2 {
    width: 100% !important;
}

/* Fix Select2 dropdown alignment */
.select2-dropdown {
    width: 100% !important;
}
</style>
@pushends

@push("scripts")
  <script>
    console.log('Script section loaded');
   
    $(document).ready(function() {
      $('#districtSelect').select2({
        placeholder: "Select a District",
        allowClear: true,
        width: "100%",
        minimumResultsForSearch: 
    }).next('.select2-container').addClass('fix-select2-container');

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
