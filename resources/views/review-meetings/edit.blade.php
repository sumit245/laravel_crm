@extends('layouts.main')

@section('content')
    <div class="content-wrapper p-2 my-4">
        <div class="mb-4 d-flex justify-content-start align-items-center">
            <a href={{ route('meets.index') }} class="btn btn-light border mb-3"><i class="bi bi-arrow-left me-2"></i>Back to
                Meetings</a>
            <div class="ms-3">
                <h2 class="mb-1">Edit Meeting</h2>
                <p class="text-muted-light">Update meeting details and participants.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <form class="forms-sample" id="editMeetingForm" action="{{ route('meets.update', $meet->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @if ($meet->attendees->count() > 0)
                        @foreach ($meet->attendees as $attendee)
                            <input type="hidden" name="users[]" value="{{ $attendee->id }}" id="user-input-{{ $attendee->id }}">
                        @endforeach
                    @endif
                    <div class="card mb-4" style="border-radius: 0.35em !important;">
                        <div class="card-header">
                            <h5 class="mb-0">Meeting Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                            <div class="col-md-6 mb-3 form-group">
                                <label for="meetingTitle" class="form-label">Meeting Title
                                    <span class="text-danger small">*</span>
                                </label>
                                <input type="text" class="form-control" id="meetingTitle" name="title" required
                                    value="{{ old('title', $meet->title) }}" placeholder="Enter meeting title">
                            </div>
                                <div class="col-md-6 mb-3 form-group">
                                    <label for="meetingType" class="form-label">Meeting Type
                                        <span class="text-danger small">*</span>
                                    </label>
                                    <select name="type" class="form-select" required>
                                        <option value="Review" {{ old('type', $meet->type) == 'Review' ? 'selected' : '' }}>Review</option>
                                        <option value="Planning" {{ old('type', $meet->type) == 'Planning' ? 'selected' : '' }}>Planning</option>
                                        <option value="Discussion" {{ old('type', $meet->type) == 'Discussion' ? 'selected' : '' }}>Discussion</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 form-group">
                                <label for="meetingAgenda" class="form-label">Meeting Agenda
                                    <span class="text-danger small">*</span>
                                </label>
                                <textarea name="agenda" class="form-control" id="meetingAgenda" rows="4"
                                    placeholder="Describe the meeting agenda and objectives" style="height: 100px;">{{ old('agenda', $meet->agenda) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4" style="border-radius: 0.35em !important;">
                        <div class="card-header">
                            <h5 class="mb-0">Schedule</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3 form-group">
                                    <label for="meet_date" class="form-label">Meeting Date
                                        <span class="text-danger small">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="date" name="meet_date" id="meet_date" class="form-control"
                                            value="{{ old('meet_date', $meet->meet_date->format('Y-m-d')) }}" 
                                            min="{{ date('Y-m-d') }}" placeholder="pick a date" required>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="meet_time_from" class="form-label">Start Time <span
                                            class="text-danger small">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="time" class="form-control" name="meet_time_from" id="meet_time1"
                                            value="{{ old('meet_time_from', $meet->meet_time ? \Carbon\Carbon::parse($meet->meet_time)->format('H:i') : '') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="meet_time2" class="form-label">End Time
                                        <span class="text-danger small">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="time" class="form-control" name="meet_time_to" id="meet_time2"
                                            value="{{ old('meet_time_to', '') }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4" style="border-radius: 0.35em !important;">
                        <div class="card-header">
                            <h5 class="mb-0">Meeting Platform</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label">Select Platform
                                            <span class="text-danger small">*</span>
                                        </label>
                                        <select name="platform" id="platform-select" class="form-select" required>
                                            <option value="" disabled>-- Choose Platform --</option>
                                            <option value="Google Meet" {{ old('platform', $meet->platform) == 'Google Meet' ? 'selected' : '' }}>Google Meet</option>
                                            <option value="Zoom" {{ old('platform', $meet->platform) == 'Zoom' ? 'selected' : '' }}>Zoom</option>
                                            <option value="Teams" {{ old('platform', $meet->platform) == 'Teams' ? 'selected' : '' }}>Teams</option>
                                            <option value="Other" {{ old('platform', $meet->platform) == 'Other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label">Link to Join</label>
                                        <input type="url" class="form-control" name="meet_link" id="meet-link-input"
                                            value="{{ old('meet_link', $meet->meet_link) }}" placeholder="Meeting Link" required>
                                        <small id="link-helper" class="form-text text-muted" style="display: none;">
                                            Please create the meeting in the new tab or select a platform and paste the link
                                            here.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4" style="border-radius: 0.35em !important;" id="participants-card">
                    <div class="card-header">
                        <h5 class="mb-0">Participants</h5>
                    </div>
                    <div class="card-body" id="participants-section">
                        @php
                            $roleNames = [
                                0 => 'Admin',
                                1 => 'Site Engineer',
                                2 => 'Project Manager',
                                3 => 'Vendor',
                                4 => 'Store Incharge',
                                5 => 'Coordinator',
                            ];
                            $existingAttendeeIds = $meet->attendees->pluck('id')->toArray();
                        @endphp
                        <div class="input-group mb-3">
                            <input type="text" class="form-control participant-search"
                                placeholder="Search by name or email..." />
                        </div>

                        <div id="selected-participants" class="mb-3">
                            @if ($meet->attendees->count() > 0)
                                @foreach ($meet->attendees as $attendee)
                                    <div class="selected-participant-badge bg-light border rounded-pill me-2 mb-2" id="badge-{{ $attendee->id }}">
                                        <span>{{ $attendee->firstName }} {{ $attendee->lastName }}</span>
                                        <button type="button" class="btn-close btn-sm ms-2 remove-participant-btn" aria-label="Close" data-user-id="{{ $attendee->id }}"></button>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted-light py-3">
                                    No participants added yet.
                                </div>
                            @endif
                        </div>

                        <div class="border rounded p-2">
                            <div class="participant-scroll">
                                @foreach ($users as $user)
                                    <div class="participant-item d-flex justify-content-between align-items-center p-2 {{ in_array($user->id, $existingAttendeeIds) ? 'hidden' : '' }}"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->firstName }} {{ $user->lastName }}"
                                        data-user-email="{{ strtolower($user->email ?? '') }}"
                                        data-search-term="{{ strtolower(trim($user->firstName . ' ' . $user->lastName . ' ' . $user->email)) }}">
                                        <div>
                                            <div class="fw-bold">{{ $user->firstName }} {{ $user->lastName }}</div>
                                            <small class="text-muted">{{ $roleNames[$user->role] ?? 'Role' }}</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary add-participant-btn">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <div class="my-2">
                        <a href="javascript:void(0);" id="toggle-add-others">Add Others</a> |
                        <label class="mb-0 ms-2" style="cursor:pointer;">
                            Import Participants (CSV)
                            <input type="file" name="import_participants" id="import_participants" accept=".csv"
                                style="display:none">
                        </label>
                    </div>

                    <button type="submit" form="editMeetingForm" class="btn btn-primary"><i
                            class="bi bi-calendar-check me-2"></i>Update
                        Meeting</button>
                    <a href="{{ route('meets.index') }}" class="btn btn-light border">Cancel</a>
                </div>

                <div class="card mb-4" style="border-radius: 0.35em !important; display: none;" id="add-others-panel">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Add New Participants</h5>
                        <a href="javascript:void(0);" id="back-to-participants-search" class="small">Back</a>
                    </div>
                    <div class="card-body">
                        <div id="other-rows">
                            <div class="row other-row mb-2 align-items-center">
                                <div class="col-sm-4">
                                    <input type="text" class="form-control form-control-sm" placeholder="First Name*">
                                </div>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control form-control-sm" placeholder="Last Name">
                                </div>
                                <div class="col-sm-4">
                                    <input type="email" class="form-control form-control-sm" placeholder="Email*">
                                </div>
                                <div class="col-sm-12 mt-2">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="WhatsApp Number*" maxlength="15">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <button type="button" id="add-new-participant-btn" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-lg"></i> Add Participant
                            </button>
                            <small class="text-muted ms-2">Users will be created upon submission.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
        }

        .text-muted-light {
            color: #6c757d;
        }

        .form-label {
            font-weight: 500;
        }

        .participant-scroll {
            max-height: 250px;
            overflow-y: auto;
        }

        .participant-item.hidden {
            display: none !important;
        }

        .participant-item:hover {
            background-color: #f8f9fa;
        }

        .selected-participant-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.3em 0.6em;
            font-size: 90%;
            font-weight: 500;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // --- PLATFORM SELECTION SCRIPT ---
        const platformSelect = document.getElementById('platform-select');
        const meetLinkInput = document.getElementById('meet-link-input');
        const linkHelper = document.getElementById('link-helper');

        if (platformSelect) {
            platformSelect.addEventListener('change', function(event) {
                const selectedPlatform = event.target.value;
                let url;

                switch (selectedPlatform) {
                    case 'Google Meet':
                        url = 'https://meet.new';
                        break;
                    case 'Zoom':
                        url = 'https://zoom.us/meeting/schedule';
                        break;
                    case 'Teams':
                        url = 'https://teams.microsoft.com/';
                        break;
                    default:
                        url = null;
                }

                if (url) {
                    window.open(url, '_blank');
                    linkHelper.style.display = 'block';
                    meetLinkInput.focus();
                    meetLinkInput.placeholder = `Paste your ${selectedPlatform} link here`;
                } else {
                    linkHelper.style.display = 'none';
                    meetLinkInput.placeholder = 'Meeting Link';
                }
            });
        }

        // --- PARTICIPANT SELECTION SCRIPT ---
        const searchInput = document.querySelector('.participant-search');
        const participantItems = document.querySelectorAll('.participant-item');
        const selectedParticipantsContainer = document.getElementById('selected-participants');
        const form = document.getElementById('editMeetingForm');

        // --- "ADD OTHERS" SCRIPT ---
        const toggleAddOthersLink = document.getElementById('toggle-add-others');
        const backToSearchLink = document.getElementById('back-to-participants-search');

        // Search functionality
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                participantItems.forEach(item => {
                    const searchTerm = item.dataset.searchTerm || '';
                    if (searchTerm.includes(query)) {
                        item.classList.remove('hidden');
                    } else {
                        item.classList.add('hidden');
                    }
                });
            });
        }

        // Add/Remove participant
        document.querySelector('.col-lg-4').addEventListener('click', function(e) {
            const addButton = e.target.closest('.add-participant-btn');
            const removeButton = e.target.closest('.remove-participant-btn');
            const addNewBtn = e.target.closest('#add-new-participant-btn');

            if (addButton) {
                const participantItem = addButton.closest('.participant-item');
                const userId = participantItem.dataset.userId;
                const userName = participantItem.dataset.userName;

                // Hide initial "No participants" message
                if (selectedParticipantsContainer.querySelector('.text-muted-light')) {
                    selectedParticipantsContainer.innerHTML = '';
                }

                // Create and add badge
                const badge = document.createElement('div');
                badge.className = 'selected-participant-badge bg-light border rounded-pill me-2 mb-2';
                badge.id = `badge-${userId}`;
                badge.innerHTML = `
                    <span>${userName}</span>
                    <button type="button" class="btn-close btn-sm ms-2 remove-participant-btn" aria-label="Close" data-user-id="${userId}"></button>
                `;
                selectedParticipantsContainer.appendChild(badge);

                // Create and add hidden input
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'users[]';
                hiddenInput.value = userId;
                hiddenInput.id = `user-input-${userId}`;
                form.appendChild(hiddenInput);

                // Hide the item from the list
                participantItem.classList.add('hidden');
            }

            if (removeButton) {
                const userId = removeButton.dataset.userId;

                // Remove the badge
                const badge = document.getElementById(`badge-${userId}`);
                if (badge) {
                    badge.remove();
                }

                // Remove the hidden input
                const input = document.getElementById(`user-input-${userId}`);
                if (input) {
                    input.remove();
                }

                // Show the item in the list again
                const participantItem = document.querySelector(`.participant-item[data-user-id="${userId}"]`);
                if (participantItem) {
                    participantItem.classList.remove('hidden');
                }

                // Show "No participants" message if container is empty
                if (selectedParticipantsContainer.children.length === 0) {
                    selectedParticipantsContainer.innerHTML =
                        '<div class="text-center text-muted-light py-3">No participants added yet.</div>';
                }
            }

            if (addNewBtn) {
                const otherRow = document.querySelector('#other-rows .other-row');
                const firstNameInput = otherRow.querySelector('input[placeholder="First Name*"]');
                const lastNameInput = otherRow.querySelector('input[placeholder="Last Name"]');
                const emailInput = otherRow.querySelector('input[placeholder="Email*"]');
                const contactNoInput = otherRow.querySelector('input[placeholder="WhatsApp Number*"]');

                const firstName = firstNameInput.value.trim();
                const email = emailInput.value.trim();
                const contactNo = contactNoInput.value.trim();

                if (!firstName || !contactNo) {
                    alert('First Name and WhatsApp Number are required for new participants.');
                    return;
                }

                const lastName = lastNameInput.value.trim();
                const userName = `${firstName} ${lastName}`.trim();
                const uniqueId = email ? email.replace(/[^a-zA-Z0-9]/g, '') : contactNo.replace(/[^a-zA-Z0-9]/g, '');
                const newParticipantId = `new_${uniqueId}`;

                // Check if already added
                if (document.getElementById(`user-input-${newParticipantId}`)) {
                    alert('This participant has already been added.');
                    return;
                }

                // Hide initial "No participants" message
                if (selectedParticipantsContainer.querySelector('.text-muted-light')) {
                    selectedParticipantsContainer.innerHTML = '';
                }

                // Create and add badge
                const badge = document.createElement('div');
                badge.className = 'selected-participant-badge bg-light border rounded-pill me-2 mb-2';
                badge.innerHTML = `
                    <span>${userName} (New)</span>
                    <button type="button" class="btn-close btn-sm ms-2 remove-participant-btn" aria-label="Close" data-user-id="${newParticipantId}"></button>
                `;
                selectedParticipantsContainer.appendChild(badge);

                // Create and add hidden inputs for the new participant
                const container = document.createElement('div');
                container.id = `user-input-${newParticipantId}`;
                container.innerHTML = `
                    <input type="hidden" name="new_participants[${newParticipantId}][firstName]" value="${firstName}">
                    <input type="hidden" name="new_participants[${newParticipantId}][lastName]" value="${lastName}">
                    <input type="hidden" name="new_participants[${newParticipantId}][email]" value="${email}">
                    <input type="hidden" name="new_participants[${newParticipantId}][contactNo]" value="${contactNo}">
                `;
                form.appendChild(container);

                // Clear input fields
                firstNameInput.value = '';
                lastNameInput.value = '';
                emailInput.value = '';
                contactNoInput.value = '';
                firstNameInput.focus();
            }
        });

        if (toggleAddOthersLink) {
            const participantsCard = document.getElementById('participants-card');
            const addOthersPanel = document.getElementById('add-others-panel');

            toggleAddOthersLink.addEventListener('click', function(e) {
                e.preventDefault();
                participantsCard.style.display = 'none';
                addOthersPanel.style.display = 'block';
            });

            backToSearchLink.addEventListener('click', function(e) {
                e.preventDefault();
                participantsCard.style.display = 'block';
                addOthersPanel.style.display = 'none';
            });
        }
    </script>
@endpush

