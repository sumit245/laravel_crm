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
            <th>Meeting</th>
            <th>From</th>
            <th>To</th>
            <th>Department</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>PNR number start</th>
            <th>PNR number return</th>
            <th>Visit Approved By</th>
            <th>Transport mode</th>
            <th>Objective</th>
            <th>Meeting/Visit</th>
            <th>Achievements</th>
            <th>Designation</th>
            <th>Categories</th>
            <th>Description</th>
            <th>Total Kilometers</th>
            <th>KM Rate</th>
            <th>Rent</th>
            <th>Vehicle Number</th>
            <th>Amount</th>
            <th>Actions</th>
      </tr>
    </x-slot:thead>
    <x-slot:tbody>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </x-slot:tbody>

    
  </x-data-table>
  @endsection