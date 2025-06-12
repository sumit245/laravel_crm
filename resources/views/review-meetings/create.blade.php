@extends("layouts.main")

@section("content")
<div class="content-wrapper p-2">
  <div class="card">
    <div class="card-body">
      <h4 class="card-title">Add Projects</h4>

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
          <label class="form-label">Meeting Title</label>
          <input type="text" name="title" class="form-control" placeholder="Meeting Title" required>
        </div>

        <div class="form-group">
          <label class="form-label">Agenda of Meeting</label>
          <textarea name="agenda" class="form-control" placeholder="Enter your agenda..." style="height: 100px;"></textarea>
        </div>

        <div class="row">
          <div class="col-sm-8">
            <div class="form-group">
              <label class="form-label">Link to Join</label>
              <input type="url" class="form-control" name="meet_link" placeholder="Meeting Link" required>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <label class="form-label">Select Platform</label>
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
          <label class="form-label">Date of Meeting</label>
          <input type="date" name="meet_date" id="meet_date" class="form-control" value="{{ old('meet_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
        </div>

        <div class="form-group">
          <label class="form-label">Time of Meeting</label>
          <div class="d-flex gap-2">
            <div class="flex-fill">
              <label class="form-label">From</label>
              <input type="time" class="form-control" name="meet_time_from" id="meet_time1" required>
            </div>
            <div class="flex-fill">
              <label class="form-label">To</label>
              <input type="time" class="form-control" name="meet_time_to" id="meet_time2" required>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Meeting Type</label>
          <select name="type" class="form-select" required>
            <option value="Review">Review</option>
            <option value="Planning">Planning</option>
            <option value="Discussion">Discussion</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Project:</label>
          <select name="project_id" class="form-select">
            <option value="">-- Select Project --</option>
            @foreach ($projects as $project)
            <option value="{{ $project->id }}">{{ $project->project_name }}</option>
            @endforeach
          </select>
        </div>

        {{-- Participants --}}
        <div class="form-group">
          <label class="form-label d-block">Select Participants:</label>
          <div class="row">
            @foreach ($usersByRole as $role => $roleUsers)
            <div class="col-md-4 mb-3">
              <div class="border rounded p-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <strong>{{ $role }}</strong>
                  <a href="javascript:void(0);" class="text-primary small select-all" data-role="{{ \Illuminate\Support\Str::slug($role, '_') }}">
                    Select All
                  </a>
                </div>
                <input type="text" class="form-control mb-2 participant-search" placeholder="Search..." data-role="{{ \Illuminate\Support\Str::slug($role, '_') }}">
                <div class="participant-scroll role-group-{{ \Illuminate\Support\Str::slug($role, '_') }}">
                  @foreach ($roleUsers as $user)
                  <label class="circle-checkbox-label d-flex align-items-start gap-2 mb-2">
                    <input type="checkbox" name="users[]" value="{{ $user->id }}" class="circle-checkbox role-{{ \Illuminate\Support\Str::slug($role, '_') }}">
                    <span>
                      {{ $user->firstName }} {{ $user->lastName }}<br>
                      <small class="text-muted">{{ $user->email }}</small>
                    </span>
                  </label>
                  @endforeach
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Create Meeting</button>
      </form>
    </div>
  </div>
</div>
@endsection

@push("styles")
<style>
.participant-scroll {
  max-height: 220px;
  overflow-y: hidden;
  transition: all 0.3s ease;
}
.participant-scroll:hover {
  overflow-y: auto;
}
.participant-scroll::-webkit-scrollbar {
  width: 6px;
}
.participant-scroll::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.2);
  border-radius: 4px;
}

/* Circle Checkbox */
.circle-checkbox-label {
  cursor: pointer;
  user-select: none;
}
.circle-checkbox {
  appearance: none;
  width: 22px;
  height: 22px;
  border: 2px solid #888;
  border-radius: 50%;
  position: relative;
  margin-top: 3px;
  transition: background-color 0.3s ease, border-color 0.3s ease;
  display: inline-block;
  vertical-align: middle;
  cursor: pointer;
}
.circle-checkbox:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}
.circle-checkbox:checked::after {
  content: '✔';
  color: #fff;
  font-size: 14px;
  font-weight: bold;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

/* Enhanced Select Box */
.form-select {
  appearance: none;
  background-color: #fff;
  border: 1px solid #ced4da;
  padding: 0.5rem 1.5rem 0.5rem 0.75rem;
  border-radius: 0.375rem;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 140 140' width='12' height='12' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolygon points='0,0 140,0 70,100' fill='%23666'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 12px;
}
.form-select:focus {
  border-color: #86b7fe;
  outline: 0;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>
@endpush

@push("scripts")
<script>
  
document.querySelectorAll('.select-all').forEach(function (btn) {
  btn.addEventListener('click', function () {
    const role = this.dataset.role;
    const checkboxes = document.querySelectorAll('.role-' + role);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
    this.textContent = allChecked ? 'Select All' : 'Unselect All';
  });
});

// Participant Search Filter
document.querySelectorAll('.participant-search').forEach(function (searchBox) {
  searchBox.addEventListener('input', function () {
    const role = this.dataset.role;
    const keyword = this.value.toLowerCase();
    const participants = document.querySelectorAll('.role-group-' + role + ' .circle-checkbox-label');
    participants.forEach(label => {
      const text = label.textContent.toLowerCase();
      label.style.display = text.includes(keyword) ? '' : 'none';
    });
  });
});

// Auto-show date/time picker
['meet_date', 'meet_time1', 'meet_time2'].forEach(id => {
  const el = document.getElementById(id);
  if (el) {
    el.addEventListener('click', function () {
      this.showPicker && this.showPicker();
    });
  }
});

</script>
@endpush