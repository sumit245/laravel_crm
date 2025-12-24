<div class="datatable-wrapper" id="datatable-wrapper-{{ $id }}">
  <table id="{{ $id }}" class="table table-striped table-bordered table-sm">
    <thead>
      {{ $thead }}
    </thead>
    <tbody>
      {{ $tbody }}
    </tbody>
  </table>
</div>

@push('styles')
<style>
  /* Compact rows and cells, similar to main datatable component */
  #{{ $id }} thead th,
  #{{ $id }} tbody td,
  #{{ $id }} tbody tr {
    height: 32px !important;
    min-height: 32px !important;
    max-height: 32px !important;
    padding-top: 0.125rem !important;
    padding-bottom: 0.125rem !important;
    padding-left: 0.25rem !important;
    padding-right: 0.25rem !important;
    line-height: 1.15 !important;
    box-sizing: border-box !important;
    margin: 0 !important;
    vertical-align: middle !important;
  }

  /* Hide default DataTables sort icons for this legacy table */
  table.dataTable#{{ $id }} thead th::before,
  table.dataTable#{{ $id }} thead th::after {
    display: none !important;
    content: none !important;
  }

  /* Compact header styling */
  #{{ $id }} thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
  }

  /* Compact buttons in header toolbars */
  .dataTables_wrapper .dt-buttons .btn {
    border-radius: 4px;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
  }

  /* Keep actions column narrow but large enough for 2–3 icon buttons */
  #{{ $id }} thead th:last-child,
  #{{ $id }} tbody td:last-child {
    width: 140px !important;
    min-width: 140px !important;
    max-width: 140px !important;
    white-space: nowrap !important;
    text-align: center !important;
  }

  #{{ $id }} tbody td:last-child .btn,
  #{{ $id }} tbody td:last-child .btn-icon {
    padding: 4px 6px !important;
    margin: 0 1px !important;
    min-width: auto !important;
  }

  /* Checkbox column (if present) */
  #{{ $id }} thead th:first-child,
  #{{ $id }} tbody td:first-child {
    width: 30px !important;
    min-width: 30px !important;
    max-width: 30px !important;
  }

  /* Truncation helper for long content cells */
  #{{ $id }} tbody td.dt-truncate {
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    max-width: 260px !important;
  }
</style>
@endpush

@push('scripts')
<script>
  window.onload = function() {
    const table = $('#{{ $id }}').DataTable({
      dom: "<'row'<'col-sm-6 d-flex align-items-center'f><'col-sm-6 d-flex justify-content-end'B>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
      buttons: [{
          extend: 'collection',
          text: '<i class="mdi mdi-menu"></i> Actions',
          className: 'btn btn-sm btn-secondary',
          buttons: [{
            text: 'Delete Selected',
            action: function() {
              performBulkAction('delete');
            }
          }]
        },
        {
          extend: 'excel',
          text: '<i class="mdi mdi-file-excel"></i>',
          className: 'btn btn-sm btn-success',
          titleAttr: 'Export to Excel'
        },
        {
          extend: 'print',
          text: '<i class="mdi mdi-printer"></i>',
          className: 'btn btn-sm btn-info',
          titleAttr: 'Print Table'
        }
      ],
      paging: true,
      pageLength: {{ $pageLength ?? 25 }},
      searching: true,
      responsive: true,
      language: {
        search: '',
        searchPlaceholder: 'Search...'
      },
      columnDefs: [{
        orderable: false,
        targets: [0, -1] // Targets the first column for select-checkbox
      }],
      ordering: true,
      select: {
        style: 'multi',
        selector: 'td:first-child'
      },
    });

    $('#selectAll').on('click', function() {
      const isChecked = $(this).is(':checked');
      table.rows().nodes().to$().find('input[type="checkbox"]').prop('checked', isChecked);
      if (isChecked) {
        table.rows().select();
      } else {
        table.rows().deselect();
      }
    });
    // Track individual row selection to update "Select All" state
    $('#{{ $id }} tbody').on('click', 'input[type="checkbox"]', function() {
      const allChecked = $('#{{ $id }} tbody input[type="checkbox"]:checked').length === table
        .rows()
        .count();
      $('#selectAll').prop('checked', allChecked);
    });
    // Apply truncation + native title tooltips to inner data cells
    function applyTruncation() {
      $('#{{ $id }} tbody tr').each(function() {
        const $cells = $(this).children('td');
        $cells.each(function(idx) {
          const isCheckboxCol = idx === 0;
          const isActionsCol = idx === $cells.length - 1;
          if (isCheckboxCol || isActionsCol) {
            return;
          }
          const $cell = $(this);
          $cell.addClass('dt-truncate');
          const text = $.trim($cell.text());
          if (text) {
            $cell.attr('title', text);
          } else {
            $cell.removeAttr('title');
          }
        });
      });
    }

    applyTruncation();
    table.on('draw', function() {
      applyTruncation();
    });

    // Bulk action function
    function performBulkAction(action) {
      const selectedIds = [];
      table.rows({
        selected: true
      }).data().each(function(rowData) {
        selectedIds.push(rowData[0]); // Assuming the ID is in the first column
      });

      if (selectedIds.length === 0) {
        alert('Please select at least one row.');
        return;
      }

      // Example: Show selected IDs in the console
      console.log(`Performing "${action}" on IDs: `, selectedIds);

      // Perform the actual action here
      // Example:
      // $.ajax({
      //   url: `/bulk/${action}`,
      //   method: 'POST',
      //   data: { ids: selectedIds },
      //   success: function(response) {
      //     alert(`${action} successful!`);
      //     table.ajax.reload();
      //   },
      //   error: function() {
      //     alert(`Failed to perform ${action}.`);
      //   }
      // });
    }
  }
  
</script>
@endpush
