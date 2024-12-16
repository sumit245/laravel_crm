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
            <th>Category</th>
            <th>Sub Category</th>
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Total Value</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($inventory as $item)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $item->category }}</td>
              <td>{{ $item->sub_category }}</td>
              <td style="max-width:200px;word-wrap: break-word;white-space: normal;">{{ $item->productName }}</td>
              <td>{{ $item->initalQuantity }}</td>
              <td>{{ $item->rate }}</td>
              <td>{{ $item->total }}</td>
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
        ['Item Name', 'Quantity', 'Unit Price', 'Total Value']
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
