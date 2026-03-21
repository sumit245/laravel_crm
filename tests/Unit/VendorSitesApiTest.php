<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

/**
 * Unit tests for Vendor Sites API edge cases
 * 
 * Feature: vendor-sites-api-modification
 */
class VendorSitesApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable rate limiting for tests
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        // Create minimal schema required for these tests
        Schema::dropIfExists('streetlight_tasks');
        Schema::dropIfExists('streetlights');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('project_type')->default(0);
            $table->timestamps();
        });

        Schema::create('streetlights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('block')->nullable();
            $table->string('panchayat')->nullable();
            $table->string('ward')->nullable();
            $table->string('district_code')->nullable();
            $table->string('block_code')->nullable();
            $table->string('panchayat_code')->nullable();
            $table->string('mukhiya_contact')->nullable();
            $table->integer('number_of_surveyed_poles')->nullable();
            $table->integer('number_of_installed_poles')->nullable();
            $table->integer('total_poles')->nullable();
            $table->timestamps();
        });

        Schema::create('streetlight_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('engineer_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('status')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('allotted_wards')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Test that null allotted_wards returns null in ward field
     * 
     * Validates: Requirements 2.3
     */
    public function test_null_allotted_wards_returns_null_in_ward_field(): void
    {
        // Create a vendor user
        $vendor = User::create([
            'name' => 'Test Vendor',
            'email' => 'vendor@test.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
        ]);

        // Create a project
        $project = Project::create([
            'name' => 'Test Project',
            'project_type' => 0,
        ]);

        // Create a site with a ward value
        $site = Streetlight::create([
            'project_id' => $project->id,
            'state' => 'Bihar',
            'district' => 'Patna',
            'block' => 'Danapur',
            'panchayat' => 'Test Panchayat',
            'ward' => 'Original Ward Value',  // This should NOT appear in response
            'district_code' => '01',
            'block_code' => '02',
            'panchayat_code' => '03',
            'mukhiya_contact' => '9876543210',
            'number_of_surveyed_poles' => 50,
            'number_of_installed_poles' => 45,
            'total_poles' => 100,
        ]);

        // Create a task with NULL allotted_wards
        $task = StreetlightTask::create([
            'project_id' => $project->id,
            'site_id' => $site->id,
            'vendor_id' => $vendor->id,
            'status' => 'Pending',
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'allotted_wards' => null,  // Explicitly set to null
        ]);

        // Make API request
        $response = $this->getJson("/api/vendor/{$vendor->id}/sites");

        // Assert: Response is successful
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        // Assert: Response contains one site
        $responseData = $response->json();
        $this->assertArrayHasKey('sites', $responseData);
        $this->assertCount(1, $responseData['sites']);

        // Assert: Ward field is null (not the original site ward value)
        $siteData = $responseData['sites'][0];
        $this->assertArrayHasKey('ward', $siteData);
        $this->assertNull(
            $siteData['ward'],
            'Ward field should be null when allotted_wards is null'
        );

        // Assert: Ward field is NOT the original site ward value
        $this->assertNotEquals(
            'Original Ward Value',
            $siteData['ward'],
            'Ward field should not contain the site\'s original ward value when allotted_wards is null'
        );
    }

    /**
     * Test that project_id comes from task record, not site record
     * 
     * Validates: Requirements 1.5
     */
    public function test_project_id_comes_from_task_record(): void
    {
        // Create a vendor user
        $vendor = User::create([
            'name' => 'Test Vendor',
            'email' => 'vendor@test.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
        ]);

        // Create two different projects
        $siteProject = Project::create([
            'name' => 'Site Project',
            'project_type' => 0,
        ]);

        $taskProject = Project::create([
            'name' => 'Task Project',
            'project_type' => 0,
        ]);

        // Create a site associated with siteProject
        $site = Streetlight::create([
            'project_id' => $siteProject->id,  // Site belongs to siteProject
            'state' => 'Bihar',
            'district' => 'Patna',
            'block' => 'Danapur',
            'panchayat' => 'Test Panchayat',
            'ward' => 'Ward 1',
            'district_code' => '01',
            'block_code' => '02',
            'panchayat_code' => '03',
            'mukhiya_contact' => '9876543210',
            'number_of_surveyed_poles' => 50,
            'number_of_installed_poles' => 45,
            'total_poles' => 100,
        ]);

        // Create a task associated with taskProject (different from site's project)
        $task = StreetlightTask::create([
            'project_id' => $taskProject->id,  // Task belongs to taskProject
            'site_id' => $site->id,
            'vendor_id' => $vendor->id,
            'status' => 'Pending',
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'allotted_wards' => 'Ward A, Ward B',
        ]);

        // Make API request
        $response = $this->getJson("/api/vendor/{$vendor->id}/sites");

        // Assert: Response is successful
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        // Assert: Response contains one site
        $responseData = $response->json();
        $this->assertArrayHasKey('sites', $responseData);
        $this->assertCount(1, $responseData['sites']);

        // Assert: project_id in response matches task's project_id, NOT site's project_id
        $siteData = $responseData['sites'][0];
        $this->assertArrayHasKey('project_id', $siteData);
        $this->assertEquals(
            $taskProject->id,
            $siteData['project_id'],
            'Response project_id should match task project_id'
        );

        // Assert: project_id does NOT match site's project_id
        $this->assertNotEquals(
            $siteProject->id,
            $siteData['project_id'],
            'Response project_id should NOT match site project_id when they differ'
        );
    }
}
