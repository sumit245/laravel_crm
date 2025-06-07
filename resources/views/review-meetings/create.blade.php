@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Create Meeting</h4>

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
                <input type="text" class="form-control" name="meet_link" placeholder="Meeting Link" required>
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
          <div class="row">
            <div class="col-sm-6">

              <div class="form-group">
                <label for="agreement_date" class="form-label">Date of meeting</label>
                <input type="date" name="meet_date" class="form-control" value="{{ old("meet_date", date("Y-m-d")) }}"
                  min="{{ date("Y-m-d") }}" required>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="agreement_date" class="form-label">Time of Meeting</label>
                <input type="time" class="form-control" name="meet_time" required>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="agreement_date" class="form-label">Meeting Type</label>
            <select name="type" class="form-select" required>
              <option value="Review">Review</option>
              <option value="Planning">Planning</option>
              <option value="Discussion">Discussion</option>
            </select>
          </div>

          {{-- TODO: Select Project --}}
          <div class="form-group">
            <label for="agreement_date" class="form-label">Project:</label>
            <select name="project_id" class="form-select">
              <option value="">-- Select Project --</option>
              @foreach ($projects as $project)
                <option value="{{ $project->id }}">{{ $project->project_name }}</option>
              @endforeach
            </select>
          </div>

          {{-- TODO: Select Users by Role Create columns for each user roles. In each column show FirstName LastName email and a checkBox. Also Give a searchbox in each column. In each row provide checkboxe to select/deselect --}}
          <div>
            <label class="mb-2 block font-bold">Select Participants:</label>
            <div class="user-columns">
              @foreach ($usersByRole as $role => $roleUsers)
                <div class="user-column">
                  <strong>{{ $role }}</strong>
                  <input type="text" class="form-control" placeholder="Search {{ $role }}"
                    onkeyup="filterUsers(this)">
                  <div class="list-wrapper">
                    <ul class="todo-list todo-list-rounded">
                      @foreach ($roleUsers as $user)
                        <li class="d-block">
                          <div class="form-check w-100">
                            <label class="form-check-label">
                              <input type="checkbox" class="form-check-input" name="user_ids[]"
                                value="{{ $user->id }}">
                              {{ $user->firstName }} {{ $user->lastName }}
                              <i class="input-helper rounded"></i>
                              <i class="input-helper"></i>
                            </label>
                            <div class="text-small me-3 ps-4">{{ $user->email }}</div>
                          </div>
                        </li>
                      @endforeach
                    </ul>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
          <button type="submit" class="btn btn-primary" onclick="console.log('Working')">Create Meeting</button>
        </form>
      </div>
    </div>
  </div>
@endsection

@push("styles")
  <style>
    .user-columns {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .user-column {
      flex: 1;
      min-width: 200px;
      max-height: 400px;
      overflow-y: auto;
      border: 0.5px solid #ccc;
      padding: 10px;
      border-radius: 6px;
    }

    .user-search {
      margin-bottom: 10px;
      width: 100%;
    }

    .user-checkbox {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 4px 0;
    }
  </style>
@endpush

@push("scripts")
  <script>
    function filterUsers(input) {
      const filter = input.value.toLowerCase();
      const list = input.closest('.user-column').querySelector('.todo-list');


      Array.from(list.children).forEach(item => {
        const labelText = item.innerText.toLowerCase();
        item.style.display = labelText.includes(filter) ? 'flex' : 'none';
      });
    }
  </script>
@endpush
