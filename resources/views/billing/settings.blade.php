@extends("layouts.main")
@section("content")
  <div class="container-fluid p-0">
    <div class="row g-0">
      <!-- Vertical Tab Navigation -->
      <div class="col-md-3 col-lg-2 bg-light" style="min-height: calc(100vh - 60px);">
        <div class="settings-sidebar">
          <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
            <div class="p-3">
              <h5 class="fw-bold mb-0">Settings</h5>
            </div>
            <button class="nav-link active px-4 py-3 text-start" id="v-pills-vehicle-tab" data-bs-toggle="pill"
              data-bs-target="#v-pills-vehicle" type="button" role="tab" aria-controls="v-pills-vehicle"
              aria-selected="true">
              <i class="bi bi-car-front me-2"></i> Vehicle Settings
            </button>
            <button class="nav-link px-4 py-3 text-start" id="v-pills-user-tab" data-bs-toggle="pill"
              data-bs-target="#v-pills-user" type="button" role="tab" aria-controls="v-pills-user"
              aria-selected="false">
              <i class="bi bi-people me-2"></i> User Settings
            </button>
            <button class="nav-link px-4 py-3 text-start" id="v-pills-category-tab" data-bs-toggle="pill"
              data-bs-target="#v-pills-category" type="button" role="tab" aria-controls="v-pills-category"
              aria-selected="false">
              <i class="bi bi-tags me-2"></i> Category Settings
            </button>
            <button class="nav-link px-4 py-3 text-start" id="v-pills-allowed-expense-tab" data-bs-toggle="pill"
             data-bs-target="#v-pills-allowed-expense" type="button" role="tab" aria-controls="v-pills-allowed-expense"
                aria-selected="false">
             <i class="bi bi-cash-stack me-2"></i> City Category
            </button>
          </div>
        </div>
      </div>
      <!-- Tab Content Area -->
      <div class="col-md-9 col-lg-10">
        <div class="tab-content m-3 p-3" id="v-pills-tabContent">
          <!-- Vehicle Settings Tab -->
          <div class="tab-pane fade show active" id="v-pills-vehicle" role="tabpanel"
            aria-labelledby="v-pills-vehicle-tab">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h4 class="mb-0"><i class="bi bi-car-front me-2"></i>Vehicle Settings</h4>
              <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                <i class="mdi mdi-plus-circle me-1"></i> Add Vehicle
              </button>
            </div>
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="table-responsive">
                  <x-data-table id="vehicleTable" class="table table-bordered table-striped table-sm display nowrap" style="width:100%">
  <x-slot:thead class="table-light">
    <tr>
      <th>Vehicle Name</th>
      <th>Category</th>
      <th>Sub Category</th>
      <th>Rate/KM</th>
      <th>Actions</th>
    </tr>
  </x-slot:thead>
  <x-slot:tbody>
    @foreach ($vehicles as $vehicle)
      <tr>
        <td>{{ $vehicle->vehicle_name ?? "N/A" }}</td>
        <td>{{ $vehicle->category ?? "N/A" }}</td>
        <td>{{ $vehicle->sub_category }}</td>
        <td>{{ $vehicle->rate }}</td>
        <td>
          <a href="{{ route('billing.editvehicle', $vehicle->id) }}" class="btn btn-icon btn-warning" title="Edit Vehicle">
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
  </x-slot:tbody>
