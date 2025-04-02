@extends("layouts.main")
@section("content")
<x-data-table id="convenienceTable" :pageLength="50">
    <x-slot:thead>
      <tr>
        <th data-select="true">
          <input type="checkbox" id="selectAll" />
        </th>
            <th>Breda Sl No</th>
            <th>Site Name</th>
            <th>Address</th>
            <th>Vendor</th>
            <th>Engineer</th>
            <th>Actions</th>
      </tr>
    </x-slot:thead>
    <x-slot:tbody>
    </x-slot:tbody>

    
  </x-data-table>
  @endsection