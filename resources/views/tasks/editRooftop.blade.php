@extends('layouts.main')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Edit Rooftop Task</h4>
                        <form action="{{ route('tasks.updaterooftop', $task->id) }}" method="POST">
                            @csrf
                            @method('POST')

                            <div class="mb-3">
                                <label for="site_id" class="form-label">Site Name</label>
                                <select class="form-select" id="site_id" name="site_id" required>
                                    <option value="">Select a site</option>
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}"
                                            {{ $task->site_id == $site->id ? 'selected' : '' }}>
                                            {{ $site->site_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="activity" class="form-label">Activity</label>
                                <select id="activity" name="activity" class="form-select" required>
                                    <option value="Installation" {{ $task->activity == 'Installation' ? 'selected' : '' }}>
                                        Installation</option>
                                    <option value="RMS" {{ $task->activity == 'RMS' ? 'selected' : '' }}>RMS</option>
                                    <option value="Billing" {{ $task->activity == 'Billing' ? 'selected' : '' }}>Billing
                                    </option>
                                    <option value="Add Team" {{ $task->activity == 'Add Team' ? 'selected' : '' }}>Add Team
                                    </option>
                                    <option value="Survey" {{ $task->activity == 'Survey' ? 'selected' : '' }}>Survey
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="selectEngineer" class="form-label">Select Site Engineer</label>
                                <select id="selectEngineer" name="engineer_id" class="form-select" required>
                                    <option value="">Select Engineer</option>
                                    @foreach ($engineers as $engineer)
                                        <option value="{{ $engineer->id }}"
                                            {{ $task->engineer_id == $engineer->id ? 'selected' : '' }}>
                                            {{ $engineer->firstName }} {{ $engineer->lastName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-select">
                                    <option value="">Select Status</option>
                                    @foreach (\App\Enums\TaskStatus::cases() as $status)
                                        <option value="{{ $status->value }}"
                                            {{ ($task->status ?? '') == $status->value ? 'selected' : '' }}>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('tasks.index') }}" class="btn btn-light me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Task</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize any plugins or form validation here
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection
