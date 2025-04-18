@extends("layouts.main")

@section("content")
  <div class="container-fluid p-3">
<<<<<<< HEAD
  <h3 class="fw-bold mt-2">Installed Lights</h3>
  <p>Total Installed Lights: <strong>{{ $totalInstalled }}</strong></p>
=======
    <h3 class="fw-bold mt-2">Installed Lights</h3>
    <p>Total Installed Lights: <strong>{{ $totalInstalled }}</strong></p>

>>>>>>> 68a0b9d9e9ed8588728014731e54baf08f235665
    <x-data-table id="installedPole" class="table-striped table">
      <x-slot:thead>
        <tr>
          <th data-select="true">
            <input type="checkbox" id="selectAll" />
          </th>
          <th>Pole Number</th>
          <th>IMEI</th>
          <th>Sim Number</th>
          <th>Battery</th>
          <th>Panel</th>
          <th>Bill Raised</th>
          <!-- <th>Location</th> -->
          <th>RMS</th>
          <th>Actions</th>
        </tr>
      </x-slot:thead>
      <x-slot:tbody>
        @foreach ($poles as $pole)
          <tr>
            <td>
              <input type="checkbox" id="selectAll" />
            </td>
            <td onclick="locateOnMap({{ $pole->lat }}, {{ $pole->lng }})" style="cursor:pointer;"> <span class="text-primary">{{ $pole->complete_pole_number ?? "N/A" }}</span></td>
            <td>{{ $pole->luminary_qr ?? "N/A" }}</td>
            <td>{{ $pole->sim_number ?? "N/A" }}</td>
            <td>{{ $pole->battery_qr ?? "N/A" }}</td>
            <td>{{ $pole->panel_qr ?? "N/A" }}</td>
            <td>0</td>
            <!-- <td onclick="locateOnMap({{ $pole->lat }}, {{ $pole->lng }})" style="cursor:pointer;">
              {{-- TODO:  --}}
              <span class="text-primary">View Location</span>
            </td> -->
            <td>{{ $pole->rms_status ?? "N/A" }}</td>
            <td>
              <!-- View Button -->
              <a href="{{ route("poles.show", $pole->id) }}" class="btn btn-icon btn-info" data-toggle="tooltip"
                title="View Details">
                <i class="mdi mdi-eye">info</i>
              </a>
            </td>
          </tr>
        @endforeach
      </x-slot:tbody>
    </x-data-table>
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
  </script>
@endpush

@push("styles")
    <style>
      #installedPole{
        margin-top: 15px;
      }
    </style>
@endpush