@php
    $billingData = $billing ?? [];
    $vehicles = $billingData['vehicles'] ?? collect();
    $categories = $billingData['categories'] ?? collect();
    $users = $billingData['users'] ?? collect();
    $cities = $billingData['cities'] ?? collect();
    $billingTab = $billingData['billingTab'] ?? 'vehicles';
    $cityLabels = ['0' => 'Tier-3', '1' => 'Non-Metro', '2' => 'Metro'];
    $billingDecoder = app(\App\Services\Settings\BillingSettingsService::class);

    $billingTabs = [
        'vehicles'   => ['label' => 'Vehicles',          'icon' => 'bi-car-front'],
        'categories' => ['label' => 'Staff categories',  'icon' => 'bi-tags'],
        'users'      => ['label' => 'User assignment',   'icon' => 'bi-people'],
        'cities'     => ['label' => 'City tiers',        'icon' => 'bi-building'],
    ];

    $billingTabActions = [
        'vehicles'   => ['modal' => 'addVehicleModal',  'label' => 'Add vehicle'],
        'categories' => ['modal' => 'addCategoryModal', 'label' => 'Add category'],
    ];
@endphp

<div class="settings-card billing-settings mb-3">
    <div class="settings-card-header">
        <h4 class="mb-1">Billing / TA-DA Settings</h4>
        <p class="text-muted small mb-0">Manage vehicles, staff categories, user assignments, and city tiers.</p>
    </div>

    <nav class="billing-tabs-nav" aria-label="Billing settings sections">
        <ul class="nav nav-tabs" id="billingSettingsTabs" role="tablist">
            @foreach ($billingTabs as $tabKey => $tabMeta)
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $billingTab === $tabKey ? 'active' : '' }}"
                       href="{{ route('settings.section', ['section' => 'billing', 'tab' => $tabKey]) }}"
                       role="tab"
                       @if ($billingTab === $tabKey) aria-current="page" @endif>
                        <i class="bi {{ $tabMeta['icon'] }} me-1" aria-hidden="true"></i>{{ $tabMeta['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
        @if (isset($billingTabActions[$billingTab]))
            @php $tabAction = $billingTabActions[$billingTab]; @endphp
            <div class="billing-tab-action-wrap">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#{{ $tabAction['modal'] }}">
                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>{{ $tabAction['label'] }}
                </button>
            </div>
        @endif
    </nav>

    <div class="billing-tab-panel">
        @include('settings.partials.billing.' . $billingTab . '-tab')
    </div>
</div>

@include('settings.partials.billing.modals')

@push('scripts')
    @include('settings.partials.billing.scripts')
@endpush
