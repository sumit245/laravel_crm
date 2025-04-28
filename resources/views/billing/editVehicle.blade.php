@extends('layouts.main')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="mdi mdi-car me-2"></i>Vehicle Information</h5>
    </div>
    <div class="card-body">
        <form id="vehicleForm" method="POST" action="{{ route('billing.updatevehicle') }}">
            @csrf
            @method('POST') <!-- Assuming this is an update form -->

            <!-- Hidden ID field -->
            <input type="hidden" name="user_id" value="{{ $ev->id }}">

            <!-- Vehicle Name -->
            <div class="mb-3">
                <label for="vehicleName" class="form-label fw-bold">Vehicle Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="vehicleName" name="vehicle_name" 
                       value="{{ old('vehicle_name', $ev->vehicle_name ?? '') }}" 
                       required placeholder="e.g., Toyota Innova">
                <div class="invalid-feedback">Please enter a vehicle name</div>
            </div>

            <!-- Rate per KM -->
            <div class="mb-3">
                <label for="ratePerKm" class="form-label fw-bold">Rate per KM (₹) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" class="form-control" id="ratePerKm" name="rate" 
                           value="{{ old('rate', $ev->rate ?? '') }}"
                           step="0.01" min="0" required placeholder="e.g., 12.50">
                </div>
                <div class="invalid-feedback">Please enter a valid rate</div>
            </div>

            <div class="row">
                <!-- Category -->
                <div class="col-md-6 mb-3">
                    <label for="category" class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="" disabled {{ !isset($ev->category) ? 'selected' : '' }}>Select Category</option>
                        <option value="Car" {{ (old('category', $ev->category ?? '')) == 'Car' ? 'selected' : '' }}>Car</option>
                        <option value="Bike" {{ (old('category', $ev->category ?? '')) == 'Bike' ? 'selected' : '' }}>Bike</option>
                        <option value="Public Transport" {{ (old('category', $ev->category ?? '')) == 'Auto' ? 'selected' : '' }}>Public Transport</option>
                    </select>
                    <div class="invalid-feedback">Please select a category</div>
                </div>
                <!-- Sub-Category -->
<div class="col-md-6 mb-3">
    <label for="subcategory" class="form-label fw-bold">Sub-Category</label>
    <input type="text" class="form-control" id="subcategory" name="subcategory" 
           value="{{ old('subcategory', $ev->subcategory ?? '') }}" 
           placeholder="e.g., SUV, Sedan, Cruiser">
</div>
                <!-- Active Status -->
                
            </div>

            <!-- Submit Button -->
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="mdi mdi-content-save me-1"></i>Save Vehicle
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
