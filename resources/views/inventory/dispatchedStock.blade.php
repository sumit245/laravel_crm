@extends("layouts.main")
@section("content")
  @if ($title)
    <h2> {{ $title }} Dispatched Inventory</h2>
  @else
    <h2> Dispatched Inventory</h2>
  @endif

  <x-data-table id="tadaTable" :pageLength="50">
    <x-slot:thead>
      <tr>
        <th data-select="true">
          <input type="checkbox" id="selectAll" />
        </th>
        <th>Item</th>
        <th>Serial number</th>
        {{-- <th>Available Quantity</th> --}}
        <th>Value</th>
        <th>Vendor</th>
        <th>Used At</th>
        <th>Action</th>
      </tr>
    </x-slot:thead>
    <x-slot:tbody>
      @foreach ($specificDispatch as $item)
        <tr>
          <td><input type="checkbox" class="select-item" data-id="{{ $item->id }}" /></td>
          <td>{{ $item->item_code }}-{{ $item->item }}</td>
          <td>{{ $item->serial_number ?? "NA" }}</td>
          {{-- <td>{{ $availableBattery ?? "N/A" }}</td> --}}
          <td>₹{{ $item->total_value ?? "N/A" }}</td>
          <td>{{ $item->vendor->name ?? "N/A" }}</td>
          <td>
            @if ($item->is_consumed && $item->streetlightPole)
              <a href="{{ route("poles.show", $item->streetlightPole->id) }}">
                {{ $item->streetlightPole->complete_pole_number }}
              </a>
            @else
              @php
                $daysInCustody = \Carbon\Carbon::parse($item->dispatch_date)->diffInDays(\Carbon\Carbon::now());
              @endphp
              In Vendor Custody
              <span style="color: {{ $daysInCustody > 5 ? "red" : "green" }}">
                ({{ $daysInCustody }} days)
              </span>
            @endif

          </td>
          <td>
            @if ($item->is_consumed && $item->streetlightPole)
              {{-- Write code to show a modal --}}
              <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                data-bs-target="#itemDetailsModal" data-item-id="{{ $item->id }}"
                data-item-code="{{ $item->item_code }}" data-item-name="{{ $item->item }}"
                data-manufacturer="{{ $item->make }}" data-serial-number="{{ $item->serial_number }}"
                data-model="{{ $item->model }}" data-quantity="{{ $item->total_quantity }}"
                data-vendor="{{ $item->vendor->name ?? "N/A" }}" data-status="{{ $item->status }}"
                data-total="{{ $item->total_value }}" data-date="{{ $item->dispatch_date }}"
                data-site="{{ $item->streetlightPole->complete_pole_number ?? "N/A" }}" class="item-details">
                Replace
              </button>
            @else
              <form action="{{ route("inventory.return") }}" method="POST" style="display:inline;">
                @csrf
                <input type="hidden" name="serial_number" value="{{ $item->serial_number }}">
                <button type="submit" class="btn btn-warning btn-sm">Return</button>
              </form>
            @endif
          </td>
        </tr>
      @endforeach
    </x-slot:tbody>
  </x-data-table>

  <!-- Modal -->
  <div class="modal fade" id="itemDetailsModal" tabindex="-1" role="dialog" aria-labelledby="itemDetailsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="itemDetailsModalLabel">Replace Item: <span id="modal-serial-number"></span></h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="replaceItemForm" method="POST" action="{{ route("inventory.replace") }}">
          @csrf
          <input type="hidden" name="item_id" id="replace_item_id">
          <input type="hidden" name="old_serial_number" id="old_serial_number">
          <div class="modal-body">
            <div class="d-flex">
              <div>
                <h6><span id="modal-item-code"></span></h6>
                <p><span id="modal-item-name"></span></p>
                <p><strong>Manufacturer:</strong> <span id="modal-manufacturer"></span></p>
                <p><strong>Model:</strong> <span id="modal-model"></span></p>
              </div>
              <div>
                <h6><strong>Dispatched to: </strong> <span id="modal-vendor"></span></h6>
                <p><strong> On (date and time): </strong> <span id="modal-date"></span></p>
                <p><strong>Used at</strong> <span id="modal-site"></span></p>
              </div>
            </div>
            <div class="form-group">
              <label for="new_serial_number">New Serial Number</label>
              <input type="text" class="form-control" id="new_serial_number" name="new_serial_number"
                placeholder="New Serial Number" required>
            </div>
            <div class="form-group" id="authentication_code_group" style="display: none;">
              <label for="authentication_code">Authentication Code</label>
              <input type="password" class="form-control" id="authentication_code" name="authentication_code"
                placeholder="Authentication Code" required>
            </div>
            <div class="form-group mx-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="agreement_checkbox" name="agreement_checkbox"
                  required>
                <label class="form-check-label" for="agreement_checkbox">
                  Our Team has replaced the old item during maintenance and agrees to return the inventory. I understand
                  that the action is irreversible.
                </label>
              </div>
            </div>

            <div id="replace-message"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Replace Item</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Handle modal data population
      $('#itemDetailsModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var item_id = button.data('item-id')
        var item_code = button.data('item-code')
        var item_name = button.data('item-name')
        var manufacturer = button.data('manufacturer')
        var serial_number = button.data('serial-number')
        var model = button.data('model')
        var vendor = button.data('vendor')
        var date = button.data('date')
        var site = button.data('site')

        var modal = $(this)
        modal.find('#modal-item-code').text(item_code)
        modal.find("#old_serial_number").val(serial_number)
        modal.find('#modal-item-name').text(item_name)
        modal.find('#modal-manufacturer').text(manufacturer)
        modal.find('#modal-serial-number').text(serial_number)
        modal.find('#modal-model').text(model)
        modal.find('#modal-vendor').text(vendor)
        modal.find('#modal-date').text(date)
        modal.find('#modal-site').text(site)
        modal.find('#replace_item_id').val(item_id) // Set the item_id in the hidden field
      });
      // Show/hide authentication code based on serial number input
      $('#new_serial_number').on('input', function() {
        if ($(this).val().length > 0) {
          $('#authentication_code_group').show();
        } else {
          $('#authentication_code_group').hide();
        }
      });
      // Inside the DOMContentLoaded event listener, after the existing code

      // Function to check if the form can be submitted
      function checkFormValidity() {
        var authenticationCode = $('#authentication_code').val();
        var agreementChecked = $('#agreement_checkbox').is(':checked');

        // Replace this with your actual authentication code validation logic
        var authenticationCodeValid = authenticationCode.length > 0; // Example: Check if it's not empty

        if (authenticationCodeValid && agreementChecked) {
          $('.btn-primary[type="submit"]').prop('disabled', false); // Enable the submit button
        } else {
          $('.btn-primary[type="submit"]').prop('disabled', true); // Disable the submit button
        }
      }

      // Disable the submit button initially
      $('.btn-primary[type="submit"]').prop('disabled', true);

      // Add event listeners to the authentication code and agreement checkbox
      $('#authentication_code, #agreement_checkbox').on('input change', function() {
        checkFormValidity();
      });

      // Optional: Re-check validity when the modal is shown (in case values are pre-filled)
      $('#itemDetailsModal').on('show.bs.modal', function() {
        checkFormValidity();
      });

      @if (session("success"))
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: '{{ session("success") }}',
          confirmButtonColor: '#3085d6',
        });
      @endif

      @if (session("replace_error"))
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: '{{ session("replace_error") }}',
          confirmButtonColor: '#d33',
        });
      @endif
    });
  </script>
@endsection
