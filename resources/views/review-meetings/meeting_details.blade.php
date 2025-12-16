@extends('layouts.main')
@section('content')
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        .modern-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
        }

        .modern-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
            border: none;
        }

        .stat-card:nth-child(2) {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-card:nth-child(3) {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-card .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 8px 0;
        }

        .stat-card .stat-label {
            font-size: 0.875rem;
            opacity: 0.9;
            font-weight: 500;
        }

        .modern-btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.2s ease;
            border: none;
        }

        .modern-btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .modern-btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .modern-input {
            border-radius: 8px;
            border: 1px solid var(--gray-300);
            padding: 10px 14px;
            transition: all 0.2s ease;
        }

        .modern-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        .modern-select {
            border-radius: 8px;
            border: 1px solid var(--gray-300);
            padding: 10px 14px;
            transition: all 0.2s ease;
        }

        .modern-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        .task-card-modern {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: var(--shadow);
            border-left: 4px solid;
            transition: all 0.2s ease;
        }

        .task-card-modern:hover {
            box-shadow: var(--shadow-md);
            transform: translateX(4px);
        }

        /* Simple Horizontal Tabs */
        .nav-tabs-modern {
            border-bottom: 1px solid var(--gray-200);
            background: transparent;
            margin: 0;
            padding: 0 24px;
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .nav-tabs-modern::-webkit-scrollbar {
            height: 4px;
        }

        .nav-tabs-modern::-webkit-scrollbar-track {
            background: transparent;
        }

        .nav-tabs-modern::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 2px;
        }

        .nav-tabs-modern .nav-item {
            margin-bottom: 0;
            flex-shrink: 0;
        }

        .nav-tabs-modern .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            color: var(--gray-600);
            font-weight: 500;
            font-size: 0.9375rem;
            padding: 14px 20px;
            margin-bottom: -1px;
            transition: all 0.2s ease;
            background: transparent;
            white-space: nowrap;
            position: relative;
        }

        .nav-tabs-modern .nav-link:hover {
            color: var(--primary-color);
            background: transparent;
            border-bottom-color: var(--gray-300);
        }

        .nav-tabs-modern .nav-link.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            background: transparent;
            font-weight: 600;
        }

        .nav-tabs-modern .nav-link i {
            font-size: 0.875rem;
            margin-right: 6px;
            opacity: 0.8;
        }

        .nav-tabs-modern .nav-link.active i {
            opacity: 1;
        }

        .modal-modern .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: var(--shadow-xl);
        }

        .modal-modern .modal-header {
            border-bottom: 1px solid var(--gray-200);
            padding: 24px;
        }

        .modal-modern .modal-body {
            padding: 24px;
        }

        .badge-modern {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .table-modern {
            border-radius: 12px;
            overflow: hidden;
        }

        .table-modern thead {
            background: var(--gray-50);
        }

        .table-modern thead th {
            font-weight: 600;
            color: var(--gray-700);
            border-bottom: 2px solid var(--gray-200);
            padding: 16px;
        }

        .table-modern tbody td {
            padding: 16px;
            border-bottom: 1px solid var(--gray-100);
        }

        .table-modern tbody tr:hover {
            background: var(--gray-50);
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .stat-card .stat-value {
                font-size: 2rem;
            }

            .container-fluid {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }

            .modern-card {
                margin: 0 -16px;
                border-radius: 0;
            }
        }

        /* Form improvements */
        .form-label {
            margin-bottom: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
        }

        /* Better spacing for tab content */
        .tab-pane {
            min-height: 300px;
        }

        /* Discussion Points header - single line layout */
        #discussion .d-flex.justify-content-between {
            flex-wrap: nowrap !important;
        }

        #discussion #action-buttons {
            flex-wrap: nowrap !important;
        }

        /* Responsive: wrap on smaller screens */
        @media (max-width: 992px) {
            #discussion .d-flex.justify-content-between {
                flex-wrap: wrap !important;
            }

            #discussion #action-buttons {
                flex-wrap: wrap !important;
                width: 100%;
                margin-top: 12px;
            }
        }

        @media (max-width: 768px) {
            #discussion #action-buttons>* {
                flex: 1 1 auto;
                min-width: 100% !important;
                max-width: 100% !important;
            }
        }

        /* Empty state styling */
        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .empty-state i {
            font-size: 4rem;
            opacity: 0.2;
            color: var(--gray-400);
            margin-bottom: 16px;
        }

        /* Project header improvements */
        .project-header {
            transition: all 0.2s ease;
        }

        .project-header:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Task card improvements */
        .task-card-modern h5 {
            font-size: 1.1rem;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .task-card-modern p {
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 0;
        }

        /* Select multiple styling */
        select[multiple].modern-select {
            min-height: 140px;
        }

        select[multiple].modern-select option {
            padding: 8px 12px;
        }

        select[multiple].modern-select option:checked {
            background: var(--primary-color);
            color: white;
        }

        /* Better focus states */
        select[multiple].modern-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Select2 Modern ERP Styling */
        .select2-container--default .select2-selection--multiple {
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            min-height: 42px;
            padding: 4px 8px;
            background: white;
            transition: all 0.2s ease;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: var(--primary-color);
            border: none;
            border-radius: 6px;
            color: white;
            padding: 4px 10px;
            margin: 4px 4px 4px 0;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            margin-right: 4px;
            font-weight: bold;
            font-size: 1rem;
            line-height: 1;
            opacity: 0.8;
            transition: opacity 0.2s ease;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            opacity: 1;
            color: white;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            padding: 0;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__placeholder {
            color: var(--gray-400);
            margin-left: 4px;
        }

        .select2-container--default .select2-search--inline .select2-search__field {
            margin-top: 4px;
            padding: 4px 8px;
            font-size: 0.95rem;
            color: var(--gray-700);
            border: none;
            outline: none;
        }

        .select2-container--default .select2-search--inline .select2-search__field::placeholder {
            color: var(--gray-400);
        }

        .select2-dropdown {
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            box-shadow: var(--shadow-lg);
            margin-top: 4px;
        }

        .select2-container--default .select2-results__option {
            padding: 10px 14px;
            font-size: 0.95rem;
            color: var(--gray-700);
            transition: all 0.2s ease;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: var(--primary-color);
            color: white;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background: var(--gray-100);
            color: var(--gray-900);
        }

        .select2-container--default .select2-results__option[aria-selected=true].select2-results__option--highlighted {
            background: var(--primary-color);
            color: white;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid var(--gray-300);
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 0.95rem;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }
    </style>

    <div class="container-fluid px-4 py-4" style="max-width: 1400px; margin: 0 auto;">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
            <div class="flex-grow-1">
                <a href="javascript:history.back()" class="btn btn-light border-0 mb-3 modern-btn"
                    style="background: var(--gray-100); color: var(--gray-700); padding: 8px 16px;">
                    <i class="bi bi-arrow-left me-2"></i>Back to Meetings
                </a>
                <h2 class="mb-2 fw-bold" style="color: var(--gray-900); font-size: 1.75rem; line-height: 1.2;">
                    {{ $meet->title }}
                </h2>
                <p class="text-muted mb-0" style="font-size: 0.95rem;">
                    <i class="bi bi-calendar3 me-2"></i>{{ \Carbon\Carbon::parse($meet->meet_date)->format('F d, Y') }}
                    <span class="mx-2">•</span>
                    <i class="bi bi-clock me-2"></i>{{ \Carbon\Carbon::parse($meet->meet_time)->format('h:i A') }}
                </p>
            </div>
            <a href="{{ $meet->meet_link }}" target="_blank" class="btn modern-btn modern-btn-primary"
                style="white-space: nowrap;">
                <i class="bi bi-camera-video me-2"></i>Join Meeting
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-lg-4 col-md-6">
                <div class="stat-card h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="stat-label">Platform</div>
                            <div class="stat-value">{{ $meet->platform }}</div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-camera-video"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="stat-card h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="stat-label">Attendees</div>
                            <div class="stat-value">{{ $meet->attendees->count() }}</div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12">
                <div class="stat-card h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="stat-label">Tasks</div>
                            <div class="stat-value">{{ $taskCounts['total'] }}</div>
                            <div class="mt-2 d-flex gap-2 flex-wrap">
                                <span class="badge badge-modern"
                                    style="background: rgba(255,255,255,0.3); font-size: 0.7rem;">
                                    <span class="task-status-dot dot-done"></span>{{ $taskCounts['done'] }} Done
                                </span>
                                <span class="badge badge-modern"
                                    style="background: rgba(255,255,255,0.3); font-size: 0.7rem;">
                                    <span class="task-status-dot dot-progress"></span>{{ $taskCounts['progress'] }} Progress
                                </span>
                                <span class="badge badge-modern"
                                    style="background: rgba(255,255,255,0.3); font-size: 0.7rem;">
                                    <span class="task-status-dot dot-pending"></span>{{ $taskCounts['pending'] }} Pending
                                </span>
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="modern-card" style="padding: 0; overflow: hidden;">
            <!-- Simple Horizontal Tabs -->
            <ul class="nav nav-tabs nav-tabs-modern" id="meetingTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ ($activeTab ?? 'overview') === 'overview' ? 'active' : '' }}"
                        id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab"
                        aria-controls="overview"
                        aria-selected="{{ ($activeTab ?? 'overview') === 'overview' ? 'true' : 'false' }}">
                        <i class="bi bi-info-circle"></i>Overview
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ ($activeTab ?? 'overview') === 'discussion' ? 'active' : '' }}"
                        id="discussion-tab" data-bs-toggle="tab" data-bs-target="#discussion" type="button" role="tab"
                        aria-controls="discussion"
                        aria-selected="{{ ($activeTab ?? 'overview') === 'discussion' ? 'true' : 'false' }}">
                        <i class="bi bi-list-check"></i>Discussion Points
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ ($activeTab ?? 'overview') === 'attendees' ? 'active' : '' }}"
                        id="attendees-tab" data-bs-toggle="tab" data-bs-target="#attendees" type="button" role="tab"
                        aria-controls="attendees"
                        aria-selected="{{ ($activeTab ?? 'overview') === 'attendees' ? 'true' : 'false' }}">
                        <i class="bi bi-people"></i>Attendees
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ ($activeTab ?? 'overview') === 'responsibilities' ? 'active' : '' }}"
                        id="responsibilities-tab" data-bs-toggle="tab" data-bs-target="#responsibilities" type="button"
                        role="tab" aria-controls="responsibilities"
                        aria-selected="{{ ($activeTab ?? 'overview') === 'responsibilities' ? 'true' : 'false' }}">
                        <i class="bi bi-person-check"></i>Responsibilities
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ ($activeTab ?? 'overview') === 'followups' ? 'active' : '' }}"
                        id="followups-tab" data-bs-toggle="tab" data-bs-target="#followups" type="button"
                        role="tab" aria-controls="followups"
                        aria-selected="{{ ($activeTab ?? 'overview') === 'followups' ? 'true' : 'false' }}">
                        <i class="bi bi-calendar-event"></i>Follow-ups
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="meetingTabContent" style="min-height: 400px;">
                {{-- Tab 1 - Overview --}}
                <div class="tab-pane fade {{ ($activeTab ?? 'overview') === 'overview' ? 'show active' : '' }}"
                    id="overview" role="tabpanel" aria-labelledby="overview-tab" style="padding: 24px;">
                    <div class="row">
                        <div class="col-lg-8">
                            <h4 class="mb-4 fw-bold" style="color: var(--gray-900);">Meeting Details</h4>
                            <div class="mb-4">
                                <label class="form-label fw-semibold text-muted mb-2"
                                    style="font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px;">Agenda</label>
                                <div class="p-3 rounded"
                                    style="background: var(--gray-50); border-left: 3px solid var(--primary-color);">
                                    <p class="mb-0" style="color: var(--gray-700); line-height: 1.6;">
                                        {{ $meet->agenda ?? 'No agenda provided.' }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <label class="form-label fw-semibold text-muted mb-2"
                                    style="font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px;">Task
                                    Status</label>
                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge badge-modern bg-success">{{ $taskCounts['done'] }} Done</span>
                                    <span class="badge badge-modern bg-warning text-dark">{{ $taskCounts['progress'] }} In
                                        Progress</span>
                                    <span class="badge badge-modern bg-secondary">{{ $taskCounts['pending'] }}
                                        Pending</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="p-4 rounded"
                                style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%); color: white;">
                                <h5 class="mb-3 fw-bold">Quick Info</h5>
                                <div class="mb-3">
                                    <div class="small opacity-75 mb-1">Meeting Type</div>
                                    <div class="fw-semibold">{{ $meet->type ?? 'N/A' }}</div>
                                </div>
                                <div class="mb-3">
                                    <div class="small opacity-75 mb-1">Platform</div>
                                    <div class="fw-semibold">{{ $meet->platform }}</div>
                                </div>
                                <div>
                                    <div class="small opacity-75 mb-1">Total Attendees</div>
                                    <div class="fw-semibold">{{ $meet->attendees->count() }} People</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab 2 - Discussion Points & Tasks --}}
                <div class="tab-pane fade {{ ($activeTab ?? 'overview') === 'discussion' ? 'show active' : '' }}"
                    id="discussion" role="tabpanel" aria-labelledby="discussion-tab" style="padding: 24px;">
                    <div class="d-flex justify-content-between align-items-center mb-4 gap-3" style="flex-wrap: nowrap;">
                        <h4 class="mb-0 fw-bold flex-shrink-0" style="color: var(--gray-900); white-space: nowrap;">
                            Discussion Points & Tasks</h4>
                        <div class="d-flex gap-2 align-items-center flex-nowrap" id="action-buttons"
                            style="flex: 1; justify-content: flex-end; min-width: 0;">
                            <div class="position-relative flex-shrink-1" style="min-width: 180px; max-width: 250px;">
                                <i class="bi bi-search position-absolute"
                                    style="left: 12px; top: 50%; transform: translateY(-50%); color: var(--gray-400); z-index: 1; pointer-events: none;"></i>
                                <input type="text" id="task-search-input" class="form-control modern-input ps-4"
                                    placeholder="Search tasks..." style="width: 100%;">
                            </div>
                            <select class="form-select modern-select flex-shrink-0" id="department-filter"
                                style="min-width: 160px; max-width: 200px;">
                                <option selected value="all">All Departments</option>
                                @foreach ($departments as $department)
                                    @if ($department)
                                        <option value="{{ $department }}">{{ $department }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button
                                class="btn modern-btn modern-btn-primary d-flex align-items-center justify-content-center flex-shrink-0"
                                data-bs-toggle="modal" data-bs-target="#addDiscussionPointModal"
                                style="white-space: nowrap;">
                                <i class="bi bi-plus-lg me-2"></i>Add Task
                            </button>
                        </div>
                    </div>
                    @php
                        // Color palettes for different projects - each project gets a distinct color scheme
                        $projectColors = [
                            'no-project' => ['border' => '#e0e0e0', 'bg' => '#f8f9fa', 'accent' => '#6c757d'],
                        ];
                        $colorIndex = 0;
                        $colorSchemes = [
                            ['border' => '#d4e5f7', 'bg' => '#e8f4f8', 'accent' => '#2196F3', 'text' => '#1565C0'],
                            ['border' => '#ffe0b2', 'bg' => '#fff3e0', 'accent' => '#FF9800', 'text' => '#E65100'],
                            ['border' => '#c8e6c9', 'bg' => '#e8f5e9', 'accent' => '#4CAF50', 'text' => '#2E7D32'],
                            ['border' => '#f8bbd0', 'bg' => '#fce4ec', 'accent' => '#E91E63', 'text' => '#C2185B'],
                            ['border' => '#d1c4e9', 'bg' => '#ede7f6', 'accent' => '#9C27B0', 'text' => '#7B1FA2'],
                            ['border' => '#b3e5fc', 'bg' => '#e0f7fa', 'accent' => '#00BCD4', 'text' => '#00838F'],
                        ];
                    @endphp
                    @forelse ($discussionPointsByProject as $projectKey => $points)
                        @php
                            if ($projectKey === 'no-project') {
                                $project = null;
                                $projectName = 'No Project';
                                $colorScheme = $projectColors['no-project'];
                            } else {
                                $project = $points->first()->project;
                                $projectName = $project->project_name ?? 'Unknown Project';
                                if (!isset($projectColors[$projectKey])) {
                                    $projectColors[$projectKey] = $colorSchemes[$colorIndex % count($colorSchemes)];
                                    $colorIndex++;
                                }
                                $colorScheme = $projectColors[$projectKey];
                            }
                        @endphp
                        <div class="project-group mb-4">
                            <div class="project-header p-3 mb-3 rounded"
                                style="background: linear-gradient(135deg, {{ $colorScheme['bg'] }} 0%, {{ $colorScheme['border'] }} 100%); border-left: 4px solid {{ $colorScheme['accent'] }}; box-shadow: var(--shadow-sm);">
                                <h5 class="mb-0 fw-bold d-flex align-items-center"
                                    style="color: {{ $colorScheme['text'] ?? $colorScheme['accent'] }};">
                                    <i class="bi bi-folder-fill me-2"></i>{{ $projectName }}
                                    <span class="badge ms-2 badge-modern"
                                        style="background-color: {{ $colorScheme['accent'] }}; color: white;">{{ $points->count() }}
                                        Task{{ $points->count() > 1 ? 's' : '' }}</span>
                                </h5>
                            </div>
                            @foreach ($points as $pointIndex => $point)
                                <div class="task-card-modern"
                                    data-assignee-name="{{ $point->assignedUsers->pluck('name')->join(', ') ?: $point->assignedToUser->name ?? '' }}"
                                    style="border-left-color: {{ $colorScheme['accent'] }};">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 style="color: {{ $colorScheme['text'] ?? $colorScheme['accent'] }};">
                                                @if ($point->status == 'Completed')
                                                    <i class="bi bi-check-circle-fill me-2" style="color: #28a745;"></i>
                                                @elseif($point->status == 'In Progress')
                                                    <i class="bi bi-clock-history me-2" style="color: #ffc107;"></i>
                                                @else
                                                    <i class="bi bi-exclamation-circle-fill me-2"
                                                        style="color: #dc3545;"></i>
                                                @endif
                                                {{ $point->title }}
                                                @if ($point->priority == 'High')
                                                    <span class="badge bg-danger ms-2"
                                                        style="font-size: 0.7rem;">HIGH</span>
                                                @elseif ($point->priority == 'Medium')
                                                    <span class="badge bg-warning text-dark ms-2"
                                                        style="font-size: 0.7rem;">MED</span>
                                                @else
                                                    <span class="badge bg-info ms-2" style="font-size: 0.7rem;">LOW</span>
                                                @endif
                                            </h5>
                                            <p class="small" style="color: #495057; font-weight: 500;">
                                                {{ $point->description }}</p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <div class="dropdown">
                                                <button
                                                    class="badge-modern dropdown-toggle badge w-100 border-0
                                            @if ($point->status == 'Completed') bg-success
                                            @elseif ($point->status == 'In Progress') bg-warning text-dark
                                            @else bg-secondary text-dark @endif"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                    style="cursor: pointer;">
                                                    {{ $point->status }}
                                                </button>
                                                <ul class="dropdown-menu shadow-md"
                                                    style="border-radius: 8px; border: 1px solid var(--gray-200);">
                                                    <li><a class="dropdown-item" href="#"
                                                            onclick="event.preventDefault(); document.getElementById('status-form-{{ $point->id }}-pending').submit();">
                                                            <i class="bi bi-clock me-2"></i>Pending
                                                        </a>
                                                    </li>
                                                    <li><a class="dropdown-item" href="#"
                                                            onclick="event.preventDefault(); document.getElementById('status-form-{{ $point->id }}-progress').submit();">
                                                            <i class="bi bi-arrow-repeat me-2"></i>In Progress
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#"
                                                            onclick="event.preventDefault(); document.getElementById('status-form-{{ $point->id }}-completed').submit();">
                                                            <i class="bi bi-check-circle me-2"></i>Completed
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <button
                                                class="btn btn-sm modern-btn d-flex align-items-center justify-content-center"
                                                style="background: var(--gray-100); color: var(--gray-700); padding: 6px 16px;"
                                                data-bs-toggle="modal" data-bs-target="#addNoteModal"
                                                data-point-id="{{ $point->id }}">
                                                <i class="bi bi-plus-lg me-1"></i>Add Note
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2"
                                        style="border-top: 1px solid {{ $colorScheme['border'] }};">
                                        <span class="small"
                                            style="color: {{ $colorScheme['text'] ?? $colorScheme['accent'] }}; font-weight: 600;">
                                            <i class="bi bi-person-fill me-1"
                                                style="color: {{ $colorScheme['accent'] }};"></i>
                                            <strong>Responsible:</strong>
                                            @if ($point->assignedUsers->count() > 0)
                                                @foreach ($point->assignedUsers as $assignedUser)
                                                    <span class="badge me-1"
                                                        style="background-color: {{ $colorScheme['accent'] }}; color: white; font-size: 0.75rem;">
                                                        {{ $assignedUser->name }}
                                                        @if ($assignedUser->department)
                                                            <small>({{ $assignedUser->department }})</small>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            @elseif ($point->assignedToUser)
                                                {{ $point->assignedToUser->name }}
                                                @if ($point->assignedToUser->department)
                                                    <span class="badge ms-2"
                                                        style="background-color: {{ $colorScheme['accent'] }}; color: white; font-size: 0.75rem;">{{ $point->assignedToUser->department }}</span>
                                                @endif
                                            @else
                                                Unassigned
                                            @endif
                                        </span>
                                        @if ($point->due_date)
                                            <span class="small fw-bold"
                                                style="color: {{ $point->due_date < now() && $point->status != 'Completed' ? '#dc3545' : $colorScheme['text'] ?? $colorScheme['accent'] }};">
                                                <i class="bi bi-calendar-event-fill me-1"></i>
                                                Due: {{ \Carbon\Carbon::parse($point->due_date)->format('d-m-Y') }}
                                            </span>
                                        @endif
                                    </div>
                                    @if ($point->updates->isNotEmpty())
                                        <div class="task-updates mt-3 pt-3"
                                            style="border-top: 2px dashed {{ $colorScheme['border'] }};">
                                            <h6 class="small mb-3 fw-bold" style="color: {{ $colorScheme['accent'] }};">
                                                <i class="bi bi-clock-history me-1"></i>Task Updates & Progress
                                            </h6>
                                            <ul class="timeliness">
                                                @foreach ($point->updates as $updateIndex => $update)
                                                    <li class="timeliness-item">
                                                        <div class="timeliness-marker"
                                                            style="border-color: {{ $colorScheme['accent'] }}; background-color: {{ $colorScheme['bg'] }};">
                                                        </div>
                                                        <div class="timeliness-content"
                                                            style="background-color: rgba(255,255,255,0.7); padding: 12px; border-radius: 8px; margin-bottom: 12px; border-left: 3px solid {{ $colorScheme['accent'] }};">
                                                            <p class="mb-2 fw-bold"
                                                                style="color: {{ $colorScheme['text'] ?? $colorScheme['accent'] }}; font-size: 0.9rem;">
                                                                <i class="bi bi-pencil-square me-1"></i>Update
                                                                #{{ $updateIndex + 1 }}
                                                                <span class="text-muted"
                                                                    style="font-size: 0.8rem; font-weight: normal;">({{ $update->created_at->format('d-m-Y H:i') }})</span>
                                                            </p>
                                                            <p class="mb-2" style="color: #495057; line-height: 1.6;">
                                                                {{ $update->update_text }}
                                                            </p>
                                                            @if ($update->vertical_head_remark)
                                                                <div class="remark mb-2 p-2 rounded"
                                                                    style="background-color: #fff3cd; border-left: 3px solid #ffc107;">
                                                                    <strong style="color: #856404;"><i
                                                                            class="bi bi-person-badge me-1"></i>Vertical
                                                                        Head Remark:</strong>
                                                                    <span
                                                                        style="color: #856404;">{{ $update->vertical_head_remark }}</span>
                                                                </div>
                                                            @endif
                                                            @if ($update->admin_remark)
                                                                <div class="remark mb-0 p-2 rounded"
                                                                    style="background-color: #d1ecf1; border-left: 3px solid #0dcaf0;">
                                                                    <strong style="color: #0c5460;"><i
                                                                            class="bi bi-shield-check me-1"></i>Admin
                                                                        Remark:</strong>
                                                                    <span
                                                                        style="color: #0c5460;">{{ $update->admin_remark }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        {{-- Hidden forms for status update --}}
                        @foreach ($points as $point)
                            {{-- Hidden forms for status update --}}
                            <form id="status-form-{{ $point->id }}-pending"
                                action="{{ route('discussion-points.update-status', $point->id) }}" method="POST"
                                class="d-none">
                                @csrf <input type="hidden" name="status" value="Pending">
                                <input type="hidden" name="active_tab" value="discussion">
                            </form>
                            <form id="status-form-{{ $point->id }}-progress"
                                action="{{ route('discussion-points.update-status', $point->id) }}" method="POST"
                                class="d-none">
                                @csrf <input type="hidden" name="status" value="In Progress">
                                <input type="hidden" name="active_tab" value="discussion">
                            </form>
                            <form id="status-form-{{ $point->id }}-completed"
                                action="{{ route('discussion-points.update-status', $point->id) }}" method="POST"
                                class="d-none">
                                @csrf <input type="hidden" name="status" value="Completed">
                                <input type="hidden" name="active_tab" value="discussion">
                            </form>
                        @endforeach
                    @empty
                        <div class="text-center text-muted p-4">No discussion points or tasks have been added yet.</div>
                    @endforelse
                </div>

                {{-- Tab 3 - Attendees --}}
                <div class="tab-pane fade {{ ($activeTab ?? 'overview') === 'attendees' ? 'show active' : '' }}"
                    id="attendees" role="tabpanel" aria-labelledby="attendees-tab" style="padding: 24px;">
                    <h4 class="mb-4 fw-bold" style="color: var(--gray-900);">Meeting Attendees</h4>
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th scope="col" style="font-weight: 600; color: var(--gray-700);">Name</th>
                                    <th scope="col" style="font-weight: 600; color: var(--gray-700);">Department</th>
                                    <th scope="col" style="font-weight: 600; color: var(--gray-700);">Role</th>
                                    <th scope="col" style="font-weight: 600; color: var(--gray-700);">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($meet->attendees as $attendee)
                                    <tr>
                                        <td style="font-weight: 500; color: var(--gray-900);">{{ $attendee->name }}</td>
                                        <td style="color: var(--gray-600);">{{ $attendee->department ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-modern"
                                                style="background: var(--gray-100); color: var(--gray-700);">
                                                {{ $attendee->role_name ?? 'Attendee' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn modern-btn modern-btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#addDiscussionPointModal"
                                                data-assignee-id="{{ $attendee->id }}"
                                                style="padding: 8px 16px; font-size: 0.9rem; font-weight: 500; white-space: nowrap; min-width: 110px;">
                                                <i class="bi bi-plus-lg me-2"></i>Add Task
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="bi bi-people fs-1 d-block mb-2" style="opacity: 0.3;"></i>
                                            No attendees found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab 4 - Responsibilities --}}
                <div class="tab-pane fade {{ ($activeTab ?? 'overview') === 'responsibilities' ? 'show active' : '' }}"
                    id="responsibilities" role="tabpanel" aria-labelledby="responsibilities-tab" style="padding: 24px;">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <h4 class="mb-0 fw-bold" style="color: var(--gray-900);">Staff Responsibilities Summary</h4>
                        <select class="form-select modern-select w-auto" id="responsibility-filter"
                            style="min-width: 200px;">
                            <option value="all">All Attendees</option>
                            @foreach ($responsibilities->keys() as $assigneeName)
                                @if ($assigneeName)
                                    <option value="{{ $assigneeName }}">{{ $assigneeName }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div id="responsibility-list">
                        @forelse ($responsibilities as $assigneeName => $tasks)
                            @if ($assigneeName)
                                <div class="responsibility-card mb-3" data-assignee-name="{{ $assigneeName }}">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="mb-0">{{ $assigneeName }}</h5>
                                            <small
                                                class="text-muted">{{ $tasks->first()->assignee->department ?? 'N/A' }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-secondary text-dark mb-1">{{ $tasks->count() }}
                                                Total</span>
                                            <span
                                                class="badge bg-success mb-1">{{ $tasks->where('status', 'Completed')->count() }}
                                                Done</span>
                                            <span
                                                class="badge bg-warning text-dark mb-1">{{ $tasks->where('status', 'In Progress')->count() }}
                                                Progress</span>
                                            <span
                                                class="badge bg-light text-dark mb-1">{{ $tasks->where('status', 'Pending')->count() }}
                                                Pending</span>
                                        </div>
                                    </div>
                                    <hr>
                                    <h6>All Tasks:</h6>
                                    @foreach ($tasks as $task)
                                        <div class="p-2 rounded d-flex justify-content-between align-items-center mb-2"
                                            style="background-color: #f8f9fa;">
                                            <div>
                                                @if ($task->status == 'Completed')
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                @endif
                                                {{ $task->title }}
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="dropdown">
                                                    <button
                                                        class="badge-dropdown dropdown-toggle badge 
                                                        @if ($task->status == 'Completed') bg-success
                                                        @elseif($task->status == 'In Progress') bg-warning text-dark
                                                        @else bg-secondary text-dark @endif"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        {{ $task->status }}
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#"
                                                                onclick="event.preventDefault(); document.getElementById('status-form-{{ $task->id }}-pending').submit();">Pending</a>
                                                        </li>
                                                        <li><a class="dropdown-item" href="#"
                                                                onclick="event.preventDefault(); document.getElementById('status-form-{{ $task->id }}-progress').submit();">In
                                                                Progress</a></li>
                                                        <li><a class="dropdown-item" href="#"
                                                                onclick="event.preventDefault(); document.getElementById('status-form-{{ $task->id }}-completed').submit();">Completed</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                @if ($task->due_date)
                                                    <span class="small text-muted">Due:
                                                        {{ \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @empty
                            <div class="text-center text-muted p-4">No one has been assigned any responsibilities yet.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Tab 5 - Follow-up Meetings --}}
                <div class="tab-pane fade {{ ($activeTab ?? 'overview') === 'followups' ? 'show active' : '' }}"
                    id="followups" role="tabpanel" aria-labelledby="followups-tab" style="padding: 24px;">
                    <h4 class="mb-4 fw-bold" style="color: var(--gray-900);">Follow-up Meetings</h4>
                    <div class="row g-3 mb-4">
                        @forelse ($meet->followUps as $followUp)
                            <div class="col-md-6">
                                <div class="modern-card p-3 h-100" style="border-left: 4px solid var(--primary-color);">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2 fw-bold" style="color: var(--gray-900);">
                                                {{ $followUp->title }}</h6>
                                            <p class="mb-2 text-muted small">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                {{ \Carbon\Carbon::parse($followUp->meet_date)->format('F d, Y') }}
                                            </p>
                                            @if ($followUp->meet_time)
                                                <p class="mb-2 text-muted small">
                                                    <i class="bi bi-clock me-1"></i>
                                                    {{ \Carbon\Carbon::parse($followUp->meet_time)->format('h:i A') }}
                                                </p>
                                            @endif
                                            @if ($followUp->status)
                                                <span class="badge badge-modern"
                                                    style="background: var(--gray-100); color: var(--gray-700);">
                                                    {{ $followUp->status }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($followUp->meet_id)
                                            <a href="{{ route('meets.details', $followUp->meet_id) }}"
                                                class="btn btn-sm modern-btn modern-btn-primary"
                                                style="white-space: nowrap; margin-left: 12px;">
                                                <i class="bi bi-eye me-1"></i>View
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2" style="opacity: 0.3;"></i>
                                    <p class="mb-0">No follow-up meetings have been scheduled.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-4">
                        <button class="btn modern-btn modern-btn-primary w-100" data-bs-toggle="modal"
                            data-bs-target="#scheduleFollowUpModal" style="padding: 12px;">
                            <i class="bi bi-plus-lg me-2"></i>Schedule Follow-up Meeting
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Add New Discussion Point Modal -->
    <div class="modal fade modal-modern" id="addDiscussionPointModal" tabindex="-1"
        aria-labelledby="addDiscussionPointModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 700px;">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: var(--shadow-xl);">
                <form action="{{ route('discussion-points.store') }}" method="POST" id="addTaskForm">
                    @csrf
                    <input type="hidden" name="meet_id" value="{{ $meet->id }}">
                    <input type="hidden" name="active_tab" id="active_tab_discussion" value="discussion">
                    <div class="modal-header" style="border-bottom: 1px solid var(--gray-200); padding: 24px 24px 20px;">
                        <h5 class="modal-title fw-bold mb-0" id="addDiscussionPointModalLabel"
                            style="color: var(--gray-900); font-size: 1.25rem;">
                            <i class="bi bi-plus-circle me-2" style="color: var(--primary-color);"></i>Create New Task
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="padding: 24px;">
                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label fw-semibold mb-2"
                                style="color: var(--gray-700); font-size: 0.875rem;">
                                Task Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control modern-input" id="title" name="title"
                                placeholder="Enter task title" required style="font-size: 0.95rem;">
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold mb-2"
                                style="color: var(--gray-700); font-size: 0.875rem;">
                                Description
                            </label>
                            <textarea class="form-control modern-input" id="description" name="description" rows="3"
                                style="min-height: 100px; resize: vertical; font-size: 0.95rem;" placeholder="Enter task description"></textarea>
                        </div>

                        <!-- Project -->
                        <div class="mb-4">
                            <label for="project_select" class="form-label fw-semibold mb-2"
                                style="color: var(--gray-700); font-size: 0.875rem;">
                                Project
                            </label>
                            <input type="text" class="form-control modern-input" id="project_select"
                                name="project_name" list="projects_list"
                                placeholder="Select project or type to create new..." autocomplete="off"
                                style="font-size: 0.95rem;">
                            <datalist id="projects_list">
                                @foreach ($projects as $project)
                                    <option value="{{ $project->project_name }}"
                                        data-project-id="{{ $project->id }}">
                                @endforeach
                            </datalist>
                            <input type="hidden" id="project_id" name="project_id" value="">
                            <small class="text-muted mt-1 d-block" style="font-size: 0.8rem;">
                                <i class="bi bi-info-circle me-1"></i>Type to search existing projects or enter a new
                                project name
                            </small>
                        </div>

                        <!-- Assigned To (Multiple) -->
                        <div class="mb-4">
                            <label for="assigned_to" class="form-label fw-semibold mb-2"
                                style="color: var(--gray-700); font-size: 0.875rem;">
                                Assigned To <small class="text-muted fw-normal">(Multiple selection allowed)</small>
                            </label>
                            <select class="form-select modern-select select2-assignees" id="assigned_to"
                                name="assigned_to[]" multiple style="width: 100%; font-size: 0.95rem;">
                                @foreach ($assignees as $assignee)
                                    <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted mt-1 d-block" style="font-size: 0.8rem;">
                                <i class="bi bi-info-circle me-1"></i>Search and select multiple assignees. Selected items
                                will appear as chips.
                            </small>
                        </div>

                        <!-- Assigned By & Department Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="assignee_id" class="form-label fw-semibold mb-2"
                                    style="color: var(--gray-700); font-size: 0.875rem;">
                                    Assigned By
                                </label>
                                <select class="form-select modern-select" id="assignee_id" name="assignee_id"
                                    style="font-size: 0.95rem;">
                                    <option value="">Select Assignee</option>
                                    @foreach ($assignees as $assignee)
                                        <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="department" class="form-label fw-semibold mb-2"
                                    style="color: var(--gray-700); font-size: 0.875rem;">
                                    Department <small class="text-muted fw-normal">(Type to add custom)</small>
                                </label>
                                <input type="text" class="form-control modern-input" id="department"
                                    name="department" list="departments_list"
                                    placeholder="Select or type department name..." autocomplete="off"
                                    style="font-size: 0.95rem;">
                                <datalist id="departments_list">
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept }}">
                                    @endforeach
                                    <option value="Design">
                                    <option value="Engineering">
                                    <option value="Product">
                                    <option value="QA">
                                    <option value="Client">
                                </datalist>
                                <small class="text-muted mt-1 d-block" style="font-size: 0.8rem;">
                                    <i class="bi bi-info-circle me-1"></i>Select from list or type a custom department name
                                </small>
                            </div>
                        </div>

                        <!-- Priority & Due Date Row -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="priority" class="form-label fw-semibold mb-2"
                                    style="color: var(--gray-700); font-size: 0.875rem;">
                                    Priority
                                </label>
                                <select class="form-select modern-select" id="priority" name="priority"
                                    style="font-size: 0.95rem;">
                                    <option value="High">High</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="due_date" class="form-label fw-semibold mb-2"
                                    style="color: var(--gray-700); font-size: 0.875rem;">
                                    Due Date
                                </label>
                                <input type="date" class="form-control modern-input" id="due_date" name="due_date"
                                    style="font-size: 0.95rem;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"
                        style="border-top: 1px solid var(--gray-200); padding: 20px 24px; background: var(--gray-50);">
                        <button type="button" class="btn modern-btn" data-bs-dismiss="modal"
                            style="background: white; color: var(--gray-700); border: 1px solid var(--gray-300); padding: 10px 20px;">
                            Cancel
                        </button>
                        <button type="submit" class="btn modern-btn modern-btn-primary" style="padding: 10px 24px;">
                            <i class="bi bi-check-lg me-2"></i>Create Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Note Modal -->
    <div class="modal fade modal-modern" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('discussion-points.updates.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="discussion_point_id" id="modal_discussion_point_id">
                    <input type="hidden" name="active_tab" id="active_tab_note" value="discussion">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="addNoteModalLabel">
                            <i class="bi bi-pencil-square me-2"></i>Add Note/Update
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="update_text" class="form-label fw-semibold">Update Note <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control modern-input" id="update_text" name="update_text" rows="3"
                                style="height:100px; min-height: 100px;" required placeholder="Provide a short update on the task..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="vertical_head_remark" class="form-label fw-semibold">Vertical Head Remark</label>
                            <textarea class="form-control modern-input" id="vertical_head_remark" name="vertical_head_remark" rows="2"
                                style="height:100px; min-height: 100px;" placeholder="Add remark from the vertical head (optional)"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="admin_remark" class="form-label fw-semibold">Admin Remark</label>
                            <textarea class="form-control modern-input" id="admin_remark" name="admin_remark" rows="2"
                                style="height:100px; min-height: 100px;" placeholder="Add admin remark (optional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid var(--gray-200); padding: 20px 24px;">
                        <button type="button" class="btn btn-light modern-btn" data-bs-dismiss="modal"
                            style="background: var(--gray-100); color: var(--gray-700);">Cancel</button>
                        <button type="submit" class="btn modern-btn modern-btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Save Note
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Schedule Follow-up Modal -->
    <div class="modal fade" id="scheduleFollowUpModal" tabindex="-1" aria-labelledby="scheduleFollowUpModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('meets.schedule-follow-up', $meet->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="active_tab" value="followups">
                    <div class="modal-header">
                        <h5 class="modal-title" id="scheduleFollowUpModalLabel">Schedule Follow-up Meeting</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="followup_title" class="form-label">Follow-up Title</label>
                            <input type="text" class="form-control" id="followup_title" name="title"
                                value="Follow-up: {{ $meet->title }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="followup_date" class="form-label">New Date</label>
                            <input type="date" class="form-control" id="followup_date" name="meet_date" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="followup_time_from" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="followup_time_from" name="meet_time_from"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="followup_time_to" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="followup_time_to" name="meet_time_to"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Schedule Follow-up</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .summary-card {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
        }

        .summary-card .card-body {
            padding: 1.25rem;
        }

        .summary-card h1,
        .summary-card .h1 {
            font-size: 2rem;
            font-weight: 600;
        }

        /* Ensure tabs are horizontal and not fixed */
        .modern-card .nav-tabs {
            position: relative !important;
            max-width: 100% !important;
            flex-wrap: nowrap !important;
            display: flex !important;
        }

        .nav-tabs .nav-link {
            border: 0;
            color: #6c757d;
            border-bottom: 2px solid transparent;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .nav-tabs .nav-link.active {
            color: #0d6efd;
            background-color: transparent;
            border-bottom: 2px solid #0d6efd;
        }

        .task-status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 4px;
        }

        .dot-done {
            background-color: #198754;
        }

        .dot-progress {
            background-color: #ffc107;
        }

        .dot-pending {
            background-color: #6c757d;
        }

        .task-card,
        .responsibility-card {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1.25rem;
        }

        .timeliness {
            list-style-type: none;
            padding-left: 0;
            margin-left: 10px;
            border-left: 2px solid #e9ecef;
            border-right: none;
        }

        .timeliness-item {
            position: relative;
            padding: 0.5rem 0 0.5rem 1.5rem;
        }

        .timeliness-marker {
            position: absolute;
            top: 1.0rem;
            left: -7px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #fff;
            border: 2px solid #0d6efd;
        }

        .timeliness-item:last-child {
            padding-bottom: 0;
        }

        .timeliness-content {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .timeliness-content strong {
            color: #343a40;
        }

        .remark {
            font-size: 0.8rem;
            padding-left: 1rem;
            border-left: 2px solid #f0f2f5;
            margin-top: 0.5rem;
            color: #495057;
            max-width: 460px;
            white-space: normal;
            word-wrap: break-word;
        }

        .table {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .table thead {
            background-color: #f8f9fa;
        }

        .project-group {
            margin-bottom: 2rem;
        }

        .project-header {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .project-header:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .task-card {
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .task-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateX(4px);
        }

        .task-updates {
            background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.3) 50%, transparent 100%);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Activate the correct tab based on URL parameter or activeTab variable
            var activeTab = '{{ $activeTab ?? 'overview' }}';
            if (activeTab) {
                var tabButton = document.querySelector('#' + activeTab + '-tab');
                var tabPane = document.querySelector('#' + activeTab);
                if (tabButton && tabPane) {
                    // Remove active class from all tabs and panes
                    document.querySelectorAll('.nav-link').forEach(function(link) {
                        link.classList.remove('active');
                        link.setAttribute('aria-selected', 'false');
                    });
                    document.querySelectorAll('.tab-pane').forEach(function(pane) {
                        pane.classList.remove('show', 'active');
                    });

                    // Activate the correct tab
                    tabButton.classList.add('active');
                    tabButton.setAttribute('aria-selected', 'true');
                    tabPane.classList.add('show', 'active');
                }
            }

            // Track active tab when user clicks on tabs
            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function(tabButton) {
                tabButton.addEventListener('shown.bs.tab', function(event) {
                    var targetTab = event.target.getAttribute('data-bs-target').replace('#', '');
                    // Update hidden fields in forms
                    document.querySelectorAll('input[name="active_tab"]').forEach(function(input) {
                        input.value = targetTab;
                    });
                });
            });
            const searchInput = document.getElementById('task-search-input');
            if (searchInput) {
                const taskCards = document.querySelectorAll('.task-card');
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();

                    taskCards.forEach(card => {
                        const assigneeName = (card.dataset.assigneeName || '').toLowerCase();
                        if (assigneeName.includes(searchTerm)) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }

            var addNoteModal = document.getElementById('addNoteModal');
            addNoteModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var pointId = button.getAttribute('data-point-id');
                var modalPointIdInput = addNoteModal.querySelector('#modal_discussion_point_id');
                modalPointIdInput.value = pointId;

                // Set active tab to discussion for notes
                var activeTabInput = addNoteModal.querySelector('#active_tab_note');
                if (activeTabInput) {
                    activeTabInput.value = 'discussion';
                }
            });

            // Initialize Select2 for assigned_to field
            function initializeSelect2() {
                const assigneeSelect = $('#assigned_to');
                if (assigneeSelect.length && !assigneeSelect.hasClass('select2-hidden-accessible')) {
                    assigneeSelect.select2({
                        placeholder: 'Search and select assignees...',
                        allowClear: false,
                        width: '100%',
                        closeOnSelect: false,
                        tags: false,
                        theme: 'default',
                        dropdownParent: $('#addDiscussionPointModal')
                    });
                }
            }

            // Initialize Select2 when modal is shown
            $('#addDiscussionPointModal').on('shown.bs.modal', function() {
                // Small delay to ensure modal is fully rendered
                setTimeout(function() {
                    initializeSelect2();
                }, 100);
            });

            // Handle modal show event for pre-selecting assignee
            $('#addDiscussionPointModal').on('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var assigneeId = button ? button.getAttribute('data-assignee-id') : null;

                // Clear previous selections when modal opens
                setTimeout(function() {
                    const assigneeSelect = $('#assigned_to');
                    if (assigneeSelect.hasClass('select2-hidden-accessible')) {
                        assigneeSelect.val(null).trigger('change');

                        // Pre-select the assignee if provided
                        if (assigneeId) {
                            assigneeSelect.val([assigneeId]).trigger('change');
                        }
                    }
                }, 200);
            });

            // Clean up Select2 when modal is hidden
            $('#addDiscussionPointModal').on('hidden.bs.modal', function() {
                const assigneeSelect = $('#assigned_to');
                if (assigneeSelect.hasClass('select2-hidden-accessible')) {
                    assigneeSelect.select2('destroy');
                }
            });

            const responsibilityFilter = document.getElementById('responsibility-filter');
            if (responsibilityFilter) {
                responsibilityFilter.addEventListener('change', function() {
                    const selectedAssignee = this.value;
                    const responsibilityCards = document.querySelectorAll(
                        '#responsibility-list .responsibility-card');

                    responsibilityCards.forEach(card => {
                        card.style.display = (selectedAssignee === 'all' || card.dataset
                            .assigneeName === selectedAssignee) ? 'block' : 'none';
                    });
                });
            }

            // Project selection handler
            const projectSelect = document.getElementById('project_select');
            const projectIdInput = document.getElementById('project_id');
            const projectsList = document.getElementById('projects_list');

            if (projectSelect) {
                projectSelect.addEventListener('input', function() {
                    const selectedValue = this.value;
                    const matchingOption = Array.from(projectsList.options).find(option => option.value ===
                        selectedValue);

                    if (matchingOption && matchingOption.dataset.projectId) {
                        projectIdInput.value = matchingOption.dataset.projectId;
                    } else {
                        projectIdInput.value = ''; // New project - will be created
                    }
                });

                projectSelect.addEventListener('change', function() {
                    const selectedValue = this.value;
                    const matchingOption = Array.from(projectsList.options).find(option => option.value ===
                        selectedValue);

                    if (matchingOption && matchingOption.dataset.projectId) {
                        projectIdInput.value = matchingOption.dataset.projectId;
                    } else {
                        projectIdInput.value = '';
                    }
                });
            }
        });

        // Show session messages as toast using SweetAlert2
        @if (session()->has('success'))
            const successMsg = {!! json_encode(session('success')) !!};
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: successMsg,
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });
        @endif

        @if (session()->has('error'))
            const errorMsg = {!! json_encode(session('error')) !!};
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: errorMsg,
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true
            });
        @endif
    </script>
@endpush
