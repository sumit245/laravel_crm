@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Inventory</h2>
    <form action="{{ route('inventory.updateInventory', $inventory->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" value="{{ $inventory->name }}" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" name="quantity" value="{{ $inventory->quantity }}" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
    </form>
</div>
@endsection
