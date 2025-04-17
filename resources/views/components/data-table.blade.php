<div>
  <table id="{{ $id }}" class="table-striped table-bordered table-sm mt-4 table">
    <thead>
      {{ $thead }}
    </thead>
    <tbody>
      {{ $tbody }}
    </tbody>
  </table>
</div>

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
