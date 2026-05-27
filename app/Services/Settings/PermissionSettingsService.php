<?php

namespace App\Services\Settings;

use App\Enums\UserRole;
use App\Models\Settings\ModulePermission;

class PermissionSettingsService
{
    public const MODULES = ['dashboard', 'projects', 'sites', 'tasks', 'inventory', 'staff', 'vendors', 'billing', 'meetings', 'performance', 'hrm', 'rms', 'backup', 'settings'];
    public const ACTIONS = ['can_view', 'can_create', 'can_update', 'can_delete', 'can_export'];

    public function __construct(private SettingsAuditService $audit)
    {
    }

    public function matrix()
    {
        $this->ensureDefaults();

        return ModulePermission::orderBy('role')
            ->orderBy('module_key')
            ->get()
            ->groupBy('role');
    }

    public function can(UserRole|int|string $role, string $module, string $action): bool
    {
        $roleValue = $role instanceof UserRole ? $role->value : (int) $role;
        if ($roleValue === UserRole::ADMIN->value) {
            return true;
        }

        $column = str_starts_with($action, 'can_') ? $action : 'can_' . $action;
        if (!in_array($column, self::ACTIONS, true)) {
            return false;
        }

        return (bool) ModulePermission::where('role', $roleValue)
            ->where('module_key', $module)
            ->value($column);
    }

    public function update(array $permissions, ?int $userId): void
    {
        $this->ensureDefaults();
        $before = ModulePermission::all()->mapWithKeys(fn ($permission) => [
            $permission->role . ':' . $permission->module_key => $permission->only(self::ACTIONS),
        ])->toArray();

        foreach (UserRole::cases() as $role) {
            foreach (self::MODULES as $module) {
                $data = [];
                foreach (self::ACTIONS as $action) {
                    $data[$action] = (bool) data_get($permissions, "{$role->value}.{$module}.{$action}", false);
                }

                if ($role === UserRole::ADMIN) {
                    $data = array_fill_keys(self::ACTIONS, true);
                }

                ModulePermission::updateOrCreate(
                    ['role' => $role->value, 'module_key' => $module],
                    $data
                );
            }
        }

        $after = ModulePermission::all()->mapWithKeys(fn ($permission) => [
            $permission->role . ':' . $permission->module_key => $permission->only(self::ACTIONS),
        ])->toArray();

        $this->audit->log('permissions', 'matrix', $before, $after, $userId);
    }

    public function ensureDefaults(): void
    {
        foreach (UserRole::cases() as $role) {
            foreach (self::MODULES as $module) {
                ModulePermission::firstOrCreate(
                    ['role' => $role->value, 'module_key' => $module],
                    array_fill_keys(self::ACTIONS, $role === UserRole::ADMIN)
                );
            }
        }
    }
}
