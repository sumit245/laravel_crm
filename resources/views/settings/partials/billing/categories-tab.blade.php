<div id="billing-tab-categories" class="billing-tab-pane" role="tabpanel" aria-labelledby="billing-tab-categories-label">
    <div class="billing-section-toolbar">
        <div>
            <h5 id="billing-tab-categories-label" class="billing-section-title mb-1">Staff categories</h5>
            <p class="text-muted small mb-0">Daily allowance rules by category and city tier.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal" @disabled($vehicles->isEmpty())>
            <i class="mdi mdi-plus me-1" aria-hidden="true"></i>Add category
        </button>
    </div>

    @if ($vehicles->isEmpty())
        <div class="alert alert-warning" role="alert">
            Add vehicles on the Vehicles tab before defining staff categories.
        </div>
    @endif

    @if ($categories->isEmpty())
        <div class="card billing-data-card border shadow-sm">
            <div class="card-body">
                @include('settings.partials.billing._empty-state', [
                    'title' => 'No staff categories',
                    'message' => 'Categories control daily TA/DA amounts and which vehicles staff may use.',
                    'actionLabel' => $vehicles->isNotEmpty() ? 'Add category' : null,
                    'actionTarget' => $vehicles->isNotEmpty() ? 'addCategoryModal' : null,
                ])
            </div>
        </div>
    @else
        <div class="card billing-data-card border shadow-sm">
            <div class="card-body">
                <x-datatable
                    id="billingCategoryTable"
                    title="Staff categories"
                    :columns="[
                        ['title' => 'Code', 'width' => '12%'],
                        ['title' => 'Name', 'width' => '18%'],
                        ['title' => 'Vehicles allowed', 'width' => '30%'],
                        ['title' => 'City tier', 'width' => '12%'],
                        ['title' => 'Daily amount (₹)', 'width' => '14%'],
                    ]"
                    :exportEnabled="true"
                    :importEnabled="false"
                    :bulkDeleteEnabled="false"
                    pageLength="25"
                    searchPlaceholder="Search categories…">
                    @foreach ($categories as $category)
                        @php
                            $allowedIds = $billingDecoder->decodeAllowedVehicles($category->allowed_vehicles);
                            $vehicleLabels = $vehicles->whereIn('id', $allowedIds)->map(fn ($v) => $v->category.' — '.$v->vehicle_name)->implode(', ');
                        @endphp
                        <tr>
                            <td>{{ $category->category_code }}</td>
                            <td class="billing-cell-truncate" title="{{ $category->name }}">{{ $category->name }}</td>
                            <td class="billing-cell-truncate" title="{{ $vehicleLabels }}">{{ $vehicleLabels ?: '—' }}</td>
                            <td>{{ $cityLabels[(string) $category->city_category] ?? $category->city_category }}</td>
                            <td>{{ number_format((float) $category->dailyamount, 2) }}</td>
                            <td class="text-center billing-row-actions">
                                <button type="button"
                                    class="btn btn-icon btn-warning btn-edit-category"
                                    title="Edit category"
                                    aria-label="Edit category {{ $category->category_code }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editCategoryModal"
                                    data-action="{{ route('settings.billing.categories.update', $category) }}"
                                    data-category-code="{{ $category->category_code }}"
                                    data-name="{{ $category->name }}"
                                    data-description="{{ $category->description }}"
                                    data-city-category="{{ $category->city_category }}"
                                    data-daily-amount="{{ $category->dailyamount }}"
                                    data-vehicle-ids="{{ json_encode($allowedIds) }}">
                                    <i class="mdi mdi-pencil" aria-hidden="true"></i>
                                </button>
                                <form action="{{ route('settings.billing.categories.destroy', $category) }}" method="POST" class="d-inline billing-delete-form"
                                    onsubmit="return confirm('Delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-danger" title="Delete category" aria-label="Delete category {{ $category->category_code }}">
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
