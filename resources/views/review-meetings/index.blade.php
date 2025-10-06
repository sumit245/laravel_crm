@extends('layouts.main')
@section('content')
    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Meetings</h2>
                <p class="text-muted-light">Manage and track all your meetings in one place.</p>
            </div>
            <a href={{ route('meets.create') }} class="btn btn-primary">Create Meeting</a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Search Meetings</h5>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0"
                        placeholder="Search by title, agenda, or type...">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Meetings</h5>
                @if ($meets instanceof \Illuminate\Pagination\LengthAwarePaginator && $meets->total() > 0)
                    <span class="text-muted-light small">
                        Showing {{ $meets->firstItem() }} to {{ $meets->lastItem() }} of {{ $meets->total() }}
                        meetings
                    </span>
                @elseif(!$meets instanceof \Illuminate\Pagination\LengthAwarePaginator && $meets->count() > 0)
                    <span class="text-muted-light small">
                        Showing {{ $meets->count() }} of {{ $meets->count() }} meetings
                    </span>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-4">Title</th>
                                <th scope="col">Agenda</th>
                                <th scope="col">Platform</th>
                                <th scope="col">Date & Time</th>
                                <th scope="col">Type</th>
                                <th scope="col">Participants</th>
                                {{-- <th scope="col">Status</th> --}}
                                <th scope="col" class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($meets as $meeting)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $meeting['title'] }}</td>

                                    <td class="agenda-cell">{{ $meeting['agenda'] }}</td>
                                    <td>
                                        <a href="{{ $meeting['meet_link'] ?? '#' }}" target="_blank"
                                            class="text-dark text-decoration-none">
                                            @if ($meeting['platform'] == 'Google Meet')
                                                <i class="bi bi-camera-video text-warning"></i>
                                            @elseif($meeting['platform'] == 'Zoom')
                                                <i class="bi bi-camera-reels text-primary"></i>
                                            @else
                                                <i class="bi bi-microsoft-teams text-info"></i>
                                            @endif
                                            {{ $meeting['platform'] }}
                                        </a>
                                    </td>
                                    <td>
                                        <div>{{ \Carbon\Carbon::parse($meeting['meet_date'])->format('Y-m-d') }}</div>
                                        <div class="small text-muted-light">
                                            {{ \Carbon\Carbon::parse($meeting['meet_time'])->format('H:i') }}</div>
                                    </td>
                                    <td><span
                                            class="badge rounded-pill text-dark bg-{{ $meeting['type_color'] }} bg-opacity-10 border border-{{ $meeting['type_color'] }} border-opacity-10">{{ $meeting['type'] }}</span>
                                    </td>

                                    <td><i class="bi bi-people"></i>
                                        {{-- FIXME: I want to show number of attendees here --}}
                                        {{ $meeting->attendees_count }}
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="dropdown actions-dropdown">
                                            <button class="btn btn-light btn-sm dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="{{ $meeting['meet_link'] ?? '#' }}"
                                                        target="_blank"><i class="bi bi-box-arrow-in-right me-2"></i>Join
                                                        Meeting</a></li>
                                                <li><a class="dropdown-item"
                                                        href={{ route('meets.details', parameters: $meeting['id']) }}>
                                                        <i class="bi bi-eye me-2"></i>
                                                        View Meeting
                                                    </a></li>
                                                <li><a class="dropdown-item"
                                                        href="{{ route('meets.edit', $meeting['id']) }}"><i
                                                            class="bi bi-pencil me-2"></i>Edit Meeting</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                        data-bs-target="#rescheduleModal"
                                                        data-meeting-id="{{ $meeting['id'] }}"
                                                        data-meeting-date="{{ \Carbon\Carbon::parse($meeting['meet_date'])->format('Y-m-d') }}"
                                                        data-meeting-time="{{ \Carbon\Carbon::parse($meeting['meet_time'])->format('H:i') }}"><i
                                                            class="bi bi-calendar-event me-2"></i>Reschedule</a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li><a class="dropdown-item text-danger" href="#"><i
                                                            class="bi bi-trash me-2"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($meets instanceof \Illuminate\Pagination\LengthAwarePaginator && $meets->hasPages())
                <div class="card-footer bg-white">
                    {{ $meets->links() }}
                </div>
            @endif
        </div>

        <!-- Reschedule Modal -->
        <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rescheduleModalLabel">Reschedule Meeting</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="rescheduleForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="reschedule_date" class="form-label">New Date</label>
                                <input type="date" class="form-control" id="reschedule_date" name="meet_date" required>
                            </div>
                            <div class="mb-3">
                                <label for="reschedule_time" class="form-label">New Time</label>
                                <input type="time" class="form-control" id="reschedule_time" name="meet_time"
                                    required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Reschedule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('styles')
    <style>
        .table> :not(caption)>*>* {
            vertical-align: middle;
        }

        .table .fw-bold {
            font-weight: 600 !important;
        }

        .text-muted-light {
            color: #6c757d !important;
        }

        .actions-dropdown .dropdown-toggle::after {
            display: none;
        }

        .badge {
            font-weight: 500;
        }

        .agenda-cell {
            max-width: 40ch;
            word-wrap: break-word;
            overflow: hidden;
            text-overflow: ellipsis
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rescheduleModal = document.getElementById('rescheduleModal');
            if (rescheduleModal) {
                rescheduleModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const meetingId = button.getAttribute('data-meeting-id');
                    const meetingDate = button.getAttribute('data-meeting-date');
                    const meetingTime = button.getAttribute('data-meeting-time');

                    const form = document.getElementById('rescheduleForm');
                    form.action = `/meets/${meetingId}/reschedule`;
                    form.querySelector('#reschedule_date').value = meetingDate;
                    form.querySelector('#reschedule_time').value = meetingTime;
                });
            }
        });
    </script>
@endpush