</x-data-table>
                </div>
              </div>
            </div>
          </div>
          <!-- User Settings Tab -->
          <div class="tab-pane fade" id="v-pills-user" role="tabpanel" aria-labelledby="v-pills-user-tab">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h4 class="mb-0"><i class="bi bi-people me-2"></i>User Settings</h4>
              <!-- <button class="btn btn-primary" id="assignCategoryBtn">
                                  <i class="mdi mdi-tag-multiple me-1"></i> Assign Category
                              </button> -->
            </div>
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="table-responsive">
                  <table id="userTable" class="table-bordered table-striped table-sm table">
                    <thead class="table-light">
                      <tr>
                        <th><input type="checkbox" id="selectAllUsers"></th>
                        <!-- <th>#</th> -->
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
                          <td><input type="checkbox" class="user-checkbox" data-id="{{ $user->id }}"></td>
                          <!-- <td>{{ $user->id ?? 0 }}</td> -->
                          <td>{{ $user->firstName ?? "N/A" }} {{ $user->lastName ?? "N/A" }}</td>
                          <td>{{ $user->role ?? "N/A" }}</td>
                          <td>{{ $user->email ?? "N/A" }}</td>
                          <td>{{ $user->usercategory->category_code ?? "N/A" }}</td>
                          <td>
                            <a href="{{ route("billing.edituser", $user->id) }}" class="btn btn-icon btn-primary"
                              title="Edit Category">
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
              <a href="{{ route("billing.addcategory") }}" class="btn btn-primary">
                <i class="mdi mdi-plus-circle me-1"></i> Add Category
              </a>
            </div>
            <div class="card shadow-sm">
              <div class="card-body">
                @if (request('tab') === 'category' && session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                <div class="table-responsive">
                  <x-data-table id="categoryTable" class="table-bordered table-striped table-sm table">
                    <x-slot:thead class="table-light">
                      <tr>
                        <!-- <th>#</th> -->
                        <th>User Category</th>
                        <th>Vehicles Allowed</th>
                        <th>City Category</th>
                        <th>Daily Amount</th>
                        <th>Actions</th>
                      </tr>
                    </x-slot:thead>
                    <x-slot:tbody>
                      @foreach ($categories as $cat)
                        <tr>
                          <!-- <td>{{ $cat->id }}</td> -->
                          <td>{{ $cat->category_code }}</td>
                          <!-- <td>{{ $cat->allowed_vehicles }}</td> -->
                          <td>
                            @php
                              $vehicleIds = json_decode($cat->allowed_vehicles, true);
                              $vehicleList = [];
                              if (is_array($vehicleIds)) {
                                  foreach ($vehicleIds as $id) {
                                      // Try to find the vehicle by ID and get its category
                                      $vehicle = $vehicles->firstWhere("id", $id);
                                      $vehicleList[] = $vehicle ? $vehicle->category ?? $vehicle->id : $id;
                                  }
                                  echo implode(", ", $vehicleList);
                              } else {
                                  echo $cat->allowed_vehicles;
                              }
                            @endphp
                          </td>
                          <td>{{ $cat->city_category ?? 'Define' }}</td>
                          <td>{{ $cat->dailyamount ?? "null" }}</td>
                          <td>
                            <a href="{{ route("billing.editcategory", $cat->id) }}" class="btn btn-icon btn-warning">
                              <i class="mdi mdi-pencil"></i>
                            </a>
                            <form action="{{ route("billing.deletecategory", $cat->id) }}" method="POST"
                              style="display:inline;">
                              @csrf
                              @method("DELETE")
                              <button type="submit" class="btn btn-icon btn-danger" title="Delete Category"
                                onclick="return confirm('Are you sure you want to delete {{ $cat->category_code }}?')">
                                <i class="mdi mdi-delete"></i>
                              </button>
                            </form>
                          </td>
                        </tr>
                      @endforeach
                    </x-slot:tbody>
                  </x-data-table>
                </div>
              </div>
            </div>
          </div>
          <!-- Allowed Expenses Settings Tab -->
          <div class="tab-pane fade" id="v-pills-allowed-expense" role="tabpanel" aria-labelledby="v-pills-allowed-expense-tab">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h4 class="mb-0"><i class="bi bi-cash-stack me-2"></i>City Category</h4>
            </div>
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="table-responsive">
                  <x-data-table id="allowedExpenseTable" class="table table-bordered table-striped table-sm">
                    <x-slot:thead class="table-light">
                      <tr>
                        <th>
                          <input type="checkbox" id="selectAllExpense" />
                        </th>
                        <th>City Name</th>
                        <th>City Category</th>
                        <th>Actions</th>
                      </tr>
                    </x-slot:thead>
                    <x-slot:tbody>
                      <!-- Example Row -->
                      @foreach($cities as $city)
                      <tr>
                        <td>
                          <input type="checkbox" class="expenseCheckbox" data-id="{{ $city->id }}" />
                        </td>
                        <td>{{ $city->name }}</td>
                        <td>{{ $city->category }}</td>
                        <td>
                          <a href="{{ route('billing.allowedexpense', $city->id) }}" class="btn btn-icon btn-warning">
                              <i class="mdi mdi-pencil"></i>
                          </a>
                        </td>
                      </tr>
                      @endforeach
                      <!-- Add more rows as needed -->
                    </x-slot:tbody>
                  </x-data-table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Add Vehicle Modal -->
  <div class="modal fade" id="addVehicleModal" tabindex="-1" aria-labelledby="addVehicleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content shadow">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold" id="addVehicleModalLabel"><i class="bi bi-plus-circle me-2"></i> Add New
            Vehicle</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addVehicleForm" action="{{ route("billing.addvehicle") }}" method="POST">
            @csrf
            <div class="row mb-3">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="vehicleName" class="form-label fw-bold">Vehicle Name</label>
                  <input type="text" class="form-control" name="vehicle_name" id="vehicleName"
                    placeholder="Enter vehicle name" required>
                  <div class="invalid-feedback">Please enter a vehicle name.</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="ratePerKm" class="form-label fw-bold">Rate per KM (₹)</label>
                  <input type="number" class="form-control" name="rate" id="ratePerKm" placeholder="Eg. 14"
                    step="0.01" required>
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
                    <option value="Bike">Bike</option>
                    <option value="Car">Car</option>
                    <option value="Public Transport">Public Transport</option>
                  </select>
                  <div class="invalid-feedback">Please select a category.</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="sub_category" class="form-label fw-bold">Sub Category</label>
                  <input type="text" class="form-control" name="sub_category" id="subCategory" placeholder="motorcycle">
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
    <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content shadow">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title fw-bold" id="deleteConfirmModalLabel"><i class="bi bi-exclamation-triangle me-2"></i>
            Delete Confirmation</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
            aria-label="Close"></button>
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
  @if(session('success'))
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: "{{ session('success') }}",
        confirmButtonColor: '#0d6efd'
      });
    });
  </script>
