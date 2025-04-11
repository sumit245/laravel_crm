@extends("layouts.main")

@php
  $projectId = request()->get("project_id");
  $storeId = request()->get("store_id");
@endphp

@section("content")
  <div class="container-fluid m-2">
    <div class="d-flex justify-content-between my-1">
      <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
      <div>
        <h4>Store: {{ $storeName }} (Project ID: {{ $projectId }})</h4>
        <h4>Incharge Name: {{ $inchargeName }}</h4>
      </div>
    </div>
    <div class="row">
      <!-- Inventory Summary -->
      <div class="col-sm-3 mb-2 mt-2">
  <div class="card bg-success">
    <div class="card-header">
      <div class="d-flex justify-content-between">
        <h3 class="card-title">Battery</h3>
        <i class="mdi mdi-battery"></i>
      </div>
    </div>
    <div class="card-body">
      <p>Total Quantity: <span>{{ $totalBattery }}</span></p>
      <p>Total Value: <span>₹{{ $totalBatteryValue }}</span></p>
      <p><a style="color:black;" href='{{ route("inventory.showDispatchInventory",  [ "type" => "battery"]) }}'>Dispatched Quantity</a></p>
      <p>Available Quantity: <span>{{ $availableBattery }}</span></p>
    </div>
  </div>
</div>
<div class="col-sm-3 mb-2 mt-2">
  <div class="card bg-warning">
    <div class="card-header">
      <div class="d-flex justify-content-between">
        <h3 class="card-title">Luminary</h3>
        <i class="mdi mdi mdi-led-on"></i>
      </div>
    </div>
    <div class="card-body">
      <p>Total Quantity: <span>{{ $totalLuminary }}</span> </p>
      <p>Total Value: <span>₹{{ $totalLuminaryValue }}</span></p>
      <p><a style="color:black;" href='{{ route("inventory.showDispatchInventory", "luminary") }}'>Dispatched Quantity</a></p>
      <p>Available Quantity: <span>{{ $availableLuminary }}</span></p>
    </div>
  </div>
</div>
<div class="col-sm-3 mb-2 mt-2">
  <div class="card"style="background-color: #FF5733; color: black;">
    <div class="card-header">
      <div class="d-flex justify-content-between">
        <h3 class="card-title">Structure</h3>
        <img width="20" height="20"
          src="https://img.icons8.com/external-others-pike-picture/50/external-Steel-Frame-house-others-pike-picture-2.png"
          alt="external-Steel-Frame-house-others-pike-picture-2" />
      </div>
    </div>
    <div class="card-body">
      <p>Total Quantity: <span>{{ $totalStructure }}</span></p>
      <p>Total Value: <span>₹{{ $totalStructureValue }}</span></p>
      <p><a style="color:black;" href='{{ route("inventory.showDispatchInventory", "structure") }}'>Dispatched Quantity</a></p>
      <p>Available Quantity: <span>{{ $availableStructure }}</span></p>
    </div>
  </div>
</div>
<div class="col-sm-3 mb-2 mt-2">
  <div class="card bg-info">
    <div class="card-header">
      <div class="d-flex justify-content-between">
        <h3 class="card-title">Module</h3>
        <i class="mdi mdi-solar-panel"></i>
      </div>
    </div>
    <div class="card-body">
      <p>Total Quantity: <span>{{ $totalModule }}</span> </p>
      <p>Total Value: <span>₹{{ $totalModuleValue }}</span></p>
      <p><a style="color:black;" href='{{ route("inventory.showDispatchInventory", "module") }}'>Dispatched Quantity</a></p>
      <p>Available Quantity: <span>{{ $availableModule }}</span></p>
    </div>
  </div>
</div>
    </div>

    <!-- Inventory Table -->
    <div class="mt-4">
      <table id="inventoryTable" class="table-striped table-bordered table mt-4">
        <thead>
          <tr>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Manufacturer</th>
            <th>Model</th>
            <th>Serial Number</th>
            <th>HSN Code</th>
            <th>Unit</th>
            <th class="actions">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($inventory as $item)
            <tr>
              <td>{{ $item->item_code }}</td>
              <td>{{ $item->item }}</td>
              <td>{{ $item->manufacturer }}</td>
              <td>{{ $item->model }}</td>
              <td>{{ $item->serial_number }}</td>
              <td>{{ $item->hsn }}</td>
              <td>{{ $item->unit }}</td>
              <td>
                <a href="#modal{{ $item->id }}" data-bs-toggle="modal" class="btn btn-sm btn-info">Details</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <!-- Inventory Details Modal -->
  @foreach ($inventory as $item)
    <div class="modal fade" id="modal{{ $item->id }}" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Item Details: {{ $item->item_name }}</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <p><strong>Item Code:</strong> {{ $item->item_code }}</p>
            <p><strong>Manufacturer:</strong> {{ $item->manufacturer }}</p>
            <p><strong>Model:</strong> {{ $item->model }}</p>
            <p><strong>Unit:</strong> {{ $item->unit }}</p>
            <p><strong>Quantity:</strong> {{ $item->quantity }}</p>
            <p><strong>Rate:</strong> {{ $item->rate }}</p>
            <p><strong>Total Value:</strong> {{ number_format($item->quantity * $item->rate, 2) }}</p>
          </div>
        </div>
      </div>
    </div>
  @endforeach
@endsection

@push("scripts")
  <script>
    $(document).ready(function() {
      $('#inventoryTable').DataTable({
        dom: "<'row'<'col-sm-6 d-flex align-items-center'f><'col-sm-6 d-flex justify-content-end'B>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{
            extend: 'excel',
            text: '<i class="mdi mdi-file-excel"></i>',
            className: 'btn btn-sm btn-success',
            titleAttr: 'Export to Excel', // Tooltip,
            exportOptions: {
              columns: ':not(.actions)'
            }
          },
          {
            extend: 'pdf',
            text: '<i class="mdi mdi-file-pdf"></i>',
            className: 'btn btn-sm btn-danger',
            titleAttr: 'Export to PDF', // Tooltip
            exportOptions: {
              columns: ':not(.actions)'
            }
          },
          {
            extend: 'print',
            text: '<i class="mdi mdi-printer"></i>',
            className: 'btn btn-sm btn-info',
            titleAttr: 'Print Table',
            exportOptions: {
              columns: ':not(.actions)'
            } // Tooltip
          }
        ],
        paging: true,
        pageLength: 50, // Show 50 rows per page
        searching: true,
        ordering: true,
        responsive: true,
        language: {
          search: '',
          searchPlaceholder: 'Search Inventory'
        }
      });
    });

    // Export to CSV function
    function exportToCSV() {
      let csvContent = "data:text/csv;charset=utf-8,";
      let rows = [
        ['Item Code', 'Item Name', 'Manufacturer', 'Model', 'Unit', 'Quantity', 'Rate', 'Total Value']
      ];

      @foreach ($inventory as $item)
        rows.push([
          "{{ $item->item_code }}",
          "{{ $item->item }}",
          "{{ $item->manufacturer }}",
          "{{ $item->model }}",
          "{{ $item->serial_number }}",
          "{{ $item->hsn }}",
          "{{ $item->unit }}",
        ]);
      @endforeach

      rows.forEach(row => {
        csvContent += row.join(",") + "\n";
      });

      const encodedUri = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", encodedUri);
      link.setAttribute("download", "inventory.csv");
      document.body.appendChild(link);
      link.click();
    }
  </script>
@endpush
