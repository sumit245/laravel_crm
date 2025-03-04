@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Add Item(s)</h4>

        <!-- Display validation errors -->
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
        <form class="forms-sample" action="{{ route("inventory.store") }}" method="POST">
          @csrf
          <div class="form-group">
            <label for="productName" class="form-label">Product Name</label>
            <input type="text" name="productName" class="form-control" id="productName" placeholder="Bracing">
          </div>
          <div class="form-group">
            <label for="brand" class="form-label">Brand</label>
            <input type="text" name="brand" class="form-control" id="brand" placeholder="Toshiba">
          </div>
          <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <input type="text" name="description" class="form-control" id="description"
              placeholder="Briefly Describe the product">
          </div>
          <div class="form-group">
            <label for="unit" class="form-label">Unit</label>
            <input type="text" name="unit" class="form-control" id="unit" placeholder="mm">
          </div>
          <div class="form-group">
            <label for="initialQuantity" class="form-label">Initial Quantity</label>
            <input type="numeric" name="initialQuantity" class="form-control" id="productName" placeholder="1200">
          </div>
          <div class="form-group">
            <label for="quantityStock" class="form-label">Quantity(in Stock)</label>
            <input type="numeric" name="quantityStock" class="form-control" id="quantityStock" placeholder="1100">
          </div>
          <div class="form-group">
            <label for="receivedDate" class="form-label">Received Date</label>
            <input type="date" name="receivedDate" class="form-control" id="receivedDate" placeholder="2024-12-11">
          </div>

          <button type="submit" class="btn btn-primary">Add Item</button>
        </form>
      </div>
    </div>
  </div>
@endsection
