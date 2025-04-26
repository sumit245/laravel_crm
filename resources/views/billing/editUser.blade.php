@extends('layouts.main')

@section('content')

<form method="POST" action="{{ route('billing.updateuser') }}" class="m-3">
    @csrf
    @method('POST') <!-- or POST, depending on your need -->

    <input type="hidden" name="user_id" value="{{ request()->route('id') }}">

    <!-- Name -->
    <div class="mb-3">
        <label class="form-label fw-bold">Name</label>
        <input type="text" class="form-control" 
               value="{{ $ue->firstName }} {{ $ue->lastName }}" 
               disabled>
    </div>

    <!-- Role -->
    <div class="mb-3">
        <label class="form-label fw-bold">Role</label>
        <input type="text" class="form-control" 
               value="{{ $ue->role }}" 
               disabled>
    </div>

    <!-- Email -->
    <div class="mb-3">
        <label class="form-label fw-bold">Email</label>
        <input type="email" class="form-control" 
               value="{{ $ue->email }}" 
               disabled>
    </div>

    <!-- Category (Editable) -->
    <div class="mb-3">
        <label for="category" class="form-label fw-bold">Category</label>
        <select class="form-select" id="category" name="category" required>
            <option value="" disabled {{ !$ue->category ? 'selected' : '' }}>Select Category</option>
            <option value="M1" {{ $ue->category == 'M1' ? 'selected' : '' }}>M1</option>
            <option value="M2" {{ $ue->category == 'M2' ? 'selected' : '' }}>M2</option>
            <option value="M3" {{ $ue->category == 'M3' ? 'selected' : '' }}>M3</option>
            <option value="M4" {{ $ue->category == 'M4' ? 'selected' : '' }}>M4</option>
            <option value="M5" {{ $ue->category == 'M5' ? 'selected' : '' }}>M5</option>
        </select>
    </div>

    <!-- Submit Button -->
    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-content-save me-1"></i> Update Category
        </button>
    </div>
</form>


@endsection