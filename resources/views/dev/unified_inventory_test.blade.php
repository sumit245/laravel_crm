<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dev Unified Inventory Test</title>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <style>body{font-family: system-ui, -apple-system, sans-serif; padding:20px}</style>
</head>
<body>
  <h3>Dev: Unified Inventory Datatable Test</h3>

  <table id="unifiedInventoryTable" class="display" style="width:100%">
    <thead>
      <tr>
        <th>Item Code</th>
        <th>Item</th>
        <th>Serial Number</th>
        <th>Availability</th>
        <th>Vendor</th>
        <th>Dispatch Date</th>
        <th>In Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>IT1</td>
        <td>Item 1</td>
        <td>SERI1</td>
        <td><span class="badge">In Stock</span></td>
        <td>Vendor 1</td>
        <td>01/01/2025</td>
        <td>01/01/2025</td>
        <td></td>
      </tr>
      <tr>
        <td>IT2</td>
        <td>Item 2</td>
        <td>SERI2</td>
        <td><span class="badge">In Stock</span></td>
        <td>Vendor 2</td>
        <td>01/01/2025</td>
        <td>01/01/2025</td>
        <td></td>
      </tr>
    </tbody>
  </table>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
    $(function(){
      $('#unifiedInventoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: '/__dev/inventory-data',
          data: function(d) {
            return d;
          }
        },
        pageLength: 2,
        deferLoading: 100,
        columns: [
          { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 }, { data: 4 }, { data: 5 }, { data: 6 }, { data: 7 }
        ]
      });
    });
  </script>
</body>
</html>
