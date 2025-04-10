@extends("layouts.main")
@section("content")

@if($title)
    <h2> {{ $title }} Dispatched Inventory</h2>
@else
    <h2> Dispatched Inventory</h2>
@endif

<x-data-table id="tadaTable" :pageLength="50">
    <x-slot:thead>
      <tr>
        <th data-select="true">
          <input type="checkbox" id="selectAll" />
        </th>
          <th>Item Code</th>
          <th>Item Name</th>
          <th>Dispatched Quantity</th>
          <th>Available Quantity</th>
          <th>Value</th>
          <th>Vendor</th>
          <th>Actions</th>
      </tr>
    </x-slot:thead>
    <x-slot:tbody>
        @if(isset($specificDispatch))
            @foreach($specificDispatch as $item)
            <tr>
                <td><input type="checkbox" class="select-item" /></td>
                <td>{{ $item->item_code }}</td>
                <td>
                    {{ $item->item }}
                </td>
                <td>{{ $item->total_quantity ?? 'NA' }}</td>
                <td>{{ $availableBattery ?? 'N/A' }}</td>
                <td>₹{{ $item->total_value ?? 'N/A' }}</td>
                <td>{{ $item->vendor->name ?? 'N/A' }}</td>
                <td>
                    <a href="#" class="btn btn-info btn-sm item-details" data-bs-toggle="modal" data-bs-target="#detailsModal" 
                       data-item-code="{{ $item->item_code }}"
                       data-item-name="{{ $item->item_name ?? '' }}"
                       data-manufacturer="{{ $item->manufacturer ?? '' }}"
                       data-serial-number="{{ $item->serial_number ?? '' }}"
                       data-model="{{ $item->model ?? '' }}"
                       data-quantity="{{ $item->quantity }}"
                       data-vendor="{{ $item->vendor->name ?? '' }}"
                       data-status="{{ $item->status ?? '' }}"
                       data-total="{{ $item->total ?? '' }}"
                       data-date="{{ $item->created_at ? date('d F Y', strtotime($item->created_at)) : 'N/A' }}"
                       data-site="{{ $item->project->name ?? 'N/A' }}">
                        <i class="mdi mdi-eye"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        @elseif(isset($dispatch))
            @foreach($dispatch as $item)
            <tr>
                <td><input type="checkbox" class="select-item" /></td>
                <td>{{ $item->item_code }}</td>
                <td>
                    @if($item->item_code == 'SL01')
                        Module
                    @elseif($item->item_code == 'SL02')
                        Luminary
                    @elseif($item->item_code == 'SL03')
                        Battery
                    @elseif($item->item_code == 'SL04')
                        Structure
                    @else
                        Unknown
                    @endif
                </td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->available_quantity ?? 'N/A' }}</td>
                <td>₹{{ $item->value ?? 'N/A' }}</td>
                <td>{{ $item->vendor ?? 'N/A' }}</td>
                <td>
                    <a href="#" class="btn btn-info btn-sm item-details" data-bs-toggle="modal" data-bs-target="#detailsModal" 
                       data-item-code="{{ $item->item_code }}"
                       data-item-name="{{ $item->item_name ?? '' }}"
                       data-manufacturer="{{ $item->manufacturer ?? '' }}"
                       data-serial-number="{{ $item->serial_number ?? '' }}"
                       data-model="{{ $item->model ?? '' }}"
                       data-quantity="{{ $item->quantity }}"
                       data-vendor="{{ $item->vendor ?? '' }}"
                       data-status="{{ $item->status ?? '' }}"
                       data-total="{{ $item->total ?? '' }}"
                       data-date="{{ $item->created_at ? date('d F Y', strtotime($item->created_at)) : 'N/A' }}"
                       data-site="{{ $item->project->name ?? 'N/A' }}">
                        <i class="mdi mdi-eye"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        @else
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailsModal">
                    <i class="mdi mdi-eye"></i>
                </a>
                </td>
            </tr>
        @endif
    </x-slot:tbody>
  </x-data-table>

    <!-- Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Item Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row mb-2">
                            <div class="col-md-4"><strong>Item Code</strong> <br> <span id="modal-item-code">SL02</span></div>
                            <div class="col-md-4"><strong>Item Name</strong> <br> <span id="modal-item-name">Luminary</span></div>
                            <div class="col-md-4"><strong>Manufacturer</strong> <br> <span id="modal-manufacturer">Ecosis</span></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4"><strong>Serial Number</strong> <br> <span id="modal-serial-number">861268075241878</span></div>
                            <div class="col-md-4"><strong>Model</strong> <br> <span id="modal-model">SSL20W</span></div> 
                            <div class="col-md-4"><strong>Quantity</strong> <br> <span id="modal-quantity">5</span></div>
                        </div>
                     
                        <div class="row mb-2"> 
                            <div class="col-md-4"><strong>Vendor</strong> <br> <span id="modal-vendor">RS Constructions</span></div>
                            <div class="col-md-4"><strong>Status</strong> <br> <span id="modal-status">Stock</span></div>
                            <div class="col-md-4"><strong>Total</strong> <br> <span id="modal-total">2785</span></div>
                        </div>  
                        <div class="row mb-2">
                            <div class="col-md-4"><strong>Date</strong> <br> <span id="modal-date">4 April 2025</span></div>
                            <div class="col-md-4"><strong>Site</strong> <br> <span id="modal-site">Madhepura</span></div>  
                        </div>  
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle modal data population
    const detailsLinks = document.querySelectorAll('.item-details');
    
    detailsLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Get data attributes
            const itemCode = this.getAttribute('data-item-code');
            const itemName = this.getAttribute('data-item-name');
            const manufacturer = this.getAttribute('data-manufacturer');
            const serialNumber = this.getAttribute('data-serial-number');
            const model = this.getAttribute('data-model');
            const quantity = this.getAttribute('data-quantity');
            const vendor = this.getAttribute('data-vendor');
            const status = this.getAttribute('data-status');
            const total = this.getAttribute('data-total');
            const date = this.getAttribute('data-date');
            const site = this.getAttribute('data-site');
            
            // Set modal content
            document.getElementById('modal-item-code').textContent = itemCode;
            document.getElementById('modal-item-name').textContent = itemName;
            document.getElementById('modal-manufacturer').textContent = manufacturer;
            document.getElementById('modal-serial-number').textContent = serialNumber;
            document.getElementById('modal-model').textContent = model;
            document.getElementById('modal-quantity').textContent = quantity;
            document.getElementById('modal-vendor').textContent = vendor;
            document.getElementById('modal-status').textContent = status;
            document.getElementById('modal-total').textContent = total;
            document.getElementById('modal-date').textContent = date;
            document.getElementById('modal-site').textContent = site;
        });
    });
});
</script>

@endsection