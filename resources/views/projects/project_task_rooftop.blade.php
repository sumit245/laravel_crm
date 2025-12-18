<div>
    <div class="d-flex justify-content-between mb-4">
        <div class="d-flex mx-2">
            <div class="card bg-success mx-2" style="min-width: 33%;">
                <div class="card-body">
                    <h5 class="card-title">{{ $installationCount }}</h5>
                    <p class="card-text">Installation</p>
                </div>
            </div>
            <div class="card bg-warning mx-2" style="min-width: 33%;">
                <div class="card-body">
                    <h5 class="card-title">{{ $rmsCount }}</h5>
                    <p class="card-text">RMS</p>
                </div>
            </div>
            <div class="card bg-info mx-2" style="min-width: 33%;">
                <div class="card-body">
                    <h5 class="card-title">{{ $inspectionCount }}</h5>
                    <p class="card-text">Final Inspection</p>
                </div>
            </div>
        </div>
        <!-- Button to trigger modal -->
        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 add-target-btn"
            style="max-height: 2.8rem;" data-bs-toggle="modal" data-bs-target="#addTargetModal">
            <i class="mdi mdi-plus-circle"></i>
            <span>Add Target</span>
        </button>
    </div>

    <!-- Modal for adding a target -->
    <div class="modal fade" id="addTargetModal" tabindex="-1" aria-labelledby="addTargetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('tasks.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $project->id }}" />
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTargetModalLabel">Add Target for Project:
                            {{ $project->project_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <!-- <label for="siteSearch" class="form-label">Search Site</label>
              <!-- <label for="siteSearch" class="form-label">Search Site</label>
              <input type="text" id="siteSearch" placeholder="Search Site..." class="form-control">
              <div id="siteList"></div> -->

                            <label for="siteSearch" class="form-label">Search Site</label>
                            <select id="siteSearch" name="sites[]" class="form-control" multiple style="width: 100%;">
                                <option value="">Search Site...</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->site_name }}</option>
                                @endforeach
                            </select>

                            <!-- Selected Sites -->
                            <!-- <ul id="selectedSites"></ul> -->
                            <!-- Hidden Select to Store Selected Sites -->
                            <!-- <select id="selectedSitesSelect" name="sites[]" multiple class="d-none">
              </select> -->
                        </div>
                        <div class="mb-3">
                            <label for="activity" class="form-label">Activity</label>
                            <select id="activity" name="activity" class="form-select" required>
                                <option value="Installation">Installation</option>
                                <option value="RMS">RMS</option>
                                <option value="Billing">Billing</option>
                                <option value="Add Team">Add Team</option>
                                <option value="Survey">Survey</option>
                                <option value="Survey">Survey</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="selectEngineer" class="form-label">Select Site Engineer</label>
                            <select id="selectEngineer" name="engineer_id" class="form-select" required>
                                @foreach ($engineers as $engineer)
                                    <option value="{{ $engineer->id }}">{{ $engineer->firstName }}
                                        {{ $engineer->lastName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" id="startDate" name="start_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" id="endDate" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-secondary">Reset</button>
                        <button type="submit" class="btn btn-primary">Allot Target</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Table to display targets -->
    <div class="table-responsive">
        <x-data-table id="bredaTargetTable" class="table-striped table">
            <x-slot:thead>
                <tr>
                    <th>Site Name</th>
                    <th>Activity</th>
                    <th>Site Engineer</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </x-slot:thead>
            <x-slot:tbody>
                @forelse ($targets as $target)
                    <tr>
                        <td>{{ $target->site->site_name }}</td>
                        <td>{{ $target->activity }}</td>
                        <td>
                            @if ($target && $target->engineer)
                                {{ $target->engineer->firstName }} {{ $target->engineer->lastName }}
                            @else
                                Not Assigned
                            @endif
                        </td>
                        <td>{{ $target->start_date }}</td>
                        <td>{{ $target->end_date }}</td>
                        <td>
                            <a href="{{ route('tasks.show', ['id' => $target->id]) }}"
                                class="btn btn-icon btn-info"><i class="mdi mdi-eye"></i></a>
                            <a href="{{ route('tasks.editrooftop', $target->id) }}"
                                class="btn btn-icon btn-warning"><i class="mdi mdi-pencil"></i></a>
                            <form action="{{ route('tasks.destroy', $target->id) }}" method="POST"
                                style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-danger"><i
                                        class="mdi mdi-delete"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <!-- <tr>
          <td colspan="6">No targets found.</td>
        </tr> -->
                @endforelse
                </x-slot-tbody>
        </x-data-table>
    </div>

</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#siteSearch').on('keyup', function() {
                let query = $(this).val();
                if (query.length > 1) {
                    $.ajax({
                        url: "{{ route('sites.search') }}",
                        method: 'GET',
                        data: {
                            search: query
                        },
                        success: function(response) {
                            let html = '';
                            response.forEach(site => {
                                html += `<div>
                                    <input type="checkbox" class="siteCheckbox" data-name="${site.text}" value="${site.id}">
                                    ${site.text}
                                </div>`;
                            });
                            $('#siteList').html(html);
                        }
                    });
                } else {
                    $('#siteList').html('');
                }
            });

            $(document).on('change', '.siteCheckbox', function() {
                let siteId = $(this).val();
                let siteName = $(this).data('name');

                if ($(this).is(':checked')) {
                    // Add to selected list
                    $('#selectedSites').append(`<li data-id="${siteId}">${siteName}</li>`);

                    // Add to hidden select
                    $('#selectedSitesSelect').append(
                        `<option value="${siteId}" selected>${siteName}</option>`);
                } else {
                    // Remove from selected list
                    $(`#selectedSites li[data-id="${siteId}"]`).remove();

                    // Remove from hidden select
                    $(`#selectedSitesSelect option[value="${siteId}"]`).remove();
                }
            });
        });
        // Select 2
        $(document).ready(function() {
            $('#addTargetModal').on('shown.bs.modal', function() {
                $('#activity').select2({
                    width: '100%',
                    dropdownParent: $('#addTargetModal')
                });

                $('#selectEngineer').select2({
                    width: '100%',
                    dropdownParent: $('#addTargetModal')
                });

                $('#siteSearch').select2({
                    allowClear: true,
                    dropdownParent: $('#addTargetModal')
                });


            });
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Consistent button width for Add buttons */
        .add-target-btn {
            min-width: 140px;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            padding: 6px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
@endpush
