<div id="billing-tab-vehicles" class="billing-tab-pane" role="tabpanel" aria-labelledby="billing-tab-vehicles-label">
    <div class="billing-section-toolbar">
        <div>
            <h5 id="billing-tab-vehicles-label" class="billing-section-title mb-1">Vehicles</h5>
            <p class="text-muted small mb-0">Conveyance rates by vehicle type.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
            <i class="mdi mdi-plus me-1" aria-hidden="true"></i>Add vehicle
        </button>
    </div>

    @if ($vehicles->isEmpty())
        <div class="card billing-data-card border shadow-sm">
            <div class="card-body">
                @include('settings.partials.billing._empty-state', [
                    'title' => 'No vehicles yet',
                    'message' => 'Add vehicle types and per-kilometre rates used for conveyance claims.',
                    'actionLabel' => 'Add vehicle',
                    'actionTarget' => 'addVehicleModal',
                ])
            </div>
        </div>
    @else
        <div class="card billing-data-card border shadow-sm">
            <div class="card-body">
                <x-datatable
                    id="billingVehicleTable"
                    title="Vehicles"
                    :columns="[
                        ['title' => 'Vehicle name', 'width' => '25%'],
                        ['title' => 'Category', 'width' => '20%'],
                        ['title' => 'Sub category', 'width' => '20%'],
                        ['title' => 'Rate / KM (₹)', 'width' => '15%'],
                    ]"
                    :exportEnabled="true"
                    :importEnabled="false"
                    :bulkDeleteEnabled="false"
                    pageLength="25"
                    searchPlaceholder="Search vehicles…">
                    @foreach ($vehicles as $vehicle)
                        <tr>
                            <td class="billing-cell-truncate" title="{{ $vehicle->vehicle_name }}">{{ $vehicle->vehicle_name ?: '—' }}</td>
                            <td class="billing-cell-truncate" title="{{ $vehicle->category }}">{{ $vehicle->category ?: '—' }}</td>
                            <td class="billing-cell-truncate" title="{{ $vehicle->sub_category }}">{{ $vehicle->sub_category ?: '—' }}</td>
                            <td>{{ number_format((float) $vehicle->rate, 2) }}</td>
                            <td class="text-center billing-row-actions">
                                <button type="button"
                                    class="btn btn-icon btn-warning btn-edit-vehicle"
                                    title="Edit vehicle"
                                    aria-label="Edit {{ $vehicle->category ?: 'vehicle' }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editVehicleModal"
                                    data-action="{{ route('settings.billing.vehicles.update', $vehicle) }}"
                                    data-vehicle-name="{{ $vehicle->vehicle_name }}"
                                    data-category="{{ $vehicle->category }}"
                                    data-sub-category="{{ $vehicle->sub_category }}"
                                    data-rate="{{ $vehicle->rate }}">
                                    <i class="mdi mdi-pencil" aria-hidden="true"></i>
                                </button>
                                <form action="{{ route('settings.billing.vehicles.destroy', $vehicle) }}" method="POST" class="d-inline billing-delete-form"
                                    onsubmit="return confirm('Delete this vehicle?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-danger" title="Delete vehicle" aria-label="Delete {{ $vehicle->category ?: 'vehicle' }}">
                                        <i class="mdi mdi-delete" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </x-datatable>
            </div>
        </div>
    @endif
</div>
