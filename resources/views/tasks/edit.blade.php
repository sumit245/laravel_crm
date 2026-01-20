@extends('layouts.main')
@section('content')

<style>
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }
    .border-left-secondary {
        border-left: 4px solid #6c757d !important;
    }
    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }
    .card {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 1px solid #dee2e6;
    }
    .card-header {
        padding: 1rem 1.25rem;
    }
    .form-label {
        color: #495057;
        margin-bottom: 0.5rem;
    }
    .form-control:disabled {
        background-color: #e9ecef;
        opacity: 0.7;
    }
</style>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Task</h4>
                    @if(isset($tasks->status) && $tasks->status === 'Completed')
                        <div class="alert alert-warning mt-2 mb-0" style="border-left: 4px solid #ffc107;">
                            <i class="mdi mdi-alert-circle text-warning"></i> 
                            <strong>Completed Task:</strong> Changing assignments will not affect historical performance reports. 
                            Completed work remains credited to the original assignees.
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <form action="{{ route('tasks.update', $tasks->id) }}" method="POST" id="taskEditForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="project_id" value="{{ $projectId }}">
                        
                        <!-- Current Assignments Section -->
                        <div class="card mb-3 border-left-primary">
                            <div class="card-header" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-bottom: 2px solid #007bff;border-top-left-radius: inherit; border-top-right-radius: inherit;">
                                <h5 class="mb-0" style="color: #495057; font-weight: 600;">
                                    <i class="mdi mdi-account-group text-primary"></i> Current Assignments
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Project Manager -->
                                <div class="mb-3">
                                    <label for="manager_id" class="form-label fw-semibold">
                                        Project Manager <span class="text-danger">*</span>
                                    </label>
                                    <select name="manager_id" id="manager_id" class="form-control" 
                                            {{ (isset($tasks->status) && $tasks->status === 'Completed') ? 'disabled' : '' }}>
                                        <option value="">Select Project Manager</option>
                                        @foreach($managers ?? [] as $manager)
                                            <option value="{{ $manager->id }}" 
                                                {{ $tasks->manager_id == $manager->id ? 'selected' : '' }}>
                                                {{ $manager->firstName }} {{ $manager->lastName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if(isset($tasks->status) && $tasks->status === 'Completed')
                                        <small class="text-muted d-block mt-1">
                                            <i class="mdi mdi-information-outline"></i> Cannot change manager for completed tasks
                                        </small>
                                    @endif
                                    @if(isset($tasks->manager))
                                        <small class="text-dark d-block mt-2" style="font-weight: 500;">
                                            <i class="mdi mdi-account-check text-success"></i> 
                                            <strong>Current:</strong> {{ $tasks->manager->firstName }} {{ $tasks->manager->lastName }}
                                        </small>
                                    @endif
                                </div>

                                <!-- Engineer Name -->
                                <div class="mb-3">
                                    <label for="engineer_id" class="form-label fw-semibold">
                                        Site Engineer <span class="text-danger">*</span>
                                    </label>
                                    <select name="engineer_id" id="engineer_id" class="form-control" 
                                            {{ (isset($tasks->status) && $tasks->status === 'Completed') ? 'disabled' : '' }}>
                                        <option value="">Select Engineer</option>
                                        @foreach($engineers as $engineer)
                                            <option value="{{ $engineer->id }}" 
                                                {{ $tasks->engineer_id == $engineer->id ? 'selected' : '' }}>
                                                {{ $engineer->firstName }} {{ $engineer->lastName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if(isset($tasks->status) && $tasks->status === 'Completed')
                                        <small class="text-muted d-block mt-1">
                                            <i class="mdi mdi-information-outline"></i> Cannot change engineer for completed tasks
                                        </small>
                                    @endif
                                    @if(isset($tasks->engineer))
                                        <small class="text-dark d-block mt-2" style="font-weight: 500;">
                                            <i class="mdi mdi-account-check text-success"></i> 
                                            <strong>Current:</strong> {{ $tasks->engineer->firstName }} {{ $tasks->engineer->lastName }}
                                        </small>
                                    @endif
                                </div>

                                <!-- Vendor Name -->
                                <div class="mb-3">
                                    <label for="vendor_id" class="form-label fw-semibold">
                                        Vendor <span class="text-danger">*</span>
                                    </label>
                                    <select name="vendor_id" id="vendor_id" class="form-control" 
                                            {{ (isset($tasks->status) && $tasks->status === 'Completed') ? 'disabled' : '' }}>
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" 
                                                data-vendor-name="{{ $vendor->name ?? ($vendor->firstName . ' ' . $vendor->lastName) }}"
                                                {{ $tasks->vendor_id == $vendor->id ? 'selected' : '' }}>
                                                {{ $vendor->name ?? ($vendor->firstName . ' ' . $vendor->lastName) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if(isset($tasks->status) && $tasks->status === 'Completed')
                                        <small class="text-muted d-block mt-1">
                                            <i class="mdi mdi-information-outline"></i> Cannot change vendor for completed tasks
                                        </small>
                                    @endif
                                    @if(isset($tasks->vendor))
                                        <small class="text-dark d-block mt-2" style="font-weight: 500;">
                                            <i class="mdi mdi-account-check text-success"></i> 
                                            <strong>Current:</strong> {{ $tasks->vendor->name ?? 'N/A' }}
                                        </small>
                                    @endif
                                    
                                    <!-- Ward Conflict Warning -->
                                    <div id="wardConflictWarning" class="alert alert-warning mt-2" style="display: none; border-left: 4px solid #ffc107;">
                                        <i class="mdi mdi-alert-circle text-warning"></i>
                                        <strong>Warning:</strong> This vendor has completed installations in the same wards. 
                                        Consider selecting a different vendor to avoid conflicts.
                                    </div>
                                    
                                    <!-- Inventory Status Warning -->
                                    @if(isset($inventoryStatus) && $inventoryStatus['has_pending'])
                                        <div class="alert alert-info mt-2" style="border-left: 4px solid #17a2b8; background-color: #d1ecf1; color: #0792a8;">
                                            <i class="mdi mdi-information-outline text-info"></i>
                                            <strong style="color: #0792a8;">Inventory Status:</strong> 
                                            Current vendor has {{ $inventoryStatus['pending_count'] }} pending inventory item(s) 
                                            (dispatched but not consumed). An action item will be created for inventory return.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Date Extension Section -->
                        <div class="card mb-3" style="border-left: 4px solid #28a745 !important;">
                            <div class="card-header" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-bottom: 2px solid #28a745; border-top-left-radius: inherit; border-top-right-radius: inherit;">
                                <h5 class="mb-0" style="color: #155724; font-weight: 600;">
                                    <i class="mdi mdi-calendar-clock text-success"></i> Extend Target Date
                                </h5>
                            </div>
                            <div class="card-body">
                                {{-- <div class="alert alert-info" style="border-left: 4px solid #17a2b8; background-color: #d1ecf1;">
                                    <i class="mdi mdi-information-outline text-info"></i>
                                    <strong>Extend the target completion date instead of creating multiple targets.</strong>
                                    <p class="mb-0 mt-2">Update the end date below to extend the deadline for this target.</p>
                                </div> --}}
                                
                                <div class="row">
                                    <!-- Start Date -->
                                    <div class="col-md-6 mb-3">
                                        <label for="start_date" class="form-label fw-semibold">
                                            <i class="mdi mdi-calendar-start text-primary"></i> Start Date
                                        </label>
                                        <input type="date" 
                                               name="start_date" 
                                               id="start_date" 
                                               class="form-control" 
                                               value="{{ isset($tasks->start_date) ? \Carbon\Carbon::parse($tasks->start_date)->format('Y-m-d') : '' }}"
                                               onclick="this.showPicker()">
                                        @if(isset($tasks->start_date))
                                            <small class="text-muted d-block mt-1">
                                                Current: {{ \Carbon\Carbon::parse($tasks->start_date)->format('M d, Y') }}
                                            </small>
                                        @endif
                                    </div>

                                    <!-- End Date (Extension Date) -->
                                    <div class="col-md-6 mb-3">
                                        <label for="end_date" class="form-label fw-semibold">
                                            <i class="mdi mdi-calendar-arrow-right text-danger"></i> End Date (Extended) <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" 
                                               name="end_date" 
                                               id="end_date" 
                                               class="form-control" 
                                               value="{{ isset($tasks->end_date) ? \Carbon\Carbon::parse($tasks->end_date)->format('Y-m-d') : '' }}"
                                               onclick="this.showPicker()"
                                               required>
                                        @if(isset($tasks->end_date))
                                            <small class="text-muted d-block mt-1">
                                                <i class="mdi mdi-calendar-check text-info"></i> 
                                                Current: {{ \Carbon\Carbon::parse($tasks->end_date)->format('M d, Y') }}
                                                @php
                                                    $daysRemaining = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($tasks->end_date), false);
                                                @endphp
                                                @if($daysRemaining >= 0)
                                                    <span class="badge badge-success ml-2">{{ $daysRemaining }} days remaining</span>
                                                @else
                                                    <span class="badge badge-danger ml-2">{{ abs($daysRemaining) }} days overdue</span>
                                                @endif
                                            </small>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Extension Reason -->
                                <div class="mb-3">
                                    <label for="extension_reason" class="form-label fw-semibold">
                                        <i class="mdi mdi-note-text text-warning"></i> Reason for Extension (Optional)
                                    </label>
                                    <textarea name="extension_reason" 
                                              id="extension_reason" 
                                              class="form-control" 
                                              rows="2" 
                                              placeholder="Enter reason for extending the target date (for audit trail)"></textarea>
                                    <small class="text-muted">This information will be logged for audit purposes.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Task Details Section -->
                        <div class="card mb-3 border-left-secondary">
                            <div class="card-header" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-bottom: 2px solid #6c757d;border-top-left-radius: inherit; border-top-right-radius: inherit;">
                                <h5 class="mb-0" style="color: #495057; font-weight: 600;">
                                    <i class="mdi mdi-clipboard-text text-secondary"></i> Task Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Status Field -->
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">Select Status</option>
                                        @foreach(\App\Enums\TaskStatus::cases() as $status)
                                            <option value="{{ $status->value }}" 
                                                {{ (isset($tasks->status) && $tasks->status == $status->value) ? 'selected' : '' }}>
                                                {{ $status->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Billed Field -->
                                <div class="mb-3">
                                    <label for="billed" class="form-label">Billed</label>
                                    <select name="billed" id="billed" class="form-control">
                                        <option value="">Select Option</option>
                                        <option value="1" {{ (isset($tasks->billed) && $tasks->billed == 1) ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ (isset($tasks->billed) && $tasks->billed == 0) ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>

                                <!-- Site Information (Read-only) -->
                                @if(isset($tasks->site))
                                    <div class="mb-3">
                                        <label class="form-label">Site Information</label>
                                        <div class="form-control-plaintext">
                                            <strong>Panchayat:</strong> {{ $tasks->site->panchayat ?? 'N/A' }}<br>
                                            @if(isset($wardInfo))
                                                <strong>Wards:</strong> {{ $wardInfo['ward'] ?? 'N/A' }}<br>
                                                <strong>District:</strong> {{ $wardInfo['district'] ?? 'N/A' }}
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Reassignment Reason (Optional) -->
                        <div class="card mb-3 border-left-info">
                            <div class="card-header" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-bottom: 2px solid #17a2b8; border-top-left-radius: inherit; border-top-right-radius: inherit;">
                                <h5 class="mb-0" style="color: #495057; font-weight: 600;">
                                    <i class="mdi mdi-note-text text-info"></i> Reassignment Information (Optional)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="reassignment_reason" class="form-label">Reason for Reassignment</label>
                                    <textarea name="reassignment_reason" id="reassignment_reason" 
                                              class="form-control" rows="3" 
                                              placeholder="Enter reason for changing assignments (for audit trail)"></textarea>
                                    <small class="text-muted">This information will be logged for audit purposes.</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('projects.show', $projectId) }}" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="mdi mdi-content-save"></i> Update Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/task-edit-reassignment.js') }}"></script>
<script>
    // Pass data to JavaScript
    window.taskEditData = {
        taskId: {{ $tasks->id }},
        projectId: {{ $projectId ?? ($tasks->project_id ?? 'null') }},
        currentVendorId: {{ $tasks->vendor_id ?? 'null' }},
        taskStatus: '{{ $tasks->status ?? "" }}',
        wardInfo: @json($wardInfo ?? null),
        checkWardConflictUrl: '{{ route("api.check-ward-conflict") }}',
        csrfToken: '{{ csrf_token() }}',
        originalEndDate: '{{ isset($tasks->end_date) ? \Carbon\Carbon::parse($tasks->end_date)->format("Y-m-d") : "" }}'
    };

    // Date extension tracking
    $(document).ready(function() {
        const $endDateInput = $('#end_date');
        const $startDateInput = $('#start_date');
        const originalEndDate = window.taskEditData.originalEndDate;

        // Show extension information when end date changes
        $endDateInput.on('change', function() {
            const newEndDate = $(this).val();
            
            if (newEndDate && originalEndDate) {
                const originalDate = new Date(originalEndDate);
                const newDate = new Date(newEndDate);
                const daysDifference = Math.ceil((newDate - originalDate) / (1000 * 60 * 60 * 24));
                
                // Remove any existing extension info
                $('.date-extension-info').remove();
                
                if (daysDifference > 0) {
                    // Extension
                    const infoHtml = `
                        <div class="alert alert-success date-extension-info mt-2" style="border-left: 4px solid #28a745;">
                            <i class="mdi mdi-calendar-plus text-success"></i>
                            <strong>Date Extended:</strong> ${Math.abs(daysDifference)} day(s) added to the target completion date.
                            ${daysDifference >= 7 ? '<br><small>Consider adding a reason for this extension.</small>' : ''}
                        </div>
                    `;
                    $endDateInput.closest('.mb-3').append(infoHtml);
                } else if (daysDifference < 0) {
                    // Reduction
                    const infoHtml = `
                        <div class="alert alert-warning date-extension-info mt-2" style="border-left: 4px solid #ffc107;">
                            <i class="mdi mdi-calendar-minus text-warning"></i>
                            <strong>Date Reduced:</strong> ${Math.abs(daysDifference)} day(s) reduced from the target completion date.
                        </div>
                    `;
                    $endDateInput.closest('.mb-3').append(infoHtml);
                } else {
                    // No change
                    const infoHtml = `
                        <div class="alert alert-info date-extension-info mt-2" style="border-left: 4px solid #17a2b8;">
                            <i class="mdi mdi-calendar-check text-info"></i>
                            <strong>No Change:</strong> End date remains the same.
                        </div>
                    `;
                    $endDateInput.closest('.mb-3').append(infoHtml);
                }
            }
        });

        // Validate end date is after start date
        function validateDates() {
            const startDate = $startDateInput.val();
            const endDate = $endDateInput.val();
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                if (end < start) {
                    $endDateInput.addClass('is-invalid');
                    if (!$endDateInput.next('.invalid-feedback').length) {
                        $endDateInput.after('<div class="invalid-feedback">End date must be after or equal to start date.</div>');
                    }
                    return false;
                } else {
                    $endDateInput.removeClass('is-invalid');
                    $endDateInput.next('.invalid-feedback').remove();
                    return true;
                }
            }
            return true;
        }

        $startDateInput.on('change', validateDates);
        $endDateInput.on('change', validateDates);

        // Form submission validation
        $('#taskEditForm').on('submit', function(e) {
            if (!validateDates()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date Range',
                    text: 'End date must be after or equal to start date.',
                    confirmButtonText: 'OK'
                });
                return false;
            }
        });
    });
</script>
@endpush

@endsection
