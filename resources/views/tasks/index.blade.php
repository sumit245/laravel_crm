@extends('layouts.main')

@section('content')
<div class="container p-2">
    <div class="d-flex justify-content-between mb-3">
        <!-- Search box is added automatically by DataTables -->
        <div></div> <!-- Empty div to align with search box -->
        <a href="{{ route('tasks.create') }}" class="btn btn-icon btn-primary" data-toggle="tooltip" title="Add New Staff">
            <i class="mdi mdi-plus-circle"></i>
        </a>
    </div>
    <table id="tasksTable" class="table table-striped table-bordered table-sm">
        <thead>
            <tr>
                <th>#</th>
                <th>Task Name</th>
                <th>Vendor</th>
                <th>Site</th>
                <th>Status</th>
                <th>Approved By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $member)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $member->task_name }}</td>
                    <td>{{ $member->vendor->name }}</td>
                    <td>{{ $member->site->site_name }}</td>
                    <td>{{ $member->status }}</td>
                    <td>{{ $member->approved_by }}</td>
                    <td>
                        <!-- View Button -->
                        <a href="{{ route('staff.show', $member->id) }}" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
                            <i class="mdi mdi-eye"></i>
                        </a>
                        <!-- Edit Button -->
                        <a href="{{ route('staff.edit', $member->id) }}" class="btn btn-icon btn-warning" data-toggle="tooltip" title="Edit Staff">
                            <i class="mdi mdi-pencil"></i>
                        </a>
                        <!-- Delete Button -->
                        <form action="{{ route('staff.destroy', $member->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-icon btn-danger" data-toggle="tooltip" title="Delete Staff" onclick="return confirm('Are you sure?')">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#tasksTable').DataTable({
            dom: "<'row'<'col-sm-6 d-flex align-items-center'f><'col-sm-6 d-flex justify-content-end'B>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [
                {
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
    });
</script>
@endpush
