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
                <form action="{{ route('tasks.store') }}" method="POST" id="rooftopTargetForm">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $project->id }}" />
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTargetModalLabel">Add Target for Project:
                            {{ $project->project_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="siteSearch" class="form-label">Search Site <span class="text-danger">*</span></label>
                            <select id="siteSearch" name="sites[]" class="form-control" multiple style="width: 100%;" required>
                                <option value="">Search Site...</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->site_name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="sites_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="activity" class="form-label">Activity <span class="text-danger">*</span></label>
                            <select id="activity" name="activity" class="form-select" required>
                                <option value="">Select Activity</option>
                                <option value="Installation">Installation</option>
                                <option value="RMS">RMS</option>
                                <option value="Billing">Billing</option>
                                <option value="Add Team">Add Team</option>
                                <option value="Survey">Survey</option>
                            </select>
                            <div class="invalid-feedback" id="activity_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="selectEngineer" class="form-label">Select Site Engineer <span class="text-danger">*</span></label>
                            <select id="selectEngineer" name="engineer_id" class="form-select" required>
                                <option value="">Select Engineer</option>
                                @foreach ($engineers as $engineer)
                                    <option value="{{ $engineer->id }}">{{ $engineer->firstName }}
                                        {{ $engineer->lastName }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="engineer_id_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="startDate" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" id="startDate" name="start_date" class="form-control" required>
                            <div class="invalid-feedback" id="start_date_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" id="endDate" name="end_date" class="form-control" required>
                            <div class="invalid-feedback" id="end_date_error"></div>
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
    <x-datatable id="bredaTargetTable" 
        title="Rooftop Targets" 
        :columns="[
            ['title' => 'Site Name', 'width' => '20%'],
            ['title' => 'Activity', 'width' => '15%'],
            ['title' => 'Site Engineer', 'width' => '20%'],
            ['title' => 'Start Date', 'width' => '15%'],
            ['title' => 'End Date', 'width' => '15%'],
        ]" 
        :exportEnabled="true" 
        :importEnabled="false" 
        :bulkDeleteEnabled="false"
        pageLength="50" 
        searchPlaceholder="Search targets...">
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
                <td class="text-center">
                    <a href="{{ route('tasks.show', ['id' => $target->id]) }}"
                        class="btn btn-icon btn-info" data-toggle="tooltip" title="View"><i class="mdi mdi-eye"></i></a>
                    <a href="{{ route('tasks.editrooftop', $target->id) }}"
                        class="btn btn-icon btn-warning" data-toggle="tooltip" title="Edit"><i class="mdi mdi-pencil"></i></a>
                    <form action="{{ route('tasks.destroy', $target->id) }}" method="POST"
                        style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-icon btn-danger" data-toggle="tooltip" title="Delete"
                            onclick="return confirm('Are you sure you want to delete this target?')"><i class="mdi mdi-delete"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">No targets found.</td>
            </tr>
        @endforelse
    </x-datatable>

</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            // Helper function to clear all error states
            function clearFormErrors() {
                $('.form-control, .form-select').removeClass('is-invalid');
                $('.select2-container .select2-selection').removeClass('is-invalid');
                $('.invalid-feedback').text('').hide();
            }

            // Helper function to show field error
            function showFieldError(fieldName, message) {
                const field = $(`[name="${fieldName}"]`);
                const errorDiv = $(`#${fieldName}_error`);
                
                field.addClass('is-invalid');
                
                // Handle Select2 fields
                if (field.hasClass('select2-hidden-accessible')) {
                    field.next('.select2-container').find('.select2-selection').addClass('is-invalid');
                }
                
                if (errorDiv.length) {
                    errorDiv.text(message).show();
                } else {
                    // Create error div if it doesn't exist
                    const errorHtml = `<div class="invalid-feedback" id="${fieldName}_error">${message}</div>`;
                    field.closest('.form-group, .mb-3').append(errorHtml);
                }
            }

            // Client-side validation
            function validateForm() {
                let isValid = true;
                clearFormErrors();

                // Validate sites
                const sites = $('#siteSearch').val();
                if (!sites || sites.length === 0) {
                    showFieldError('sites', 'Please select at least one site.');
                    isValid = false;
                }

                // Validate activity
                const activity = $('#activity').val();
                if (!activity) {
                    showFieldError('activity', 'Please select an activity.');
                    isValid = false;
                }

                // Validate engineer
                const engineerId = $('#selectEngineer').val();
                if (!engineerId) {
                    showFieldError('engineer_id', 'Please select an engineer.');
                    isValid = false;
                }

                // Validate start date
                const startDate = $('#startDate').val();
                if (!startDate) {
                    showFieldError('start_date', 'Start date is required.');
                    isValid = false;
                }

                // Validate end date
                const endDate = $('#endDate').val();
                if (!endDate) {
                    showFieldError('end_date', 'End date is required.');
                    isValid = false;
                }

                // Validate date range
                if (startDate && endDate) {
                    const start = new Date(startDate);
                    const end = new Date(endDate);
                    if (end < start) {
                        showFieldError('end_date', 'End date must be equal to or after start date.');
                        isValid = false;
                    }
                }

                return isValid;
            }

            // Clear errors when modal is opened
            $('#addTargetModal').on('show.bs.modal', function() {
                clearFormErrors();
            });

            // Clear errors when modal is closed
            $('#addTargetModal').on('hidden.bs.modal', function() {
                clearFormErrors();
                // Reset form
                $('#rooftopTargetForm')[0].reset();
                $('#siteSearch').val(null).trigger('change');
            });

            // Clear errors when reset button is clicked
            $('#rooftopTargetForm').on('reset', function() {
                setTimeout(function() {
                    clearFormErrors();
                }, 100);
            });

            // AJAX Form submission
            $('#rooftopTargetForm').on('submit', function(e) {
                e.preventDefault();
                
                // Clear previous errors
                clearFormErrors();

                // Client-side validation
                if (!validateForm()) {
                    // Scroll to first error
                    const firstError = $('.is-invalid').first();
                    if (firstError.length) {
                        $('html, body').animate({
                            scrollTop: firstError.offset().top - 100
                        }, 500);
                    }
                    return false;
                }

                // Get form data
                const formData = new FormData(this);
                const submitButton = $(this).find('button[type="submit"]');
                const originalButtonText = submitButton.html();
                
                // Disable submit button and show loading state
                submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');

                // Make AJAX request
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success toast
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message || 'Targets successfully added.',
                                timer: 2000,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            }).then(() => {
                                // Close modal
                                const modal = bootstrap.Modal.getInstance(document.getElementById('addTargetModal'));
                                if (modal) {
                                    modal.hide();
                                }
                                // Reset form
                                $('#rooftopTargetForm')[0].reset();
                                $('#siteSearch').val(null).trigger('change');
                                clearFormErrors();
                                // Reload page to show new targets
                                if (response.redirect_url) {
                                    window.location.href = response.redirect_url;
                                } else {
                                    window.location.reload();
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        // Re-enable submit button
                        submitButton.prop('disabled', false).html(originalButtonText);

                        if (xhr.status === 422) {
                            // Validation errors
                            const errors = xhr.responseJSON?.errors || {};
                            
                            // Show field-level errors
                            Object.keys(errors).forEach(function(field) {
                                const errorMessages = errors[field];
                                const errorMessage = Array.isArray(errorMessages) ? errorMessages[0] : errorMessages;
                                
                                // Handle array fields like sites[]
                                const fieldName = field.replace(/\./g, '_').replace(/\[\]/g, '');
                                showFieldError(fieldName, errorMessage);
                            });

                            // Scroll to first error
                            const firstError = $('.is-invalid').first();
                            if (firstError.length) {
                                $('html, body').animate({
                                    scrollTop: firstError.offset().top - 100
                                }, 500);
                            }

                            // Show toast notification
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: xhr.responseJSON?.message || 'Please check your input and try again.',
                                timer: 3000,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        } else if (xhr.status >= 500) {
                            // Server errors
                            Swal.fire({
                                icon: 'error',
                                title: 'Server Error',
                                html: `
                                    <p><strong>Status:</strong> ${xhr.status}</p>
                                    <p><strong>Error:</strong> ${xhr.responseJSON?.error || xhr.responseJSON?.message || 'An unexpected error occurred.'}</p>
                                    ${xhr.responseJSON?.code ? `<p><strong>Code:</strong> ${xhr.responseJSON.code}</p>` : ''}
                                `,
                                confirmButtonText: 'OK'
                            });
                        } else {
                            // Other errors
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || xhr.responseJSON?.error || 'An error occurred. Please try again.',
                                confirmButtonText: 'OK'
                            });
                        }
                    }
                });

                return false;
            });

            // Select 2 initialization
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

            // Clear errors when modal is closed
            $('#addTargetModal').on('hidden.bs.modal', function() {
                clearFormErrors();
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

        /* Error styling */
        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6 .4.4.4-.4m0 4.8h.8'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #dc3545;
        }

        .form-label .text-danger {
            margin-left: 0.25rem;
        }

        /* Select2 error styling */
        .select2-container--default .select2-selection.is-invalid {
            border-color: #dc3545 !important;
        }
    </style>
@endpush
