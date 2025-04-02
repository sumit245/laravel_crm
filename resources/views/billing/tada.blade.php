@extends("layouts.main")
@section("content")
<x-data-table id="tadaTable" :pageLength="50">
    <x-slot:thead>
      <tr>
        <th>id</th>
          <th>Kilometer</th>
          <th>Mode of Transport</th>
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
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
            <a href="#" class="btn btn-info btn-sm">
                <i class="mdi mdi-eye"></i>
            </a>
            </td>
        </tr>
    </x-slot:tbody>

    
  </x-data-table>
  @endsection