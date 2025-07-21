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
</div>
<table id="sitesTable" class="table-striped table-bordered table-sm mt-4 table">
  <thead>
    <tr>
      <th><input type="checkbox" id="selectAll" /></th>

      @if ($project->project_type == 0)
        <th>Breda Sl No</th>
        <th>Site Name</th>
        <th>Address</th>
        <th>Vendor</th>
        <th>Engineer</th>
      @else
        <th>Site Code</th>
        <th>State</th>
        <th>District</th>
        <th>Block</th>
        <th>Panchayat</th>
        <th>Ward</th>
      @endif

      <th>Actions</th>
    </tr>
  </thead>

  <tbody>
    @foreach ($sites as $site)
      <tr>
        <td>
          <input type="checkbox" name="selected[]" value="{{ $site->id }}" class="select-checkbox">
        </td>

        @if ($project->project_type == 0)
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
          <td>{{ $site->task_id }}</td>
          <td>{{ $site->state }}</td>
          <td>{{ $site->district }}</td>
          <td>{{ $site->block }}</td>
          <td>{{ $site->panchayat }}</td>
          <td>{{ $site->ward }}</td>
        @endif

        <td>
          <a href="{{ route("sites.show", $site->id) }}?project_type={{ $project->project_type }}"
            class="btn btn-info btn-icon" title="View Details">
            <i class="mdi mdi-eye"></i>
          </a>
          <a href="{{ route("sites.edit", $site->id) }}?project_id={{ $project->id }}"
            class="btn btn-warning btn-icon" title="Edit Site">
            <i class="mdi mdi-pencil"></i>
          </a>
          <form action="{{ route("sites.destroy", $site->id) }}?project_id={{ $project->id }}" method="POST"
            style="display:inline;">
            @csrf
            @method("DELETE")
            <button type="submit" class="btn btn-danger btn-icon" title="Delete Site">
              <i class="mdi mdi-delete"></i>
            </button>
          </form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

@push("styles")
  <style>
    /* This forces the search box alignment to the left */
    div.dataTables_filter {
      text-align: left !important;
    }

    div.dataTables_filter label {
      float: left !important;
    }

    div.dataTables_filter input {
      margin-left: 0.5rem;
    }
  </style>
@endpush

@push("scripts")
  <!-- Include jQuery and DataTables -->
  <script>
    $(document).ready(function() {
      const table = $('#sitesTable').DataTable({
        dom: "<'row'<'col-sm-6 d-flex align-items-center'f><'col-sm-6 d-flex justify-content-end'B>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{
            extend: 'print',
            text: '<i class="mdi mdi-printer"></i>',
            className: 'btn btn-sm btn-info',
            titleAttr: 'Print Table'
          },
          {
            extend: 'excelHtml5',
            text: '<i class="mdi mdi-file-excel"></i>',
            className: 'btn btn-sm btn-success',
            titleAttr: 'Export to Excel'
          }
        ],
        pageLength: 50,
        searching: true,
        responsive: true,
        columnDefs: [{
            orderable: false,
            targets: [0, -1]
          } // checkbox and actions
        ],
        language: {
          search: '',
          searchPlaceholder: 'Search...'
        },
        select: {
          style: 'multi',
          selector: 'td:first-child input[type="checkbox"]'
        }
      });

      $('#selectAll').on('click', function() {
        const checked = this.checked;
        $('input.select-checkbox').prop('checked', checked);
        checked ? table.rows().select() : table.rows().deselect();
      });

      $('#sitesTable tbody').on('click', 'input.select-checkbox', function() {
        const total = $('input.select-checkbox').length;
        const checked = $('input.select-checkbox:checked').length;
        $('#selectAll').prop('checked', total === checked);
      });
    });
  </script>
@endpush
