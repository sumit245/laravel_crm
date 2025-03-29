<div>
  <div class="d-flex justify-content-between mb-4">
    <div class="d-flex mx-2 mt-4">
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
        <button type="submit" class="btn btn-primary">Save Store</button>
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
          <div class="list-group-item">
            <div class="d-flex justify-content-between">
              <div class="d-block mt-2">
                <strong>{{ $store->store_name }}</strong><br>
                Address: {{ $store->address }}<br>
                Incharge: {{ $store->storeIncharge }}
              </div>
              <div class="d-flex mt-2">
                <button class="btn btn-success m-2" style="max-height: 3.4rem;"
                  onclick="toggleAddInventory({{ $store->id }})">
                  Add Inventory
                </button>
                <a href="{{ route("inventory.view", ["project_id" => $project->id, "store_id" => $store->id]) }}"
                  class="btn btn-primary m-2" style="max-height: 3.4rem;">
                  View Inventory
                </a>

                <button class="btn btn-warning m-2" style="max-height: 3.4rem;"
                  onclick="openDispatchModal({{ $store->id }})">
                  Dispatch Inventory
                </button>
                <button class="btn btn-danger m-2" style="max-height: 3.4rem;"
                  onclick="deleteStore({{ $store->id }})">
                  Delete Store
                </button>
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
                  <div class="input-group">
                    <input type="file" style="height:40px !important" name="file" class="form-control form-control-sm" required>
                    <button type="submit" class="btn btn-sm btn-primary" data-toggle="tooltip"
                      title="Import Inventory">
                      <i class="mdi mdi-upload"></i> Import
                    </button>
                  </div>
                </form>
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
          </div>
        @endforeach
      </ul>
    @endif
  </div>
  <div id="dispatchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="dispatchModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="dispatchModalLabel">Dispatch Inventory</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="dispatchForm" action="{{ route("inventory.dispatch") }}" method="POST">
          @csrf
          <div class="modal-body">
            <input type="hidden" id="dispatchStoreId" name="store_id">
            <!-- Vendor Selection -->
            <div class="form-group">
              <label for="vendorName">Vendor Name:</label>
              <select class="form-select" id="storeIncharge" name="storeIncharge" required>
                <option value="">Select Vendor</option>
                @foreach ($users as $user)
                  <option value="{{ $user->id }}">{{ $user->firstName }} {{ $user->lastName }}</option>
                @endforeach
              </select>
            </div>

            <div class="row">
              <div class="col-sm-6">
                <button type="button" class="btn btn-danger btn-sm remove-item-btn mt-4">Remove Item</button>
              </div>
              <div class="col-sm-6">
                <button type="button" class="btn btn-success btn-sm mt-4" id="addMoreItems">Add More Items</button>
              </div>
            </div>
            <!-- Dynamic Items Section -->
            <div id="itemsContainer">
              <div class="item-row mb-3">
                <div class="row">
                  <div class="col-sm-8 form-group">
                    <label for="items">Item:</label>
                    <select class="form-select item-select" name="items[]" required>
                      <option value="">Select Item</option>
                      @foreach ($inventoryItems as $item)
                        <option value="{{ $item->id }}" data-stock="{{ $item->initialQuantity }}">
                          {{ $item->productName }}
                        </option>
                      @endforeach
                    </select>

                  </div>
                  <div class="col-sm-4 form-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" class="form-control item-quantity" name="quantities[]" min="1"
                      required>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="site">Site:</label>
              <select class="form-select item-site" name="sites[]" required>
                <option value="">Select Site</option>
                @foreach ($sites as $site)
                  <option value="{{ $site->id }}">{{ $site->site_name }}</option>
                @endforeach
              </select>
            </div>

            <!-- Add More Items Button -->

            <!-- Dispatch Date -->
            <div class="form-group mt-4">
              <label for="dispatchDate">Dispatch Date:</label>
              <input type="date" class="form-control" id="dispatchDate" name="dispatch_date" required>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Approve</button>
          </div>
        </form>

      </div>
    </div>
  </div>

</div>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const addStoreButton = document.getElementById("addStoreButton");
    const cancelStoreButton = document.getElementById("cancelStoreButton");
    const storeFormContainer = document.getElementById("storeFormContainer");

    addStoreButton.addEventListener("click", () => {
      storeFormContainer.style.display = "block";
      addStoreButton.style.display = "none";
    });

    cancelStoreButton.addEventListener("click", () => {
      storeFormContainer.style.display = "none";
      addStoreButton.style.display = "block";
    });
  });

  function toggleAddInventory(storeId) {
    const form = document.getElementById(`addInventoryForm-${storeId}`);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
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
      // Send a DELETE request to the server to delete the store
      fetch(`/store/${storeId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert('Failed to delete store.');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Failed to delete store.');
        });
    }
  }

  function openDispatchModal(storeId) {
    // Set store ID in the hidden field
    document.getElementById('dispatchStoreId').value = storeId;

    // Fetch and populate items in the dropdown
    fetch(`/store/${storeId}/inventory`)
      .then(response => response.json())
      .then(data => {
        const itemsSelect = document.getElementById('items');
        itemsSelect.innerHTML = ''; // Clear previous options
        data.forEach(item => {
          const option = document.createElement('option');
          option.value = item.id;
          option.textContent = `${item.name} (${item.quantity})`;
          itemsSelect.appendChild(option);
        });
      })
      .catch(error => console.error('Error fetching inventory:', error));

    // Show the modal
    $('#dispatchModal').modal('show');
  }

  function viewStoreInventory(storeId) {
    // Redirect to a page showing store inventory with export/print options
    window.location.href = `/store/${storeId}/inventory/view`;
  }
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const itemsContainer = document.getElementById('itemsContainer');
    const addMoreItemsButton = document.getElementById('addMoreItems');

    // Add New Item Row
    addMoreItemsButton.addEventListener('click', function() {
      const newItemRow = document.querySelector('.item-row').cloneNode(true);
      newItemRow.querySelector('.item-select').value = '';
      newItemRow.querySelector('.item-quantity').value = '';
      // newItemRow.querySelector('.item-site').value = '';
      itemsContainer.appendChild(newItemRow);
    });

    // Remove Item Row
    itemsContainer.addEventListener('click', function(e) {
      if (e.target.classList.contains('remove-item-btn')) {
        e.target.closest('.item-row').remove();
      }
    });

    // Validate Quantity Against Stock
    itemsContainer.addEventListener('input', function(e) {
      if (e.target.classList.contains('item-quantity')) {
        const stock = e.target.closest('.item-row').querySelector('.item-select').selectedOptions[0]
          .getAttribute('data-stock');
        console.log(stock)
        if (parseInt(e.target.value) > parseInt(stock)) {
          alert('Quantity cannot exceed stock.');
          e.target.value = stock;
        }
      }
    });
  });
</script>
