@extends('layouts.main')

@section('content')

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Edit Allowed Expense</h4>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form action="{{ route('billing.updateallowedexpense', $city->id) }}" method="POST">
        <!-- City Name -->
         @csrf
         @method('POST')
        <div class="mb-3">
          <label class="form-label fw-bold">City Name</label>
          <input type="text" class="form-control" value="{{ $city->name }}" disabled>
        </div>

        <!-- City Category -->
        <div class="mb-3">
          <label for="city_category" class="form-label fw-bold">City Category</label>
          <select id="city_category" name="city_category" class="form-select" required>
            <option value="" disabled {{ old('city_category', $city->category ?? '') === '' ? 'selected' : '' }}>
              Select City Category
            </option>
            <option value="2" {{ old('city_category', $city->category ?? '') == 2 ? 'selected' : '' }}>
              Metro City
            </option>
            <option value="1" {{ old('city_category', $city->category ?? '') == 1 ? 'selected' : '' }}>
              Non Metro City
            </option>
            <option value="0" {{ old('city_category', $city->category ?? '') == 0 ? 'selected' : '' }}>
              Tier 3 City
            </option>
          </select>
        </div>


        <!-- User Category -->
        <div class="mb-3">
          <label for="user_category" class="form-label fw-bold">User Category</label>
          <select id="user_category" name="user_category" class="form-select" required>
            <option value="" disabled {{ old('user_category', $usercategory->id ?? '') === '' ? 'selected' : '' }}>
              Select User Category
            </option>

            @foreach ($usercategories as $category)
              <option value="{{ $category->id }}"
                {{ old('user_category', $usercategory->id ?? '') == $category->id ? 'selected' : '' }}>
                {{ $category->category_code }}
              </option>
            @endforeach
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
            value="{{ old('hotel_bill', $city->room_max_price ?? '') }}"
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