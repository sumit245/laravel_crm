@extends('layouts.main')

@section('content')
<div class="content-wrapper p-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i> Edit Category</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('billing.updatecategory') }}" method="POST">
                @csrf
                @method('POST')
                <input type="hidden" name="category_id" value="{{ request()->route('id') }}">
                {{-- Category Dropdown --}}
                <div class="mb-3">
                <label for="category_code" class="form-label">Category</label>
                    <input type="text" name="category_code" id="category_code" class="form-control" value="{{ old('category_code', $uc->category_code) }}" readonly>
                </div>

                {{-- Allowed Vehicles Dropdown --}}
                <div class="mb-3">
                    <label for="vehicle_id" class="form-label">Allowed Vehicles</label>
                    <select name="vehicle_id[]" id="vehicle_id" class="form-select select2-multiple" multiple>
                        @foreach($uv as $v)
                            <option value="{{ $v->id }}" {{ isset($selectedVehicles) && in_array($v->id, $selectedVehicles) ? 'selected' : '' }}>
                                {{ $v->category }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">You can select multiple vehicles</small>
                </div>

                <!-- City Category -->
                @php
                    $cityLabels = [
                        0 => 'Tier-3',
                        1 => 'Non-Metro',
                        2 => 'Metro',
                    ];
                @endphp

                <div class="mb-3">
                    <label for="city_category" class="form-label">City Category</label>
                    <input 
                        type="text" 
                        name="city_category" 
                        id="city_category" 
                        class="form-control" 
                        value="{{ $cityLabels[$uc->city_category] ?? 'Unknown' }}" 
                        readonly
                    >
                </div>
                <!-- Daily Amount -->
                <div class="mb-3">
                    <label for="daily_amount" class="form-label">Daily Amount</label>
                    <input type="number" name="daily_amount" id="daily_amount" class="form-control" required
                        value="{{ old('dailyamount') ?? ($uc->dailyamount ?? '') }}">
                </div>
                <button type="submit" class="btn btn-primary">Update Category</button>
            </form>
        </div>
    </div>
</div>
@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2-multiple').select2({
            placeholder: "Select allowed vehicles",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
