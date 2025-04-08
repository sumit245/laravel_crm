@extends("layouts.main")

@section("content")
  <div class="pd-20 pd-xl-25 container">
    <div class="d-flex align-items-center justify-content-between mg-b-25">
      <h6 class="mg-b-0">Pole Details</h6>
      <div class="d-flex">
        <a href="#" data-toggle="modal" class="btn btn-sm btn-white d-flex align-items-center"
          onclick="window.location.goBack();">
          <span class="d-none d-sm-inline mg-l-5"> Go Back</span></a>
      </div>
    </div>
    <div class="row">
      <div class="col-6 col-sm">
        <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Sl. No.</label>
        <p class="mg-b-0">{{ $pole->id }}</p>
      </div>
      <div class="col-6 col-sm">
        <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Complete Pole Number</label>
        <p class="mg-b-0">{{ $pole->complete_pole_number }}</p>
      </div>
      <div class="col-6 col-sm">
        <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Beneficiary</label>
        <p class="mg-b-0">{{ $pole->beneficiary }}</p>
      </div>
      <div class="col-6 col-sm">
        <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Location</label>
        <p class="mg-b-0">{{ $pole->lat }}, {{ $pole->lng }}</p>
      </div>
    </div>
    <hr />
    <div class="row">
      <div class="col-6 col-sm">
        <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Survey Status</label>
        <p class="mg-b-0">{{ $pole->isSurveyDone ? "Yes" : "No" }}</p>
      </div>
      <div class="col-6 col-sm">
        <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Installation Status</label>
        <p class="mg-b-0">{{ $pole->isInstallationDone ? "Yes" : "No" }}</p>
      </div>
      <div class="col-6 col-sm">
        <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Network Status</label>
        <p class="mg-b-0">{{ $pole->isNetworkAvailable ? "Yes" : "No" }}</p>
      </div>
      <div class="col-6 col-sm">
        <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Installer Name</label>
        <p class="mg-b-0">{{ $installer->name ?? "" }}</p>
      </div>
    </div>
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
          <p>No survey images available.</p>
        @endif
      </div>
    </div>
  </div>
@endsection
