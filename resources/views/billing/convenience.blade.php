@extends("layouts.main")
@section("content")
<x-data-table id="convenienceTable" :pageLength="50">
    <x-slot:thead>
      <tr>
        <th data-select="true">
          <input type="checkbox" id="selectAll" />
        </th>
            <th>Name</th>
            <th>Employee Id</th>
            <!-- <th>Meeting</th> -->
            <!-- <th>From</th> -->
            <!-- <th>To</th> -->
            <th>Department</th>
            <!-- <th>Start Date</th> -->
            <!-- <th>End Date</th> -->
            <!-- <th>PNR number start</th> -->
            <!-- <th>PNR number return</th> -->
            <!-- <th>Visit Approved By</th> -->
            <!-- <th>Transport mode</th> -->
            <th>Objective</th>
            <!-- <th>Meeting/Visit</th> -->
            <!-- <th>Achievements</th> -->
            <!-- <th>Designation</th> -->
            <!-- <th>Categories</th> -->
            <!-- <th>Description</th> -->
            <!-- <th>Total Kilometers</th> -->
            <!-- <th>KM Rate</th> -->
            <!-- <th>Rent</th> -->
            <!-- <th>Vehicle Number</th> -->
            <th>Amount</th>
            <th>Actions</th>
      </tr>
    </x-slot:thead>
    <x-slot:tbody>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <!-- <td></td> -->
            <!-- <td></td> -->
            <!-- <td></td> -->
            <td></td>
            <!-- <td></td> -->
            <!-- <td></td> -->
            <!-- <td></td> -->
            <!-- <td></td> -->
            <!-- <td></td> -->
            <!-- <td></td> -->
            <td></td>
            <!-- <td></td> -->
            <!-- <td></td> -->
            <!-- <td></td> -->
            <!-- <td></td> -->
            <!-- <td></td> -->
            <!-- <td></td> -->
            <!-- <td></td> -->
            <!-- <td></td> -->
            <!-- <td></td> -->
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
                            <div class="col-md-4"><strong>Name:</strong> <br> <span>John Doe</span></div>
                            <div class="col-md-4"><strong>Employee ID:</strong> <br> <span>EMP123</span></div>
                            <div class="col-md-4"><strong>Meeting:</strong> <br> <span>Business Meeting</span></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4"><strong>From:</strong> <br> <span>New York</span></div>
                            <div class="col-md-4"><strong>To:</strong> <br> <span>Los Angeles</span></div>
                            <div class="col-md-4"><strong>Department:</strong> <br> <span>Sales</span></div>
                        </div>
                     
                        <div class="row mb-2">
                            <div class="col-md-4"><strong>Start Date:</strong> <br> <span>2024-04-01</span></div>
                            <div class="col-md-4"><strong>End Date:</strong> <br> <span>2024-04-05</span></div>
                            <div class="col-md-4"><strong>PNR Start:</strong> <br> <span>PNR12345</span></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4"><strong>PNR Return:</strong> <br> <span>PNR67890</span></div>
                            <div class="col-md-4"><strong>Visit Approved By:</strong> <br> <span>Manager</span></div>
                            <div class="col-md-4"><strong>Transport Mode:</strong> <br> <span>Flight</span></div>
                        </div>
                      
                        <div class="row mb-2">
                            <div class="col-md-4"><strong>Objective:</strong> <br> <span>Client Acquisition</span></div>
                            <div class="col-md-4"><strong>Meeting/Visit:</strong> <br> <span>Visit</span></div>
                            <div class="col-md-4"><strong>Achievements:</strong> <br> <span>Signed new deal</span></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4"><strong>Designation:</strong> <br> <span>Senior Executive</span></div>
                            <div class="col-md-4"><strong>Categories:</strong> <br> <span>Corporate</span></div>
                            <div class="col-md-4"><strong>Description:</strong> <br> <span>Client visit for negotiations</span></div>
                        </div>
                       
                        <div class="row mb-2">
                            <div class="col-md-4"><strong>Total Kilometers:</strong> <br> <span>500</span></div>
                            <div class="col-md-4"><strong>KM Rate:</strong> <br> <span>5</span></div>
                            <div class="col-md-4"><strong>Rent:</strong> <br> <span>$200</span></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4"><strong>Vehicle Number:</strong> <br> <span>NYC-1234</span></div>
                            <div class="col-md-4"><strong>Amount:</strong> <br> <span>$2500</span></div>
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

  

    
   
    

   