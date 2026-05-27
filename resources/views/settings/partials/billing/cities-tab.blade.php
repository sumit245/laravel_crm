<div id="billing-tab-cities" class="billing-tab-pane" role="tabpanel">
    <p class="text-muted small billing-tab-description mb-3 tier-legend">
        <strong>Tier-3</strong> · <strong>Non-Metro</strong> · <strong>Metro</strong> — {{ $cities->count() }} cities total.
    </p>

    <div class="card billing-data-card border shadow-sm">
        <div class="card-body">
            <x-datatable
                id="billingCityTable"
                title="Cities"
                :columns="[
                    ['title' => 'City', 'width' => '45%'],
                    ['title' => 'Tier', 'width' => '35%'],
                ]"
                :exportEnabled="true"
                :importEnabled="false"
                :bulkDeleteEnabled="false"
                pageLength="50"
                searchPlaceholder="Search cities…">
        @foreach ($cities as $city)
            <tr>
                <td class="billing-cell-truncate" title="{{ $city->name }}">{{ $city->name }}</td>
                <td>{{ $cityLabels[(string) $city->category] ?? $city->category }}</td>
                <td class="text-center billing-row-actions">
                    <button type="button"
                        class="btn btn-icon btn-warning btn-edit-city"
                        title="Edit city tier"
                        aria-label="Edit tier for {{ $city->name }}"
                                data-bs-toggle="modal"
                                data-bs-target="#editCityModal"
                                data-action="{{ route('settings.billing.cities.update', $city) }}"
                                data-city-name="{{ $city->name }}"
                                data-city-category="{{ $city->category }}">
                                <i class="mdi mdi-pencil"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </x-datatable>
        </div>
    </div>
</div>
