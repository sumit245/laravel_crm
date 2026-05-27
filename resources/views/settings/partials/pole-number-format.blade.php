@php
    $poleTokenDefaults = $poleTokenDefaults ?? \App\Services\Settings\PoleNumberFormatService::defaultTokenFormState();
    $poleFormatEditorPayload = $poleFormatEditorPayload ?? [];
    $previewSampleCount = $preview ? count($preview['samples'] ?? []) : 0;
    $previewAffectedCount = (int) ($preview['affected_count'] ?? 0);
@endphp

<div class="settings-card pole-number-settings">
    <div class="settings-card-header">
        <h4 class="mb-1">Pole Number Format</h4>
        <p class="text-muted mb-0">Configure how complete pole numbers are built for normal and GP wards. Preview changes before applying them to existing poles.</p>
    </div>

    <div class="settings-card-body">
        @if($latestBatch)
            @php
                $batchAlertClass = match ($latestBatch->status) {
                    'completed' => 'alert-success',
                    'failed' => 'alert-danger',
                    'running' => 'alert-warning',
                    default => 'alert-info',
                };
            @endphp
            <div class="alert {{ $batchAlertClass }} d-flex flex-wrap align-items-center gap-2 mb-3" role="status">
                <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                <span>
                    Latest regeneration for this format:
                    <strong>{{ ucfirst($latestBatch->status) }}</strong>
                    ({{ number_format($latestBatch->processed_count) }}/{{ number_format($latestBatch->affected_count) }} poles)
                    @if($latestBatch->status === 'pending')
                        — waiting for queue worker. Run <code>php artisan queue:work</code> or click Apply again after the fix is deployed.
                    @endif
                    @if($latestBatch->error_message)
                        — {{ $latestBatch->error_message }}
                    @endif
                    @if($latestBatch->status === 'completed')
                        — in-app notification sent to admins and the user who applied the update.
                    @endif
                </span>
            </div>
        @endif

        @if($preview)
            <div class="alert alert-warning pole-format-preview-panel mb-4" role="region" aria-labelledby="polePreviewHeading">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-2">
                    <div>
                        <h5 class="alert-heading h6 mb-1" id="polePreviewHeading">
                            Preview: {{ $preview['format_name'] }}
                        </h5>
                        <p class="mb-0 small">
                            <strong>{{ number_format($previewAffectedCount) }}</strong> pole(s) match this format.
                            @if($previewSampleCount > 0)
                                Showing up to <strong>{{ $previewSampleCount }}</strong> of {{ number_format($previewAffectedCount) }} below.
                            @else
                                No sample rows to display.
                            @endif
                        </p>
                    </div>
                    <form
                        id="poleFormatApplyForm"
                        class="pole-format-apply-form flex-shrink-0"
                        action="{{ route('settings.pole-number-format.apply', $preview['format_id']) }}"
                        method="POST"
                        data-format-name="{{ $preview['format_name'] }}"
                        data-affected-count="{{ $previewAffectedCount }}"
                    >
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">
                            Apply update
                        </button>
                    </form>
                </div>
                <div class="table-responsive settings-datatable-wrap">
                    <table class="table table-sm table-striped table-bordered table-hover settings-table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Pole ID</th>
                                <th scope="col">Current</th>
                                <th scope="col">New</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($preview['samples'] as $sample)
                                <tr>
                                    <td>{{ $sample['pole_id'] }}</td>
                                    <td><code class="pole-format-code">{{ $sample['old'] ?: '—' }}</code></td>
                                    <td><code class="pole-format-code">{{ $sample['new'] ?: '—' }}</code></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-muted">No poles matched this format.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <h5 class="pole-section-title">Saved formats</h5>
        <p class="text-muted small mb-2">
            One active format per project scope and ward type (Normal vs GP). Global project applies to all streetlight projects.
        </p>
        <div class="table-responsive settings-datatable-wrap mb-4">
            <table class="table table-sm table-striped table-bordered table-hover settings-table mb-0">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Project</th>
                        <th scope="col">Ward type</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($poleNumberFormats as $format)
                        @php
                            $isFormatActive = filter_var($format->is_active, FILTER_VALIDATE_BOOLEAN);
                            $wardTypeKey = strtolower((string) $format->ward_type);
                            $wardTypeLabel = $wardTypeKey === 'gp' ? 'GP' : 'NORMAL';
                        @endphp
                        <tr>
                            <td class="fw-medium">{{ $format->name }}</td>
                            <td>{{ $format->project?->project_name ?? 'Global' }}</td>
                            <td>
                                <span class="settings-scope-badge {{ $wardTypeKey === 'gp' ? 'settings-scope-badge-ward-gp' : 'settings-scope-badge-ward-normal' }}">
                                    {{ $wardTypeLabel }}
                                </span>
                            </td>
                            <td>
                                @if($isFormatActive)
                                    <span class="settings-scope-badge settings-scope-badge-active" title="Used when generating pole numbers for {{ $wardTypeLabel }} wards">
                                        Active
                                    </span>
                                @else
                                    <span class="settings-scope-badge settings-scope-badge-inactive">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="settings-row-actions">
                                <button
                                    type="button"
                                    class="btn btn-icon btn-warning pole-format-edit-btn"
                                    title="Edit format"
                                    aria-label="Edit {{ $format->name }}"
                                    data-format-id="{{ $format->id }}"
                                >
                                    <i class="mdi mdi-pencil" aria-hidden="true"></i>
                                </button>
                                <form action="{{ route('settings.pole-number-format.preview', $format) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="btn btn-icon btn-info"
                                        title="Preview format"
                                        aria-label="Preview {{ $format->name }}"
                                    >
                                        <i class="mdi mdi-eye" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted py-4 text-center">
                                No pole number formats saved yet. Use the form below to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pole-format-editor-divider"></div>

        <form id="poleFormatEditorForm" action="{{ route('settings.pole-number-format.update') }}" method="POST">
            @csrf
            <h5 class="pole-section-title mb-3" id="poleFormatEditorHeading">Add / update format</h5>
            <div class="row g-3 mb-3">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label" for="pole_format_id">Existing format</label>
                    <select name="format_id" id="pole_format_id" class="form-select">
                        <option value="">Create new</option>
                        @foreach($poleNumberFormats as $format)
                            <option value="{{ $format->id }}">
                                {{ $format->name }} ({{ $format->project?->project_name ?? 'Global' }} / {{ strtoupper($format->ward_type) }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Choose a format to load its tokens into this form.</div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label" for="pole_format_project_id">Project</label>
                    <select name="project_id" id="pole_format_project_id" class="form-select">
                        <option value="">Global</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label" for="pole_format_ward_type">Ward type</label>
                    <select name="ward_type" id="pole_format_ward_type" class="form-select" required>
                        <option value="normal">Normal</option>
                        <option value="gp">GP</option>
                    </select>
                </div>
                <div class="col-md-8 col-lg-3">
                    <label class="form-label" for="pole_format_name">Name</label>
                    <input type="text" name="name" id="pole_format_name" class="form-control" value="Custom Pole Format" required>
                </div>
                <div class="col-md-4 col-lg-1 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input type="hidden" name="is_active" value="0">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            name="is_active"
                            id="pole_format_is_active"
                            value="1"
                            checked
                        >
                        <label class="form-check-label" for="pole_format_is_active">Active</label>
                    </div>
                </div>
            </div>

            <p class="text-muted small mb-2">
                Enable parts in order. <strong>Max chars</strong> truncates location names; <strong>zero pad</strong> applies to ward and pole sequence numbers.
            </p>

            <div class="table-responsive pole-token-table-wrap">
                <table class="table table-sm pole-token-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col" class="pole-token-col-use">Use</th>
                            <th scope="col">Part</th>
                            <th scope="col">Fixed text</th>
                            <th scope="col" class="pole-token-col-num">Max chars</th>
                            <th scope="col" class="pole-token-col-num">Zero pad</th>
                            <th scope="col">Separator after</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($poleTokenTypes as $type)
                            @php
                                $partLabel = ucwords(str_replace('_', ' ', $type));
                                $defaults = $poleTokenDefaults[$type] ?? [];
                            @endphp
                            <tr>
                                <td class="text-center">
                                    <div class="form-check mb-0 pole-token-check-wrap">
                                        <input
                                            type="checkbox"
                                            class="form-check-input pole-token-check"
                                            name="tokens[{{ $type }}][enabled]"
                                            id="pole_token_enabled_{{ $type }}"
                                            value="1"
                                            @checked($defaults['enabled'] ?? false)
                                            aria-label="Include {{ $partLabel }} in pole number"
                                        >
                                    </div>
                                </td>
                                <th scope="row" class="pole-token-part-label">{{ $partLabel }}</th>
                                <td>
                                    <label class="visually-hidden" for="pole_token_value_{{ $type }}">Fixed text for {{ $partLabel }}</label>
                                    <input
                                        type="text"
                                        name="tokens[{{ $type }}][value]"
                                        id="pole_token_value_{{ $type }}"
                                        class="form-control form-control-sm"
                                        value="{{ $defaults['value'] ?? '' }}"
                                    >
                                </td>
                                <td>
                                    <label class="visually-hidden" for="pole_token_length_{{ $type }}">Max characters for {{ $partLabel }}</label>
                                    <input
                                        type="number"
                                        min="1"
                                        max="20"
                                        name="tokens[{{ $type }}][length]"
                                        id="pole_token_length_{{ $type }}"
                                        class="form-control form-control-sm"
                                        value="{{ $defaults['length'] ?? 3 }}"
                                    >
                                </td>
                                <td>
                                    <label class="visually-hidden" for="pole_token_pad_{{ $type }}">Zero pad for {{ $partLabel }}</label>
                                    <input
                                        type="number"
                                        min="0"
                                        max="10"
                                        name="tokens[{{ $type }}][pad]"
                                        id="pole_token_pad_{{ $type }}"
                                        class="form-control form-control-sm"
                                        value="{{ $defaults['pad'] ?? 0 }}"
                                    >
                                </td>
                                <td>
                                    <label class="visually-hidden" for="pole_token_separator_{{ $type }}">Separator after {{ $partLabel }}</label>
                                    <input
                                        type="text"
                                        name="tokens[{{ $type }}][separator_after]"
                                        id="pole_token_separator_{{ $type }}"
                                        class="form-control form-control-sm"
                                        value="{{ $defaults['separator_after'] ?? '' }}"
                                        maxlength="5"
                                    >
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pole-format-footer">
                <button type="submit" class="btn btn-primary" id="poleFormatSaveBtn">Save format</button>
                <button type="button" class="btn btn-outline-secondary d-none" id="poleFormatResetBtn">Reset form</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .pole-number-settings .pole-section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #212529;
    }

    .pole-number-settings .pole-format-editor-divider {
        border-top: 1px solid #dee2e6;
        margin: 1.5rem 0;
    }

    .pole-token-table-wrap {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        overflow: hidden;
    }

    .pole-token-table thead th {
        background: #f8f9fa;
        color: #495057;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }

    .pole-token-table td,
    .pole-token-table th[scope="row"] {
        vertical-align: middle;
    }

    .pole-format-code {
        font-size: 0.8125rem;
        color: #212529;
        word-break: break-all;
    }

    .pole-format-preview-panel .settings-datatable-wrap {
        border-color: #ffecb5;
    }

    .pole-token-col-use {
        width: 3.5rem;
    }

    .pole-token-col-num {
        width: 5.5rem;
    }

    .pole-token-check-wrap {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .pole-token-check {
        width: 1rem;
        height: 1rem;
        margin: 0;
        cursor: pointer;
        border-color: #adb5bd;
    }

    .pole-token-check:checked {
        background-color: #1F3BB3;
        border-color: #1F3BB3;
    }

    .pole-token-check:focus {
        border-color: #1F3BB3;
        box-shadow: 0 0 0 0.2rem rgba(31, 59, 179, 0.2);
    }

    .pole-token-part-label {
        font-size: 0.9rem;
        font-weight: 500;
        color: #212529;
        white-space: nowrap;
    }

    .pole-token-table .form-control-sm {
        min-width: 4.5rem;
    }

    .pole-format-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
        padding-top: 1.25rem;
        margin-top: 1.25rem;
        border-top: 1px solid #dee2e6;
    }

    @media (max-width: 767.98px) {
        .pole-token-table .form-control-sm {
            min-width: 3.25rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const poleFormatsById = @json($poleFormatEditorPayload);
    const poleTokenDefaults = @json($poleTokenDefaults);
    const tokenTypes = @json($poleTokenTypes);

    const form = document.getElementById('poleFormatEditorForm');
    const formatSelect = document.getElementById('pole_format_id');
    const heading = document.getElementById('poleFormatEditorHeading');
    const saveBtn = document.getElementById('poleFormatSaveBtn');
    const resetBtn = document.getElementById('poleFormatResetBtn');
    const applyForm = document.getElementById('poleFormatApplyForm');

    function setFieldValue(id, value) {
        const el = document.getElementById(id);
        if (el) {
            el.value = value ?? '';
        }
    }

    function fillTokenRow(type, token) {
        const enabledEl = document.getElementById('pole_token_enabled_' + type);
        if (enabledEl) {
            enabledEl.checked = !!token.enabled;
        }
        setFieldValue('pole_token_value_' + type, token.value ?? '');
        setFieldValue('pole_token_length_' + type, token.length ?? 3);
        setFieldValue('pole_token_pad_' + type, token.pad ?? 0);
        setFieldValue('pole_token_separator_' + type, token.separator_after ?? '');
    }

    function applyFormatToForm(format) {
        if (!format) {
            formatSelect.value = '';
            setFieldValue('pole_format_project_id', '');
            setFieldValue('pole_format_ward_type', 'normal');
            setFieldValue('pole_format_name', 'Custom Pole Format');
            const activeEl = document.getElementById('pole_format_is_active');
            if (activeEl) {
                activeEl.checked = true;
            }
            tokenTypes.forEach(function (type) {
                fillTokenRow(type, poleTokenDefaults[type] || {});
            });
            heading.textContent = 'Add / update format';
            saveBtn.textContent = 'Save format';
            resetBtn.classList.add('d-none');
            return;
        }

        formatSelect.value = String(format.id);
        setFieldValue('pole_format_project_id', format.project_id ?? '');
        setFieldValue('pole_format_ward_type', format.ward_type || 'normal');
        setFieldValue('pole_format_name', format.name || '');
        const activeEl = document.getElementById('pole_format_is_active');
        if (activeEl) {
            activeEl.checked = !!format.is_active;
        }

        tokenTypes.forEach(function (type) {
            const token = (format.tokens && format.tokens[type])
                ? format.tokens[type]
                : (poleTokenDefaults[type] || {});
            fillTokenRow(type, token);
        });

        heading.textContent = 'Edit format: ' + (format.name || '');
        saveBtn.textContent = 'Update format';
        resetBtn.classList.remove('d-none');
    }

    if (formatSelect) {
        formatSelect.addEventListener('change', function () {
            const id = this.value;
            if (!id) {
                applyFormatToForm(null);
                return;
            }
            applyFormatToForm(poleFormatsById[id] || null);
        });
    }

    document.querySelectorAll('.pole-format-edit-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-format-id');
            if (!id) {
                return;
            }
            applyFormatToForm(poleFormatsById[id] || null);
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            applyFormatToForm(null);
        });
    }

    if (applyForm && typeof Swal !== 'undefined') {
        applyForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const formatName = applyForm.dataset.formatName || 'this format';
            const affected = applyForm.dataset.affectedCount || '0';

            Swal.fire({
                title: 'Apply pole number update?',
                html: 'This will regenerate pole numbers for <strong>' + affected + '</strong> pole(s) using <strong>' + formatName + '</strong>. Existing complete pole numbers will be overwritten.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1F3BB3',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, apply update',
                cancelButtonText: 'Cancel',
            }).then(function (result) {
                if (result.isConfirmed) {
                    applyForm.submit();
                }
            });
        });
    }

    @if($preview && !empty($preview['format_id']))
        (function () {
            const previewFormatId = @json($preview['format_id']);
            if (previewFormatId && poleFormatsById[previewFormatId]) {
                applyFormatToForm(poleFormatsById[previewFormatId]);
            }
        })();
    @endif
});
</script>
@endpush
