{{-- Add vehicle --}}
<div class="modal fade" id="addVehicleModal" tabindex="-1" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('settings.billing.vehicles.store') }}" method="POST" class="billing-modal-form" data-billing-form="add_vehicle">
                @csrf
                <input type="hidden" name="_billing_form" value="add_vehicle">
                <div class="modal-header">
                    <h5 class="modal-title" id="addVehicleModalLabel">Add vehicle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_vehicle_name" class="form-label">Vehicle name</label>
                        <input type="text" class="form-control @error('vehicle_name') is-invalid @enderror" id="add_vehicle_name" name="vehicle_name" value="{{ old('vehicle_name') }}" maxlength="255" required autocomplete="off">
                        @error('vehicle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="add_vehicle_category" class="form-label">Category</label>
                        <input type="text" class="form-control @error('category') is-invalid @enderror" id="add_vehicle_category" name="category" value="{{ old('category') }}" maxlength="255" required autocomplete="off">
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="add_vehicle_sub_category" class="form-label">Sub category</label>
                        <input type="text" class="form-control @error('sub_category') is-invalid @enderror" id="add_vehicle_sub_category" name="sub_category" value="{{ old('sub_category') }}" maxlength="255" autocomplete="off">
                        @error('sub_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="add_vehicle_rate" class="form-label">Rate per KM (₹)</label>
                        <input type="number" step="0.01" min="0" max="999999.99" class="form-control @error('rate') is-invalid @enderror" id="add_vehicle_rate" name="rate" value="{{ old('rate') }}" required inputmode="decimal">
                        @error('rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary billing-submit-btn">Save vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit vehicle --}}
<div class="modal fade" id="editVehicleModal" tabindex="-1" aria-labelledby="editVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editVehicleForm" method="POST" class="billing-modal-form" data-billing-form="edit_vehicle">
                @csrf
                @method('PUT')
                <input type="hidden" name="_billing_form" value="edit_vehicle">
                <input type="hidden" name="_billing_action" value="{{ old('_billing_action') }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="editVehicleModalLabel">Edit vehicle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_vehicle_name" class="form-label">Vehicle name</label>
                        <input type="text" class="form-control @error('vehicle_name') is-invalid @enderror" id="edit_vehicle_name" name="vehicle_name" value="{{ old('vehicle_name') }}" maxlength="255" required autocomplete="off">
                        @error('vehicle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_vehicle_category" class="form-label">Category</label>
                        <input type="text" class="form-control @error('category') is-invalid @enderror" id="edit_vehicle_category" name="category" value="{{ old('category') }}" maxlength="255" required autocomplete="off">
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_vehicle_sub_category" class="form-label">Sub category</label>
                        <input type="text" class="form-control @error('sub_category') is-invalid @enderror" id="edit_vehicle_sub_category" name="sub_category" value="{{ old('sub_category') }}" maxlength="255" autocomplete="off">
                        @error('sub_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_vehicle_rate" class="form-label">Rate per KM (₹)</label>
                        <input type="number" step="0.01" min="0" max="999999.99" class="form-control @error('rate') is-invalid @enderror" id="edit_vehicle_rate" name="rate" value="{{ old('rate') }}" required inputmode="decimal">
                        @error('rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary billing-submit-btn">Update vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add category --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('settings.billing.categories.store') }}" method="POST" class="billing-modal-form" data-billing-form="add_category">
                @csrf
                <input type="hidden" name="_billing_form" value="add_category">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add staff category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($vehicles->isEmpty())
                        <div class="alert alert-warning mb-0" role="alert">
                            Add at least one vehicle before creating a staff category.
                        </div>
                    @else
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="add_category_code" class="form-label">Code</label>
                                <input type="text" class="form-control @error('category_code') is-invalid @enderror" id="add_category_code" name="category_code" value="{{ old('category_code') }}" maxlength="255" required autocomplete="off">
                                @error('category_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8">
                                <label for="add_category_name" class="form-label">Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="add_category_name" name="name" value="{{ old('name') }}" maxlength="255" required autocomplete="off">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="add_category_vehicles" class="form-label">Allowed vehicles</label>
                                <select class="form-select @error('vehicle_ids') is-invalid @enderror" id="add_category_vehicles" name="vehicle_ids[]" multiple required size="5" aria-describedby="add_category_vehicles_help">
                                    @foreach ($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" @selected(collect(old('vehicle_ids', []))->contains($vehicle->id))>
                                            {{ $vehicle->category }} — {{ $vehicle->vehicle_name ?: 'Unnamed' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="add_category_vehicles_help" class="form-text">Hold Ctrl (Cmd on Mac) to select multiple.</div>
                                @error('vehicle_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="add_category_city" class="form-label">City tier</label>
                                <select class="form-select @error('city_category') is-invalid @enderror" id="add_category_city" name="city_category" required>
                                    @foreach ($cityLabels as $value => $label)
                                        <option value="{{ $value }}" @selected((string) old('city_category', '0') === (string) $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('city_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="add_category_daily" class="form-label">Daily amount (₹)</label>
                                <input type="number" step="0.01" min="0" max="999999.99" class="form-control @error('daily_amount') is-invalid @enderror" id="add_category_daily" name="daily_amount" value="{{ old('daily_amount') }}" required inputmode="decimal">
                                @error('daily_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary billing-submit-btn" @disabled($vehicles->isEmpty())>Save category</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit category --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editCategoryForm" method="POST" class="billing-modal-form" data-billing-form="edit_category">
                @csrf
                @method('PUT')
                <input type="hidden" name="_billing_form" value="edit_category">
                <input type="hidden" name="_billing_action" value="{{ old('_billing_action') }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit staff category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="description" id="edit_category_description" value="{{ old('description') }}">
                    @if ($vehicles->isEmpty())
                        <div class="alert alert-warning mb-0" role="alert">
                            No vehicles available. Add vehicles before editing category rules.
                        </div>
                    @else
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="edit_category_code" class="form-label">Code</label>
                                <input type="text" class="form-control @error('category_code') is-invalid @enderror" id="edit_category_code" name="category_code" value="{{ old('category_code') }}" maxlength="255" required autocomplete="off">
                                @error('category_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8">
                                <label for="edit_category_name" class="form-label">Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="edit_category_name" name="name" value="{{ old('name') }}" maxlength="255" required autocomplete="off">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="edit_category_vehicles" class="form-label">Allowed vehicles</label>
                                <select class="form-select @error('vehicle_ids') is-invalid @enderror" id="edit_category_vehicles" name="vehicle_ids[]" multiple required size="5">
                                    @foreach ($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">{{ $vehicle->category }} — {{ $vehicle->vehicle_name ?: 'Unnamed' }}</option>
                                    @endforeach
                                </select>
                                @error('vehicle_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="edit_category_city" class="form-label">City tier</label>
                                <select class="form-select @error('city_category') is-invalid @enderror" id="edit_category_city" name="city_category" required>
                                    @foreach ($cityLabels as $value => $label)
                                        <option value="{{ $value }}" @selected((string) old('city_category', '0') === (string) $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('city_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="edit_category_daily" class="form-label">Daily amount (₹)</label>
                                <input type="number" step="0.01" min="0" max="999999.99" class="form-control @error('daily_amount') is-invalid @enderror" id="edit_category_daily" name="daily_amount" value="{{ old('daily_amount') }}" required inputmode="decimal">
                                @error('daily_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary billing-submit-btn" @disabled($vehicles->isEmpty())>Update category</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit user category --}}
<div class="modal fade" id="editUserCategoryModal" tabindex="-1" aria-labelledby="editUserCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editUserCategoryForm" method="POST" class="billing-modal-form" data-billing-form="edit_user_category">
                @csrf
                @method('PUT')
                <input type="hidden" name="_billing_form" value="edit_user_category">
                <input type="hidden" name="_billing_action" value="{{ old('_billing_action') }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserCategoryModalLabel">Assign category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small" id="edit_user_category_label"></p>
                    @if ($categories->isEmpty())
                        <div class="alert alert-warning mb-0" role="alert">
                            Create a staff category before assigning users.
                        </div>
                    @else
                        <div class="mb-3">
                            <label for="edit_user_category_id" class="form-label">Staff category</label>
                            <select class="form-select @error('category_id') is-invalid @enderror" id="edit_user_category_id" name="category_id" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                                        {{ $category->category_code }} — {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary billing-submit-btn" @disabled($categories->isEmpty())>Save assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit city tier --}}
<div class="modal fade" id="editCityModal" tabindex="-1" aria-labelledby="editCityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCityForm" method="POST" class="billing-modal-form" data-billing-form="edit_city">
                @csrf
                @method('PUT')
                <input type="hidden" name="_billing_form" value="edit_city">
                <input type="hidden" name="_billing_action" value="{{ old('_billing_action') }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCityModalLabel">Edit city tier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_city_name" class="form-label">City</label>
                        <input type="text" class="form-control" id="edit_city_name" name="city_name" value="{{ old('city_name') }}" maxlength="255" required readonly aria-readonly="true">
                    </div>
                    <div class="mb-3">
                        <label for="edit_city_category" class="form-label">Tier</label>
                        <select class="form-select @error('city_category') is-invalid @enderror" id="edit_city_category" name="city_category" required>
                            @foreach ($cityLabels as $value => $label)
                                <option value="{{ $value }}" @selected((string) old('city_category', '0') === (string) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('city_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary billing-submit-btn">Update tier</button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
    $billingModalMap = [
        'add_vehicle' => 'addVehicleModal',
        'edit_vehicle' => 'editVehicleModal',
        'add_category' => 'addCategoryModal',
        'edit_category' => 'editCategoryModal',
        'edit_user_category' => 'editUserCategoryModal',
        'edit_city' => 'editCityModal',
    ];
    $billingModalToOpen = $billingModalMap[old('_billing_form', '')] ?? null;
@endphp
