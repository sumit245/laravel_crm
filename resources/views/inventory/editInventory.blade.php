@extends("layouts.main")


@section('content')
<div class="container">
    <h2>Edit Inventory Item</h2>
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <!-- action="{{ route('inventory.updateInventory', $inventoryItem->id) }}" method="POST" -->
    <form>
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="productName" class="form-label">Product Name</label>
            <input type="text" class="form-control" id="productName" name="productName" value="{{ $inventoryItem->productName }}" required>
        </div>

        <div class="mb-3">
            <label for="brand" class="form-label">Brand</label>
            <input type="text" class="form-control" id="brand" name="brand" value="{{ $inventoryItem->brand }}">
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description">{{ $inventoryItem->description }}</textarea>
        </div>

        <div class="mb-3">
            <label for="initialQuantity" class="form-label">Initial Quantity</label>
            <input type="text" class="form-control" id="initialQuantity" name="initialQuantity" value="{{ $inventoryItem->initialQuantity }}" required>
        </div>

        <div class="mb-3">
            <label for="quantityStock" class="form-label">Quantity in Stock</label>
            <input type="text" class="form-control" id="quantityStock" name="quantityStock" value="{{ $inventoryItem->quantityStock }}">
        </div>

        <div class="mb-3">
            <label for="unit" class="form-label">Unit</label>
            <input type="text" class="form-control" id="unit" name="unit" value="{{ $inventoryItem->unit }}" required>
        </div>

        <div class="mb-3">
            <label for="receivedDate" class="form-label">Received Date</label>
            <input type="date" class="form-control" id="receivedDate" name="receivedDate" value="{{ $inventoryItem->receivedDate }}">
        </div>

        <button type="submit" class="btn btn-primary">Update Inventory</button>
    </form>
</div>
@endsection
