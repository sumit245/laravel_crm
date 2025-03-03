@extends("layouts.main")

@section("content")
  <div class="container">
    <h3 class="fw-bold">Surveyed Poles</h3>
    <p>Total Surveyed Poles: <strong>{{ $totalSurveyed }}</strong></p>

    <table class="table-striped table">
      <thead>
        <tr>
          <th>Pole ID</th>
          <th>Complete Pole Number</th>
          <th>Beneficiary</th>
          <th>Latitude</th>
          <th>Longitude</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($poles as $pole)
          <tr>
            <td>{{ $pole->id }}</td>
            <td>{{ $pole->complete_pole_number }}</td>
            <td>{{ $pole->beneficiary }}</td>
            <td>{{ $pole->lat }}</td>
            <td>{{ $pole->lng }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection
