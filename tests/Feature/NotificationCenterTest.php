<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\City;
use App\Models\Project;
use App\Models\User;
use App\Services\Logging\ActivityLogger;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $projectManager;
    private User $coordinator;
    private User $vendor;
    private Project $project;
    private City $district;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('user_event_notifications');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->integer('role')->default(1);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name')->nullable();
            $table->integer('project_type')->default(1);
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('state_id')->nullable();
            $table->string('tier')->nullable();
            $table->string('category')->nullable();
            $table->unsignedBigInteger('user_category_id')->nullable();
            $table->decimal('room_min_price', 10, 2)->nullable();
            $table->decimal('room_max_price', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
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
            $table->string('description', 255)->nullable();
            $table->json('changes')->nullable();
            $table->json('extra')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('request_id', 100)->nullable();
            $table->string('batch_id', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('user_event_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->string('module', 50);
            $table->string('action', 80);
            $table->string('title');
            $table->text('message');
            $table->json('payload')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        $this->admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => UserRole::ADMIN->value,
        ]);

        $this->projectManager = User::query()->create([
            'name' => 'PM User',
            'email' => 'pm@example.com',
            'role' => UserRole::PROJECT_MANAGER->value,
        ]);

        $this->coordinator = User::query()->create([
            'name' => 'Coordinator User',
            'email' => 'coordinator@example.com',
            'role' => UserRole::COORDINATOR->value,
        ]);

        $this->vendor = User::query()->create([
            'name' => 'Vendor User',
            'email' => 'vendor@example.com',
            'role' => UserRole::VENDOR->value,
        ]);

        $this->project = Project::query()->create([
            'project_name' => 'Darbhanga Project',
            'project_type' => 1,
        ]);

        $this->district = City::query()->create([
            'name' => 'Darbhanga',
        ]);

        \DB::table('project_user')->insert([
            [
                'project_id' => $this->project->id,
                'user_id' => $this->projectManager->id,
                'role' => (string) UserRole::PROJECT_MANAGER->value,
                'district_id' => $this->district->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => $this->project->id,
                'user_id' => $this->coordinator->id,
                'role' => (string) UserRole::COORDINATOR->value,
                'district_id' => $this->district->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function test_activity_log_creates_notifications_for_admin_pm_and_coordinator_only(): void
    {
        $logger = app(ActivityLogger::class);
        $logger->log('pole', 'installed', null, [
            'project_id' => $this->project->id,
            'district_id' => $this->district->id,
            'description' => 'A pole is installed in Darbhanga.',
        ]);

        $this->assertDatabaseHas('user_event_notifications', [
            'user_id' => $this->admin->id,
            'module' => 'pole',
            'action' => 'installed',
        ]);

        $this->assertDatabaseHas('user_event_notifications', [
            'user_id' => $this->projectManager->id,
            'module' => 'pole',
            'action' => 'installed',
        ]);

        $this->assertDatabaseHas('user_event_notifications', [
            'user_id' => $this->coordinator->id,
            'module' => 'pole',
            'action' => 'installed',
        ]);

        $this->assertDatabaseMissing('user_event_notifications', [
            'user_id' => $this->vendor->id,
            'module' => 'pole',
            'action' => 'installed',
        ]);
    }

    public function test_notification_summary_and_mark_read_endpoints_work(): void
    {
        $logger = app(ActivityLogger::class);
        $logger->log('inventory', 'dispatched', null, [
            'project_id' => $this->project->id,
            'district_id' => $this->district->id,
            'description' => 'Materials dispatched.',
        ]);

        $summaryResponse = $this->actingAs($this->projectManager)
            ->getJson(route('notifications.summary'));

        $summaryResponse->assertOk()
            ->assertJsonStructure([
                'unread_count',
                'notifications' => [
                    '*' => ['id', 'title', 'message', 'module', 'action', 'is_read'],
                ],
            ]);

        $notificationId = $summaryResponse->json('notifications.0.id');
        $this->assertNotNull($notificationId);

        $markReadResponse = $this->actingAs($this->projectManager)
            ->postJson(route('notifications.read', ['notification' => $notificationId]));

        $markReadResponse->assertOk();

        $this->assertDatabaseHas('user_event_notifications', [
            'id' => $notificationId,
            'user_id' => $this->projectManager->id,
            'is_read' => 1,
        ]);

        $markAllResponse = $this->actingAs($this->projectManager)
            ->postJson(route('notifications.readAll'));

        $markAllResponse->assertOk();
    }

    public function test_smoke_events_for_installation_inventory_and_entity_additions_notify_expected_roles(): void
    {
        $logger = app(ActivityLogger::class);

        $events = [
            ['module' => 'pole', 'action' => 'deleted', 'description' => 'Smoke: installation deleted'],
            ['module' => 'inventory', 'action' => 'deleted', 'description' => 'Smoke: inventory deleted'],
            ['module' => 'user', 'action' => 'created', 'description' => 'Smoke: user added'],
            ['module' => 'site', 'action' => 'created', 'description' => 'Smoke: site added'],
            ['module' => 'task', 'action' => 'created', 'description' => 'Smoke: target added'],
        ];

        foreach ($events as $event) {
            $logger->log($event['module'], $event['action'], null, [
                'project_id' => $this->project->id,
                'district_id' => $this->district->id,
                'description' => $event['description'],
            ]);
        }

        $this->assertDatabaseCount('user_event_notifications', 15); // 5 events * 3 recipients

        $this->assertDatabaseMissing('user_event_notifications', [
            'user_id' => $this->vendor->id,
        ]);
    }
}
