@extends('layouts.main')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Vertical Tab Navigation -->
        <div class="col-md-3 col-lg-2 bg-light" style="min-height: calc(100vh - 60px);">
            <div class="settings-sidebar">
                
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical" style="border-bottom: 0px;">
                <div class="p-3">
                    <h5 class="mb-0 fw-bold">Settings</h5>
                </div>
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
                            <div class="mb-3">
                                <div class="dataTables_filter">
                                    <label>
                                        <input type="search" class="form-control form-control-sm" placeholder="Search Vehicles" aria-controls="vehicleTable">
                                    </label>
                                </div>
                            </div>
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
                                        <tr>
                                            <td>1</td>
                                            <td>Two Wheelers</td>
                                            <td>Personal</td>
                                            <td>Petrol</td>
                                            <td>₹5.00</td>
                                            <td>
                                                <a href="#" class="btn btn-icon btn-warning" data-bs-toggle="modal" data-bs-target="#editVehicleModal" title="Edit Vehicle">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-icon btn-danger delete-vehicle" title="Delete Vehicle" data-id="1" data-name="Two Wheelers">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Four Wheelers</td>
                                            <td>Taxi</td>
                                            <td>Diesel</td>
                                            <td>₹12.00</td>
                                            <td>
                                                <a href="#" class="btn btn-icon btn-warning" data-bs-toggle="modal" data-bs-target="#editVehicleModal" title="Edit Vehicle">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-icon btn-danger delete-vehicle" title="Delete Vehicle" data-id="2" data-name="Four Wheelers">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>Public Transport</td>
                                            <td>Bus</td>
                                            <td>CNG</td>
                                            <td>₹8.50</td>
                                            <td>
                                                <a href="#" class="btn btn-icon btn-warning" data-bs-toggle="modal" data-bs-target="#editVehicleModal" title="Edit Vehicle">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-icon btn-danger delete-vehicle" title="Delete Vehicle" data-id="3" data-name="Public Transport">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </td>
                                        </tr>
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
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="dataTables_filter">
                                    <label>
                                        <input type="search" class="form-control form-control-sm" placeholder="Search Users" aria-controls="userTable">
                                    </label>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="userTable" class="table table-bordered table-striped table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Role</th>
                                            <th>Phone</th>
                                            <th>Category</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>John Doe</td>
                                            <td>Site Engineer</td>
                                            <td>9876543210</td>
                                            <td>Field Staff</td>
                                            <td>
                                                <a href="#" class="btn btn-icon btn-primary" data-bs-toggle="modal" data-bs-target="#editUserCategoryModal" title="Edit Category">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Jane Smith</td>
                                            <td>Project Manager</td>
                                            <td>8765432109</td>
                                            <td>Management</td>
                                            <td>
                                                <a href="#" class="btn btn-icon btn-primary" data-bs-toggle="modal" data-bs-target="#editUserCategoryModal" title="Edit Category">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>Robert Johnson</td>
                                            <td>Store Incharge</td>
                                            <td>7654321098</td>
                                            <td>Store Staff</td>
                                            <td>
                                                <a href="#" class="btn btn-icon btn-primary" data-bs-toggle="modal" data-bs-target="#editUserCategoryModal" title="Edit Category">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
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
                            <div class="mb-3">
                                <div class="dataTables_filter">
                                    <label>
                                        <input type="search" class="form-control form-control-sm" placeholder="Search Categories" aria-controls="categoryTable">
                                    </label>
                                </div>
                            </div>
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
                                        <tr>
                                            <td>1</td>
                                            <td>Personal</td>
                                            <td>Two Wheelers, Four Wheelers</td>
                                            <td>
                                                <a href="#" class="btn btn-icon btn-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal" title="Edit Category">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-icon btn-danger delete-category" title="Delete Category" data-id="1" data-name="Personal">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Taxi</td>
                                            <td>Four Wheelers</td>
                                            <td>
                                                <a href="#" class="btn btn-icon btn-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal" title="Edit Category">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-icon btn-danger delete-category" title="Delete Category" data-id="2" data-name="Taxi">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>Bus</td>
                                            <td>Public Transport</td>
                                            <td>
                                                <a href="#" class="btn btn-icon btn-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal" title="Edit Category">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-icon btn-danger delete-category" title="Delete Category" data-id="3" data-name="Bus">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </td>
                                        </tr>
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
                <form id="addVehicleForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vehicleName" class="form-label fw-bold">Vehicle Name</label>
                                <select class="form-select" id="vehicleName" required>
                                    <option value="" selected disabled>Select vehicle type</option>
                                    <option value="Two Wheelers">Two Wheelers</option>
                                    <option value="Four Wheelers">Four Wheelers</option>
                                    <option value="Public Transport">Public Transport</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ratePerKm" class="form-label fw-bold">Rate per KM (₹)</label>
                                <input type="number" class="form-control" id="ratePerKm" placeholder="Enter rate per km" step="0.01" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label fw-bold">Category</label>
                                <select class="form-select" id="category" required>
                                    <option value="" selected disabled>Select category</option>
                                    <option value="Personal">Personal</option>
                                    <option value="Taxi">Taxi</option>
                                    <option value="Bus">Bus</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="subCategory" class="form-label fw-bold">Sub Category</label>
                                <select class="form-select" id="subCategory" required>
                                    <option value="" selected disabled>Select sub category</option>
                                    <option value="Petrol">Petrol</option>
                                    <option value="Diesel">Diesel</option>
                                    <option value="Electric">Electric</option>
                                    <option value="CNG">CNG</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveVehicleBtn">
                    <i class="bi bi-save me-1"></i> Save Vehicle
                </button>
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
                <form id="editVehicleForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editVehicleName" class="form-label fw-bold">Vehicle Name</label>
                                <select class="form-select" id="editVehicleName" required>
                                    <option value="Two Wheelers" selected>Two Wheelers</option>
                                    <option value="Four Wheelers">Four Wheelers</option>
                                    <option value="Public Transport">Public Transport</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editRatePerKm" class="form-label fw-bold">Rate per KM (₹)</label>
                                <input type="number" class="form-control" id="editRatePerKm" value="5.00" step="0.01" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCategory" class="form-label fw-bold">Category</label>
                                <select class="form-select" id="editCategory" required>
                                    <option value="Personal" selected>Personal</option>
                                    <option value="Taxi">Taxi</option>
                                    <option value="Bus">Bus</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editSubCategory" class="form-label fw-bold">Sub Category</label>
                                <select class="form-select" id="editSubCategory" required>
                                    <option value="Petrol" selected>Petrol</option>
                                    <option value="Diesel">Diesel</option>
                                    <option value="Electric">Electric</option>
                                    <option value="CNG">CNG</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="updateVehicleBtn">
                    <i class="bi bi-save me-1"></i> Update Vehicle
                </button>
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
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Vehicles Allowed</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="vehicleTwoWheelers" name="vehiclesAllowed[]" value="Two Wheelers">
                            <label class="form-check-label" for="vehicleTwoWheelers">
                                Two Wheelers
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="vehicleFourWheelers" name="vehiclesAllowed[]" value="Four Wheelers">
                            <label class="form-check-label" for="vehicleFourWheelers">
                                Four Wheelers
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="vehiclePublicTransport" name="vehiclesAllowed[]" value="Public Transport">
                            <label class="form-check-label" for="vehiclePublicTransport">
                                Public Transport
                            </label>
                        </div>
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
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Vehicles Allowed</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="editVehicleTwoWheelers" name="editVehiclesAllowed[]" value="Two Wheelers" checked>
                            <label class="form-check-label" for="editVehicleTwoWheelers">
                                Two Wheelers
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="editVehicleFourWheelers" name="editVehiclesAllowed[]" value="Four Wheelers" checked>
                            <label class="form-check-label" for="editVehicleFourWheelers">
                                Four Wheelers
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="editVehiclePublicTransport" name="editVehiclesAllowed[]" value="Public Transport">
                            <label class="form-check-label" for="editVehiclePublicTransport">
                                Public Transport
                            </label>
                        </div>
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
                            <option value="Field Staff" selected>Field Staff</option>
                            <option value="Management">Management</option>
                            <option value="Store Staff">Store Staff</option>
                            <option value="Admin">Admin</option>
                        </select>
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

        $('.dataTables_filter').addClass('d-none');
        $('[data-toggle="tooltip"]').tooltip();

        $('.delete-vehicle').on('click', function() {
            let vehicleId = $(this).data('id');
            let vehicleName = $(this).data('name');
            
            $('#deleteConfirmText').text(Are you sure you want to delete the vehicle "${vehicleName}"? This action cannot be undone.);
            $('#deleteConfirmModal').modal('show');
            
            $('#confirmDeleteBtn').off('click').on('click', function() {

                $(button[data-id="${vehicleId}"].delete-vehicle).closest('tr').remove();
                $('#deleteConfirmModal').modal('hide');
            
                Swal.fire(
                    'Deleted!',
                    Vehicle "${vehicleName}" has been deleted.,
                    'success'
                );
            });
        });

  
        $('.delete-category').on('click', function() {
            let categoryId = $(this).data('id');
            let categoryName = $(this).data('name');
            
            $('#deleteConfirmText').text(Are you sure you want to delete the category "${categoryName}"? This action cannot be undone.);
            $('#deleteConfirmModal').modal('show');
            
            $('#confirmDeleteBtn').off('click').on('click', function() {
          
                $(button[data-id="${categoryId}"].delete-category).closest('tr').remove();
                $('#deleteConfirmModal').modal('hide');
                
        
                Swal.fire(
                    'Deleted!',
                    Category "${categoryName}" has been deleted.,
                    'success'
                );
            });
        });

        $('#saveVehicleBtn').on('click', function() {
            $('#addVehicleModal').modal('hide');
            
            Swal.fire(
                'Success!',
                'Vehicle has been added successfully.',
                'success'
            );
        });

        $('#updateVehicleBtn').on('click', function() {
          
            $('#editVehicleModal').modal('hide');
        
            Swal.fire(
                'Success!',
                'Vehicle has been updated successfully.',
                'success'
            );
        });

        $('#saveCategoryBtn').on('click', function() {

            $('#addCategoryModal').modal('hide');
            

            Swal.fire(
                'Success!',
                'Category has been added successfully.',
                'success'
            );
        });


        $('#updateCategoryBtn').on('click', function() {
 
            $('#editCategoryModal').modal('hide');
            
            Swal.fire(
                'Success!',
                'Category has been updated successfully.',
                'success'
            );
        });

        $('#updateUserCategoryBtn').on('click', function() {
            
            $('#editUserCategoryModal').modal('hide');
            
           
            Swal.fire(
                'Success!',
                'User category has been updated successfully.',
                'success'
            );
        });
    });
</script>
@endpush

@push('styles')
<style>
    
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

   
    .table-responsive {
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none; 
    }

    .table-responsive::-webkit-scrollbar {
        display: none; 
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

   
    .form-label {
        font-size: 0.875rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    #v-pills-tabContent {
        min-height: 500px;
        border:0px;
    }

    .dataTables_filter {
        margin-bottom: 15px;
        text-align: left;
    }

    .dataTables_filter input {
        width: 300px;
        border-radius: 4px;
        padding: 6px 12px;
    }
    .form-check .form-check-input {
        margin-left: 0;
    }
</style>
@endpush