<!-- Dispatch Inventory Modal -->
<div id="dispatchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="dispatchModalLabel" aria-hidden="true">
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
            <button type="button" class="btn btn-success btn-sm m-1" id="addMoreItems">
              <i class="mdi mdi-plus"></i>
              Add More Items
            </button>
          </div>
          <!-- Dynamic Items Section -->
          <div id="itemsContainer">
            <div class="item-row mb-3" data-row-id="1">
              <div class="row">
                <div class="col-sm-8 form-group">
                  <label for="items">Item:</label>
                  <select class="form-select item-select" name="item_code[]" required>
                    <option value="">Select Item</option>
                    @foreach ($inventoryItems as $item)
                      <option value="{{ $item->item_code }}" data-stock="{{ $item->total_quantity }}"
                        data-item="{{ $item->item }}" data-rate="{{ $item->rate }}" data-make="{{ $item->make }}"
                        data-model="{{ $item->model }}">
                        {{ $item->item_code }} {{ $item->item }}
                      </option>
                    @endforeach
                  </select>
                  <input type="hidden" name="item[]" class="item-name-hidden">
                  <input type="hidden" name="rate[]" class="item-rate-hidden">
                  <input type="hidden" name="make[]" class="item-make-hidden">
                  <input type="hidden" name="model[]" class="item-model-hidden">
                </div>
                <div class="col-sm-4 form-group">
                  <label for="quantity">Quantity:</label>
                  <input type="number" class="form-control item-quantity" name="total_quantity[]" min="1" required>
                  <input type="hidden" name="total_value[]" class="total-value-hidden">
                </div>
              </div>
              <div class="row">
                <div class="col-sm-4">
                  <!-- QR Code Scanning -->
                  <div class="form-group">
                    <label class="form-label">Scan Item QR Code:</label>
                    <input type="text" class="form-control qr-scanner-input" placeholder="Scan QR code here..." />
                    <small class="text-muted">Keep scanning QR codes...</small>
                    <div class="qr-error-message text-danger mt-2"></div>
                  </div>
                </div>
                <div class="col-sm-8">
                  <!-- Scanned QR Codes List -->
                  <ul class="scanned-qrs-list list-group my-1"></ul>
                  <div class="serial-numbers-container"></div>
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
          <button type="button" id="issueMaterial" class="btn btn-primary">Issue items</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const itemsContainer = document.getElementById('itemsContainer');
  const addMoreItemsButton = document.getElementById('addMoreItems');
  let rowCounter = 1;
  let loadingIssue = false;
  
  // Store scanned QRs per row
  const rowData = {};
  
  // Initialize first row
  initializeRow(1);

  function initializeRow(rowId) {
    rowData[rowId] = {
      scannedQRs: [],
      availableQuantity: 0
    };
  }

  function createNewRow() {
    rowCounter++;
    const originalRow = document.querySelector(".item-row");
    if (!originalRow) return null;

    const newRow = originalRow.cloneNode(true);
    newRow.setAttribute('data-row-id', rowCounter);
    
    // Clear all form inputs
    const inputs = newRow.querySelectorAll('input, select');
    inputs.forEach(input => {
      if (input.type === 'hidden') {
        input.value = '';
      } else if (input.tagName === 'SELECT') {
        input.selectedIndex = 0;
      } else {
        input.value = '';
      }
    });
    
    // Clear lists and containers
    newRow.querySelector('.scanned-qrs-list').innerHTML = '';
    newRow.querySelector('.serial-numbers-container').innerHTML = '';
    newRow.querySelector('.qr-error-message').textContent = '';
    
    // Add remove button
    if (!newRow.querySelector('.remove-item-btn')) {
      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'btn btn-danger btn-sm remove-item-btn m-1';
      removeBtn.innerHTML = '<i class="mdi mdi-delete"></i> Remove';
      newRow.appendChild(removeBtn);
    }
    
    // Initialize row data
    initializeRow(rowCounter);
    
    return newRow;
  }

  // Add More Items Event
  addMoreItemsButton.addEventListener("click", function() {
    const newRow = createNewRow();
    if (newRow) {
      itemsContainer.appendChild(newRow);
    }
  });

  // Remove Item Row Event (using event delegation)
  itemsContainer.addEventListener("click", function(e) {
    if (e.target.closest(".remove-item-btn")) {
      const rows = itemsContainer.querySelectorAll(".item-row");
      if (rows.length > 1) {
        const rowToRemove = e.target.closest(".item-row");
        const rowId = parseInt(rowToRemove.getAttribute('data-row-id'));
        
        // Clean up row data
        delete rowData[rowId];
        rowToRemove.remove();
      }
    }
  });

  // QR Scanner Event (using event delegation)
  itemsContainer.addEventListener('keydown', function(event) {
    if (event.target.classList.contains('qr-scanner-input') && event.key === 'Enter') {
      event.preventDefault();
      handleQRScan(event.target);
    }
  });

  function handleQRScan(scannerInput) {
    const scannedValue = scannerInput.value.trim();
    if (!scannedValue) return;

    const currentRow = scannerInput.closest('.item-row');
    const rowId = parseInt(currentRow.getAttribute('data-row-id'));
    
    // Clear the input immediately
    scannerInput.value = '';
    
    if (!rowData[rowId]) {
      initializeRow(rowId);
    }

    // Check for duplicate QR in this row
    if (rowData[rowId].scannedQRs.includes(scannedValue)) {
      showErrorMessage(currentRow, 'QR code already scanned in this row!');
      return;
    }

    // Get selected item for this row
    const itemSelect = currentRow.querySelector('.item-select');
    const selectedItemCode = itemSelect.value;
    
    if (!selectedItemCode) {
      showErrorMessage(currentRow, 'Please select an item first before scanning QR codes!');
      return;
    }

    // Process the scanned code
    let processedCode = scannedValue;
    if (selectedItemCode === "SL02") {
      processedCode = scannedValue.split(';')[0];
    }

    const storeId = document.getElementById('dispatchStoreId').value;

    // Validate QR code via AJAX
    fetch('{{ route("inventory.checkQR") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({
        qr_code: processedCode,
        store_id: storeId,
        item_code: selectedItemCode
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.exists) {
        // Add to scanned QRs
        rowData[rowId].scannedQRs.push(processedCode);
        
        // Update UI
        updateScannedQRsList(currentRow, rowId);
        addSerialNumberInput(currentRow, processedCode);
        updateQuantityAndTotal(currentRow, rowId);
        clearErrorMessage(currentRow);
        
        // Focus back to scanner for next scan
        scannerInput.focus();
      } else {
        showErrorMessage(currentRow, 'Invalid QR code! Item not found in inventory.');
      }
    })
    .catch(error => {
      console.error('QR Check Error:', error);
      showErrorMessage(currentRow, 'Error checking QR code!');
    });
  }

  function showErrorMessage(row, message) {
    const errorElement = row.querySelector('.qr-error-message');
    if (errorElement) {
      errorElement.textContent = message;
      setTimeout(() => {
        errorElement.textContent = '';
      }, 3000);
    }
  }

  function clearErrorMessage(row) {
    const errorElement = row.querySelector('.qr-error-message');
    if (errorElement) {
      errorElement.textContent = '';
    }
  }

  function updateScannedQRsList(row, rowId) {
    const list = row.querySelector('.scanned-qrs-list');
    if (!list || !rowData[rowId]) return;
    
    list.innerHTML = '';
    
    rowData[rowId].scannedQRs.forEach((qr, index) => {
      const li = document.createElement('li');
      li.className = 'list-group-item d-flex justify-content-between align-items-center';
      
      const qrText = document.createElement('span');
      qrText.textContent = qr;
      
      const deleteBtn = document.createElement('button');
      deleteBtn.type = 'button';
      deleteBtn.className = 'btn btn-sm btn-danger';
      deleteBtn.innerHTML = '&times;';
      deleteBtn.onclick = function(e) {
        e.preventDefault();
        removeQRCode(row, rowId, index);
      };
      
      li.appendChild(qrText);
      li.appendChild(deleteBtn);
      list.appendChild(li);
    });
  }

  function removeQRCode(row, rowId, index) {
    if (rowData[rowId] && rowData[rowId].scannedQRs[index]) {
      rowData[rowId].scannedQRs.splice(index, 1);
      updateScannedQRsList(row, rowId);
      updateQuantityAndTotal(row, rowId);
      
      // Remove corresponding serial number input
      const serialInputs = row.querySelectorAll('input[name="serial_numbers[]"]');
      if (serialInputs[index]) {
        serialInputs[index].remove();
      }
    }
  }

  function addSerialNumberInput(row, serialNumber) {
    const container = row.querySelector('.serial-numbers-container');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'serial_numbers[]';
    input.value = serialNumber;
    container.appendChild(input);
  }

  function updateQuantityAndTotal(row, rowId) {
    if (!rowData[rowId]) return;
    
    const quantityInput = row.querySelector('.item-quantity');
    const rateInput = row.querySelector('.item-rate-hidden');
    const totalValueInput = row.querySelector('.total-value-hidden');
    
    const rate = parseFloat(rateInput.value) || 0;
    const quantity = rowData[rowId].scannedQRs.length;
    
    quantityInput.value = quantity;
    totalValueInput.value = (rate * quantity).toFixed(2);
  }

  // Handle item selection changes
  itemsContainer.addEventListener('change', function(e) {
    if (e.target.classList.contains('item-select')) {
      const currentRow = e.target.closest('.item-row');
      const rowId = parseInt(currentRow.getAttribute('data-row-id'));
      const selectedOption = e.target.options[e.target.selectedIndex];
      
      // Update hidden fields
      currentRow.querySelector('.item-name-hidden').value = selectedOption.dataset.item || '';
      currentRow.querySelector('.item-rate-hidden').value = selectedOption.dataset.rate || '';
      currentRow.querySelector('.item-make-hidden').value = selectedOption.dataset.make || '';
      currentRow.querySelector('.item-model-hidden').value = selectedOption.dataset.model || '';
      
      // Clear scanned QRs when item changes
      if (rowData[rowId]) {
        rowData[rowId].scannedQRs = [];
        updateScannedQRsList(currentRow, rowId);
        updateQuantityAndTotal(currentRow, rowId);
        currentRow.querySelector('.serial-numbers-container').innerHTML = '';
      }
    }
  });

  // Validate Quantity Against Stock
  itemsContainer.addEventListener('input', function(e) {
    if (e.target.classList.contains('item-quantity')) {
      const currentRow = e.target.closest('.item-row');
      const select = currentRow.querySelector('.item-select');
      if (select.selectedIndex > 0) {
        const stock = parseInt(select.selectedOptions[0].getAttribute('data-stock'));
        const enteredQuantity = parseInt(e.target.value);
        if (enteredQuantity > stock) {
          alert('Quantity cannot exceed available stock (' + stock + ').');
          e.target.value = stock;
        }
      }
    }
  });

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
      const rowId = parseInt(row.getAttribute('data-row-id'));
      const scannedQRs = rowData[rowId] ? rowData[rowId].scannedQRs : [];

      if (scannedQRs.length > 0) {
        itemsData.push({
          code: selectedOption.value,
          name: selectedOption.dataset.item,
          rate: selectedOption.dataset.rate,
          make: selectedOption.dataset.make,
          model: selectedOption.dataset.model,
          quantity: scannedQRs.length,
          serials: scannedQRs
        });
      }
    });

    if (itemsData.length === 0) {
      alert('Please add at least one item with scanned QR codes to print.');
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

  // Issue Material
  document.getElementById('issueMaterial').addEventListener('click', function(e) {
    e.preventDefault();
    
    if (loadingIssue) return;
    
    loadingIssue = true;
    const button = this;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = `
      <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
      Processing...
    `;

    const form = document.getElementById('dispatchForm');
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
          location.reload();
        });
      } else {
        Swal.fire({
          title: 'Error!',
          text: data.message,
          icon: 'error',
          confirmButtonText: 'OK'
        });
      }
    })
    .catch(error => {
      console.error(error);
      loadingIssue = false;
      button.disabled = false;
      button.innerHTML = originalText;
      
      Swal.fire({
        title: 'Error!',
        text: 'Something went wrong. Please try again.',
        icon: 'error',
        confirmButtonText: 'OK'
      });
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
    border: none;
  }

  .printbtn:hover {
    background: rgb(223, 152, 1);
    border: none;
  }

  .text-danger {
    color: #F95F53 !important;
    font-size: 14px;
  }

  .list-group-item {
    padding: 5px;
  }

  .remove-item-btn {
    margin-top: 10px;
  }

  .qr-scanner-input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
  }
</style>
@endpush
