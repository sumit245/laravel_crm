@extends('layouts.main')

@section('content')

<form method="POST" action="{{ route('billing.updateuser') }}" class="m-3">
    @csrf
    @method('POST') <!-- or POST, depending on your need -->

    <input type="hidden" name="user_id" value="{{ request()->route('id') }}">

    <!-- Name -->
    <div class="mb-3">
        <label class="form-label fw-bold">Name</label>
        <input type="text" class="form-control" 
               value="{{ $ue->firstName }} {{ $ue->lastName }}" 
               disabled>
    </div>

    <!-- Role -->
    <div class="mb-3">
        <label class="form-label fw-bold">Role</label>
        <input type="text" class="form-control" 
               value="{{ $ue->role }}" 
               disabled>
    </div>

    <!-- Email -->
    <div class="mb-3">
        <label class="form-label fw-bold">Email</label>
        <input type="email" class="form-control" 
               value="{{ $ue->email }}" 
               disabled>
    </div>

    <!-- Category (Editable) -->
    <div class="mb-3">
        <label for="category" class="form-label fw-bold">Category</label>
        <select class="form-select " id="category" name="category" required>
            @foreach ($uc as $u)
            <option value="{{$u->id}}">{{ $u->category_code }}</option>
            @endforeach
        </select>
    </div>

    <!-- Submit Button -->
    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-content-save me-1"></i> Update Category
        </button>
    </div>
</form>


@endsection

@push('scripts')
<script>
    
</script>
@endpush