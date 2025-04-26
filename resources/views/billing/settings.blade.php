@extends('layouts.main')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Vertical Tab Navigation -->
        <div class="col-md-3 col-lg-2 bg-light" style="min-height: calc(100vh - 60px);">
            <div class="settings-sidebar">
                <div class="p-3">
                    <h5 class="mb-0 fw-bold">Settings</h5>
                </div>
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <button class="nav-link active text-start py-3 px-4" id="v-pills-vehicle-tab" data-bs-toggle="pill" data-bs-target="#v-pills-vehicle" type="button" role="tab" aria-controls="v-pills-vehicle" aria-selected="true">
                        <i class="bi bi-car-front me-2"></i> Vehicle Settings
                    </button>
                    <button class="nav-link text-start py-3 px-4" id="v-pills-user-tab" data-bs-toggle="pill" data-bs-target="#v-pills-user" type="button" role="tab" aria-controls="v-pills-user" aria-selected="false">
                        <i class="bi bi-people me-2"></i> User Settings
                    </button>
                    <button class="nav-link text-start py-3 px-4" id="v-pills-category-tab" data-bs-toggle="pill" data-bs-target="#v-pills-category" type="button" role="tab" aria-controls="v-pills-category" aria-selected="false">
                        <i class="bi bi-tags me-2"></i> Category Settings
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab Content Area -->
        <div class="col-md-9 col-lg-10">
            <div class="tab-content p-3" id="v-pills-tabContent">
                
                <!-- Vehicle Settings Tab -->
                <div class="tab-pane fade show active" id="v-pills-vehicle" role="tabpanel" aria-labelledby="v-pills-vehicle-tab">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0"><i class="bi bi-car-front me-2"></i>Vehicle Settings</h4>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                            <i class="mdi mdi-plus-circle me-1"></i> Add Vehicle
                        </button>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="vehicleTable" class="table table-bordered table-striped table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Vehicle Name</th>
                                            <th>Category</th>
                                            <th>Sub Category</th>
                                            <th>Rate/KM</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($vehicles as $vehicle)
                                        <tr>
                                            <td>{{ $vehicle->id }}</td>
                                            <td>{{ $vehicle->vehicle_name ?? "N/A" }}</td>
                                            <td>{{ $vehicle->category ?? "N/A" }}</td>
                                            <td>{{ $vehicle->sub_category }}</td>
                                            <td>{{ $vehicle->rate }}</td>
                                            <td>
                                                <a href="{{ route('billing.editvehicle',  $vehicle->id) }}" class="btn btn-icon btn-warning" title="Edit Vehicle">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <form action="{{ route('billing.deletevehicle', $vehicle->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-icon btn-danger" title="Delete Vehicle"
                                                        onclick="return confirm('Are you sure you want to delete {{ $vehicle->vehicle_name }}?')">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- User Settings Tab -->
                <div class="tab-pane fade" id="v-pills-user" role="tabpanel" aria-labelledby="v-pills-user-tab">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0"><i class="bi bi-people me-2"></i>User Settings</h4>
                        <button class="btn btn-primary" id="assignCategoryBtn">
                            <i class="mdi mdi-tag-multiple me-1"></i> Assign Category
                        </button>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="userTable" class="table table-bordered table-striped table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th><input type="checkbox" id="selectAllUsers"></th>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Role</th>
                                            <th>Email</th>
                                            <th>Category</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                        <tr>
                                            <td><input type="checkbox" class="user-checkbox" data-id="1"></td>
                                            <td>{{ $user->id }}</td>
                                            <td>{{ $user->firstName }} {{ $user->lastName }}</td>
                                            <td>{{ $user->role }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->category }}</td>
                                            <td>
                                                <a href="{{ route('billing.edituser', $user->id) }}" class="btn btn-icon btn-primary" title="Edit Category">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Category Settings Tab -->
                <div class="tab-pane fade" id="v-pills-category" role="tabpanel" aria-labelledby="v-pills-category-tab">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0"><i class="bi bi-tags me-2"></i>Category Settings</h4>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="mdi mdi-plus-circle me-1"></i> Add Category
                        </button>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="categoryTable" class="table table-bordered table-striped table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Category Name</th>
                                            <th>Vehicles Allowed</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($categories as $cat)
                                        <tr>
                                            <td>{{ $cat->id }}</td>
                                            <td>{{ $cat->category_code }}</td>
                                            <td>{{ $cat->allowed_vehicles }}</td>
                                            <td>
                                                <a href="#" class="btn btn-icon btn-primary editVehicleBtn" data-bs-toggle="modal" data-bs-target="#editCategoryModal" title="Edit Category">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-icon btn-danger delete-category" title="Delete Category" data-id="1" data-name="Personal">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="addVehicleModalLabel"><i class="bi bi-plus-circle me-2"></i> Add New Vehicle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
            <form id="addVehicleForm" action="{{ route('billing.addvehicle') }}" method="POST">
    @csrf
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="vehicleName" class="form-label fw-bold">Vehicle Name</label>
                <input type="text" class="form-control" name="vehicle_name" id="vehicleName" placeholder="Enter vehicle name" required>
                <div class="invalid-feedback">Please enter a vehicle name.</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="ratePerKm" class="form-label fw-bold">Rate per KM (₹)</label>
                <input type="number" class="form-control" name="rate" id="ratePerKm" placeholder="Eg. 14" step="0.01" required>
                <div class="invalid-feedback">Please enter a valid rate.</div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="category" class="form-label fw-bold">Category</label>
                <select class="form-select" name="category" id="category" required>
                    <option value="" selected disabled>Select category</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->category }}">{{ $vehicle->category }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Please select a category.</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="subCategory" class="form-label fw-bold">Sub Category</label>
                <input type="text" class="form-control" name="sub_category" id="subCategory" placeholder="Optional">
                <div class="invalid-feedback">Please enter a sub category.</div>
            </div>
        </div>
    </div>
    <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary" id="saveVehicleBtn">
                    <i class="bi bi-save me-1"></i> Save Vehicle
                </button>
            </div>
