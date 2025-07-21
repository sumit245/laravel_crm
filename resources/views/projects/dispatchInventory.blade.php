<!-- Dispatch Inventory Modal -->
<div id="dispatchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="dispatchModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dispatchModalLabel">Dispatch Inventory</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="dispatchForm">
        @csrf
        <input type="hidden" id="dispatchStoreId" name="store_id">
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        <input type="hidden" name="store_incharge_id" value="{{ $store->store_incharge_id ?? "N/A" }}">
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
            <!-- <button type="button" class="btn btn-danger btn-sm remove-item-btn m-1" id="removeItemButton">
                <i class="mdi mdi-delete"></i> Remove
              </button> -->
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
                  <select class="form-select item-select" name="item_code" required>
                    <option value="">Select Item</option>
                    @foreach ($inventoryItems as $item)
                      <option value="{{ $item->item_code }}" data-stock="{{ $item->total_quantity }}"
                        data-item="{{ $item->item }}" data-rate="{{ $item->rate }}" data-make="{{ $item->make }}"
                        data-model="{{ $item->model }}">
                        {{ $item->item_code }} {{ $item->item }}
                      </option>
                    @endforeach
                  </select>
                  <input type="hidden" name="item" id="item_namesss">
                  <input type="hidden" name="rate" id="item_rate">
                  <input type="hidden" name="make" id="item_make">
                  <input type="hidden" name="model" id="item_model">
                </div>
                <div class="col-sm-4 form-group">
                  <label for="quantity">Quantity:</label>
                  <input type="number" class="form-control item-quantity" name="total_quantity" min="1"
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
          <button type="button" class="btn btn-primary printbtn" id="printButton">
            <i class="mdi mdi-printer"></i> Print
          </button>

          <button type="button" id="issueMaterial" class="btn btn-primary">Issue
            items</button>
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
    let loadingIssue = false

    // Add New Item Row
    let rowCount = 1;
    addMoreItemsButton.addEventListener("click", function() {
      const originalRow = document.querySelector(".item-row");
      if (!originalRow) return;

      const newItemRow = originalRow.cloneNode(true);
      rowCount++;

      newItemRow.querySelector(".item-select").value = "";
      newItemRow.querySelector(".item-quantity").value = "";


      const scannedList = newItemRow.querySelector("#scanned_qrs");
      if (scannedList) {
        scannedList.innerHTML = "";
        scannedList.id = `scanned_qrs_${rowCount}`;
      }

      const qrScannerInput = newItemRow.querySelector("#qr_scanner");
      if (qrScannerInput) {
        qrScannerInput.value = "";
        qrScannerInput.setAttribute("data-row", rowCount);
      }

      const serialContainer = newItemRow.querySelector("#serial_numbers_container");
      if (serialContainer) {
        serialContainer.id = `serial_numbers_container_${rowCount}`;
      }

      let removeButton = newItemRow.querySelector(".remove-item-btn");
      if (!removeButton) {
        removeButton = document.createElement("button");
        removeButton.className = "btn btn-danger btn-sm remove-item-btn m-1";
        removeButton.innerHTML = '<i class="mdi mdi-delete"></i> Remove';
        newItemRow.appendChild(removeButton);
      }
      itemsContainer.appendChild(newItemRow);
    });

    // Remove Item Row
    itemsContainer.addEventListener("click", function(e) {
      if (e.target.closest(".remove-item-btn")) {
        const rows = itemsContainer.querySelectorAll(".item-row");
        if (rows.length > 1) {
          e.target.closest(".item-row").remove();
        }
      }
    });

    // Handle Qr Scanning
    const qrScanner = document.getElementById('qr_scanner');
    // TODO: Modify with keyup listener so that form doesnot submit on scan
    qrScanner.addEventListener('keyup', function(event) {
      if (event.key === 'Enter' && this.value.trim() !== '') {
        let scannedCode = this.value.trim();

        this.value = ''; // Clear input for next scan

        if (scannedQRs.includes(scannedCode)) {
          showError('QR code already scanned!', 'qr_error');
          return;
        }
        // Find the item-row that contains this QR scanner
        const currentRow = this.closest('.item-row');
        if (!currentRow) {
          showError('Cannot determine which item row this scanner belongs to!', 'qr_error');
          return;
        }
        // Get the selected item ID
        const selectedItemCode = document.querySelector('.item-select').value;
        if (!selectedItemCode) {
          showError('Please select an item first before scanning QR codes!', 'qr_error');
          return;
        }
        if (selectedItemCode === "SL02") {
          scannedCode = scannedCode.split(';')[0]
        }

        const storeId = document.getElementById('dispatchStoreId').value; // Get store_id from hidden input
        // Check if QR exists in database via AJAX
        fetch('{{ route("inventory.checkQR") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              qr_code: scannedCode,
              store_id: storeId,
              item_code: selectedItemCode
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
              showError('Invalid QR code! Item not found in inventory.', 'qr_error');
            }
          })
          .catch(() => showError('Error checking QR code!', 'qr_error'));
      }
    });

    // Show error message
    function showError(message, context) {
      const errorElement = document.getElementById(context);
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


    const itemSelect = document.querySelector('.item-select');
    if (itemSelect) {
      itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        // Update hidden fields with item details
        console.log(document.querySelectorAll('#item_namesss').length);
        document.getElementById('item_namesss').value = selectedOption.dataset.item || '';
        document.getElementById('item_rate').value = selectedOption.dataset.rate || '';
        document.getElementById('item_make').value = selectedOption.dataset.make || '';
        document.getElementById('item_model').value = selectedOption.dataset.model || '';
        // Clear scanned QRs when item changes
        const form = document.getElementById('dispatchForm');
        console.log(form)

        scannedQRs = [];
        updateScannedQRs();
        updateQuantityAndTotal();
      });
    }

    // Update scanned QR list
    function updateScannedQRs() {
      const list = document.getElementById('scanned_qrs');
      if (!list) return;

      list.innerHTML = ''; // Clear the list

      scannedQRs.forEach((qr, index) => {
        const li = document.createElement('li');
        li.className = 'list-group-item';

        // Create a wrapper div to separate text and delete button
        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex justify-content-between align-items-center';

        const qrText = document.createElement('span');
        qrText.textContent = qr;

        const deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.className = 'btn btn-sm btn-danger';
        deleteBtn.innerHTML = '&times;';
        deleteBtn.onclick = (e) => {
          e.preventDefault();
          scannedQRs.splice(index, 1); // Remove QR from array
          updateScannedQRs(); // Refresh UI
        };

        wrapper.appendChild(qrText);
        wrapper.appendChild(deleteBtn);
        li.appendChild(wrapper);
        list.appendChild(li);
      });
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

    // Print Functionality
    document.getElementById('printButton').addEventListener('click', function(e) {
      e.preventDefault();


      const vendorSelect = document.getElementById('vendorName');
      if (vendorSelect.selectedIndex === 0) {
        alert('Please select a vendor first.');
        return;
      }
      const vendorName = vendorSelect.options[vendorSelect.selectedIndex].textContent;

      const itemRows = document.querySelectorAll('#itemsContainer .item-row');
      const itemsData = [];

      itemRows.forEach(row => {
        const itemSelect = row.querySelector('.item-select');
        if (itemSelect.selectedIndex === 0) return;

        const selectedOption = itemSelect.options[itemSelect.selectedIndex];
        const scannedQRsList = row.querySelector('ul.list-group.my-1');
        const scannedQRs = Array.from(scannedQRsList.querySelectorAll('li')).map(li => li.textContent);

        itemsData.push({
          code: selectedOption.value,
          name: selectedOption.dataset.item,
          rate: selectedOption.dataset.rate,
          make: selectedOption.dataset.make,
          model: selectedOption.dataset.model,
          quantity: row.querySelector('.item-quantity').value,
          serials: scannedQRs
        });
      });

      if (itemsData.length === 0) {
        alert('Please add at least one item to print.');
        return;
      }

      const printWindow = window.open('');
      printWindow.document.write(`
        <html>
          <head>
            <title>Dispatch Report</title>
            <style>
              body { font-family: Arial; margin: 20px; }
              .header { text-align: center; margin-bottom: 30px; }
              table { width: 100%; border-collapse: collapse; margin-top: 20px; }
              th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
              th { background-color: #f5f5f5; }
              .serial-list { max-width: 300px; word-break: break-all; }
            </style>
          </head>
          <body>
            <div class="header">
              <h2>Inventory Dispatch Report</h2>
              <p><strong>Vendor:</strong> ${vendorName}</p>
              <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
            </div>
            
            <table>
              <thead>
                <tr>
                  <th>Item Code</th>
                  <th>Item Name</th>
                  <th>Quantity</th>
                  <th>Rate</th>
                  <th>Make/Model</th>
                  <th>Serial Numbers</th>
                </tr>
              </thead>
              <tbody>
                ${itemsData.map(item => `
                  <tr>
                    <td>${item.code}</td>
                    <td>${item.name}</td>
                    <td>${item.quantity}</td>
                    <td>₹${item.rate}</td>
                    <td>${item.make} ${item.model}</td>
                    <td class="serial-list">${item.serials.join(', ')}</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>

            <script>
              window.onload = function() {
                window.print();
                setTimeout(() => window.close(), 500);
              }
            <\/script>
          </body>
        </html>
      `);
      printWindow.document.close();
    });

    document.getElementById('issueMaterial').addEventListener('click', function(e) {
      e.preventDefault();
      loadingIssue = true
      const button = this;
      const originalText = button.innerHTML;
      button.disabled = true;
      button.innerHTML = `
      <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
      Processing...
      `;
      const form = document.getElementById('dispatchForm');
      console.log(form)
      const formData = new FormData(form);

      fetch("{{ route("inventory.dispatchweb") }}", {
          method: "POST",
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          loadingIssue = false;
          button.disabled = false;
          button.innerHTML = originalText;
          if (data.status === 'success') {
            Swal.fire({
              title: 'Success!',
              text: data.message,
              icon: 'success',
              confirmButtonText: 'OK'
            }).then(() => {
              form.reset();
              location.reload()
            });
          } else {
            Swal.fire({
              title: 'Error!',
              text: data.message,
              icon: 'error',
              confirmButtonText: 'OK'
            }).then(() => {
              loadingIssue = false;
              button.disabled = false;
              button.innerHTML = originalText;
            });
          }
        })
        .catch(error => {
          console.error(error);
          Swal.fire({
            title: 'Error!',
            text: 'Something went wrong. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
          }).then(() => {
            loadingIssue = false;
            button.disabled = false;
            button.innerHTML = originalText;
          });;
        });
    });

    // Sweet alert success popup
    @if (session("success"))
      Swal.fire({
        title: 'Success!',
        text: "{{ session("success") }}",
        icon: 'success',
        confirmButtonText: 'OK'

      });
    @elseif (session("error"))
      Swal.fire({
        title: 'Error!',
        text: "{{ session("error") }}",
        icon: 'error',
        confirmButtonText: 'OK'
      });
    @endif
  });
</script>

@push("styles")
  <style>
    .printbtn {
      background: #ffaf00;
      border: none
    }

    .printbtn:hover {
      background: rgb(223, 152, 1);
      border: none
    }

    .text-danger {
      color: #F95F53 !important;
      font-size: 14px;
    }

    .list-group-item {
      padding: 5px;
      top: 25px;
    }

    .remove-item-btn {}
  </style>
@endpush
