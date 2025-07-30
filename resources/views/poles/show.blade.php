@extends("layouts.main")

@section("content")
<div class="pd-20 pd-xl-25 container">
    <div class="d-flex align-items-center justify-content-between mg-b-25">
        <h6 class="mg-b-0">Pole Details</h6>
        <div class="d-flex">
            <a href="{{ route('poles.edit', $pole->id) }}" class="btn btn-sm btn-primary d-flex align-items-center mr-2">
                <i class="fa fa-edit"></i>
                <span class="d-none d-sm-inline mg-l-5">Edit</span>
            </a>
            <a href="javascript:void(0);" class="btn btn-sm btn-white d-flex align-items-center" onclick="goBackWithFallback();">
                <span class="d-none d-sm-inline mg-l-5">Go Back</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Complete Pole Number</label>
            <p class="mg-b-0">{{ $pole->complete_pole_number }}</p>
        </div>
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Location</label>
            <p class="mb-0">{{ $pole->lat }}, {{ $pole->lng }}</p>
            <p class="text-primary mt-0" style="cursor: pointer" onclick="locateOnMap({{ $pole->lat }}, {{ $pole->lng }})">View on Map</p>
        </div>
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Beneficiary</label>
            <p class="mg-b-0">{{ $pole->beneficiary }}</p>
        </div>
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Beneficiary Contact</label>
            <p class="mg-b-0">{{ $pole->beneficiary_contact }}</p>
        </div>
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Survey Status</label>
            <p class="mg-b-0">{{ $pole->isSurveyDone ? "Yes" : "No" }}</p>
        </div>
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Installation Status</label>
            <p class="mg-b-0">{{ $pole->isInstallationDone ? "Yes" : "No" }}</p>
        </div>
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Network Status</label>
            <p class="mg-b-0">{{ $pole->isNetworkAvailable ? "Yes" : "No" }}</p>
        </div>
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Installer Name</label>
            <p class="mg-b-0">{{ $installer->name ?? "" }}</p>
        </div>
    </div>

    <hr />

    <div class="row">
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Luminary QR</label>
            <p class="mg-b-0">{{ $pole->luminary_qr ?? "" }}</p>
        </div>
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Sim Number</label>
            <p class="mg-b-0">{{ $pole->sim_number ?? "" }}</p>
        </div>
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Battery QR</label>
            <p class="mg-b-0">{{ $pole->battery_qr ?? "" }}</p>
        </div>
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Panel QR</label>
            <p class="mg-b-0">{{ $pole->panel_qr ?? "" }}</p>
        </div>
    </div>

    <hr />

    <div class="row">
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Engineer</label>
            <p class="mg-b-0">{{ $siteEngineer->name ?? "Yes" }}</p>
        </div>
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Project Manager</label>
            <p class="mg-b-0">{{ $projectManager->name ?? "" }}</p>
        </div>
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Installation Date</label>
            <p class="mg-b-0">{{$pole-> created_at}}</p>
        </div>
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Submitted at</label>
            <p class="mg-b-0"><?= $pole->isInstallationDone == 1 ? $pole->updated_at : "" ?></p>
        </div>
    </div>

    <hr />

    <div class="row">
        <div class="col-3 col-sm-3">
            <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Remarks</label>
            <p class="mg-b-0">{{ $pole->remarks ?? "" }}</p>
        </div>
    </div>

    <hr />

    <div class="row">
        <div class="col-sm-6">
            @if (!empty($surveyImages))
                <h3>Survey Images</h3>
                <div class="image-gallery">
                    @foreach ($surveyImages as $image)
                        <img src="{{ $image }}" alt="Survey Image" style="width: 200px; height: auto; margin: 10px;">
                    @endforeach
                </div>
            @else
                <p>No survey images available.</p>
            @endif
        </div>
        <div class="col-sm-6">
            @if (!empty($submissionImages))
                <h3>Installation Image</h3>
                <div class="image-gallery">
                    @foreach ($submissionImages as $image)
                        <img src="{{ $image }}" alt="Installation Image" style="width: 200px; height: auto; margin: 10px;">
                    @endforeach
                </div>
            @else
                <p>No submission images available.</p>
            @endif
        </div>
    </div>
</div>
@endsection

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
