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
                    <input type="file" style="height:40px !important" name="file"
                      class="form-control form-control-sm" required>
                    <button type="submit" class="btn btn-sm btn-primary" data-toggle="tooltip"
                      title="Import Inventory">
                      <i class="mdi mdi-upload"></i> Import
                    </button>
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
          </div>
        @endforeach
      </ul>
    @endif
  </div>

  <!-- Dispatch Inventory Modal -->
  <div id="dispatchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="dispatchModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="dispatchModalLabel">Dispatch Inventory</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="dispatchForm" action="{{ route("inventory.dispatch") }}" method="POST">
          @csrf
          <input type="hidden" id="dispatchStoreId" name="store_id">
          <div class="modal-body">
            <!-- Vendor Selection -->
            <div class="form-group">
              <label for="vendorName">Vendor Name:</label>
              <select class="form-select" id="storeIncharge" name="storeIncharge" required>
                <option value="">Select Vendor</option>
                @foreach ($assignedVendors as $user)
                  <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="d-flex justify-content-end align-items-center">
              <button type="button" class="btn btn-danger btn-sm remove-item-btn m-1">
                <i class="mdi mdi-delete"></i> Remove
              </button>
              <button type="button" class="btn btn-success btn-sm m-1" id="addMoreItems">
                <i class="mdi mdi-plus"></i>
                Add More Items
              </button>
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
                        <option value="{{ $item->id }}" data-stock="{{ $item->quantity }}">
                          {{ $item->item_code }} {{ $item->item }}
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
                <div class="row">
                  <div class="col-sm-4">
                    <!-- QR Code Scanning -->
                    <div class="form-group">
                      <label for="qr_scanner" class="form-label">Scan Item QR Code:</label>
                      <input type="text" id="qr_scanner" class="form-control" autofocus />
                      <small class="text-muted">Keep scanning QR codes...</small>
                      <div id="qr_error" class="text-danger mt-2"></div>
                    </div>
                  </div>
                  <div class="col-sm-8">
                    <!-- Scanned QR Codes List -->
                    <ul id="scanned_qrs" class="list-group mb-3"></ul>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </form>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Dispatch</button>
        </div>
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

  //   Delete Entire Store
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

  document.addEventListener('DOMContentLoaded', function() {
    const itemsContainer = document.getElementById('itemsContainer');
    const addMoreItemsButton = document.getElementById('addMoreItems');
    let availableQuantity = 0;
    let scannedQRs = [];



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

    function fetchItemDetails(select) {
      let selectedOption = select.options[select.selectedIndex];
      let itemData = selectedOption.getAttribute("data-details");

      if (itemData) {
        let item = JSON.parse(itemData);
        let parentDiv = select.closest('.dispatch-item');
        parentDiv.querySelector('.item-name').textContent = item.item_name;
        parentDiv.querySelector('.item-category').textContent = item.category;
        parentDiv.querySelector('.item-stock').textContent = item.stock;
        parentDiv.querySelector('.item-price').textContent = item.price;
        parentDiv.querySelector('.item-details').classList.remove('hidden');
      }
    }

    function addDispatchItem() {
      let dispatchContainer = document.getElementById("dispatchItems");
      let itemTemplate = dispatchContainer.querySelector(".dispatch-item").cloneNode(true);

      // Reset dropdown and quantity field
      itemTemplate.querySelector(".item-code").selectedIndex = 0;
      itemTemplate.querySelector(".item-quantity").value = "";
      itemTemplate.querySelector(".item-details").classList.add("hidden");

      dispatchContainer.appendChild(itemTemplate);
    }

    function removeItem(button) {
      let dispatchContainer = document.getElementById("dispatchItems");
      if (dispatchContainer.children.length > 1) {
        button.closest('.dispatch-item').remove();
      }
    }
    document.getElementById("dispatchSubmit").addEventListener("click", function() {
      let storeId = document.getElementById("dispatchStoreId").value;
      let vendorId = document.getElementById("vendorName").value;
      let items = [];

      document.querySelectorAll(".dispatch-item").forEach(itemDiv => {
        let itemCode = itemDiv.querySelector(".item-code").value;
        let quantity = itemDiv.querySelector(".item-quantity").value;

        if (itemCode && quantity > 0) {
          items.push({
            item_code: itemCode,
            quantity: quantity
          });
        }
      });

      if (!storeId || !vendorId || items.length === 0) {
        Swal.fire("Error", "Please fill all fields correctly!", "error");
        return;
      }

      // Call API to dispatch items
      fetch("/api/dispatch-inventory", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            store_id: storeId,
            vendor_id: vendorId,
            items: items
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire("Success", "Items dispatched successfully!", "success");
            closeDispatchModal();
          } else {
            Swal.fire("Error", data.message, "error");
          }
        })
        .catch(error => {
          Swal.fire("Error", "Something went wrong!", "error");
        });
    });

    // On item selection, update quantity field
    document.getElementById("item_id").addEventListener("change", function() {
      availableQuantity = parseInt(this.selectedOptions[0].getAttribute("data-quantity")) || 0;
      document.getElementById("quantity").value = availableQuantity;
      scannedQRs = [];
      updateScannedQRs();
      document.getElementById("dispatchBtn").disabled = true;
    });

    // Handle QR scanning
    document.getElementById("qr_scanner").addEventListener("keyup", function(event) {
      if (event.key === "Enter" && this.value.trim() !== "") {
        let scannedCode = this.value.trim();
        console.log("Scanned Code:", scannedCode); // Log the scanned code
        this.value = ""; // Clear input for next scan

        if (scannedQRs.includes(scannedCode)) {
          showError("QR code already scanned!");
          console.log("QR code already scanned:", scannedCode); // Log this case
          return;
        }

        if (scannedQRs.length >= availableQuantity) {
          showError("Cannot scan more than the available quantity!");
          console.log("Exceeded available quantity:", availableQuantity); // Log this case
          return;
        }


        // Check if QR exists in database via AJAX
        fetch("{{ route("inventory.checkQR") }}", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
              qr_code: scannedCode
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.exists) {
              scannedQRs.push(scannedCode);
              updateScannedQRs();
              clearError();
            } else {
              showError("Invalid QR code! Item not found in inventory.");
            }
          })
          .catch(() => showError("Error checking QR code!"));
      }
    });

    // Update scanned QR list
    function updateScannedQRs() {
      let list = document.getElementById("scanned_qrs");
      list.innerHTML = "";
      scannedQRs.forEach(qr => {
        let li = document.createElement("li");
        li.className = "list-group-item";
        li.textContent = qr;
        list.appendChild(li);
      });

      // Enable dispatch button if all scans are valid
      document.getElementById("dispatchBtn").disabled = (scannedQRs.length === 0 || scannedQRs.length !==
        availableQuantity);
    }

    // Show error message
    function showError(message) {
      document.getElementById("qr_error").textContent = message;
    }

    // Clear error message
    function clearError() {
      document.getElementById("qr_error").textContent = "";
    }
  });
</script>
