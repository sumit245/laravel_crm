@extends("layouts.main")

@section("content")
  <div class="container p-2">
    <div class="row my-2">
      <div class="col-sm-6">
        <div class="card card-rounded bg-primary">
          <div class="card-body">
            <div class="row">
              <div class="col-lg-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                    <h4 class="card-title card-title-dash text-light">Top Performer (Staff)</h4>
                  </div>
                </div>
                @foreach ($topEngineers as $engineer)
                  <div class="wrapper ms-3">
                    <p class="fw-bold text-light mb-1">{{ $engineer->engineer->firstName ?? "Unknown" }}</p>
                    <small class="text-light mb-0">{{ $engineer->task_count }} tasks completed</small>
                  </div>
                @endforeach

              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="card card-rounded bg-primary">
          <div class="card-body">
            <div class="row">
              <div class="col-lg-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                    <h4 class="card-title card-title-dash text-light">Top Performers (Vendor)</h4>
                  </div>
                </div>
                @foreach ($topVendors as $vendor)
                  <div class="wrapper ms-3">
                    <p class="fw-bold text-light mb-1">{{ $vendor->vendor->firstName ?? "Unknown" }}</p>
                    <small class="text-light mb-0">{{ $vendor->task_count }} tasks completed</small>
                  </div>
                @endforeach

              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
    <div class="d-flex justify-content-between mb-3">
      <!-- Search box is added automatically by DataTables -->
      <div></div> <!-- Empty div to align with search box -->
      <div>
        <a href="{{ route("tasks.export", ['project_id' => $project->id]) }}" class="btn btn-icon btn-success me-2" data-toggle="tooltip" title="Export to Excel">
          <i class="mdi mdi-file-excel"></i>
        </a>
        <a href="{{ route("tasks.create", ['project_id' => $project->id]) }}" class="btn btn-icon btn-primary" data-toggle="tooltip" title="Add New Task">
          <i class="mdi mdi-plus-circle"></i>
        </a>
      </div>
    </div>
    <table id="tasksTable" class="table-striped table-bordered table-sm table">
      <thead>
        <tr>
          <th>#</th>
          <th>Task Name</th>
          <th>Site</th>
          <th>Status</th>
          <th>Approved By</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($tasks as $member)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $member->activity ?? ($member->task_name ?? 'N/A') }}</td>
            <td>
              @if($project->project_type == 1)
                {{ $member->site->panchayat ?? 'N/A' }}
              @else
                {{ $member->site->site_name ?? 'N/A' }}
              @endif
            </td>
            <td>
              @if($member->status)
                <span class="badge badge-{{ \App\Enums\TaskStatus::tryFrom($member->status)?->color() ?? 'secondary' }}">
                  {{ $member->status }}
                </span>
              @else
                N/A
              @endif
            </td>
            <td>{{ $member->approved_by ?? 'N/A' }}</td>
            <td>
              <!-- View Button -->
              <a href="{{ route("tasks.show", ['id' => $member->id, 'project_type' => $project->project_type]) }}" class="btn btn-icon btn-info" data-toggle="tooltip"
                title="View Details">
                <i class="mdi mdi-eye"></i>
              </a>
              <!-- Edit Button -->
              @if($project->project_type == 1)
                <a href="{{ route("tasks.edit", ['id' => $member->id, 'project_id' => $project->id]) }}" class="btn btn-icon btn-warning" data-toggle="tooltip"
                  title="Edit Task">
                  <i class="mdi mdi-pencil"></i>
                </a>
              @else
                <a href="{{ route("tasks.editrooftop", $member->id) }}" class="btn btn-icon btn-warning" data-toggle="tooltip"
                  title="Edit Task">
                  <i class="mdi mdi-pencil"></i>
                </a>
              @endif
              <!-- Delete Button -->
              <button type="button" class="btn btn-icon btn-danger delete-task" data-toggle="tooltip"
                title="Delete Task" data-id="{{ $member->id }}" 
                data-name="{{ $member->activity ?? ($member->task_name ?? 'this task') }}"
                data-url="{{ route("tasks.destroy", $member->id) }}">
                <i class="mdi mdi-delete"></i>
              </button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection

@push("scripts")
  <script>
    $(document).ready(function() {
      $('#tasksTable').DataTable({
        dom: "<'row'<'col-sm-6 d-flex align-items-center'f><'col-sm-6 d-flex justify-content-end'B>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{
            extend: 'excel',
            text: '<i class="mdi mdi-file-excel"></i>',
            className: 'btn btn-icon btn-success',
            titleAttr: 'Export to Excel' // Tooltip
          },
          {
            extend: 'pdf',
            text: '<i class="mdi mdi-file-pdf"></i>',
            className: 'btn btn-icon btn-danger',
            titleAttr: 'Export to PDF' // Tooltip
          },
          {
            extend: 'print',
            text: '<i class="mdi mdi-printer"></i>',
            className: 'btn btn-icon btn-info',
            titleAttr: 'Print Table' // Tooltip
          }
        ],
        paging: true,
        pageLength: 50, // Show 50 rows per page
        searching: true,
        ordering: true,
        responsive: true,
        language: {
          search: '',
          searchPlaceholder: 'Search Tasks...'
        }
      });

      // Initialize tooltips
      $('[data-toggle="tooltip"]').tooltip();

      // Adjust search box alignment
      $('.dataTables_filter input').addClass('form-control form-control-sm');

      $('.delete-task').on('click', function() {
        let taskId = $(this).data('id');
        let taskName = $(this).data('name');
        let deleteUrl = $(this).data('url');

        Swal.fire({
          title: `Are you sure?`,
          text: `You are about to delete task "${taskName}". This action cannot be undone.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, delete it!',
          cancelButtonText: 'Cancel',
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: deleteUrl,
              type: 'POST',
              data: {
                _method: 'DELETE',
                _token: "{{ csrf_token() }}",
              },
              success: function(response) {
                Swal.fire(
                  'Deleted!',
                  `Task "${taskName}" has been deleted.`,
                  'success'
                );
                setTimeout(function() {
                  window.location.reload();
                }, 1500);
              },
              error: function(xhr) {
                Swal.fire(
                  'Error!',
                  'There was an error deleting the task. Please try again.',
                  'error'
                );
              }
            });
          }
        });
      });
    });
  </script>
@endpush
