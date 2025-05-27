@extends('layouts.main')
@section('content')

<div class="container">
<form action="{{ route('tasks.update', $tasks->id) }}" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="project_id" value="{{ $projectId }}">
    <!-- Panchayat -->
    <!-- <div class="mb-3">
        <label for="panchayat" class="form-label">Panchayat</label>
        <input type="text" name="panchayat" id="panchayat" class="form-control"
               value="{{ $tasks->site->panchayat ?? 'N/A' }}">
    </div> -->

    <!-- Engineer Name -->
    <div class="mb-3">
    <label for="engineer_id" class="form-label">Engineer Name</label>
    <select name="engineer_id" id="engineer_id" class="form-control">
        <option value="">Select Engineer</option>
        @foreach($engineers as $engineer)
            <option value="{{ $engineer->id }}" {{ $tasks->engineer_id == $engineer->id ? 'selected' : '' }}>
                {{ $engineer->firstName }}
            </option>
        @endforeach
    </select>
</div>

    <!-- Vendor Name -->
    <div class="mb-3">
    <label for="vendor_id" class="form-label">Vendor Name</label>
    <select name="vendor_id" id="vendor_id" class="form-control">
        <option value="">Select Vendor</option>
        @foreach($vendors as $vendor)
            <option value="{{ $vendor->id }}" {{ $tasks->vendor_id == $vendor->id ? 'selected' : '' }}>
                {{ $vendor->name }}
            </option>
        @endforeach
    </select>
</div>
        <!-- Billed Field -->
    <div class="mb-3">
        <label for="billed" class="form-label">Billed</label>
        <select name="billed" id="billed" class="form-control">
            <option value="">Select Option</option>
            <option value="1" {{ isset($tasks->billed) && $tasks->billed == 1 ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ isset($tasks->billed) && $tasks->billed == 0 ? 'selected' : '' }}>No</option>
        </select>
    </div>


    <!-- Assigned Date -->
    <!-- <div class="mb-3">
        <label for="assigned_date" class="form-label">Assigned Date</label>
        <input type="text" name="assigned_date" id="assigned_date" class="form-control"
               value="{{ $tasks->created_at->format('Y-m-d') }}">
    </div> -->

    <!-- End Date -->
    <!-- <div class="mb-3">
        <label for="end_date" class="form-label">End Date</label>
        <input type="date" name="end_date" id="end_date" class="form-control"
               value="{{ $tasks->end_date ?? '' }}">
    </div> -->

    <!-- Wards -->
    <!-- <div class="mb-3">
        <label for="ward" class="form-label">Ward</label>
        <input type="text" name="ward" id="ward" class="form-control"
               value="{{ $tasks->site->ward ?? 'N/A' }}">
    </div> -->

    <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>
@endsection