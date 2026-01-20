@extends("layouts.main")

@section("content")
<div class="content-wrapper p-3">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold">Pole Details</h4>
                    <p class="text-muted mb-0 small">{{ $pole->complete_pole_number }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('poles.edit', $pole->id) }}" class="btn btn-warning btn-sm">
                        <i class="mdi mdi-pencil"></i> Edit
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="goBackWithFallback();">
                        <i class="mdi mdi-arrow-left"></i> Go Back
                    </button>
                </div>
            </div>
        </div>
    </div>

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

    <!-- Status Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card status-card">
                <div class="card-body text-center">
                    <i class="mdi mdi-check-circle mdi-36px text-success mb-2"></i>
                    <h6 class="text-muted mb-1">Survey Status</h6>
                    <span class="badge badge-lg {{ $pole->isSurveyDone ? 'badge-success' : 'badge-secondary' }}">
                        {{ $pole->isSurveyDone ? "Completed" : "Pending" }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card status-card">
                <div class="card-body text-center">
                    <i class="mdi mdi-led-on mdi-36px text-primary mb-2"></i>
                    <h6 class="text-muted mb-1">Installation Status</h6>
                    <span class="badge badge-lg {{ $pole->isInstallationDone ? 'badge-success' : 'badge-warning' }}">
                        {{ $pole->isInstallationDone ? "Installed" : "Not Installed" }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card status-card">
                <div class="card-body text-center">
                    <i class="mdi mdi-wifi mdi-36px text-info mb-2"></i>
                    <h6 class="text-muted mb-1">Network Status</h6>
                    <span class="badge badge-lg {{ $pole->isNetworkAvailable ? 'badge-success' : 'badge-secondary' }}">
                        {{ $pole->isNetworkAvailable ? "Available" : "Not Available" }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card status-card">
                <div class="card-body text-center">
                    <i class="mdi mdi-map-marker mdi-36px text-danger mb-2"></i>
                    <h6 class="text-muted mb-1">Location</h6>
                    <button type="button" class="btn btn-sm btn-link text-primary p-0" onclick="locateOnMap({{ $pole->lat }}, {{ $pole->lng }})">
                        View on Map
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Main Details -->
        <div class="col-lg-8">
            <!-- Basic Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">
                        <i class="mdi mdi-information text-primary"></i> Basic Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase fw-semibold mb-1">Pole Number</label>
                            <p class="mb-0 fw-semibold">{{ $pole->complete_pole_number }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase fw-semibold mb-1">Location Coordinates</label>
                            <p class="mb-0">{{ $pole->lat }}, {{ $pole->lng }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase fw-semibold mb-1">Beneficiary</label>
                            <p class="mb-0">{{ $pole->beneficiary ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase fw-semibold mb-1">Beneficiary Contact</label>
                            <p class="mb-0">{{ $pole->beneficiary_contact ?? 'N/A' }}</p>
                        </div>
                        @if($pole->remarks)
                        <div class="col-12 mb-3">
                            <label class="text-muted small text-uppercase fw-semibold mb-1">Remarks</label>
                            <p class="mb-0">{{ $pole->remarks }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Equipment Details Card -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">
                        <i class="mdi mdi-battery-charging text-primary"></i> Equipment Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase fw-semibold mb-1">Luminary QR</label>
                            <p class="mb-0 font-monospace">{{ $pole->luminary_qr ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase fw-semibold mb-1">SIM Number</label>
                            <p class="mb-0 font-monospace">{{ $pole->sim_number ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase fw-semibold mb-1">Battery QR</label>
                            <p class="mb-0 font-monospace">{{ $pole->battery_qr ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase fw-semibold mb-1">Panel QR</label>
                            <p class="mb-0 font-monospace">{{ $pole->panel_qr ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team & Timeline Card -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">
                        <i class="mdi mdi-account-group text-primary"></i> Team & Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase fw-semibold mb-1">Installer</label>
                            <p class="mb-0">{{ $installer->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase fw-semibold mb-1">Site Engineer</label>
                            <p class="mb-0">{{ $siteEngineer->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase fw-semibold mb-1">Project Manager</label>
                            <p class="mb-0">{{ $projectManager->name ?? 'N/A' }}</p>
                        </div>
                        @if($pole->created_at)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase fw-semibold mb-1">Installation Date</label>
                            <p class="mb-0">
                                <i class="mdi mdi-calendar text-muted"></i>
                                {{ \Carbon\Carbon::parse($pole->created_at)->format('d M Y, h:i A') }}
                            </p>
                        </div>
                        @endif
                        @if($pole->isInstallationDone && $pole->updated_at)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase fw-semibold mb-1">Submitted At</label>
                            <p class="mb-0">
                                <i class="mdi mdi-calendar-check text-muted"></i>
                                {{ \Carbon\Carbon::parse($pole->updated_at)->format('d M Y, h:i A') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Images -->
        <div class="col-lg-4">
            <!-- Survey Images Card -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">
                        <i class="mdi mdi-camera text-primary"></i> Survey Images
                    </h5>
                </div>
                <div class="card-body">
                    @if (!empty($surveyImages) && count($surveyImages) > 0)
                        <div class="image-gallery">
                            @foreach ($surveyImages as $index => $image)
                                <div class="image-item mb-3">
                                    <a href="{{ $image }}" target="_blank" class="image-link">
                                        <img src="{{ $image }}" alt="Survey Image {{ $index + 1 }}" class="img-fluid rounded shadow-sm">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-image-off mdi-48px text-muted mb-2"></i>
                            <p class="text-muted mb-0">No survey images available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Installation Images Card -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">
                        <i class="mdi mdi-camera text-primary"></i> Installation Images
                    </h5>
                </div>
                <div class="card-body">
                    @if (!empty($submissionImages) && count($submissionImages) > 0)
                        <div class="image-gallery">
                            @foreach ($submissionImages as $index => $image)
                                <div class="image-item mb-3">
                                    <a href="{{ $image }}" target="_blank" class="image-link">
                                        <img src="{{ $image }}" alt="Installation Image {{ $index + 1 }}" class="img-fluid rounded shadow-sm">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-image-off mdi-48px text-muted mb-2"></i>
                            <p class="text-muted mb-0">No installation images available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push("styles")
<style>
    .content-wrapper {
        background-color: #f8f9fa;
        min-height: 100vh;
    }

    .status-card {
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .status-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .card {
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 8px;
    }

    .card-header {
        border-bottom: 1px solid #e9ecef;
        padding: 1rem 1.25rem;
    }

    .card-header h5 {
        color: #495057;
        font-size: 1.1rem;
    }

    .card-body {
        padding: 1.25rem;
    }

    .image-gallery {
        display: flex;
        flex-direction: column;
    }

    .image-item {
        position: relative;
    }

    .image-link {
        display: block;
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .image-link:hover {
        transform: scale(1.02);
    }

    .image-link img {
        width: 100%;
        height: auto;
        max-height: 300px;
        object-fit: cover;
        border: 1px solid #dee2e6;
    }

    .badge-lg {
        padding: 0.5em 0.75em;
        font-size: 0.875rem;
        font-weight: 600;
    }

    label.text-muted {
        letter-spacing: 0.5px;
        font-size: 0.75rem;
    }

    .font-monospace {
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        background-color: #f8f9fa;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        display: inline-block;
    }
</style>
@endpush

@push("scripts")
<script>
    function locateOnMap(lat, lng) {
        if (lat && lng) {
            const url = `https://www.google.com/maps?q=${lat},${lng}`;
            window.open(url, '_blank');
        } else {
            alert('Location coordinates not available.');
        }
    }

    function goBackWithFallback() {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = '/';
        }
    }
</script>
@endpush
