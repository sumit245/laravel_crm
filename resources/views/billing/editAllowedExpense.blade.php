@extends('layouts.main')

@section('content')

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Edit Allowed Expense</h4>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form>
        <!-- City Category -->
        <div class="mb-3">
          <label for="city_category" class="form-label fw-bold">City Category</label>
          <select id="city_category" name="city_category" class="form-select" required>
            <option value="" disabled selected>Select City Category</option>
            <option value="1">Metro City</option>
            <option value="2">Tier 1 City</option>
            <option value="3">Tier 2 City</option>
            <option value="4">Tourist Destination</option>
            <option value="5">Industrial Hub</option>
          </select>
        </div>

        <!-- User Category -->
        <div class="mb-3">
          <label for="user_category" class="form-label fw-bold">User Category</label>
          <select id="user_category" name="user_category" class="form-select" required>
            <option value="" disabled selected>Select User Category</option>
            <option value="1">M1</option>
            <option value="2">M2</option>
            <option value="3">M3</option>
            <option value="4">M4</option>
            <option value="5">M5</option>
          </select>
        </div>

        <!-- Hotel Bill Upto -->
        <div class="mb-3">
          <label for="hotel_bill" class="form-label fw-bold">Hotel Bill Upto (₹)</label>
          <input
            type="number"
            id="hotel_bill"
            name="hotel_bill"
            class="form-control"
            min="0"
            step="0.01"
            placeholder="Enter maximum allowed amount"
            required
          >
        </div>

        <!-- Submit Button -->
        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-content-save me-1"></i> Update Expense
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
