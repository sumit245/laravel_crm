@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Add Item</h4>

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
        <form class="forms-sample" action="{{ route("inventory.update", $item->id) }}" method="POST">
          @csrf
          @method("PUT")
          <div class="form-group">
            <label for="productName" class="form-label">Product Name</label>
            <input type="text" name="productName" class="form-control" id="productName" placeholder="Bracing"
              value="{{ old("productName", $item->productName) }}">
          </div>
          <div class="form-group">
            <label for="brand" class="form-label">Brand</label>
            <input type="text" name="brand" class="form-control" id="brand" placeholder="Toshiba"
              value="{{ old("brand", $item->brand) }}">
          </div>
          <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <input type="text" name="description" class="form-control" id="description"
              placeholder="Briefly Describe the product" value="{{ old("description", $item->description) }}">
          </div>
          <div class="form-group">
            <label for="unit" class="form-label">Unit</label>
            <input type="text" name="unit" class="form-control" id="unit" placeholder="mm"
              value="{{ old("unit", $item->unit) }}">
          </div>
          <div class="form-group">
            <label for="initialQuantity" class="form-label">Initial Quantity</label>
            <input type="numeric" name="initialQuantity" class="form-control" id="productName" placeholder="1200"
              value="{{ old("initialQuantity", $item->initialQuantity) }}">
          </div>
          <div class="form-group">
            <label for="quantityStock" class="form-label">Quantity(in Stock)</label>
            <input type="numeric" name="quantityStock" class="form-control" id="quantityStock" placeholder="1100"
              value="{{ old("quantityStock", $item->quantityStock) }}">
          </div>
          <div class="form-group">
            <label for="receivedDate" class="form-label">Received Date</label>
            <input type="date" name="receivedDate" class="form-control" id="receivedDate" placeholder="2024-12-11"
              value="{{ old("quantityStock", $item->quantityStock) }}">
          </div>

          <button type="submit" class="btn btn-primary">Add Item</button>
        </form>
      </div>
    </div>
  </div>
@endsection
