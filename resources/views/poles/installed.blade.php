@extends("layouts.main")

@section("content")
  <div class="container">
    <h3 class="fw-bold">Installed Poles</h3>
    <p>Total Installed Poles: <strong>{{ $totalInstalled }}</strong></p>

    <x-data-table id="installedPole" class="table-striped table">
      <x-slot:thead>
        <tr>
        <th data-select="true">
          <input type="checkbox" id="selectAll" />
        </th>
        <th>#</th>
        <th>Complete Pole Numbers</th>
        <th>Location</th>
        <th>Sim Numbers</th>
        <th>Luminary QR</th>
        <th>Battery QR</th>
        <th>Panel QR</th>
        <th>RMS status</th>
        <th>Actions</th>
        </tr>
      </x-slot:thead>
      <x-slot:tbody>
        @foreach ($poles as $pole)
          <tr>
          <td>{{ $pole->id }}</td>
          <td>{{ $pole->complete_pole_number ?? "N/A" }}</td>
          <td>{{ $pole->lat && $survey->lng ? $survey->lat .', '. $survey->lng : "N/A" }}</td>
          <td>{{ $pole->sim_number ?? "N/A" }}</td>
          <td>{{ $pole->luminary_qr ?? "N/A" }}</td>
          <td>{{ $pole->battery_qr ?? "N/A" }}</td>
          <td>{{ $pole->panel_qr ?? "N/A" }}</td>
          <td>{{ $pole->be ?? "N/A" }}</td>
          <td>
            <!-- View Button -->
            <a href="{{-- route("inventory.show", $member->id) --}}" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
              <i class="mdi mdi-eye">info</i>
            </a>
          </td>
          </tr>
        @endforeach
      </x-slot:tbody>
    </x-data-table>
  </div>
@endsection
