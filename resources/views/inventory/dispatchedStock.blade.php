@extends("layouts.main")
@section("content")

<x-data-table id="tadaTable" :pageLength="50">
    <x-slot:thead>
      <tr>
        <th data-select="true">
          <input type="checkbox" id="selectAll" />
        </th>
          <th>Item Code</th>
          <th>Item Name</th>
          <!-- <th>Manufacturer</th> -->
          <!-- <th>Model</th> -->
          
          <!-- <th>Serial NUmber</th> -->
          <th>Dispatched Quanity</th>
          <th>Available Quanity</th>
          <th>Value</th>
          <th>Vendor</th>
          <!-- <th>Total</th> -->
          <!-- <th>Status</th> -->
            <th>Actions</th>
      </tr>
    </x-slot:thead>
    <x-slot:tbody>
        <tr>
            <td></td>
            <!-- <td></td> -->
            <!-- <td></td> -->
            <!-- <td></td> -->
            <!-- <td></td> -->
            <!-- <td></td> -->
            <!-- <td></td> -->
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
    </x-slot:tbody>
  </x-data-table>

    <!-- Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Employee Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row mb-2">
                            <div class="col-md-4"><strong>Item Code</strong> <br> <span>SL02</span></div>
                            <div class="col-md-4"><strong>Item Name</strong> <br> <span>Luminary</span></div>
                            <div class="col-md-4"><strong>Manufacturer</strong> <br> <span>Ecosis </span></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4"><strong>Serial Number</strong> <br> <span>861268075241878</span></div>
                            <div class="col-md-4"><strong>Model</strong> <br> <span>SSL20W </span></div> 
                            <div class="col-md-4"><strong>Quantity</strong> <br> <span>5</span></div>
                        </div>
                     
                        <div class="row mb-2"> 
                        <div class="col-md-4"><strong>Vendor</strong> <br> <span>RS Constructions</span></div>
                            <div class="col-md-4"><strong>Status</strong> <br> <span>Stock</span></div>
                            <div class="col-md-4"><strong>Total</strong> <br> <span>2785</span></div>
                           
                        </div>  
                        <div class="row mb-2">
                        <div class="col-md-4"><strong>Date</strong> <br> <span>4 April 2025</span></div>
                        <div class="col-md-4"><strong>Site</strong> <br> <span>Madhepura</span></div>  
                        </div>  
                                       
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

  @endsection