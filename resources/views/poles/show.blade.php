@extends("layouts.main")

@section("content")
  <div class="container">

    <h1>Pole Details</h1>

    <h2>General Information</h2>
    <p><strong>Complete Pole Number:</strong> {{ $pole->complete_pole_number }}</p>
    <p><strong>Beneficiary:</strong> {{ $pole->beneficiary }}</p>
    <p><strong>Remarks:</strong> {{ $pole->remarks }}</p>
    <p><strong>Latitude:</strong> {{ $pole->lat }}</p>
    <p><strong>Longitude:</strong> {{ $pole->lng }}</p>

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

    <a href="" onclick="()=>window.location.goBack();">Back to Poles List</a>

  </div>
@endsection
