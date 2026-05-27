<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\City;
use App\Models\Project;
use App\Models\Settings\OrganizationSetting;
use App\Models\Settings\PoleNumberFormat;
use App\Models\Settings\SettingsChangeLog;
use App\Models\User;
use App\Models\UserCategory;
use App\Models\Vehicle;
use App\Services\Settings\BillingSettingsService;
use App\Services\Settings\DashboardSettingsService;
use App\Services\Settings\ImportExportSettingsService;
use App\Services\Settings\NotificationSettingsService;
use App\Services\Settings\OrganizationSettingsService;
use App\Services\Settings\PermissionSettingsService;
use App\Services\Settings\PoleNumberFormatService;
use App\Services\Settings\RmsSettingsService;
use App\Services\Settings\VendorEarningsSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    public function __construct(
        private OrganizationSettingsService $organizationSettings,
        private BillingSettingsService $billingSettings,
        private VendorEarningsSettingsService $vendorEarningsSettings,
        private PermissionSettingsService $permissionSettings,
        private NotificationSettingsService $notificationSettings,
        private ImportExportSettingsService $importExportSettings,
        private RmsSettingsService $rmsSettings,
        private DashboardSettingsService $dashboardSettings,
        private PoleNumberFormatService $poleNumberFormats
    ) {
    }

    public function index(Request $request, string $section = 'organization')
    {
        $this->authorize('viewAny', OrganizationSetting::class);

        $sections = $this->sections();
        if (! array_key_exists($section, $sections)) {
            $section = 'organization';
        }

        view()->share('pageHeaderSubtitle', 'Admin settings · ' . $sections[$section]);

        $companyName = $this->organizationSettings->get()->company_name ?? 'CRM';

        return view('settings.index', array_merge(
            [
                'activeSection' => $section,
                'sections' => $sections,
                'settingsPageTitle' => $companyName . ' | Settings · ' . $sections[$section],
            ],
            $this->sectionViewData($section)
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function sectionViewData(string $section): array
    {
        return match ($section) {
            'organization' => [
                'organization' => $this->organizationSettings->get(),
            ],
            'billing' => [
                'billing' => array_merge(
                    $this->billingSettings->data(),
                    ['billingTab' => $this->resolveBillingTab(request()->query('tab'))]
                ),
            ],
            'vendor-earnings' => [
                'vendorEarning' => $this->vendorEarningsSettings->global(),
                'vendorProjectEarnings' => \App\Models\Settings\VendorEarningSetting::with('project')
                    ->whereNotNull('project_id')
                    ->orderBy('project_id')
                    ->get(),
                'projects' => Project::orderBy('project_name')->get(['id', 'project_name']),
            ],
            'permissions' => [
                'permissions' => $this->permissionSettings->matrix(),
                'permissionModules' => PermissionSettingsService::MODULES,
                'permissionActions' => PermissionSettingsService::ACTIONS,
                'roles' => UserRole::cases(),
            ],
            'notifications' => [
                'notificationSettings' => $this->notificationSettings->all(),
                'roles' => UserRole::cases(),
            ],
            'import-export' => [
                'importExportSettings' => $this->importExportSettings->all(),
            ],
            'rms' => [
                'rmsSetting' => $this->rmsSettings->get(),
            ],
            'dashboard' => [
                'dashboardSetting' => $this->dashboardSettings->get(),
                'dashboardWidgets' => DashboardSettingsService::WIDGETS,
            ],
            'pole-number-format' => [
                'poleNumberFormats' => $this->poleNumberFormats->formats(),
                'poleTokenTypes' => PoleNumberFormatService::TOKEN_TYPES,
                'poleTokenDefaults' => PoleNumberFormatService::defaultTokenFormState(),
                'poleFormatEditorPayload' => $this->poleNumberFormats->formats()
                    ->mapWithKeys(function (PoleNumberFormat $format) {
                        $tokens = collect($format->tokens ?? [])
                            ->filter(fn ($token) => ! empty($token['type']))
                            ->keyBy('type');

                        return [
                            $format->id => [
                                'id' => $format->id,
                                'project_id' => $format->project_id,
                                'ward_type' => $format->ward_type,
                                'name' => $format->name,
                                'is_active' => (bool) $format->is_active,
                                'tokens' => $tokens->all(),
                            ],
                        ];
                    })
                    ->all(),
                'projects' => Project::orderBy('project_name')->get(['id', 'project_name']),
                'preview' => session('pole_number_preview'),
                'latestBatch' => $this->latestPoleNumberBatch(session('pole_number_preview')),
            ],
            'audit-log' => [
                'auditLogs' => SettingsChangeLog::with('changedBy')
                    ->latest('changed_at')
                    ->limit(100)
                    ->get(),
            ],
            default => [],
        };
    }

    public function updateOrganization(Request $request)
    {
        $this->authorize('update', OrganizationSetting::class);

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:2000',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,ico|max:1024',
        ]);

        $this->organizationSettings->update(
            collect($validated)->except(['logo', 'favicon'])->all(),
            $request->file('logo'),
            $request->file('favicon'),
            $request->user()->id
        );

        return redirect()->route('settings.section', 'organization')->with('success', 'Organization settings updated.');
    }

    public function storeVehicle(Request $request)
    {
        $this->authorize('update', OrganizationSetting::class);

        $validated = $this->validateBilling($request, [
            'vehicle_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'sub_category' => 'nullable|string|max:255',
            'rate' => 'required|numeric|min:0|max:999999.99',
        ], 'vehicles');

        $this->billingSettings->createVehicle($validated, $request->user()->id);

        return $this->redirectToBilling('Vehicle created.', 'vehicles');
    }

    public function updateVehicle(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', OrganizationSetting::class);

        $validated = $this->validateBilling($request, [
            'vehicle_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'sub_category' => 'nullable|string|max:255',
            'rate' => 'required|numeric|min:0|max:999999.99',
        ], 'vehicles');

        $this->billingSettings->updateVehicle($vehicle, $validated, $request->user()->id);

        return $this->redirectToBilling('Vehicle updated.', 'vehicles');
    }

    public function destroyVehicle(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', OrganizationSetting::class);
        $this->billingSettings->deleteVehicle($vehicle, $request->user()->id);

        return $this->redirectToBilling('Vehicle deleted.', 'vehicles');
    }

    public function storeCategory(Request $request)
    {
        $this->authorize('update', OrganizationSetting::class);

        $validated = $this->validateCategory($request);
        $this->billingSettings->createCategory($validated, $request->user()->id);

        return $this->redirectToBilling('Category created.', 'categories');
    }

    public function updateCategory(Request $request, UserCategory $category)
    {
        $this->authorize('update', OrganizationSetting::class);

        $validated = $this->validateCategory($request);
        $this->billingSettings->updateCategory($category, $validated, $request->user()->id);

        return $this->redirectToBilling('Category updated.', 'categories');
    }

    public function destroyCategory(Request $request, UserCategory $category)
    {
        $this->authorize('update', OrganizationSetting::class);
        $this->billingSettings->deleteCategory($category, $request->user()->id);

        return $this->redirectToBilling('Category deleted.', 'categories');
    }

    public function updateUserCategory(Request $request, User $user)
    {
        $this->authorize('update', OrganizationSetting::class);

        $validated = $this->validateBilling($request, [
            'category_id' => 'required|integer|exists:user_categories,id',
        ], 'users');

        $this->billingSettings->updateUserCategory($user, (int) $validated['category_id'], $request->user()->id);

        return $this->redirectToBilling('User category updated.', 'users');
    }

    public function updateCity(Request $request, City $city)
    {
        $this->authorize('update', OrganizationSetting::class);

        $validated = $this->validateBilling($request, [
            'city_name' => 'required|string|max:255',
            'city_category' => 'required|in:0,1,2',
        ], 'cities');

        $this->billingSettings->updateCity($city, $validated, $request->user()->id);

        return $this->redirectToBilling('City category updated.', 'cities');
    }

    public function updateVendorEarnings(Request $request)
    {
        $this->authorize('update', OrganizationSetting::class);

        $validated = $request->validate([
            'pole_rate' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $this->vendorEarningsSettings->updateGlobal($validated, $request->user()->id);

        return redirect()->route('settings.section', 'vendor-earnings')->with('success', 'Vendor earning settings updated.');
    }

    public function updateProjectVendorEarnings(Request $request)
    {
        $this->authorize('update', OrganizationSetting::class);

        $validated = $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'pole_rate' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $this->vendorEarningsSettings->updateProject($validated, $request->user()->id);

        return redirect()->route('settings.section', 'vendor-earnings')->with('success', 'Project earning override saved.');
    }

    public function updatePermissions(Request $request)
    {
        $this->authorize('update', OrganizationSetting::class);

        $this->permissionSettings->update($request->input('permissions', []), $request->user()->id);

        return redirect()->route('settings.section', 'permissions')->with('success', 'Module permissions updated.');
    }

    public function updateNotifications(Request $request)
    {
        $this->authorize('update', OrganizationSetting::class);

        $this->notificationSettings->update($request->input('settings', []), $request->user()->id);

        return redirect()->route('settings.section', 'notifications')->with('success', 'Notification settings updated.');
    }

    public function updateImportExport(Request $request)
    {
        $this->authorize('update', OrganizationSetting::class);

        $validated = $request->validate([
            'settings' => 'array',
            'settings.*.allowed_file_types' => 'array',
            'settings.*.max_file_size_kb' => 'required|integer|min:1|max:51200',
            'settings.*.sample_format_path' => 'nullable|string|max:255',
        ]);

        $this->importExportSettings->update($validated['settings'] ?? [], $request->user()->id);

        return redirect()->route('settings.section', 'import-export')->with('success', 'Import/export settings updated.');
    }

    public function updateRms(Request $request)
    {
        $this->authorize('update', OrganizationSetting::class);

        $validated = $request->validate([
            'endpoint' => 'nullable|url|max:255',
            'auth_mode' => 'required|in:none,token,basic',
            'enabled' => 'nullable|boolean',
            'timeout_seconds' => 'required|integer|min:1|max:300',
            'retry_count' => 'required|integer|min:0|max:10',
        ]);

        $this->rmsSettings->update($validated, $request->user()->id);

        return redirect()->route('settings.section', 'rms')->with('success', 'RMS settings updated.');
    }

    public function updateDashboard(Request $request)
    {
        $this->authorize('update', OrganizationSetting::class);

        $validated = $request->validate([
            'default_date_filter' => 'required|in:today,this_week,this_month,all_time',
            'default_project_behavior' => 'required|in:first_assigned,all_projects,none',
            'visible_widgets' => 'array',
        ]);

        $this->dashboardSettings->update($validated, $request->user()->id);

        return redirect()->route('settings.section', 'dashboard')->with('success', 'Dashboard settings updated.');
    }

    public function updatePoleNumberFormat(Request $request)
    {
        $this->authorize('update', OrganizationSetting::class);

        $validated = $request->validate([
            'format_id' => 'nullable|integer|exists:pole_number_formats,id',
            'project_id' => 'nullable|integer|exists:projects,id',
            'ward_type' => 'required|in:normal,gp',
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'tokens' => 'array',
        ]);

        $format = $this->poleNumberFormats->update($validated, $request->user()->id);

        return redirect()
            ->route('settings.section', 'pole-number-format')
            ->with('success', "Pole number format saved for {$format->ward_type} wards.");
    }

    public function previewPoleNumberFormat(Request $request, PoleNumberFormat $format)
    {
        $this->authorize('update', OrganizationSetting::class);

        return redirect()
            ->route('settings.section', 'pole-number-format')
            ->with('pole_number_preview', [
                'format_id' => $format->id,
                'format_name' => $format->name,
                ...$this->poleNumberFormats->preview($format),
            ]);
    }

    public function applyPoleNumberFormat(Request $request, PoleNumberFormat $format)
    {
        $this->authorize('update', OrganizationSetting::class);

        $batch = $this->poleNumberFormats->createRegenerationBatch($format, $request->user()->id);

        return redirect()
            ->route('settings.section', 'pole-number-format')
            ->with('success', $this->poleRegenerationFlashMessage($batch));
    }

    private function poleRegenerationFlashMessage(\App\Models\Settings\PoleNumberRegenerationBatch $batch): string
    {
        return match ($batch->status) {
            'completed' => "Pole numbers updated for {$batch->processed_count} of {$batch->affected_count} pole(s). Check your notifications for confirmation.",
            'failed' => 'Pole number update failed: '.($batch->error_message ?? 'Unknown error'),
            'running' => "Pole number update is running ({$batch->processed_count}/{$batch->affected_count}). Refresh this page for progress.",
            default => "Pole number update queued for {$batch->affected_count} pole(s). Ensure a queue worker is running (`php artisan queue:work`). You will be notified when it completes.",
        };
    }

    private function latestPoleNumberBatch(?array $preview): ?\App\Models\Settings\PoleNumberRegenerationBatch
    {
        if (! empty($preview['format_id'])) {
            return \App\Models\Settings\PoleNumberRegenerationBatch::query()
                ->where('pole_number_format_id', $preview['format_id'])
                ->latest()
                ->first();
        }

        return null;
    }

    private function validateCategory(Request $request): array
    {
        return $this->validateBilling($request, [
            'category_code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'vehicle_ids' => 'required|array|min:1',
            'vehicle_ids.*' => 'integer|exists:vehicles,id',
            'city_category' => 'required|in:0,1,2',
            'daily_amount' => 'required|numeric|min:0|max:999999.99',
        ], 'categories');
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function validateBilling(Request $request, array $rules, string $tab): array
    {
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw (new ValidationException($validator))
                ->redirectTo(route('settings.section', [
                    'section' => 'billing',
                    'tab' => $this->resolveBillingTab($tab),
                ]));
        }

        return $validator->validated();
    }

    private function resolveBillingTab(?string $tab): string
    {
        return in_array($tab, ['vehicles', 'categories', 'users', 'cities'], true) ? $tab : 'vehicles';
    }

    private function redirectToBilling(string $message, string $tab)
    {
        return redirect()
            ->route('settings.section', ['section' => 'billing', 'tab' => $tab])
            ->with('success', $message);
    }

    private function sections(): array
    {
        return [
            'organization' => 'Organization',
            'billing' => 'Billing / TA-DA',
            'vendor-earnings' => 'Vendor Earnings',
            'permissions' => 'Permissions',
            'notifications' => 'Notifications',
            'import-export' => 'Import / Export',
            'rms' => 'RMS Integration',
            'dashboard' => 'Dashboard',
            'pole-number-format' => 'Pole Number Format',
            'audit-log' => 'Audit Log',
        ];
    }
}
