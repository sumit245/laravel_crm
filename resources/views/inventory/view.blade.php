@extends("layouts.main")

@section("content")
  <div class="container">
    <h4>View Inventory for Store: {{ $storeName }} (Project ID: {{ $projectId }})</h4>
    <div class="mb-3">
      <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
      <button onclick="printTable()" class="btn btn-primary">Print Inventory</button>
      <button onclick="exportToCSV()" class="btn btn-success">Export Inventory</button>
    </div>

    @if ($inventory->isEmpty())
      <p>No inventory available for this store.</p>
    @else
      <table class="table-bordered table-sm table">
        <thead>
          <tr>
            <th>#</th>
            @if ($projectType == 1)
              <th>Item Code</th>
              <th>Item Name</th>
              <th>Manufacturer</th>
              <th>Make</th>
              <th>Model</th>
              <th>Serial Number</th>
              <th>HSN Code</th>
              <th>Unit</th>
              <th>Rate</th>
              <th>Quantity</th>
              <th>Total Value</th>
              <th>Description</th>
              <th>Received Date</th>
            @else
              <th>Category</th>
              <th>Sub Category</th>
              <th>Item Name</th>
              <th>Quantity</th>
              <th>Unit Price</th>
              <th>Total Value</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @foreach ($inventory as $item)
            <tr>
              <td>{{ $loop->iteration }}</td>
              @if ($projectType == 1)
                <td>{{ $item->item_code }}</td>
                <td>{{ $item->item }}</td>
                <td>{{ $item->manufacturer }}</td>
                <td>{{ $item->make }}</td>
                <td>{{ $item->model }}</td>
                <td>{{ $item->serial_number }}</td>
                <td>{{ $item->hsn }}</td>
                <td>{{ $item->unit }}</td>
                <td>{{ $item->rate }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->total_value }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->received_date }}</td>
              @else
                <td>{{ $item->category }}</td>
                <td>{{ $item->sub_category }}</td>
                <td style="max-width:200px;word-wrap: break-word;white-space: normal;">{{ $item->productName }}</td>
                <td>{{ $item->initialQuantity }}</td>
                <td>{{ $item->rate }}</td>
                <td>{{ $item->total }}</td>
              @endif
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  <script>
    function printTable() {
      const printContents = document.querySelector('table').outerHTML;
      const newWindow = window.open('', '_blank');
      newWindow.document.write('<html><head><title>Print Inventory</title></head><body>');
      newWindow.document.write(printContents);
      newWindow.document.write('</body></html>');
      newWindow.document.close();
      newWindow.print();
    }

    function exportToCSV() {
      const rows = [
        @if ($projectType == 1)
          ['Item Code', 'Item Name', 'Manufacturer', 'Make', 'Model', 'Serial Number', 'HSN Code', 'Unit', 'Rate',
            'Quantity', 'Total Value', 'Description', 'Received Date'
          ]
        @else
          ['Category', 'Sub Category', 'Item Name', 'Quantity', 'Unit Price', 'Total Value']
        @endif
      ];
      document.querySelectorAll('table tbody tr').forEach(row => {
        const rowData = [];
        row.querySelectorAll('td').forEach(cell => rowData.push(cell.innerText));
        rows.push(rowData);
      });

      const csvContent = rows.map(e => e.join(',')).join('\n');
      const blob = new Blob([csvContent], {
        type: 'text/csv;charset=utf-8;'
      });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.setAttribute('href', url);
      link.setAttribute('download', 'inventory.csv');
      link.style.display = 'none';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  </script>
@endsection
