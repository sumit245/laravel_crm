<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Pole;
use App\Models\Settings\OrganizationSetting;
use App\Models\Settings\PoleNumberFormat;
use App\Models\Settings\PoleNumberRegenerationBatch;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\User;
use App\Models\UserEventNotification;
use App\Services\Settings\PoleNumberFormatService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PoleNumberFormatSettingsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'user_event_notifications',
            'activity_logs',
            'pole_number_regeneration_batches',
            'streelight_poles',
            'streetlight_tasks',
            'streetlight_site_wards',
            'streetlights',
            'pole_number_formats',
            'organization_settings',
            'projects',
            'role_has_permissions',
            'model_has_roles',
            'model_has_permissions',
            'roles',
            'permissions',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstName')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->timestamps();
        });

        Schema::create('organization_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name')->nullable();
            $table->integer('project_type')->default(1);
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

        Schema::create('pole_number_formats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('ward_type', 20);
            $table->string('name');
            $table->json('tokens');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('streetlights', function (Blueprint $table) {
            $table->id();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('block')->nullable();
            $table->string('panchayat')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->timestamps();
        });

        Schema::create('streetlight_site_wards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('streetlight_id');
            $table->string('ward_type', 20);
            $table->string('ward_number', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('streetlight_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->timestamps();
        });

        Schema::create('streelight_poles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->unsignedBigInteger('streetlight_site_ward_id')->nullable();
            $table->string('ward_type', 20)->nullable();
            $table->string('ward_number', 50)->nullable();
            $table->string('ward_name')->nullable();
            $table->unsignedInteger('pole_sequence')->nullable();
            $table->string('complete_pole_number')->nullable();
            $table->unsignedBigInteger('pole_number_format_id')->nullable();
            $table->timestamps();
        });

        Schema::create('user_event_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->string('module', 64);
            $table->string('action', 64);
            $table->string('title');
            $table->text('message');
            $table->json('payload')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('module', 64);
            $table->string('action', 64);
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('description')->nullable();
            $table->json('changes')->nullable();
            $table->json('extra')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('request_id')->nullable();
            $table->string('batch_id')->nullable();
            $table->timestamps();
        });

        Schema::create('pole_number_regeneration_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pole_number_format_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('ward_type', 20)->nullable();
            $table->string('status')->default('pending');
            $table->unsignedInteger('affected_count')->default(0);
            $table->unsignedInteger('processed_count')->default(0);
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        $this->admin = User::create([
            'firstName' => 'Admin',
            'email' => 'admin-pole-format@test.local',
            'role' => UserRole::ADMIN->value,
            'password' => bcrypt('password'),
        ]);

        OrganizationSetting::create(['company_name' => 'Test CRM']);
    }

    public function test_admin_can_view_pole_number_format_settings(): void
    {
        PoleNumberFormat::create([
            'project_id' => null,
            'ward_type' => 'normal',
            'name' => 'Global Normal',
            'is_active' => true,
            'tokens' => app(PoleNumberFormatService::class)->normalizeTokens(
                PoleNumberFormatService::defaultTokenFormState()
            ),
        ]);

        $this->actingAs($this->admin)
            ->get(route('settings.section', 'pole-number-format'))
            ->assertOk()
            ->assertSee('Pole Number Format')
            ->assertSee('Max chars')
            ->assertDontSee('id="poleFormatApplyForm"')
            ->assertSee('Global Normal');
    }

    public function test_admin_can_save_and_update_pole_number_format(): void
    {
        $format = PoleNumberFormat::create([
            'project_id' => null,
            'ward_type' => 'gp',
            'name' => 'Original GP',
            'is_active' => true,
            'tokens' => app(PoleNumberFormatService::class)->normalizeTokens(
                PoleNumberFormatService::defaultTokenFormState()
            ),
        ]);

        $this->actingAs($this->admin)
            ->post(route('settings.pole-number-format.update'), [
                'format_id' => $format->id,
                'project_id' => '',
                'ward_type' => 'gp',
                'name' => 'Updated GP Format',
                'is_active' => 1,
                'tokens' => [
                    'prefix' => ['enabled' => 1, 'value' => 'SUG', 'length' => 3, 'pad' => 0, 'separator_after' => '/'],
                ],
            ])
            ->assertRedirect(route('settings.section', 'pole-number-format'));

        $format->refresh();
        $this->assertSame('Updated GP Format', $format->name);
        $this->assertTrue($format->is_active);
    }

    public function test_preview_shows_sample_count_message(): void
    {
        $format = PoleNumberFormat::create([
            'project_id' => null,
            'ward_type' => 'normal',
            'name' => 'Preview Format',
            'is_active' => true,
            'tokens' => app(PoleNumberFormatService::class)->normalizeTokens(
                PoleNumberFormatService::defaultTokenFormState()
            ),
        ]);

        $this->actingAs($this->admin)
            ->post(route('settings.pole-number-format.preview', $format))
            ->assertRedirect(route('settings.section', 'pole-number-format'));

        $this->actingAs($this->admin)
            ->get(route('settings.section', 'pole-number-format'))
            ->assertOk()
            ->assertSee('Preview: Preview Format')
            ->assertSee('pole(s) match this format')
            ->assertSee('Apply update');
    }

    public function test_apply_regenerates_poles_sync_and_notifies(): void
    {
        $site = Streetlight::create([
            'state' => 'BR',
            'district' => 'Madhubani',
            'block' => 'Muraul',
            'panchayat' => 'Digghi',
            'project_id' => null,
        ]);

        $task = StreetlightTask::create([
            'project_id' => null,
            'site_id' => $site->id,
        ]);

        $pole = Pole::create([
            'task_id' => $task->id,
            'ward_type' => 'normal',
            'ward_name' => 'WARD10',
            'pole_sequence' => 10,
            'complete_pole_number' => 'MAD/MUR/DIG/WARD10/10',
        ]);

        $format = PoleNumberFormat::create([
            'project_id' => null,
            'ward_type' => 'normal',
            'name' => 'Apply Format',
            'is_active' => true,
            'tokens' => app(PoleNumberFormatService::class)->normalizeTokens(
                PoleNumberFormatService::defaultTokenFormState()
            ),
        ]);

        $this->actingAs($this->admin)
            ->post(route('settings.pole-number-format.apply', $format))
            ->assertRedirect(route('settings.section', 'pole-number-format'))
            ->assertSessionHas('success');

        $batch = PoleNumberRegenerationBatch::query()->latest('id')->first();
        $this->assertNotNull($batch);
        $this->assertSame('completed', $batch->status);
        $this->assertSame(1, $batch->processed_count);

        $pole->refresh();
        $this->assertSame($format->id, $pole->pole_number_format_id);
        $this->assertNotSame('MAD/MUR/DIG/WARD10/10', $pole->complete_pole_number);
        $this->assertStringStartsWith('SUG', $pole->complete_pole_number);

        $this->assertDatabaseHas('user_event_notifications', [
            'user_id' => $this->admin->id,
            'module' => 'settings',
            'action' => 'pole_numbers_regenerated',
        ]);

        $notification = UserEventNotification::query()
            ->where('user_id', $this->admin->id)
            ->where('action', 'pole_numbers_regenerated')
            ->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString('Apply Format', $notification->message);
    }
}
