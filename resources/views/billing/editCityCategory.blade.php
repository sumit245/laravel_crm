@extends('layouts.main')

@section('content')

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit City Category Assignment</h4>
    <a href="" class="btn btn-secondary">
      <i class="mdi mdi-arrow-left"></i> Back
    </a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="#">
        <!-- CSRF & Method Spoofing Placeholder -->
        {{-- @csrf --}}
        {{-- @method('PUT') --}}

        <!-- Hidden ID (Optional) -->
        <input type="hidden" name="assignment_id" value="">

        <!-- City Name -->
        <div class="mb-3">
          <label class="form-label fw-bold">City Name</label>
          <input type="text" class="form-control" value="New Delhi" disabled>
        </div>


<!-- Assigned Categories -->
<div class="mb-3">
    <label for="category" class="form-label fw-bold">Category</label>
    <select class="form-select js-single-category" id="category" name="category" required>
        <option value="">Select a category</option>
        <option value="1">Metro City</option>
        <option value="2">Tier 1 City</option>
        <option value="3">Tier 2 City</option>
        <option value="4">Tourist Destination</option>
        <option value="5">Industrial Hub</option>
    </select>
</div>



        <!-- Submit Button -->
        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-content-save me-1"></i> Update
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
