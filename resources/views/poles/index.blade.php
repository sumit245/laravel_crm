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
            <div class="mb-3">

              <div class="import-section d-flex flex-column gap-2">
                <label for="importPoles" class="form-label">Import Poles</label>
                <form action="{{ route("import.device") }}" method="POST" enctype="multipart/form-data"
                  class="import-form-group d-flex flex-column gap-2 mt-0">
                  @csrf
                  <select name="project_id" class="form-select form-select-sm" required>
                    <option value="">Select Project</option>
                    @foreach($projects as $proj)
                      <option value="{{ $proj->id }}" {{ (isset($project) && $project->id == $proj->id) ? 'selected' : '' }}>
                        {{ $proj->project_name }}
                      </option>
                    @endforeach
                  </select>
                  <div class="input-group input-group-sm import-input-wrapper">
                    <input type="file" name="file" class="form-control form-control-sm import-file-input"
                      accept=".xlsx,.xls,.csv" required>
                    <button type="submit"
                      class="btn btn-success import-submit-btn d-inline-flex align-items-center gap-1">
                      <i class="mdi mdi-upload"></i>
                      <span>Import</span>
                    </button>
                  </div>
                </form>
                <a href="{{ route('device.import.sample') }}" class="download-format-link" target="_blank">
                  <i class="mdi mdi-download"></i>
                  <span>Download Sample</span>
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Progress Bar (hidden initially) -->
        <div id="importProgressSection" class="row p-4" style="display: none;">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h6 class="card-title">Import Progress</h6>
                <div class="progress mb-2" style="height: 25px;">
                  <div id="importProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                    role="progressbar" style="width: 0%">
                    <span id="importProgressText">0%</span>
                  </div>
                </div>
                <div id="importProgressMessage" class="text-muted small"></div>
                <div id="importErrorFileSection" style="display: none;" class="mt-3">
                  <a id="importErrorFileLink" href="#" class="btn btn-sm btn-outline-danger" download>
                    <i class="mdi mdi-download"></i> Download Error File
                  </a>
                </div>
              </div>
            </div>
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

      @if (session("import_errors_url"))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
          Import completed with {{ session("import_errors_count", 0) }} error(s).
          <a href="{{ session('import_errors_url') }}" class="alert-link" download>Download error file</a>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

    </div>
  </div>
@endsection

@push("scripts")
  <script>
    $(document).ready(function () {
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

      $('#districtSelect').on('change', function () {
        var district = $(this).val();
        $('#blockSelect').prop('disabled', false).empty().append('<option value="">Select a Block</option>');
        $('#panchayatSelect').prop('disabled', true).empty().append(
          '<option value="">Select a Panchayat</option>');

        if (district) {
          $.ajax({
            url: '/jicr/blocks/' + district,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
              $.each(data, function (index, block) {
                $('#blockSelect').append('<option value="' + block.block + '">' + block.block +
                  '</option>');
              });
            },
            error: function (xhr, status, error) {
              console.error("AJAX Error:", status, error);
              console.log("Response:", xhr.responseText);
            }
          });
        }
      });

      $('#blockSelect').on('change', function () {
        var block = $(this).val();
        $('#panchayatSelect').prop('disabled', false).empty().append(
          '<option value="">Select a Panchayat</option>');

        if (block) {
          $.ajax({
            url: '/jicr/panchayats/' + block,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
              $.each(data, function (index, block) {
                $('#panchayatSelect').append('<option value="' + block.panchayat + '">' + block
                  .panchayat +
                  '</option>');
              });
            },
            error: function (xhr, status, error) {
              console.error("AJAX Error:", status, error);
              console.log("Response:", xhr.responseText);
            }
          });
        }
      });

      $('#panchayatSelect').on('change', function () {
        var block = $(this).val();
        $('#wardSelect').prop('disabled', false).empty().append(
          '<option value="">Select a Ward</option>');

        if (block) {
          $.ajax({
            url: '/jicr/ward/' + block,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
              $.each(data, function (index, block) {
                $('#wardSelect').append('<option value="Ward ' + block.ward + '">Ward ' + block.ward +
                  '</option>');

              });
            },
            error: function (xhr, status, error) {
              console.error("AJAX Error:", status, error);
              console.log("Response:", xhr.responseText);
            }
          });
        }
      });

      // Check if there's an import job ID in the session
      @if(session('import_job_id'))
        var jobId = '{{ session("import_job_id") }}';
        startProgressPolling(jobId);
      @endif
      });

    function startProgressPolling(jobId) {
      $('#importProgressSection').show();

      var pollInterval = setInterval(function () {
        $.ajax({
          url: '{{ route("device.import.progress", ":jobId") }}'.replace(':jobId', jobId),
          type: 'GET',
          dataType: 'json',
          success: function (response) {
            if (response.status === 'success') {
              var progress = response.progress_percentage || 0;
              var status = response.job_status;

              // Update progress bar
              $('#importProgressBar').css('width', progress + '%');
              $('#importProgressBar').text(Math.round(progress) + '%');

              // Update message
              var message = response.message || 'Processing...';
              $('#importProgressMessage').text(message);

              // If completed or failed, stop polling
              if (status === 'completed' || status === 'failed') {
                clearInterval(pollInterval);

                if (status === 'completed') {
                  $('#importProgressBar').removeClass('progress-bar-animated');
                  $('#importProgressBar').addClass('bg-success');
                  $('#importProgressMessage').html('<strong>Import completed!</strong> ' +
                    'Success: ' + (response.success_count || 0) +
                    ', Errors: ' + (response.error_count || 0));

                  // Show error file link if available
                  if (response.error_file_url) {
                    $('#importErrorFileLink').attr('href', response.error_file_url);
                    $('#importErrorFileSection').show();
                  }
                } else {
                  $('#importProgressBar').removeClass('progress-bar-animated');
                  $('#importProgressBar').addClass('bg-danger');
                  $('#importProgressMessage').html('<strong>Import failed!</strong> ' +
                    (response.message || 'An error occurred'));
                }
              }
            }
          },
          error: function (xhr, status, error) {
            console.error('Error polling progress:', error);
            // Continue polling even on error
          }
        });
      }, 2000); // Poll every 2 seconds
    }
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