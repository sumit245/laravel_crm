<div>
  <div class="d-flex justify-content-between mb-4">
    <h5>Store</h5>
    <button id="addStoreButton" class="btn btn-primary">Create Store</button>
  </div>

  <!-- Store Creation Form (Initially Hidden) -->
  <div id="storeFormContainer" class="card mb-4 p-3" style="display: none;">
    <h6>Create Store</h6>
    <form id="storeForm" action="{{ route("store.store", $project->id) }}" method="POST">
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
        <select class="form-select" id="storeIncharge" name="store_incharge_id" required>
          <option value="">Select Incharge</option>
          @foreach ($users as $user)
            <option value="{{ $user->id }}">{{ $user->name }}</option>
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
          <li class="list-group-item">
            <div class="d-flex justify-content-between">
              <div class="d-block mt-2">
                <strong>{{ $store->store_name }}</strong><br>
                Address: {{ $store->address }}<br>
                Incharge: {{ $store->incharge_id }}
              </div>
              <div class="d-flex mt-2">
                <button class="btn btn-success m-2" style="max-height: 2rem;"
                  onclick="addInventory({{ $store->id }})">Add
                  Inventory</button>
                <button class="btn btn-warning m-2"
                  style="max-height: 2rem; "onclick="dispatchInventory({{ $store->id }})">Dispatch
                  Inventory</button>
                <button class="btn btn-danger m-2"style="max-height: 2rem;"
                  onclick="deleteStore({{ $store->id }})">Delete Store</button>
              </div>
            </div>
          </li>
        @endforeach
      </ul>
    @endif
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

  function addInventory(storeId) {
    // Redirect to the inventory import route with the store ID
    window.location.href = `/inventory/import?store_id=${storeId}`;
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
</script>
