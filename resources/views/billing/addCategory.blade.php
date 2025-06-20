@extends('layouts.main')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-plus-circle me-2"></i> Add New Category</h5>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong><i class="bi bi-exclamation-triangle me-2"></i> There were some problems with your input:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form id="addCategoryForm" action="{{ route('billing.addcategory') }}" method="POST">
                        @csrf
                        @method('POST')
                        
                        <div class="mb-3">
                            <label for="categoryName" class="form-label fw-bold">Category Name</label>
                            <input class="form-control" id="categoryName" name="category" required>
                        </div>
                        
                        <!-- New Name Field -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <!-- New Description Field -->
                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <!-- Vehicles Allowed Field -->
                        <div class="mb-3">
                            <label for="vehiclesAllowed" class="form-label fw-bold">Allowed Vehicles</label>
                            <select class="form-select" id="vehiclesAllowed" name="vehicle_id[]" multiple="multiple" required>
                                @foreach ($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id  }}">{{ $vehicle->category ?? "N/A" ?? $vehicle->id ?? "N/A" }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- City Category -->
                         <div class="mb-3">
                        <label for="city_category" class="form-label fw-bold">City Category</label>
                        <select class="form-select" id="city_category" name="city_category" required>
                            <option value="2">Metro</option>
                            <option value="1">Non-Metro</option>
                            <option value="0">Tier-3</option>
                        </select>
                        </div>

                        <!-- Daily Amount -->
                         <div class="mb-3">
                            <label for="daily_amount" class="form-label fw-bold">Daily Amount</label>
                            <input type="number" class="form-control" id="daily_amount" name="daily_amount" required>
                        </div>


                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2" id="saveCategoryBtn">
                                <i class="bi bi-save me-1"></i> Save Category
                            </button>
                            <a href="{{ route('billing.settings') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 for vehicles allowed dropdown
        $('#vehiclesAllowed').select2({
            placeholder: "Select vehicles",
            allowClear: true,
            width: '100%'
        });

        // Form validation function
        function validateForm(formId) {
            const form = document.getElementById(formId);
            let isValid = true;
            
            // Check all required fields
            $(form).find('[required]').each(function() {
                if ($(this).val() === '' || $(this).val() === null) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            
            // Special check for select2 fields
            if ($('#vehiclesAllowed').val() === null || $('#vehiclesAllowed').val().length === 0) {
                $('#vehiclesAllowed').next('.select2-container').css('border', '1px solid #dc3545');
                isValid = false;
            } else {
                $('#vehiclesAllowed').next('.select2-container').css('border', '');
            }
            
            // Validate min price is less than max price if both are provided
            const minPrice = parseFloat($('#roomMinPrice').val());
            const maxPrice = parseFloat($('#roomMaxPrice').val());
            
            if (!isNaN(minPrice) && !isNaN(maxPrice) && minPrice > maxPrice) {
                $('#roomMinPrice').addClass('is-invalid');
                $('#roomMaxPrice').addClass('is-invalid');
                isValid = false;
            }
            
            return isValid;
        }

        // Clear validation on input change
        $('input, select').on('change', function() {
            $(this).removeClass('is-invalid');
        });

        // Clear select2 validation on change
        $('#vehiclesAllowed').on('change', function() {
            $(this).next('.select2-container').css('border', '');
        });

        // Form submission
        $('#addCategoryForm').on('submit', function(e) {
            if (!validateForm('addCategoryForm')) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    /* Select2 styling */
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        min-height: 38px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #0d6efd;
        border: 1px solid #0d6efd;
        color: white;
        border-radius: 0.2rem;
        padding: 2px 8px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
        margin-right: 5px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #f8f9fa;
    }
</style>
@endpush
