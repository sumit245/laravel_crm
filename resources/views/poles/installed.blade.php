@extends("layouts.main")

@section("content")
  <div class="container">
    <h3 class="fw-bold">Installed Poles</h3>
    <p>Total Installed Poles: <strong>{{ $totalInstalled }}</strong></p>

    <x-data-table id="installedPoles" class="table-striped table">
      <x-slot:thead>
        <tr>
          <th>Pole ID</th>
          <th>Complete Pole Number</th>
          <th>Sim Number</th>
          <th>Latitude</th>
          <th>Longitude</th>
        </tr>
      </x-slot:thead>
      <x-slot:tbody>
        @foreach ($poles as $pole)
          <tr>
            <td>{{ $pole->id }}</td>
            <td>{{ $pole->complete_pole_number }}</td>
            <td>{{ $pole->sim_number }}</td>
            <td>{{ $pole->lat }}</td>
            <td>{{ $pole->lng }}</td>
          </tr>
        @endforeach
      </x-slot:tbody>
    </x-data-table>
  </div>
@endsection