@endif
@if(session('error'))
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: "{{ session('error') }}",
        confirmButtonColor: '#dc3545'
      });
    });
  </script>
@endif
@endsection
@push("scripts")
  <script>
    $(document).ready(function() {
      // Initialize Select2 for vehicles allowed dropdowns
      $('#editVehiclesAllowed').select2({
        placeholder: "Select vehicles",
        allowClear: true,
        dropdownParent: $('#editCategoryModal')
      });
      // Reopen workaround if needed
      $('#addCategoryModal').on('shown.bs.modal', function() {
        $('#vehiclesAllowed').select2('open');
      });
      // Initialize DataTables
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

      // Initialize DataTable for City Category (Allowed Expense)
      $('#allowedExpenseTable').DataTable({
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
          searchPlaceholder: 'Search Cities'
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

      // Select all users checkbox functionality
      $('#selectAllUsers').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('.user-checkbox').prop('checked', isChecked);
      });

      // Individual user checkbox change
      $('.user-checkbox').on('change', function() {
        var totalCheckboxes = $('.user-checkbox').length;
        var checkedCheckboxes = $('.user-checkbox:checked').length;
        
        if (checkedCheckboxes === totalCheckboxes) {
          $('#selectAllUsers').prop('checked', true);
        } else {
          $('#selectAllUsers').prop('checked', false);
        }
      });

      // Select all expense checkbox functionality
      $('#selectAllExpense').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('.expenseCheckbox').prop('checked', isChecked);
      });

      // Individual expense checkbox change
      $('.expenseCheckbox').on('change', function() {
        var totalCheckboxes = $('.expenseCheckbox').length;
        var checkedCheckboxes = $('.expenseCheckbox:checked').length;
        
        if (checkedCheckboxes === totalCheckboxes) {
          $('#selectAllExpense').prop('checked', true);
        } else {
          $('#selectAllExpense').prop('checked', false);
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
        $('#deleteConfirmText').text(`Are you sure you want to delete the vehicle "${vehicleName}"? This action cannot be undone.`);
        $('#deleteConfirmModal').modal('show');
        $('#confirmDeleteBtn').off('click').on('click', function() {
          // Add your delete logic here
          $(`button[data-id="${vehicleId}"].delete-vehicle`).closest('tr').remove();
          $('#deleteConfirmModal').modal('hide');
          // Show success message
          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: `Vehicle "${vehicleName}" has been deleted.`,
            confirmButtonColor: '#0d6efd'
          });
        });
      });
      // Delete category button click
      $('.delete-category').on('click', function() {
        let categoryId = $(this).data('id');
        let categoryName = $(this).data('name');
        $('#deleteConfirmText').text(`Are you sure you want to delete the category "${categoryName}"? This action cannot be undone.`);
        $('#deleteConfirmModal').modal('show');
        $('#confirmDeleteBtn').off('click').on('click', function() {
          // Add your delete logic here
          $(`button[data-id="${categoryId}"].delete-category`).closest('tr').remove();
          $('#deleteConfirmModal').modal('hide');
          // Show success message
          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: `Category "${categoryName}" has been deleted.`,
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
      $('#vehiclesAllowed').select2({
        placeholder: "Select vehicles",
        allowClear: true,
        dropdownParent: $('#addCategoryModal')
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
    $(document).ready(function () {
        const tab = new URLSearchParams(window.location.search).get('tab');
        if (tab === 'category') {
            $('#category-tab').tab('show'); // Or your relevant method
        }
    });
  </script>
@endpush
@push("styles")
  <style>
    /* Settings sidebar styles */
    .settings-sidebar {
      height: 100%;
      background: #ffffff;
      margin-left: 1rem;
      margin-top: 0.9rem;
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
      scrollbar-width: none;
      /* Firefox */
      -ms-overflow-style: none;
      /* IE and Edge */
    }
    .table-responsive::-webkit-scrollbar {
      display: none;
      /* Chrome, Safari, Opera */
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
      border: 0px;
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