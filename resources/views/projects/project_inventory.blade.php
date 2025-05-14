<div>
@if (session("success") || session("error"))
<div id="flash-message" style="display: none;">
   @if (session("success"))
   <div class="alert alert-success">
      {{ session("success") }}
   </div>
   @endif
   @if (session("error"))
   <div class="alert alert-danger">
      {{ session("error") }}
   </div>
   @endif
</div>
@endif
<div class="d-flex justify-content-between mb-4">
   <div class="d-flex mx-2">
      <div class="card bg-info mx-2">
         <div class="card-body">
            <h5 class="card-title">{{ $initialStockValue }}</h5>
            <p class="card-text">Initial Stock Value</p>
         </div>
      </div>
      <div class="card bg-success mx-2">
         <div class="card-body">
            <h5 class="card-title">{{ number_format($inStoreStockValue, 2) }}</h5>
            <p class="card-text">In Store Stock Value</p>
         </div>
      </div>
      <div class="card bg-warning mx-2">
         <div class="card-body">
            <h5 class="card-title">{{ $dispatchedStockValue }}</h5>
            <p class="card-text">Dispatched Stock Value</p>
         </div>
      </div>
    </div>
    <button id="addStoreButton" class="btn btn-primary" style="max-height: 2.8rem;">
      <span>Create Store</span>
    </button>
  </div>

  <!-- Store Creation Form (Initially Hidden) -->
  <div id="storeFormContainer" class="card mb-4 mt-4 p-3" style="display: none;">
    <h6>Create Store</h6>
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    <form id="storeForm" action="{{ route("store.create", $project->id) }}" method="POST">
      @csrf
      <input type="hidden" name="project_id" value="{{ $project->id }}">

      <div class="form-group">
        <label for="name">Store Name:</label>
        <input type="text" class="form-control" id="name" name="name" required>
      </div>

      <div class="form-group">
        <label for="address">Address:</label>
        <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
      </div>

      <div class="form-group">
        <label for="storeIncharge">Store Incharge:</label>
        <select class="form-select" id="storeIncharge" name="storeIncharge" required>
          <option value="">Select Incharge</option>
          @foreach ($users as $user)
            <option value="{{ $user->id }}">{{ $user->firstName }} {{ $user->lastName }}</option>
          @endforeach
        </select>
      </div>

      <div class="d-flex justify-content-end">
        <button type="button" id="cancelStoreButton" class="btn btn-secondary me-2">Cancel</button>
        <button type="submit" id="dispatchSubmit" class="btn btn-primary">Save Store</button>
      </div>
    </form>
  </div>

  <!-- Existing Stores -->
  <div id="storeList">
    <h6>Existing Stores</h6>
    @if ($stores->isEmpty())
      <p>No stores available. Click "Create Store" to add one.</p>
    @else
      <ul class="list-group">
        @foreach ($stores as $store)
          <li class="list-group-item">
            <div class="d-flex justify-content-between">
              <div class="d-block mt-2" style="max-width: 60%;">
                <strong>{{ $store->store_name }}</strong><br>
                Address: {{ $store->address }}<br>
                Incharge: {{ $store->store_incharge_id }}
              </div>
              <div class="d-flex mt-2" style="max-width: 40%;">
                <button class="btn btn-success m-2" style="max-height: 3.4rem;" id="addInventoryBtn"
                  onclick="toggleAddInventory({{ $store->id }})">
               Add Inventory
               </button>
               <a href="{{ route("inventory.view", ["project_id" => $project->id, "store_id" => $store->id]) }}"
               class="btn btn-primary m-2" style="max-height: 3.4rem;">
               View Inventory
               </a>
               <button class="btn btn-warning m-2" style="max-height: 3.4rem;"
                  onclick="openDispatchModal({{ $store->id }})">
               Material Issue
               </button>
               {{-- <button class="btn btn-danger m-2" style="max-height: 3.4rem;"
                  onclick="deleteStore({{ $store->id }})">
                  Delete Store
                </button> --}}
              </div>
            </div>
            <!-- Add Inventory Form (Initially Hidden) -->
            <div id="addInventoryForm-{{ $store->id }}" class="mt-3" style="display: none;">
              @if ($errors->any())
                <div class="alert alert-danger">
                  <ul>
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif
              @if ($project->project_type == 1)
                <span><Strong>Add inventory for streetlight</Strong></span>
                <form style="width:25%; float:right;"
                  action="{{ route("inventory.import-streetlight", ["projectId" => $project->id, "storeId" => $store->id]) }}"
                  method="POST" enctype="multipart/form-data">
                  @csrf
                  <div class="input-group" id="import-form">
                    <input type="file" style="height:40px !important" name="file"
                      class="form-control form-control-sm" required>
                    <button type="submit" class="btn btn-sm btn-primary" data-toggle="tooltip"
                      title="Import Inventory">
                      <i class="mdi mdi-upload"></i> Import
                    </button>
                  </div>
                </form>
                <!-- Form for add inventory -->
                <form action="{{ route("inventory.store") }}" method="POST" class="mt-5">
                  @csrf
                  <input type="hidden" name="project_type" value="{{ $project->project_type }}">
                  <input type="hidden" name="project_id" value="{{ $project->id }}">
                  <input type="hidden" name="store_id" value="{{ $store->id }}">
                  <div class="form-group" id="inventoryForm">
                    <div class="row">
                      <div class="col-6">
                        <label for="item_combined"><strong>Item</strong></label>
                        <select id="item_combined" class="form-control">
                          <option value="">-- Select Item --</option>
                          <option value="SL01|Module">SL01 - Module</option>
                          <option value="SL02|Luminary">SL02 - Luminary</option>
                          <option value="SL03|Battery">SL03 - Battery</option>
                          <option value="SL04|Structure">SL04 - Structure</option>
                        </select>
                      </div>
                      <!-- Hidden inputs to hold separate values -->
                      <input type="hidden" name="code" id="item_code">
                      <input type="hidden" name="dropdown" id="item_name">
                      <div class="col-6">
                      <label for="quantity"><strong>Quantity</strong></label>
                        <input type="number" id="number" name="number" class="form-control" value="1" readonly required>
                      </div>
                  </div>
                  <div class="form-group">
                     <div class="row">
                        <div class="col-6">
                           <label for="manufacturer"><strong>Manufacturer</strong></label>
                           <input type="text" id="manufacturer" name="manufacturer" class="form-control"
                              value="" required>
                        </div>
                        <div class="col-6">
                           <label for="heading"><strong>Model</strong></label>
                           <input type="text" id="model" name="model" class="form-control" value=""
                              required>
                        </div>
                     </div>
                  </div>
                  <!-- Form group 3 -->
                  <div class="form-group">
                     <div class="row">
                        <div class="col-6">
                           <label for="serialNumber"><strong>Serial Number</strong></label>
                           <input type="text" id="serialnumber" name="serialnumber" class="form-control"
                              value="" required>
                        </div>
                        <div class="col-6">
                           <label for="make"><strong>Make</strong></label>
                           <input type="text" id="make" name="make" class="form-control" value=""
                              required>
                        </div>
                     </div>
                  </div>
                  <!-- Form group 4 -->
                  <div class="form-group">
                    <div class="row">
                      <div class="col-6">
                        <label for="rate"><strong>Rate</strong></label>
                        <input type="number" id="rate" name="rate" class="form-control" value=""
                          required>
                      </div>
                      <div class="col-6">
                        <label for="receiveddate"><strong>Received Date</strong></label>
                        <input type="date" id="receiveddate" name="receiveddate" class="form-control"
                          value="" required>
                      </div>
                    </div>
                  </div>
                  <!-- Form group 5 -->
                  <div class="form-group">
                     <div class="row">
                        <div class="col-6">
                           <label for="totalvalue"><strong>Total Value</strong></label>
                           <input type="number" id="totalvalue" name="totalvalue" class="form-control" value=""
                              required>
                        </div>
                        <div class="col-6">
                           <label for="hsncode"><strong>HSN Code</strong></label>
                           <input type="text" id="hsncode" name="hsncode" class="form-control" value=""
                              required>
                        </div>
                     </div>
                  </div>
                  <!-- Form group 6 -->
                  <div class="form-group">
                     <div class="row">
                        <div class="col-6">
                           <label for="description"><strong>Description</strong></label>
                           <input type="text" id="description" name="description" class="form-control"
                              value="" required>
                        </div>
                        <div class="col-6">
                           <label for="unit"><strong>Unit</strong></label>
                           <input type="number" id="unit" name="unit" class="form-control" value=""
                              required>
                        </div>
                     </div>
                  </div>
                  <!-- Form group 7 -->
                  <div class="form-group">
                    <div class="row">
                      
                    </div>
                  </div>
                  <div class="form-group" style="float:right">
                  <a href="javascript:void(0);" class="btn btn-secondary" onclick="closeInventoryForm({{ $store->id }})">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save</button>
                  </div>
            </form>
            <!-- end form -->
            @else
            <form action="{{ route("inventory.import", ["projectId" => $project->id, "storeId" => $store->id]) }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            <div class="input-group">
            <input type="file" name="file" class="form-control form-control-sm" required>
            <button type="submit" class="btn btn-sm btn-primary" data-toggle="tooltip"
               title="Import Inventory">
            <i class="mdi mdi-upload"></i> Import
            </button>
            </div>
            </form>
            @endif
            </div>
          </li>
        @endforeach
      </ul>
    @endif
  </div>
  @include("projects.dispatchInventory")
