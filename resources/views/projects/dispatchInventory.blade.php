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
        <form id="dispatchForm" action="{{ route("inventory.dispatchweb") }}" method="POST">
          @csrf
          <input type="hidden" id="dispatchStoreId" name="store_id">
          <input type="hidden" name="project_id" value="{{ $project->id }}">
          <input type="hidden" name="store_incharge_id" value="{{ $store->store_incharge_id }}">
          <div class="modal-body">
            <!-- Vendor Selection -->
            <div class="form-group">
              <label for="vendorName">Vendor Name:</label>
              <select class="form-select select2" id="vendorName" name="vendor_id" required>
                <option value="">Select Vendor</option>
                @foreach ($assignedVendors as $user)
                  <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
              </select>
              {{-- TODO: Turn into select2 --}}
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
            {{-- <pre>{{ print_r($inventoryItems->toArray(), true) }}</pre> --}}
            <!-- Dynamic Items Section -->
            <div id="itemsContainer">
              <div class="item-row mb-3">
                <div class="row">
                  <div class="col-sm-8 form-group">
                    <label for="items">Item:</label>
                    <select class="form-select item-select" name="item_code" required>
                      <option value="">Select Item</option>
                      @foreach ($inventoryItems as $item)
                        <option value="{{ $item->item_code }}" data-stock="{{ $item->total_quantity }}"
                          data-item="{{ $item->item }}" data-rate="{{ $item->rate }}"
                          data-make="{{ $item->make }}" data-model="{{ $item->model }}">
                          {{ $item->item_code }} {{ $item->item }}
                        </option>
                      @endforeach
                    </select>
                    <input type="hidden" name="item" id="item_name">
                    <input type="hidden" name="rate" id="item_rate">
                    <input type="hidden" name="make" id="item_make">
                    <input type="hidden" name="model" id="item_model">
                  </div>
                  <div class="col-sm-4 form-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" class="form-control item-quantity" name="quantities[]" min="1"
                      required>
                    <input type="hidden" name="total_value" id="total_value">
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
                    <ul id="scanned_qrs" class="list-group my-1"></ul>
                    <div id="serial_numbers_container"></div>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Dispatch</button>
          </div>
        </form>

      </div>

    </div>
  </div>
  <script>
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
          const rows = document.querySelectorAll('.item-row');
          if (rows.length > 1) {
            e.target.closest('.item-row').remove();
          }
        }
      });

      // Validate Quantity Against Stock
      itemsContainer.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-quantity')) {
          const select = e.target.closest('.item-row').querySelector('.item-select');
          if (select.selectedIndex > 0) {
            const stock = select.selectedOptions[0].getAttribute('data-stock');
            if (parseInt(e.target.value) > parseInt(stock)) {
              alert('Quantity cannot exceed stock.');
              e.target.value = stock;
            }
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

      const itemSelect = document.querySelector('.item-select');

      if (itemSelect) {
        itemSelect.addEventListener('change', function() {
          const selectedOption = this.options[this.selectedIndex];

          // Update hidden fields with item details
          document.getElementById('item_name').value = selectedOption.dataset.item || '';
          document.getElementById('item_rate').value = selectedOption.dataset.rate || '';
          document.getElementById('item_make').value = selectedOption.dataset.make || '';
          document.getElementById('item_model').value = selectedOption.dataset.model || '';

          // Clear scanned QRs when item changes
          scannedQRs = [];
          updateScannedQRsList();
          updateQuantityAndTotal();
        });
      }


      // Handle Qr Scanning
      const qrScanner = document.getElementById('qr_scanner');
      if (qrScanner) {
        qrScanner.addEventListener('change', function(event) {
          if (this.value.trim() !== '') {
            let scannedCode = this.value.trim();
            console.log('Scanned Code:', scannedCode);
            this.value = ''; // Clear input for next scan

            if (scannedQRs.includes(scannedCode)) {
              showError('QR code already scanned!');
              return;
            }
            // Find the item-row that contains this QR scanner
            const currentRow = this.closest('.item-row');
            if (!currentRow) {
              showError('Cannot determine which item row this scanner belongs to!');
              return;
            }
            // Get the selected item ID
            const selectedItemCode = document.querySelector('.item-select').value;
            console.log(selectedItemId)
            if (!selectedItemCode) {
              showError('Please select an item first before scanning QR codes!');
              return;
            }

            // Check if QR exists in database via AJAX
            fetch('{{ route("inventory.checkQR") }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
                          // Add hidden input for the serial number
        addSerialNumberInput(scannedCode);
        updateQuantityAndTotal();
                  clearError();
                } else {
                  showError('Invalid QR code! Item not found in inventory.');
                }
              })
              .catch(() => showError('Error checking QR code!'));
          }
        });
      }

      // Update scanned QR list
      function updateScannedQRs() {
        let list = document.getElementById('scanned_qrs');
        if (list) {
          list.innerHTML = '';
          scannedQRs.forEach(qr => {
            let li = document.createElement('li');
            li.className = 'list-group-item';
            li.textContent = qr;
            list.appendChild(li);
          });
        }
      }

        // Add hidden input for serial number
  function addSerialNumberInput(serialNumber) {
    const container = document.getElementById('serial_numbers_container');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'serial_numbers[]';
    input.value = serialNumber;
    container.appendChild(input);
  }

   // Update quantity and total value
  function updateQuantityAndTotal() {
    const quantityInput = document.querySelector('.item-quantity');
    const rate = parseFloat(document.getElementById('item_rate').value) || 0;
    
    // Set quantity to number of scanned QRs
    const quantity = scannedQRs.length;
    quantityInput.value = quantity;
    
    // Calculate and set total value
    const totalValue = rate * quantity;
    document.getElementById('total_value').value = totalValue.toFixed(2);
  }
  
  


      // Show error message
      function showError(message) {
        const errorElement = document.getElementById('qr_error');
        if (errorElement) {
          errorElement.textContent = message;
        }
      }

      // Clear error message
      function clearError() {
        const errorElement = document.getElementById('qr_error');
        if (errorElement) {
          errorElement.textContent = '';
        }
      }
      @if (session("success"))
        Swal.fire({
          title: 'Success!',
          text: "{{ session("success") }}",
          icon: 'success',
          confirmButtonText: 'OK'
        });
      @endif

      @if (session("error"))
        Swal.fire({
          title: 'Error!',
          text: "{{ session("error") }}",
          icon: 'error',
          confirmButtonText: 'OK'
        });
      @endif
    });
  </script>
