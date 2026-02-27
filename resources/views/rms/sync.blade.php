@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <div
            class="page-header gap-2 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <h3 class="page-title d-flex align-items-center">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-cloud-sync"></i>
                </span> RMS Bulk Sync
            </h3>
        </div>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Sync Local Data to RMS</h4>
                        <p class="card-description">Select a project to validate and sync its poles to the government RMS
                            portal.</p>

                        <div class="form-group row">
                            <label for="projectSelect" class="col-sm-3 col-form-label">Select Project</label>
                            <div class="col-sm-6">
                                <select class="form-select" id="projectSelect">
                                    <option value="">-- Choose a Project --</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-primary w-100" id="btnValidateSync" disabled>
                                    Check & Sync
                                </button>
                            </div>
                        </div>

                        <!-- Missing Codes Form -->
                        <div id="missingCodesSection" class="mt-4" style="display: none;">
                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert"></i> <strong>Missing Location Codes Detected!</strong><br>
                                Before syncing, you must provide the missing RMS codes for the following locations.
                            </div>

                            <form id="missingCodesForm">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm text-center">
                                        <thead class="table-light">
                                            <tr>
                                                <th>District</th>
                                                <th>District Code</th>
                                                <th>Block</th>
                                                <th>Block Code</th>
                                                <th>Panchayat</th>
                                                <th>Panchayat Code</th>
                                            </tr>
                                        </thead>
                                        <tbody id="missingCodesBody">
                                            <!-- Populated via JS -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3 text-end">
                                    <button type="submit" class="btn btn-success" id="btnSaveCodes">
                                        <i class="mdi mdi-content-save"></i> Save Codes & Continue
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Sync Status & Terminal Log -->
                        <div id="syncProgressSection" class="mt-4" style="display: none;">
                            <h5 class="mb-3">Sync Progress</h5>

                            <!-- Terminal Log -->
                            <div class="terminal-log" id="terminalLog">
                                <ul id="logList" class="list-unstyled mb-0">
                                    <li>Waiting for sync job to start...</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .terminal-log {
            background-color: #1e1e1e;
            color: #4af626;
            font-family: 'Courier New', Courier, monospace;
            padding: 15px;
            height: 400px;
            overflow-y: auto;
            border-radius: 4px;
            font-size: 0.85rem;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.5);
        }

        .terminal-log li {
            margin-bottom: 4px;
            border-bottom: 1px dashed #333;
            padding-bottom: 2px;
        }

        .log-success {
            color: #4af626;
        }

        .log-error {
            color: #ff5252;
            text-shadow: 0 0 2px rgba(255, 0, 0, 0.5);
        }

        .log-info {
            color: #5bc0de;
        }

        .log-time {
            color: #888;
            margin-right: 10px;
        }
    </style>

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            let projectSelect = $('#projectSelect');
            let btnValidateSync = $('#btnValidateSync');
            let missingCodesSection = $('#missingCodesSection');
            let missingCodesBody = $('#missingCodesBody');
            let missingCodesForm = $('#missingCodesForm');
            let syncProgressSection = $('#syncProgressSection');
            let logList = $('#logList');

            let isSyncing = false;
            let pollInterval = null;
            let lastFetchTimestamp = null;
            let fetchedLogIds = new Set();

            // Enable/Disable main button based on project selection
            projectSelect.on('change', function () {
                if ($(this).val()) {
                    btnValidateSync.prop('disabled', false);
                } else {
                    btnValidateSync.prop('disabled', true);
                }
                missingCodesSection.hide();
                syncProgressSection.hide();
            });

            // Handle Validate & Sync Click
            btnValidateSync.on('click', function () {
                let projectId = projectSelect.val();
                if (!projectId) return;

                btnValidateSync.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Checking...');
                missingCodesSection.hide();
                syncProgressSection.hide();

                $.ajax({
                    url: "{{ url('/rms-sync/validate') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        project_id: projectId
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            // No missing codes, start sync immediately
                            startSyncJob(projectId);
                        } else if (response.status === 'missing_codes') {
                            // Populate missing codes table
                            populateMissingCodes(response.data);
                            missingCodesSection.show();
                            btnValidateSync.html('Check & Sync');
                        }
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to validate data', 'error');
                        btnValidateSync.prop('disabled', false).html('Check & Sync');
                    }
                });
            });

            // Populate Missing Codes Table
            function populateMissingCodes(data) {
                missingCodesBody.empty();
                data.forEach(function (row, index) {
                    missingCodesBody.append(`
                    <tr>
                        <td class="align-middle bg-light">${row.district}
                            <input type="hidden" name="codes[${index}][district]" value="${row.district}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm text-center" name="codes[${index}][district_code]" value="${row.district_code || ''}" required autocomplete="off">
                        </td>
                        <td class="align-middle bg-light">${row.block}
                            <input type="hidden" name="codes[${index}][block]" value="${row.block}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm text-center" name="codes[${index}][block_code]" value="${row.block_code || ''}" required autocomplete="off">
                        </td>
                        <td class="align-middle bg-light">${row.panchayat}
                            <input type="hidden" name="codes[${index}][panchayat]" value="${row.panchayat}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm text-center" name="codes[${index}][panchayat_code]" value="${row.panchayat_code || ''}" required autocomplete="off">
                        </td>
                    </tr>
                `);
                });
            }

            // Handle Saving Missing Codes
            missingCodesForm.on('submit', function (e) {
                e.preventDefault();
                let projectId = projectSelect.val();
                let formData = $(this).serializeArray();
                formData.push({ name: 'project_id', value: projectId });
                formData.push({ name: '_token', value: '{{ csrf_token() }}' });

                let btnSave = $('#btnSaveCodes');
                btnSave.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Saving...');

                $.ajax({
                    url: "{{ url('/rms-sync/update-codes') }}",
                    type: "POST",
                    data: $.param(formData),
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Codes Updated',
                                text: 'Starting background sync job...',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                missingCodesSection.hide();
                                startSyncJob(projectId);
                            });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to save codes.', 'error');
                        btnSave.prop('disabled', false).html('<i class="mdi mdi-content-save"></i> Save Codes & Continue');
                    }
                });
            });

            // Start the Queue Job
            function startSyncJob(projectId) {
                btnValidateSync.prop('disabled', true).html('<i class="mdi mdi-cloud-upload"></i> Syncing...');

                // Reset terminal
                logList.html('<li class="log-info"><span class="log-time">[' + new Date().toLocaleTimeString() + ']</span> Dispatching sync job to background queue...</li>');
                syncProgressSection.show();

                $.ajax({
                    url: "{{ url('/rms-sync/start') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        project_id: projectId
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            logList.append('<li class="log-info"><span class="log-time">[' + new Date().toLocaleTimeString() + ']</span> Job started successfully. Total poles queued: ' + response.total_poles + '</li>');
                            // Start polling
                            lastFetchTimestamp = Math.floor(Date.now() / 1000) - 5; // Get logs from last 5 seconds to not miss initial ones
                            startPolling();
                        } else {
                            logList.append('<li class="log-error"><span class="log-time">[' + new Date().toLocaleTimeString() + ']</span> ' + response.message + '</li>');
                            btnValidateSync.html('Check & Sync').prop('disabled', false);
                        }
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Could not start sync job.', 'error');
                        logList.append('<li class="log-error"><span class="log-time">[' + new Date().toLocaleTimeString() + ']</span> Job dispatch failed: ' + (xhr.responseJSON?.message || 'Unknown error') + '</li>');
                        btnValidateSync.html('Check & Sync').prop('disabled', false);
                    }
                });
            }

            // Polling Logic
            function startPolling() {
                if (pollInterval) clearInterval(pollInterval);

                pollInterval = setInterval(function () {
                    $.ajax({
                        url: "{{ url('/rms-sync/progress') }}",
                        type: "GET",
                        data: {
                            last_fetch: lastFetchTimestamp
                        },
                        success: function (response) {
                            if (response.logs && response.logs.length > 0) {
                                // Reverse so oldest is appended first in this batch
                                let newLogs = response.logs.reverse();

                                newLogs.forEach(function (log) {
                                    if (!fetchedLogIds.has(log.id)) {
                                        fetchedLogIds.add(log.id);
                                        let logClass = log.status === 'success' ? 'log-success' : 'log-error';
                                        let details = `Pole ${log.pole_number || 'ID:' + log.pole_id} (${log.panchayat || 'Unknown'}) - ${log.message}`;

                                        logList.append(`<li class="${logClass}">
                                        <span class="log-time">[${log.time}]</span> 
                                        <span class="text-uppercase">[${log.status}]</span> ${details}
                                    </li>`);
                                    }
                                });

                                // Auto-scroll to bottom
                                let terminal = document.getElementById('terminalLog');
                                terminal.scrollTop = terminal.scrollHeight;
                            }

                            lastFetchTimestamp = response.timestamp;
                        }
                    });
                }, 3000); // Poll every 3 seconds
            }
        });
    </script>
@endpush