</div>
@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const addStoreButton = document.getElementById("addStoreButton");
    const cancelStoreButton = document.getElementById("cancelStoreButton");
    const storeFormContainer = document.getElementById("storeFormContainer");

    // Add inventory
    const itemCombined = document.getElementById('item_combined');
    if (itemCombined) {
      itemCombined.addEventListener('change', function() {
        const [code, name] = this.value.split('|');
        document.getElementById('item_code').value = code || '';
        document.getElementById('item_name').value = name || '';
      });
    }

    if (addStoreButton && storeFormContainer) {
      addStoreButton.addEventListener("click", () => {
        storeFormContainer.style.display = "block";
        addStoreButton.style.display = "none";
      });
    }

    if (cancelStoreButton && storeFormContainer && addStoreButton) {
      cancelStoreButton.addEventListener("click", () => {
        storeFormContainer.style.display = "none";
        addStoreButton.style.display = "block";
      });
    }
  });

  function toggleAddInventory(storeId) {
    var form = document.getElementById("addInventoryForm-" + storeId);

    if (!form) return; // Ensure the form exists

    if (form.style.display === "none" || form.style.display === "") {
      form.style.display = "block";

      // Ensure the form is visible before scrolling
      setTimeout(() => {
        const formTop = form.getBoundingClientRect().top + window.scrollY;
        window.scrollTo({
          top: formTop - 110,
          behavior: "smooth"
        });
      }, 100);
    } else {
      form.style.display = "none";
    }
  }

  // Closing the add inventory form
  function closeInventoryForm(storeId) {
    var form = document.getElementById("addInventoryForm-" + storeId);
    if (form) {
      form.style.display = "none";
    }
  }

  function addInventory(storeId) {
    // Redirect to the inventory import route with the store ID
    window.location.href = `/inventory/import-streetlight?store_id=${storeId}`;
  }

  function dispatchInventory(storeId) {
    // Redirect to the dispatch inventory route with the store ID
    window.location.href = `/inventory/dispatch?store_id=${storeId}`;
  }

  function deleteStore(storeId) {
    if (confirm('Are you sure you want to delete this store?')) {
      fetch('{{ url("store") }}/' + storeId, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            // Show success message
            alert('Store deleted successfully');
            // Reload the page to reflect changes
            location.reload();
          } else {
            alert(data.message || 'Failed to delete store');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while deleting the store');
        });
    }
  }

  // Show the modal to dispatch inventory
  function openDispatchModal(storeId) {
    document.getElementById("dispatchStoreId").value = storeId;
    $('#dispatchModal').modal('show');
  }

  // close the dispatch inventory modal
  function closeDispatchModal() {
    document.getElementById("dispatchModal").classList.add("hidden");
  }

  // Redirect to a page showing store inventory with export/print options
  function viewStoreInventory(storeId) {
    window.location.href = `/store/${storeId}/inventory/view`;
  }
</script>
@endpush
