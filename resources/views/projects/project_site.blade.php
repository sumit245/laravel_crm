<div>
  <div class="d-flex justify-content-between mb-4">
    <h5>Sites</h5>
    <div class="d-flex">
      @if ($errors->any())
        <div class="alert alert-danger">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
      <form action="{{ route("sites.import", $project->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="input-group">
          <input type="file" name="file" class="form-control form-control-sm" required>
          <button type="submit" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Import Inventory">
            <i class="mdi mdi-upload"></i> Import
          </button>
        </div>
      </form>
      <a href="{{ route("sites.create", $project->id) }}" class="btn btn-primary mx-1">Add Site</a>
    </div>
  </div>
  <x-data-table id="sitesTable" :pageLength="50">
    <x-slot:thead>
      <tr>
        <th data-select="true">
          <input type="checkbox" id="selectAll" />
        </th>

        @if ($project->project_type == 0)
          {{-- Rooftop Installation --}}
          <th>Site Name</th>
          <th>Address</th>
          <th>Vendor</th>
          <th>Site Contact</th>
        @else
          {{-- Streetlight Installation --}}
          <th>State</th>
          <th>District</th>
          <th>Block</th>
          <th>Panchayat</th>
          <th>Ward</th>
          <th>Number of poles</th>
        @endif

        <th>Actions</th>
      </tr>
    </x-slot:thead>

    <x-slot:tbody>
      @foreach ($sites as $site)
        <tr>
          <td>
            <input type="checkbox" name="selected[]" value="{{ $site->id }}" class="select-checkbox">
          </td>

          @if ($project->project_type == 0)
            {{-- Rooftop Installation --}}
            <td>{{ $site->site_name }}</td>
            <td>
              {{ $site->location }},
              {{ optional($site->districtRelation)->name ?? "Unknown District" }},
              {{ optional($site->stateRelation)->name ?? "Unknown State" }}
            </td>
            <td>{{ $site->ic_vendor_name }}</td>
            <td>{{ $site->contact_no }}</td>
          @else
            {{-- Streetlight Installation --}}
            <td>{{ $site->state }}</td>
            <td>{{ $site->district }}</td>
            <td>{{ $site->block }}</td>
            <td>{{ $site->panchayat }}</td>
            <td>{{ $site->ward }}</td>
            <td>{{ $site->pole }}</td>
          @endif

          <td>
            <a href="{{ route("sites.show", $site->id) }}" class="btn btn-info btn-icon" data-toggle="tooltip"
              title="View Details">
              <i class="mdi mdi-eye"></i>
            </a>
            <a href="{{ route("sites.edit", $site->id) }}" class="btn btn-warning btn-icon" data-toggle="tooltip"
              title="Edit Site">
              <i class="mdi mdi-pencil"></i>
            </a>
            <button class="btn btn-danger btn-icon delete-site" data-id="{{ $site->id }}"
              data-name="{{ $site->site_name }}">
              <i class="mdi mdi-delete"></i>
            </button>

          </td>
        </tr>
      @endforeach
    </x-slot:tbody>
  </x-data-table>

</div>
