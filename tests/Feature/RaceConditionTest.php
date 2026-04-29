<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Pole;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RaceConditionTest extends TestCase
{
    use DatabaseTransactions;

    private User $engineer;
    private StreetlightTask $task;
    private Streetlight $site;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('streelight_poles');
        Schema::dropIfExists('inventory_dispatch');
        Schema::dropIfExists('streetlight_tasks');
        Schema::dropIfExists('streetlights');
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('username')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->integer('role')->default(1);
            $table->string('status')->nullable()->default('active');
            $table->boolean('disableLogin')->default(false);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->integer('project_type')->default(1);
            $table->string('project_name')->nullable();
            $table->timestamps();
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });

        Schema::create('streetlights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('panchayat')->nullable();
            $table->string('district')->nullable();
            $table->integer('number_of_surveyed_poles')->default(0);
            $table->integer('number_of_installed_poles')->default(0);
            $table->integer('total_poles')->default(10);
            $table->timestamps();
        });

        Schema::create('streetlight_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('engineer_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('status')->nullable()->default('Pending');
            $table->timestamps();
        });

        Schema::create('streelight_poles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('complete_pole_number')->nullable();
            $table->string('ward_name')->nullable();
            $table->string('beneficiary')->nullable();
            $table->string('beneficiary_contact')->nullable();
            $table->string('remarks')->nullable();
            $table->boolean('isSurveyDone')->default(false);
            $table->boolean('isInstallationDone')->default(false);
            $table->boolean('isNetworkAvailable')->default(false);
            $table->string('luminary_qr')->nullable();
            $table->string('battery_qr')->nullable();
            $table->string('panel_qr')->nullable();
            $table->string('sim_number')->nullable();
            $table->text('survey_image')->nullable();
            $table->text('submission_image')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
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

        $this->engineer = User::create([
            'name'     => 'Engineer',
            'username' => 'engineer1',
            'email'    => 'engineer@example.com',
            'password' => Hash::make('password'),
            'role'     => UserRole::SITE_ENGINEER->value,
            'status'   => 'active',
        ]);

        $project = Project::create(['project_name' => 'SL Project', 'project_type' => 1]);

        $this->site = Streetlight::create([
            'project_id'              => $project->id,
            'panchayat'               => 'TestPanchayat',
            'district'                => 'TestDistrict',
            'number_of_surveyed_poles'=> 0,
        ]);

        $this->task = StreetlightTask::create([
            'project_id'  => $project->id,
            'site_id'     => $this->site->id,
            'engineer_id' => $this->engineer->id,
            'status'      => 'Pending',
        ]);
    }

    /**
     * Submitting the same pole number twice for the same task must:
     * - create only one Pole row
     * - increment the surveyed_poles counter only once
     */
    public function test_duplicate_pole_submission_creates_only_one_row(): void
    {
        $payload = [
            'task_id'              => $this->task->id,
            'complete_pole_number' => 'POLE-001',
            'ward_name'            => 'Ward 1',
            'isSurveyDone'         => 'true',
            // omit isInstallationDone to avoid triggering the installation code path
        ];

        // First submission
        $response1 = $this->actingAs($this->engineer, 'sanctum')
            ->postJson('/api/streetlight/tasks/update', $payload);

        $response1->assertStatus(200);

        // Second submission with same pole number (simulates duplicate/race)
        $response2 = $this->actingAs($this->engineer, 'sanctum')
            ->postJson('/api/streetlight/tasks/update', $payload);

        $response2->assertStatus(200);

        // Only one pole row should exist
        $this->assertDatabaseCount('streelight_poles', 1);

        // Counter must be incremented exactly once
        $this->assertDatabaseHas('streetlights', [
            'id'                       => $this->site->id,
            'number_of_surveyed_poles' => 1,
        ]);
    }
}
