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

class NullSafetyTest extends TestCase
{
    use DatabaseTransactions;

    private User $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('streelight_poles');
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
            $table->string('ward')->nullable();
            $table->string('block')->nullable();
            $table->string('state')->nullable();
            $table->integer('number_of_surveyed_poles')->default(0);
            $table->integer('number_of_installed_poles')->default(0);
            $table->timestamps();
        });

        Schema::create('streetlight_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable(); // nullable — no site
            $table->unsignedBigInteger('engineer_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('streelight_poles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('complete_pole_number')->nullable();
            $table->boolean('isSurveyDone')->default(false);
            $table->boolean('isInstallationDone')->default(false);
            $table->string('beneficiary')->nullable();
            $table->string('beneficiary_contact')->nullable();
            $table->string('remarks')->nullable();
            $table->text('survey_image')->nullable();
            $table->text('submission_image')->nullable();
            $table->string('status')->nullable();
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

        $this->vendor = User::create([
            'name'     => 'Vendor',
            'username' => 'vendor1',
            'email'    => 'vendor@example.com',
            'password' => Hash::make('password'),
            'role'     => UserRole::VENDOR->value,
            'status'   => 'active',
        ]);
    }

    /**
     * A pole whose task has no site (site_id = null) must not cause a fatal error.
     * The null-safe operators (?->) ensure panchayat/district/etc. return null gracefully.
     */
    public function test_vendor_poles_handles_null_site_gracefully(): void
    {
        $project = Project::create(['project_name' => 'SL Project', 'project_type' => 1]);

        // Task with no site
        $task = StreetlightTask::create([
            'project_id' => $project->id,
            'site_id'    => null, // no site!
            'vendor_id'  => $this->vendor->id,
            'status'     => 'Pending',
        ]);

        Pole::create([
            'task_id'              => $task->id,
            'vendor_id'            => $this->vendor->id,
            'complete_pole_number' => 'POLE-NULL-SITE',
            'isSurveyDone'         => true,
            'isInstallationDone'   => false,
        ]);

        $response = $this->actingAs($this->vendor, 'sanctum')
            ->getJson("/api/installed-poles/vendor/{$this->vendor->id}");

        $response->assertStatus(200);

        // All location fields should be null, not crash
        $pole = collect($response->json('surveyed_poles'))->first();
        $this->assertNull($pole['panchayat'] ?? null);
        $this->assertNull($pole['district'] ?? null);
        $this->assertNull($pole['ward'] ?? null);
    }
}
