@extends("layouts.main")
@section("content")
<x-data-table id="tadaTable" :pageLength="50">
    <x-slot:thead>
      <tr>
        <th data-select="true">
          <input type="checkbox" id="selectAll" />
        </th>
          <th>From</th>
          <th>To</th>
          <th>Kilometer</th>
          <!-- <th>Mode of Transport</th> -->
          <th>Date</th>
          <th>Time</th>
          <th>Trip Price</th>
          <th>Total</th>
            <th>Actions</th>
      </tr>
    </x-slot:thead>
    <x-slot:tbody>
        <tr>
            <td></td>
            <!-- <td></td> -->
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
                            <div class="col-md-4"><strong>From</strong> <br> <span>Madhepura</span></div>
                            <div class="col-md-4"><strong>To</strong> <br> <span>Delhi</span></div>
                            <div class="col-md-4"><strong>Kilometer</strong> <br> <span>986Kms</span></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4"><strong>Mode of Transport</strong> <br> <span>Train</span></div>
                            <div class="col-md-4"><strong>Date</strong> <br> <span>5 April 2025</span></div>
                            <div class="col-md-4"><strong>Time</strong> <br> <span>18:00</span></div>
                        </div>
                     
                        <div class="row mb-2">
                            <div class="col-md-4"><strong>Trip Price</strong> <br> <span>1980</span></div>
                            <div class="col-md-4"><strong>Total</strong> <br> <span>2785</span></div>
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