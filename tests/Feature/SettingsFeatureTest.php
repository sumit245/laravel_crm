<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\City;
use App\Models\Project;
use App\Models\Settings\ModulePermission;
use App\Models\Settings\NotificationSetting;
use App\Models\Settings\OrganizationSetting;
use App\Models\Settings\SettingsChangeLog;
use App\Models\Settings\VendorEarningSetting;
use App\Models\User;
use App\Models\UserCategory;
use App\Models\Vehicle;
use App\Services\Settings\VendorEarningsSettingsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsFeatureTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'settings_change_logs',
            'role_has_permissions',
            'model_has_roles',
            'model_has_permissions',
            'roles',
            'permissions',
            'dashboard_settings',
            'rms_settings',
            'import_export_settings',
            'notification_settings',
            'module_permissions',
            'vendor_earning_settings',
            'organization_settings',
            'cities',
            'user_categories',
            'vehicles',
            'projects',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->unsignedBigInteger('category')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name')->nullable();
            $table->integer('project_type')->default(1);
            $table->decimal('rate', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_name')->nullable();
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('user_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_code')->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->longText('allowed_vehicles')->nullable();
            $table->decimal('dailyamount', 10, 2)->nullable();
            $table->string('city_category')->nullable();
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->unsignedBigInteger('user_category_id')->nullable();
            $table->timestamps();
        });

        Schema::create('organization_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('legal_name')->nullable();
            $table->string('gst_number')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->timestamps();
        });

        Schema::create('vendor_earning_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->decimal('pole_rate', 12, 2)->default(500);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('module_permissions', function (Blueprint $table) {
            $table->id();
            $table->integer('role');
            $table->string('module_key');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_export')->default(false);
            $table->timestamps();
        });

        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique();
            $table->boolean('enabled')->default(true);
            $table->json('recipient_roles')->nullable();
            $table->boolean('database_enabled')->default(true);
            $table->boolean('mail_enabled')->default(false);
            $table->boolean('whatsapp_enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('import_export_settings', function (Blueprint $table) {
            $table->id();
            $table->string('module_key')->unique();
            $table->json('allowed_file_types')->nullable();
            $table->unsignedInteger('max_file_size_kb')->default(5120);
            $table->string('sample_format_path')->nullable();
            $table->timestamps();
        });

        Schema::create('rms_settings', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint')->nullable();
            $table->string('auth_mode')->default('none');
            $table->boolean('enabled')->default(false);
            $table->unsignedInteger('timeout_seconds')->default(30);
            $table->unsignedInteger('retry_count')->default(3);
            $table->timestamps();
        });

        Schema::create('dashboard_settings', function (Blueprint $table) {
            $table->id();
            $table->string('default_date_filter')->default('this_month');
            $table->string('default_project_behavior')->default('first_assigned');
            $table->json('visible_widgets')->nullable();
            $table->timestamps();
        });

        Schema::create('settings_change_logs', function (Blueprint $table) {
            $table->id();
            $table->string('setting_group');
            $table->string('setting_key')->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('changed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });

        $this->admin = User::create([
            'firstName' => 'Admin',
            'email' => 'admin@example.test',
            'role' => UserRole::ADMIN->value,
            'password' => bcrypt('password'),
        ]);

        $this->manager = User::create([
            'firstName' => 'Manager',
            'email' => 'manager@example.test',
            'role' => UserRole::PROJECT_MANAGER->value,
            'password' => bcrypt('password'),
        ]);
    }

    public function test_admin_can_view_settings_and_non_admin_cannot_edit(): void
    {
        $this->actingAs($this->admin)->get(route('settings.section', 'organization'))->assertOk();

        $this->actingAs($this->manager)
            ->get(route('settings.section', 'organization'))
            ->assertForbidden();

        $this->actingAs($this->manager)
            ->post(route('settings.organization.update'), ['company_name' => 'Denied CRM'])
            ->assertForbidden();
    }

    public function test_billing_settings_redirects_to_new_settings_billing_section(): void
    {
        $this->actingAs($this->admin)
            ->get(route('billing.settings'))
            ->assertRedirect('/settings/billing');
    }

    public function test_admin_can_view_notification_settings_section(): void
    {
        NotificationSetting::create([
            'event_key' => 'activity_log_created',
            'enabled' => true,
            'recipient_roles' => [UserRole::ADMIN->value],
            'database_enabled' => true,
            'mail_enabled' => false,
            'whatsapp_enabled' => false,
        ]);

        $this->actingAs($this->admin)
            ->get(route('settings.section', 'notifications'))
            ->assertOk()
            ->assertSee('Notification Settings')
            ->assertSee('activity_log_created');
    }

    public function test_organization_settings_save_text_and_upload_assets(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->post(route('settings.organization.update'), [
                'company_name' => 'Independent CRM',
                'legal_name' => 'Independent CRM Pvt Ltd',
                'email' => 'ops@example.test',
                'website' => 'https://example.test',
                'logo' => UploadedFile::fake()->image('logo.png'),
                'favicon' => UploadedFile::fake()->image('favicon.png'),
            ])
            ->assertRedirect(route('settings.section', 'organization'));

        $this->assertDatabaseHas('organization_settings', [
            'company_name' => 'Independent CRM',
            'legal_name' => 'Independent CRM Pvt Ltd',
        ]);
        $this->assertNotNull(OrganizationSetting::first()->logo_path);
        $this->assertDatabaseHas('settings_change_logs', ['setting_group' => 'organization']);
    }

    public function test_vehicle_create_update_delete_and_sub_category_persists(): void
    {
        $this->actingAs($this->admin)
            ->post(route('settings.billing.vehicles.store'), [
                'vehicle_name' => 'Swift',
                'category' => 'Car',
                'sub_category' => 'Hatchback',
                'rate' => 12.5,
            ])
            ->assertRedirect(route('settings.section', ['section' => 'billing', 'tab' => 'vehicles']));

        $vehicle = Vehicle::firstOrFail();
        $this->assertSame('Hatchback', $vehicle->sub_category);

        $this->actingAs($this->admin)
            ->put(route('settings.billing.vehicles.update', $vehicle), [
                'vehicle_name' => 'Swift',
                'category' => 'Car',
                'sub_category' => 'Compact',
                'rate' => 13,
            ])
            ->assertRedirect(route('settings.section', ['section' => 'billing', 'tab' => 'vehicles']));

        $this->assertSame('Compact', $vehicle->fresh()->sub_category);

        $this->actingAs($this->admin)
            ->delete(route('settings.billing.vehicles.destroy', $vehicle))
            ->assertRedirect(route('settings.section', ['section' => 'billing', 'tab' => 'vehicles']));

        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
    }

    public function test_user_category_city_and_permission_settings_persist_with_audit_log(): void
    {
        $vehicle = Vehicle::create(['vehicle_name' => 'Bike', 'category' => 'Bike', 'rate' => 5]);
        $city = City::create(['name' => 'Patna', 'category' => '0']);

        $this->actingAs($this->admin)
            ->post(route('settings.billing.categories.store'), [
                'category_code' => 'M5',
                'name' => 'Manager',
                'vehicle_ids' => [$vehicle->id],
                'city_category' => '1',
                'daily_amount' => 700,
            ])
            ->assertRedirect(route('settings.section', ['section' => 'billing', 'tab' => 'categories']));

        $category = UserCategory::firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('settings.billing.users.category.update', $this->manager), [
                'category_id' => $category->id,
            ])
            ->assertRedirect(route('settings.section', ['section' => 'billing', 'tab' => 'users']));

        $this->assertSame($category->id, (int) $this->manager->fresh()->category);

        $this->actingAs($this->admin)
            ->put(route('settings.billing.cities.update', $city), [
                'city_name' => 'Patna',
                'city_category' => '2',
            ])
            ->assertRedirect(route('settings.section', ['section' => 'billing', 'tab' => 'cities']));

        $this->assertSame('2', (string) $city->fresh()->category);

        $this->actingAs($this->admin)
            ->post(route('settings.permissions.update'), [
                'permissions' => [
                    UserRole::PROJECT_MANAGER->value => [
                        'dashboard' => ['can_view' => '1', 'can_export' => '1'],
                    ],
                    UserRole::ADMIN->value => [
                        'settings' => ['can_view' => '0', 'can_update' => '0'],
                    ],
                ],
            ])
            ->assertRedirect(route('settings.section', 'permissions'));

        $this->assertTrue(ModulePermission::where('role', UserRole::ADMIN->value)->where('module_key', 'settings')->value('can_update'));
        $this->assertTrue(ModulePermission::where('role', UserRole::PROJECT_MANAGER->value)->where('module_key', 'dashboard')->value('can_view'));
        $this->assertDatabaseHas('settings_change_logs', ['setting_group' => 'permissions']);
    }

    public function test_non_admin_cannot_mutate_new_billing_routes(): void
    {
        $this->actingAs($this->manager)
            ->post(route('settings.billing.vehicles.store'), [
                'vehicle_name' => 'Denied',
                'category' => 'Car',
                'rate' => 10,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('vehicles', ['vehicle_name' => 'Denied']);
    }

    public function test_non_admin_can_mutate_legacy_billing_vehicle_route(): void
    {
        $this->actingAs($this->manager)
            ->post(route('billing.addvehicle'), [
                'vehicle_name' => 'LegacyBypass',
                'category' => 'LegacyCar',
                'rate' => 9.5,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('vehicles', [
            'vehicle_name' => 'LegacyBypass',
            'category' => 'LegacyCar',
        ]);
    }

    public function test_billing_vehicle_validation_redirects_to_vehicles_tab(): void
    {
        $this->actingAs($this->admin)
            ->from(route('settings.section', ['section' => 'billing', 'tab' => 'vehicles']))
            ->post(route('settings.billing.vehicles.store'), [
                '_billing_form' => 'add_vehicle',
                'vehicle_name' => 'Bad rate',
                'category' => 'Car',
                'rate' => 'not-a-number',
            ])
            ->assertRedirect(route('settings.section', ['section' => 'billing', 'tab' => 'vehicles']))
            ->assertSessionHasErrors('rate');
    }

    public function test_new_billing_route_allows_duplicate_vehicle_category(): void
    {
        Vehicle::create(['vehicle_name' => 'First', 'category' => 'SharedCat', 'rate' => 5]);

        $this->actingAs($this->admin)
            ->post(route('settings.billing.vehicles.store'), [
                'vehicle_name' => 'Second',
                'category' => 'SharedCat',
                'rate' => 6,
            ])
            ->assertRedirect(route('settings.section', ['section' => 'billing', 'tab' => 'vehicles']));

        $this->assertSame(2, Vehicle::where('category', 'SharedCat')->count());
    }

    public function test_admin_can_view_billing_vehicles_tab_ui(): void
    {
        Vehicle::create(['vehicle_name' => 'Swift', 'category' => 'Car', 'rate' => 12.5]);

        $response = $this->actingAs($this->admin)
            ->get(route('settings.section', ['section' => 'billing', 'tab' => 'vehicles']));

        $response->assertOk()
            ->assertSee('billingVehicleTable', false)
            ->assertSee('addVehicleModal', false)
            ->assertSee('editVehicleModal', false)
            ->assertSee('Conveyance rates by vehicle type', false);

        $html = $response->getContent();
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'data-bs-target="#addVehicleModal"'));
    }

    public function test_admin_billing_cities_tab_renders_all_rows_in_html(): void
    {
        foreach (['Alpha', 'Beta', 'Gamma'] as $name) {
            City::create(['name' => $name, 'category' => '0']);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('settings.section', ['section' => 'billing', 'tab' => 'cities']));

        $response->assertOk()->assertSee('billingCityTable', false);
        $this->assertSame(3, substr_count($response->getContent(), 'btn-edit-city'));
    }

    public function test_billing_validation_response_includes_modal_reopen_markers(): void
    {
        $this->actingAs($this->admin)
            ->from(route('settings.section', ['section' => 'billing', 'tab' => 'vehicles']))
            ->post(route('settings.billing.vehicles.store'), [
                '_billing_form' => 'add_vehicle',
                'vehicle_name' => '',
                'category' => '',
                'rate' => '',
            ])
            ->assertRedirect(route('settings.section', ['section' => 'billing', 'tab' => 'vehicles']))
            ->assertSessionHasErrors(['vehicle_name', 'category', 'rate']);

        $followUp = $this->get(route('settings.section', ['section' => 'billing', 'tab' => 'vehicles']));

        $followUp->assertOk()
            ->assertSee('id="addVehicleModal"', false)
            ->assertSee('openBillingModalFromValidation', false)
            ->assertSee('Please fix the following:', false);
    }

    public function test_category_delete_succeeds_when_users_are_assigned(): void
    {
        $vehicle = Vehicle::create(['vehicle_name' => 'Bike', 'category' => 'Bike', 'rate' => 5]);
        $category = UserCategory::create([
            'category_code' => 'M1',
            'name' => 'Manager',
            'allowed_vehicles' => json_encode([$vehicle->id]),
            'city_category' => '0',
            'dailyamount' => 500,
        ]);
        $this->manager->update(['category' => $category->id]);

        $this->actingAs($this->admin)
            ->delete(route('settings.billing.categories.destroy', $category))
            ->assertRedirect(route('settings.section', ['section' => 'billing', 'tab' => 'categories']));

        $this->assertDatabaseMissing('user_categories', ['id' => $category->id]);
        $this->assertSame($category->id, (int) $this->manager->fresh()->category);
    }

    public function test_vendor_earnings_setting_controls_rate_lookup(): void
    {
        $project = Project::create(['project_name' => 'Streetlight', 'project_type' => 1]);
        VendorEarningSetting::create(['project_id' => null, 'pole_rate' => 500, 'is_active' => true]);

        $this->actingAs($this->admin)
            ->post(route('settings.vendor-earnings.update'), [
                'pole_rate' => 650,
                'is_active' => 1,
            ])
            ->assertRedirect(route('settings.section', 'vendor-earnings'));

        $this->assertSame(650.0, app(VendorEarningsSettingsService::class)->rateForProject($project->id));

        $this->actingAs($this->admin)
            ->post(route('settings.vendor-earnings.projects.update'), [
                'project_id' => $project->id,
                'pole_rate' => 775,
                'is_active' => 1,
            ])
            ->assertRedirect(route('settings.section', 'vendor-earnings'));

        $this->assertSame(775.0, app(VendorEarningsSettingsService::class)->rateForProject($project->id));
    }
}
