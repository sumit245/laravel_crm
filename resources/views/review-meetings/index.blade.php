@extends("layouts.main")

@section("content")
  <div class="container p-2">
    <div class="d-flex justify-content-between mb-3">
      <!-- Search box is added automatically by DataTables -->
      <div></div> <!-- Empty div to align with search box -->
      <a class="btn btn-primary" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Create New Meeting"
        href="{{ route("meets.create") }}"> <i class="mdi mdi-plus-circle"></i></a>
    </div>
    <table id="meetingsTable" class="table-bordered table-striped table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Agenda</th>
          <th>Platform</th>
          <th>Meet Link</th>
          <th>Date & Time</th>
          <th>Type</th>
          <th>Participants</th>
          <th>Created At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($meets as $meet)
          <tr>
            <td>{{ $meet->title }}</td>
            <td>{{ $meet->agenda }}</td>
            <td>{{ $meet->platform }}</td>
            <td><a href="{{ $meet->meet_link }}" target="_blank">Join</a></td>
            <td>{{ $meet->meet_date }}</br>{{ $meet->meet_time }}</td>
            <td>{{ $meet->type }}</td>
            <td>
              @php
                $userIds = json_decode($meet->user_ids, true);
                $participants = \App\Models\User::whereIn("id", $userIds)->get();
              @endphp
              @foreach ($participants as $user)
                <div>{{ $user->firstName }} {{ $user->lastName }}</div>
              @endforeach
            </td>
            <td>{{ $meet->created_at->format("d M Y") }}</td>
            <td>
              <!-- View Button -->
              <a href="{{ route("meets.show", $meet->id) }}" class="btn btn-icon btn-info" data-toggle="tooltip"
                title="View Details">
                <i class="mdi mdi-eye"></i>
              </a>
              <!-- Edit Button -->
              <a href="{{ route("meets.edit", $meet->id) }}" class="btn btn-icon btn-warning" data-toggle="tooltip"
                title="Postpone">
                <i class="mdi mdi-pencil"></i>
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection

@push("scripts")
  <script>
    $(document).ready(function() {
      $('#meetingsTable').DataTable();
    });
  </script>
@endpush
