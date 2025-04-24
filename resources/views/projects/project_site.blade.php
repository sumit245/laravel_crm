<div>
  <div class="d-flex justify-content-between mb-0">
    <div>
      <h5>Sites</h5>
      @if ($project->project_type == 0)
        <a class="text-decoration-none text-primary"
          href="https://sugslloyd.s3.ap-south-1.amazonaws.com/formats/sites_format.xlsx" download>
          Download format
        </a>
      @else
        <a class="text-decoration-none text-primary"
          href="https://sugslloyd.s3.ap-south-1.amazonaws.com/formats/sample_panchayat_data.xlsx" download>
          Download format
        </a>
      @endif
    </div>

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
          <input type="file" name="file" style="height: 40px !important;" class="form-control form-control-sm"
            required>
          <button type="submit" class="btn btn-sm btn-primary"
            style="height: 40px !important; align-items:center;justify-content:center;" data-toggle="tooltip"
            title="Import Sites">
            <i class="mdi mdi-upload"></i><span>Import</span>
          </button>
        </div>
      </form>
      <a href="{{ route("sites.create", $project->id) }}" class="btn btn-primary mx-1 mb-4"
        style="height: 40px !important;">Add Site</a>
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
          <th>Breda Sl No</th>
          <th>Site Name</th>
          <th>Address</th>
          <th>Vendor</th>
          <th>Engineer</th>
        @else
          {{-- Streetlight Installation --}}
          <th>Site Code</th>
          <th>State</th>
          <th>District</th>
          <th>Block</th>
          <th>Panchayat</th>
          <th>Ward</th>
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
            <td>{{ $site->breda_sl_no }}</td>
            <td>{{ $site->site_name }}</td>
            <td>
              {{ $site->location }},
              {{ optional($site->districtRelation)->name ?? "Unknown District" }},
              {{ optional($site->stateRelation)->name ?? "Unknown State" }}
            </td>
            <td>{{ $site->vendorRelation->name ?? "" }}</td>
            <td>{{ $site->engineerRelation->firstName ?? "" }} {{ $site->engineerRelation->lastName ?? " " }}</td>
          @else
            {{-- Streetlight Installation --}}
            <td>{{ $site->task_id }}</td>
            <td>{{ $site->state }}</td>
            <td>{{ $site->district }}</td>
            <td>{{ $site->block }}</td>
            <td>{{ $site->panchayat }}</td>
            <td>{{ $site->ward }}</td>
          @endif

          <td>
            <a href="{{ route("sites.show", $site->id) }}?project_id={{ $project->id }}"
              class="btn btn-info btn-icon" data-toggle="tooltip" title="View Details">
              <i class="mdi mdi-eye"></i>
            </a>
            <a href="{{ route("sites.edit", $site->id) }}?project_id={{ $project->id }}"
              class="btn btn-warning btn-icon" data-toggle="tooltip" title="Edit Site">
              <i class="mdi mdi-pencil"></i>
            </a>
            <form action="{{ route("sites.destroy", $site->id) }}?project_id={{ $project->id }}" method="POST"
              style="display:inline;">
              @csrf
              @method("DELETE")
              <button type="submit" class="btn btn-danger btn-icon" data-toggle="tooltip" title="Delete Site">
                <i class="mdi mdi-delete"></i>
              </button>
            </form>

          </td>
        </tr>
      @endforeach
    </x-slot:tbody>
  </x-data-table>
</div>
