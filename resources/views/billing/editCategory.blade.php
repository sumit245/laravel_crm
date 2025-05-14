@extends('layouts.main')

@section('content')
<div class="content-wrapper p-4">
    <div class="card p-4">
        <form action="{{ route('billing.updatecategory') }}" method="POST">
            @csrf
            @method('POST')
            <input type="hidden" name="category_id" value="{{ request()->route('id') }}">
            {{-- Category Dropdown --}}
            <div class="mb-3">
            <label for="category_code" class="form-label">Category</label>
                <input type="text" name="category_code" id="category_code" class="form-control" value="{{ old('category_code', $uc->category_code) }}">
            </div>

            {{-- Allowed Vehicles Dropdown --}}
            <div class="mb-3">
                <label for="vehicle_id" class="form-label">Allowed Vehicles</label>
                <select name="vehicle_id" id="vehicle_id" class="form-select">
                    @foreach($uv as $v)
                        <option value="{{ $v->id }}" >
                            {{ $v->id }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Category</button>
        </form>
    </div>
</div>
@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif
@endsection

@push('scripts')
<script>
    
</script>
@endpush