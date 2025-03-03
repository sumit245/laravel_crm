@extends("layouts.main")

@section("content")
  <div class="pd-20 pd-xl-25 container">
    <div class="d-flex align-items-center justify-content-between mg-b-25">
      <h6 class="mg-b-0">Pole Details</h6>
      <div class="d-flex">
        <a href="#" data-toggle="modal" class="btn btn-sm btn-white d-flex align-items-center"
          onclick="()=>window.location.goBack();">
          <span class="d-none d-sm-inline mg-l-5"> Go Back</span></a>
      </div>
    </div>
    <div class="row">
      <div class="col-6 col-sm">
        <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Pole id</label>
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
    <h2>Survey Information</h2>
    <p><strong>Survey Done:</strong> {{ $pole->isSurveyDone ? "Yes" : "No" }}</p>
    <p><strong>Network Available:</strong> {{ $pole->isNetworkAvailable ? "Yes" : "No" }}</p>
    <p><strong>Installation Done:</strong> {{ $pole->isInstallationDone ? "Yes" : "No" }}</p>

    <h2>Related Streetlight</h2>
    @if ($pole->streetlight)
      <p><strong>Streetlight ID:</strong> {{ $pole->streetlight->id }}</p>
      <p><strong>Streetlight Number:</strong> {{ $pole->streetlight->number_of_poles }}</p>
    @else
      <p>No related streetlight found.</p>
    @endif

    <h2>Related Tasks</h2>
    @if ($pole->tasks->isNotEmpty())
      <ul>
        @foreach ($pole->tasks as $task)
          <li>
            <strong>Task ID:</strong> {{ $task->id }} - <strong>Status:</strong> {{ $task->status }}
          </li>
        @endforeach
      </ul>
    @else
      <p>No related tasks found.</p>
    @endif
  </div>
@endsection
