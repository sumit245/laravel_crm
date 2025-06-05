@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Add Projects</h4>

        <!-- Display validation errors -->
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
        <form class="forms-sample" action="{{ route("meets.store") }}" method="POST">
          @csrf
          <div class="form-group">
            <label for="meet_title" class="form-label">Meeting Title</label>
            <input type="text" name="title" class="form-control" id="meet_title" placeholder="Meeting Title"
              required>
          </div>
          <div class="form-group">
            <label for="meet_title" class="form-label">Agenda of Meeting</label>
            <textarea name="agenda" class="form-control" placeholder="Agenda"></textarea>
          </div>
          <div class="row">
            <div class="col-sm-8">
              <div class="form-group">
                <label for="meet_title" class="form-label">Link to Join</label>
                <input type="url" class="form-control" name="meet_link" placeholder="Meeting Link" required>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label for="meet_title" class="form-label">Select Platform</label>
                <select name="platform" class="form-select" required>
                  <option value="Google Meet">Google Meet</option>
                  <option value="Zoom">Zoom</option>
                  <option value="Teams">Teams</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="agreement_date" class="form-label">Date of meeting</label>
            <input type="date" name="meet_date" class="form-control" value="{{ old("meet_date", date("Y-m-d")) }}"
              min="{{ date("Y-m-d") }}" required>
          </div>
          <div class="form-group">
            <label for="agreement_date" class="form-label">Time of Meeting</label>
            <input type="time" class="form-control" name="meet_time" required>
          </div>

          <div class="form-group">
            <label for="agreement_date" class="form-label">Agreement Date</label>
            <select name="type" class="form-select" required>
              <option value="Review">Review</option>
              <option value="Planning">Planning</option>
              <option value="Discussion">Discussion</option>
            </select>
          </div>

          {{-- TODO: Select Project --}}
          <div>
            <label>Project:</label>
            <select name="project_id">
              <option value="">-- Select Project --</option>
              @foreach ($projects as $project)
                <option value="{{ $project->id }}">{{ $project->project_name }}</option>
              @endforeach
            </select>
          </div>

          {{-- TODO: Select Users by Role --}}
          <div>
            <label>Select Participants:</label>
            <select name="users[]" multiple>
              @foreach ($usersByRole as $role => $roleUsers)
                <optgroup label="{{ $role }}">
                  @foreach ($roleUsers as $user)
                    <option value="{{ $user->id }}">
                      {{ $user->firstName }} {{ $user->lastName }} ({{ $user->email }})
                    </option>
                  @endforeach
                </optgroup>
              @endforeach
            </select>
          </div>

          <button type="submit" class="btn btn-primary">Create Meeting</button>

        </form>
      </div>
    </div>
  </div>
@endsection
