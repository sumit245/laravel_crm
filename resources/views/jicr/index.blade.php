@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card p-2">
      <form id="jicrForm" action="{{ route("jicr.generate") }}" method="GET">
        @csrf
        <div class="row">
          <div class="col-sm-3">
            <div class="mb-3">
              <label for="districtSelect" class="form-label">District</label>
              <select id="districtSelect" class="form-select select2" style="width: 100%;">
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
        <div class="row">
          <div class="col-sm-3">
            <div class="mb-3"><label for="fromDate" class="form-label">From Date</label><input type="date"
                id="fromDate" name="from_date" class="form-control" required></div>
          </div>
          <div class="col-sm-3">
            <div class="mb-3"><label for="toDate" class="form-label">To Date</label><input type="date"
                id="toDate" name="to_date" class="form-control" required></div>
          </div>
        </div>
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">Generate JICR</button>
        </div>
    </div>
    </form>
    {{-- @if (isset($showReport))
      <div class="alert alert-info">
        showReport value: {{ var_export($showReport, true) }}<br>
        district: {{ $district ?? "not set" }}<br>
        block: {{ $block ?? "not set" }}<br>
        panchayat: {{ $panchayat ?? "not set" }}
      </div>
    @endif --}}
    {{-- @if (isset($showReport) && $showReport) --}}
    @include("jicr.show")
    {{-- @endif --}}
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

      $('#districtSelect').on('change', function() {
        console.log('District change event fired');
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
      const now = new Date();
      const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
      const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

      document.getElementById('fromDate').valueAsDate = firstDay;
      document.getElementById('toDate').valueAsDate = lastDay;
    });
  </script>
@endpush
