@extends("layouts.app")

@section("content")
  <div class="container">
    <h3 class="fw-bold">Installed Poles</h3>
    <p>Total Installed Poles: <strong>{{ $totalInstalled }}</strong></p>

    <table class="table-striped table">
      <thead>
        <tr>
          <th>Pole ID</th>
          <th>Complete Pole Number</th>
          <th>Sim Number</th>
          <th>Latitude</th>
          <th>Longitude</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($poles as $pole)
          <tr>
            <td>{{ $pole->id }}</td>
            <td>{{ $pole->complete_pole_number }}</td>
            <td>{{ $pole->sim_number }}</td>
            <td>{{ $pole->lat }}</td>
            <td>{{ $pole->lng }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection
