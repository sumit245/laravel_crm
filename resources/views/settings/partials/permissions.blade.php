<div class="settings-card">
    <div class="settings-card-header">
        <h4 class="mb-1">Module Permissions</h4>
        <p class="text-muted mb-0">Control which roles can view, create, edit, and delete each module. Administrator permissions are always on and cannot be changed.</p>
    </div>
    <form class="settings-card-body" action="{{ route('settings.permissions.update') }}" method="POST">
        @csrf
        <div class="table-responsive permissions-table-wrap">
            <table class="table table-sm permissions-table mb-0">
                <thead>
                    <tr>
                        <th class="perm-col-role">Role</th>
                        <th class="perm-col-module">Module</th>
                        @foreach($permissionActions as $action)
                            <th class="perm-col-action text-center">
                                {{ ucwords(str_replace(['can_', '_'], ['', ' '], $action)) }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        @php
                            $rolePermissions = ($permissions[$role->value] ?? collect())->keyBy('module_key');
                            $isAdmin = $role === \App\Enums\UserRole::ADMIN;
                            $moduleCount = count($permissionModules);
                        @endphp
                        <tr class="perm-role-divider {{ $isAdmin ? 'perm-role-admin' : '' }}">
                            <td colspan="{{ 2 + count($permissionActions) }}" class="perm-role-header-cell">
                                @if($isAdmin)
                                    <i class="bi bi-shield-fill-check me-1 text-muted" aria-hidden="true"></i>
                                @else
                                    <i class="bi bi-person-fill me-1 text-muted" aria-hidden="true"></i>
                                @endif
                                <span class="fw-semibold">{{ $role->label() }}</span>
                                @if($isAdmin)
                                    <span class="badge perm-badge-locked ms-2">
                                        <i class="bi bi-lock-fill me-1" aria-hidden="true"></i>Protected
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @foreach($permissionModules as $module)
                            @php $permission = $rolePermissions[$module] ?? null; @endphp
                            <tr class="{{ $isAdmin ? 'perm-row-admin' : 'perm-row' }}">
                                <td class="perm-col-role perm-indent"></td>
                                <td class="perm-col-module perm-module-name">
                                    {{ ucwords(str_replace('_', ' ', $module)) }}
                                </td>
                                @foreach($permissionActions as $action)
                                    <td class="perm-col-action text-center">
                                        <input type="hidden"
                                            name="permissions[{{ $role->value }}][{{ $module }}][{{ $action }}]"
                                            value="0">
                                        <div class="form-check form-check-inline mb-0 perm-check-wrap">
                                            <input class="form-check-input perm-check {{ $isAdmin ? 'perm-check-locked' : '' }}"
                                                type="checkbox"
                                                id="perm_{{ $role->value }}_{{ $module }}_{{ $action }}"
                                                name="permissions[{{ $role->value }}][{{ $module }}][{{ $action }}]"
                                                value="1"
                                                @checked($isAdmin || ($permission && $permission->{$action}))
                                                @disabled($isAdmin)
                                                aria-label="{{ $role->label() }} – {{ ucwords(str_replace('_', ' ', $module)) }} – {{ ucwords(str_replace(['can_','_'], ['', ' '], $action)) }}">
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="permissions-footer">
            <button type="submit" class="btn btn-primary">Save Permissions</button>
        </div>
    </form>
</div>

@push('styles')
<style>
    /* ── Permissions table ── */
    .permissions-table-wrap {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        overflow: hidden;
    }

    .permissions-table thead th {
        background: #f8f9fa;
        color: #495057;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 2px solid #dee2e6;
        padding: 0.625rem 0.75rem;
        white-space: nowrap;
    }

    .permissions-table .perm-col-role  { width: 0.5rem; min-width: 0; padding: 0 !important; }
    .permissions-table .perm-col-module { min-width: 140px; }
    .permissions-table .perm-col-action { min-width: 72px; }

    /* Role-group divider row */
    .permissions-table .perm-role-divider td {
        background: #f1f3f9;
        border-top: 2px solid #dee2e6;
        padding: 0.45rem 0.875rem;
        font-size: 0.875rem;
        color: #495057;
        line-height: 1.4;
    }
    .permissions-table .perm-role-divider:first-child td {
        border-top: none;
    }

    .permissions-table .perm-role-admin td {
        background: #f8f9fa;
    }

    .perm-badge-locked {
        background: rgba(108, 117, 125, 0.12);
        color: #6c757d;
        font-weight: 500;
        font-size: 0.7rem;
        letter-spacing: 0.02em;
        padding: 0.2em 0.55em;
        border-radius: 3px;
    }

    /* Module rows */
    .permissions-table .perm-row td,
    .permissions-table .perm-row-admin td {
        padding: 0.45rem 0.75rem;
        border-color: #f0f1f3;
        vertical-align: middle;
    }

    .permissions-table .perm-indent {
        border-right: none !important;
    }

    .permissions-table .perm-module-name {
        font-size: 0.9rem;
        color: #212529;
        padding-left: 1.5rem !important;
    }

    .permissions-table .perm-row-admin td {
        opacity: 0.7;
    }

    /* Checkbox cell */
    .perm-check-wrap {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0;
    }

    .perm-check {
        cursor: pointer;
        width: 1rem;
        height: 1rem;
        margin: 0;
        border-color: #adb5bd;
        border-radius: 3px;
    }

    .perm-check:checked {
        background-color: #1F3BB3;
        border-color: #1F3BB3;
    }

    .perm-check:focus {
        border-color: #1F3BB3;
        box-shadow: 0 0 0 0.2rem rgba(31, 59, 179, 0.2);
    }

    .perm-check-locked {
        cursor: not-allowed;
        opacity: 0.55;
        pointer-events: none;
    }

    /* Footer */
    .permissions-footer {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding-top: 1.25rem;
        margin-top: 1.25rem;
        border-top: 1px solid #dee2e6;
    }
</style>
@endpush