</form>
            </div>
            
            </div>
        </div>
    </div>
</div>

<!-- Edit Vehicle Modal -->
<div class="modal fade" id="editVehicleModal" tabindex="-1" aria-labelledby="editVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="editVehicleModalLabel"><i class="bi bi-pencil-square me-2"></i> Edit Vehicle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editVehicleForm" action="{{ route('billing.updatevehicle') }}" method="POST">
                    @csrf
                    @method('POST')
                    
                    <!-- Dynamic values will be inserted here directly from data attributes -->
                    <input type="hidden" id="editVehicleId" name="vehicle_id" data-value-from="data-id">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editVehicleName" class="form-label fw-bold">Vehicle Name</label>
                                <input type="text" class="form-control" id="editVehicleName" name="vehicle_name" required data-value-from="data-name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editRatePerKm" class="form-label fw-bold">Rate per KM (₹)</label>
                                <input type="number" class="form-control" id="editRatePerKm" name="rate" step="0.01" required data-value-from="data-rate">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCategory" class="form-label fw-bold">Category</label>
                                <input type="text" class="form-control" id="editCategory" name="category" data-value-from="data-category">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editSubCategory" class="form-label fw-bold">Sub Category</label>
                                <input type="text" class="form-control" id="editSubCategory" name="sub_category" required data-value-from="data-sub-category">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="updateVehicleBtn">
                            <i class="bi bi-save me-1"></i> Update Vehicle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="addCategoryModalLabel"><i class="bi bi-plus-circle me-2"></i> Add New Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addCategoryForm">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label fw-bold">Category Name</label>
                        <input type="text" class="form-control" id="categoryName" placeholder="Enter category name" required>
                        <div class="invalid-feedback">Please enter a category name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="vehiclesAllowed" class="form-label fw-bold">Vehicles Allowed</label>
                        <select id="vehiclesAllowed" name="vehiclesAllowed[]" multiple="multiple" class="form-select" style="width: 100%;" required>
                            <option value="Two Wheelers">Two Wheelers</option>
                            <option value="Four Wheelers">Four Wheelers</option>
                            <option value="Public Transport">Public Transport</option>
                        </select>
                        <div class="invalid-feedback">Please select at least one vehicle.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveCategoryBtn">
                    <i class="bi bi-save me-1"></i> Save Category
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="editCategoryModalLabel"><i class="bi bi-pencil-square me-2"></i> Edit Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editCategoryForm">
                    <div class="mb-3">
                        <label for="editCategoryName" class="form-label fw-bold">Category Name</label>
                        <input type="text" class="form-control" id="editCategoryName" value="Personal" required>
                        <div class="invalid-feedback">Please enter a category name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="editVehiclesAllowed" class="form-label fw-bold">Vehicles Allowed</label>
                        <select id="editVehiclesAllowed" name="editVehiclesAllowed[]" multiple="multiple" class="form-select" style="width: 100%;" required>
                            <option value="Two Wheelers" selected>Two Wheelers</option>
                            <option value="Four Wheelers" selected>Four Wheelers</option>
                            <option value="Public Transport">Public Transport</option>
                        </select>
                        <div class="invalid-feedback">Please select at least one vehicle.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="updateCategoryBtn">
                    <i class="bi bi-save me-1"></i> Update Category
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Category Modal -->
<div class="modal fade" id="editUserCategoryModal" tabindex="-1" aria-labelledby="editUserCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="editUserCategoryModalLabel"><i class="bi bi-pencil-square me-2"></i> Edit User Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editUserCategoryForm">
                    <div class="mb-3">
                        <label for="userName" class="form-label fw-bold">User Name</label>
                        <input type="text" class="form-control" id="userName" value="John Doe" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="userCategory" class="form-label fw-bold">Category</label>
                        <select class="form-select" id="userCategory" required>
                            <option value="" disabled>Select category</option>
                            <option value="Field Staff" selected>{{--  --}}</option>
                            <option value="Management">Management</option>
                            <option value="Store Staff">Store Staff</option>
                            <option value="Admin">Admin</option>
                        </select>
                        <div class="invalid-feedback">Please select a category.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="updateUserCategoryBtn">
                    <i class="bi bi-save me-1"></i> Update Category
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Category Modal -->
<div class="modal fade" id="assignCategoryModal" tabindex="-1" aria-labelledby="assignCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="assignCategoryModalLabel"><i class="bi bi-tag-multiple me-2"></i> Assign Category to Users</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="assignCategoryForm">
                    <div class="mb-3">
                        <label for="bulkUserCategory" class="form-label fw-bold">Select Category</label>
                        <select class="form-select" id="bulkUserCategory" required>
                            <option value="" selected disabled>Select category</option>
                            <option value="Field Staff">Field Staff</option>
                            <option value="Management">Management</option>
                            <option value="Store Staff">Store Staff</option>
                            <option value="Admin">Admin</option>
                        </select>
                        <div class="invalid-feedback">Please select a category.</div>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i> This will assign the selected category to all checked users.
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveBulkCategoryBtn">
                    <i class="bi bi-save me-1"></i> Assign Category
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="deleteConfirmModalLabel"><i class="bi bi-exclamation-triangle me-2"></i> Delete Confirmation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p id="deleteConfirmText">Are you sure you want to delete this item? This action cannot be undone.</p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>

    

    $(document).ready(function() {
        // Initialize Select2 for vehicles allowed dropdowns
        $('#vehiclesAllowed').select2({
            placeholder: "Select vehicles",
            allowClear: true,
            dropdownParent: $('#addCategoryModal')
        });

        $('#editVehiclesAllowed').select2({
            placeholder: "Select vehicles",
            allowClear: true,
            dropdownParent: $('#editCategoryModal')
        });

        // Initialize DataTables
        $('#vehicleTable').DataTable({
            dom: "<'row'<'col-sm-12'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row my-4'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [{
                    extend: 'excel',
                    text: '<i class="mdi mdi-file-excel text-light"></i>',
                    className: 'btn btn-icon btn-dark',
                    titleAttr: 'Export to Excel'
                },
                {
                    extend: 'pdf',
                    text: '<i class="mdi mdi-file-pdf"></i>',
                    className: 'btn btn-icon btn-danger',
                    titleAttr: 'Export to PDF'
                },
                {
                    extend: 'print',
                    text: '<i class="mdi mdi-printer"></i>',
                    className: 'btn btn-icon btn-info',
                    titleAttr: 'Print Table'
                }
            ],
            paging: true,
            pageLength: 50,
            searching: true,
            ordering: true,
            responsive: true,
            language: {
                search: '',
                searchPlaceholder: 'Search Vehicles'
            }
        });

        $('#userTable').DataTable({
            dom: "<'row'<'col-sm-12'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row my-4'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [{
                    extend: 'excel',
                    text: '<i class="mdi mdi-file-excel text-light"></i>',
                    className: 'btn btn-icon btn-dark',
                    titleAttr: 'Export to Excel'
                },
                {
                    extend: 'pdf',
                    text: '<i class="mdi mdi-file-pdf"></i>',
                    className: 'btn btn-icon btn-danger',
                    titleAttr: 'Export to PDF'
                },
                {
                    extend: 'print',
                    text: '<i class="mdi mdi-printer"></i>',
                    className: 'btn btn-icon btn-info',
                    titleAttr: 'Print Table'
                }
            ],
            paging: true,
            pageLength: 50,
            searching: true,
            ordering: true,
            responsive: true,
            language: {
                search: '',
                searchPlaceholder: 'Search Users'
            }
        });

        $('#categoryTable').DataTable({
            dom: "<'row'<'col-sm-12'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row my-4'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [{
                    extend: 'excel',
                    text: '<i class="mdi mdi-file-excel text-light"></i>',
                    className: 'btn btn-icon btn-dark',
                    titleAttr: 'Export to Excel'
                },
                {
                    extend: 'pdf',
                    text: '<i class="mdi mdi-file-pdf"></i>',
                    className: 'btn btn-icon btn-danger',
                    titleAttr: 'Export to PDF'
                },
                {
                    extend: 'print',
                    text: '<i class="mdi mdi-printer"></i>',
                    className: 'btn btn-icon btn-info',
                    titleAttr: 'Print Table'
                }
            ],
            paging: true,
            pageLength: 50,
            searching: true,
            ordering: true,
            responsive: true,
            language: {
                search: '',
                searchPlaceholder: 'Search Categories'
            }
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
            if (formId === 'addCategoryForm' || formId === 'editCategoryForm') {
                const selectId = formId === 'addCategoryForm' ? 'vehiclesAllowed' : 'editVehiclesAllowed';
                if ($('#' + selectId).val() === null || $('#' + selectId).val().length === 0) {
                    $('#' + selectId).next('.select2-container').css('border', '1px solid #dc3545');
                    isValid = false;
                } else {
                    $('#' + selectId).next('.select2-container').css('border', '');
                }
            }
            
            return isValid;
        }

        // Select all users checkbox
        $('#selectAllUsers').on('click', function() {
            $('.user-checkbox').prop('checked', $(this).prop('checked'));
        });

        // Individual user checkbox change
        $('.user-checkbox').on('change', function() {
            if ($('.user-checkbox:checked').length === $('.user-checkbox').length) {
                $('#selectAllUsers').prop('checked', true);
            } else {
                $('#selectAllUsers').prop('checked', false);
            }
        });

        // Assign Category button click - Check if users are selected before opening modal
        $('#assignCategoryBtn').on('click', function() {
            if ($('.user-checkbox:checked').length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Users Selected',
                    text: 'Please select at least one user to assign a category.',
                    confirmButtonColor: '#0d6efd'
                });
                return false;
            } else {
                $('#assignCategoryModal').modal('show');
            }
        });

        // Save bulk category assignment
        $('#saveBulkCategoryBtn').on('click', function() {
            if (!validateForm('assignCategoryForm')) {
                return;
            }
            
            let selectedCategory = $('#bulkUserCategory').val();
            let selectedUsers = [];
            $('.user-checkbox:checked').each(function() {
                selectedUsers.push($(this).data('id'));
            });

            // Here you would typically make an AJAX call to update the users
            console.log('Assigning category:', selectedCategory, 'to users:', selectedUsers);

            $('#assignCategoryModal').modal('hide');
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Category has been assigned to selected users.',
                confirmButtonColor: '#0d6efd'
            });
        });

        // Delete vehicle button click
        $('.delete-vehicle').on('click', function() {
            let vehicleId = $(this).data('id');
            let vehicleName = $(this).data('name');
            
            $('#deleteConfirmText').text(Are you sure you want to delete the vehicle "${vehicleName}"? This action cannot be undone.);
            $('#deleteConfirmModal').modal('show');
            
            $('#confirmDeleteBtn').off('click').on('click', function() {
                // Add your delete logic here
                $(button[data-id="${vehicleId}"].delete-vehicle).closest('tr').remove();
                $('#deleteConfirmModal').modal('hide');
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: Vehicle "${vehicleName}" has been deleted.,
                    confirmButtonColor: '#0d6efd'
                });
            });
        });

        // Delete category button click
        $('.delete-category').on('click', function() {
            let categoryId = $(this).data('id');
            let categoryName = $(this).data('name');
            
            $('#deleteConfirmText').text(Are you sure you want to delete the category "${categoryName}"? This action cannot be undone.);
            $('#deleteConfirmModal').modal('show');
            
            $('#confirmDeleteBtn').off('click').on('click', function() {
                // Add your delete logic here
                $(button[data-id="${categoryId}"].delete-category).closest('tr').remove();
                $('#deleteConfirmModal').modal('hide');
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: Category "${categoryName}" has been deleted.,
                    confirmButtonColor: '#0d6efd'
                });
            });
        });

        // Save vehicle button
        $('#saveVehicleBtn').on('click', function() {
            if (!validateForm('addVehicleForm')) {
                return;
            }
            
            // Add your save logic here
            $('#addVehicleModal').modal('hide');
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Vehicle has been added successfully.',
                confirmButtonColor: '#0d6efd'
            });
        });

        // Update vehicle button
        $('#updateVehicleBtn').on('click', function() {
            if (!validateForm('editVehicleForm')) {
                return;
            }
            
            // Add your update logic here
            $('#editVehicleModal').modal('hide');
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Vehicle has been updated successfully.',
                confirmButtonColor: '#0d6efd'
            });
        });

        // Save category button
        $('#saveCategoryBtn').on('click', function() {
            if (!validateForm('addCategoryForm')) {
                return;
            }
            
            // Add your save logic here
            $('#addCategoryModal').modal('hide');
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Category has been added successfully.',
                confirmButtonColor: '#0d6efd'
            });
        });

        // Update category button
        $('#updateCategoryBtn').on('click', function() {
            if (!validateForm('editCategoryForm')) {
                return;
            }
            
            // Add your update logic here
            $('#editCategoryModal').modal('hide');
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Category has been updated successfully.',
                confirmButtonColor: '#0d6efd'
            });
        });

        // Update user category button
        $('#updateUserCategoryBtn').on('click', function() {
            if (!validateForm('editUserCategoryForm')) {
                return;
            }
            
            // Add your update logic here
            $('#editUserCategoryModal').modal('hide');
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'User category has been updated successfully.',
                confirmButtonColor: '#0d6efd'
            });
        });

        // Clear validation on input change
        $('input, select').on('change', function() {
            $(this).removeClass('is-invalid');
        });

        // Clear select2 validation on change
        $('#vehiclesAllowed, #editVehiclesAllowed').on('change', function() {
            $(this).next('.select2-container').css('border', '');
        });
    });
</script>
@endpush

@push('styles')
<style>
    /* Settings sidebar styles */
    .settings-sidebar {
        height: 100%;
        background: #ffffff;
    }

    .settings-sidebar .nav-link {
        color: #495057;
        border-radius: 0;
        position: relative;
    }

    .settings-sidebar .nav-link.active {
        color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.1);
        font-weight: 500;
    }

    .settings-sidebar .nav-link:hover:not(.active) {
        background-color: rgba(0, 0, 0, 0.05);
    }

   

    /* Table styles */
    .table-responsive {
        overflow-x: auto;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
    }

    .table-responsive::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.25rem;
    }

    /* Form styles */
    .form-label {
        font-size: 0.875rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    /* Tab content area min-height */
   
    #v-pills-tabContent {
        min-height: 500px;
        border:0px;
    }
    .nav-pills {
    border-bottom: 0;

}
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

    .select2-container--default .select2-selection--multiple .select2-selection_choice_remove {
        color: white;
        margin-right: 5px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection_choice_remove:hover {
        color: #f8f9fa;
    }
</style>
@endpush