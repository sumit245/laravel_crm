<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

/**
 * Property-based tests for Vendor Sites API
 * 
 * Feature: vendor-sites-api-modification
 */
class VendorSitesApiPropertyTest extends TestCase
{
    use TestTrait, DatabaseTransactions;

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
     * Property 1: No Deduplication of Tasks
     * 
     * **Validates: Requirements 1.3, 1.4**
     * 
     * For any vendor with multiple tasks referencing the same panchayat (same site_id),
     * the API response should contain separate entries for each task, with the number of
     * returned sites equal to the number of tasks assigned to that vendor.
     */
    public function test_property_no_deduplication_of_tasks(): void
    {
        $this->minimumEvaluationRatio(0.5);
        
        $this->forAll(
            Generator\choose(2, 10),  // Number of tasks (at least 2 to test deduplication)
            Generator\choose(1, 3)    // Number of unique sites (fewer than tasks to force duplicates)
        )
        ->then(function ($taskCount, $uniqueSiteCount) {
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

            // Create unique sites (fewer than tasks to ensure duplicates)
            $sites = [];
            for ($i = 0; $i < $uniqueSiteCount; $i++) {
                $sites[] = Streetlight::create([
                    'project_id' => $project->id,
                    'state' => 'Bihar',
                    'district' => 'District ' . $i,
                    'block' => 'Block ' . $i,
                    'panchayat' => 'Panchayat ' . $i,
                    'ward' => 'Ward ' . $i,
                    'district_code' => '0' . $i,
                    'block_code' => '0' . $i,
                    'panchayat_code' => '0' . $i,
                    'mukhiya_contact' => '9876543210',
                    'number_of_surveyed_poles' => 50,
                    'number_of_installed_poles' => 45,
                    'total_poles' => 100,
                ]);
            }

            // Create tasks, some sharing the same site_id
            $createdTasks = [];
            for ($i = 0; $i < $taskCount; $i++) {
                // Cycle through sites to create duplicates
                $site = $sites[$i % $uniqueSiteCount];
                
                $task = StreetlightTask::create([
                    'project_id' => $project->id,
                    'site_id' => $site->id,
                    'vendor_id' => $vendor->id,
                    'status' => 'Pending',
                    'start_date' => now(),
                    'end_date' => now()->addDays(30),
                    'allotted_wards' => 'Ward ' . $i,
                ]);
                
                $createdTasks[] = $task;
            }

            // Make API request
            $response = $this->getJson("/api/vendor/{$vendor->id}/sites");

            // Assert: Response is successful
            $response->assertStatus(200);
            $response->assertJson(['status' => 'success']);

            // Assert: Response count equals task count, not unique site count
            $responseData = $response->json();
            $this->assertArrayHasKey('sites', $responseData);
            $sitesInResponse = $responseData['sites'];
            
            $this->assertCount(
                $taskCount,
                $sitesInResponse,
                "Response should contain {$taskCount} sites (one per task), not {$uniqueSiteCount} (unique sites)"
            );

            // Assert: Verify that duplicate site_ids exist in the response
            if ($taskCount > $uniqueSiteCount) {
                $siteIds = array_column($sitesInResponse, 'id');
                $uniqueSiteIds = array_unique($siteIds);
                
                $this->assertLessThan(
                    count($siteIds),
                    count($uniqueSiteIds),
                    "Response should contain duplicate site IDs when multiple tasks reference the same site"
                );
            }
        });
    }

    /**
     * Property 2: Project ID Inclusion
     * 
     * **Validates: Requirements 1.5**
     * 
     * For any task returned in the response, the site object should contain a
     * project_id field that matches the project_id from the corresponding
     * streetlight_task record.
     */
    public function test_property_project_id_inclusion(): void
    {
        $this->minimumEvaluationRatio(0.5);
        
        $this->forAll(
            Generator\choose(1, 10)  // Number of tasks
        )
        ->withMaxSize(20)
        ->then(function ($taskCount) {
            // Create a vendor user
            $vendor = User::create([
                'name' => 'Test Vendor',
                'email' => 'vendor@test.com',
                'password' => bcrypt('password'),
                'role' => 'vendor',
            ]);

            // Create multiple projects to test various project_ids
            $projects = [];
            for ($i = 0; $i < min($taskCount, 5); $i++) {
                $projects[] = Project::create([
                    'name' => 'Test Project ' . $i,
                    'project_type' => 0,
                ]);
            }

            // Create tasks with various project_ids
            $tasksData = [];
            for ($i = 0; $i < $taskCount; $i++) {
                // Cycle through projects to create variety
                $project = $projects[$i % count($projects)];

                // Create a site for each task
                $site = Streetlight::create([
                    'project_id' => $project->id,
                    'state' => 'Bihar',
                    'district' => 'District ' . $i,
                    'block' => 'Block ' . $i,
                    'panchayat' => 'Panchayat ' . $i,
                    'ward' => 'Ward ' . $i,
                    'district_code' => '0' . $i,
                    'block_code' => '0' . $i,
                    'panchayat_code' => '0' . $i,
                    'mukhiya_contact' => '9876543210',
                    'number_of_surveyed_poles' => 50,
                    'number_of_installed_poles' => 45,
                    'total_poles' => 100,
                ]);

                // Create task with specific project_id
                $task = StreetlightTask::create([
                    'project_id' => $project->id,
                    'site_id' => $site->id,
                    'vendor_id' => $vendor->id,
                    'status' => 'Pending',
                    'start_date' => now(),
                    'end_date' => now()->addDays(30),
                    'allotted_wards' => 'Ward ' . $i,
                ]);

                $tasksData[] = [
                    'task_id' => $task->id,
                    'site_id' => $site->id,
                    'project_id' => $project->id,
                ];
            }

            // Make API request
            $response = $this->getJson("/api/vendor/{$vendor->id}/sites");

            // Assert: Response is successful
            $response->assertStatus(200);
            $response->assertJson(['status' => 'success']);

            // Assert: Each site's project_id field matches the task's project_id
            $responseData = $response->json();
            $this->assertArrayHasKey('sites', $responseData);
            $sitesInResponse = $responseData['sites'];

            $this->assertCount(
                $taskCount,
                $sitesInResponse,
                "Response should contain {$taskCount} sites"
            );

            // Verify each site's project_id field matches the corresponding task's project_id
            foreach ($sitesInResponse as $index => $siteData) {
                $this->assertArrayHasKey('project_id', $siteData, "Site at index {$index} should have 'project_id' field");
                
                // Find the corresponding task data by site_id
                $matchingTask = null;
                foreach ($tasksData as $taskData) {
                    if ($taskData['site_id'] == $siteData['id']) {
                        $matchingTask = $taskData;
                        break;
                    }
                }

                $this->assertNotNull($matchingTask, "Could not find matching task for site ID {$siteData['id']}");

                // Assert project_id matches the task's project_id
                $this->assertEquals(
                    $matchingTask['project_id'],
                    $siteData['project_id'],
                    "Site project_id should match task's project_id. Expected: {$matchingTask['project_id']}, Got: {$siteData['project_id']}"
                );
            }
        });
    }

    /**
     * Property 3: Allotted Wards Mapping
     * 
     * **Validates: Requirements 2.2, 2.4**
     * 
     * For any task with a non-null allotted_wards value, the response's ward field
     * should exactly match the allotted_wards value from the task, preserving the
     * original format including commas, spaces, and special characters.
     */
    public function test_property_allotted_wards_mapping(): void
    {
        $this->minimumEvaluationRatio(0.5);
        
        $this->forAll(
            Generator\choose(1, 5)  // Number of tasks
        )
        ->withMaxSize(20)
        ->then(function ($taskCount) {
            // Define various allotted_wards test cases
            $allottedWardsTestCases = [
                'Ward 1',
                'Ward A',
                'Ward 1, Ward 2',
                'Ward A, Ward B, Ward C',
                'Ward 1,  Ward 2',
                '  Ward A  ,  Ward B  ',
                'Ward-1, Ward-2',
                'Ward (North), Ward (South)',
                'Ward #1, Ward #2, Ward #3',
                '1, 2, 3',
                'North Ward, South Ward, East Ward',
                'वार्ड 1, वार्ड 2',
                'Ward-A/B, Ward-C/D',
                '',
                ' ',
                'W1, W2, W3, W4, W5, W6, W7, W8, W9, W10',
            ];

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

            // Create tasks with various allotted_wards values
            $tasksData = [];
            for ($i = 0; $i < $taskCount; $i++) {
                // Create a site for each task
                $site = Streetlight::create([
                    'project_id' => $project->id,
                    'state' => 'Bihar',
                    'district' => 'District ' . $i,
                    'block' => 'Block ' . $i,
                    'panchayat' => 'Panchayat ' . $i,
                    'ward' => 'Original Ward ' . $i,  // This should be replaced in response
                    'district_code' => '0' . $i,
                    'block_code' => '0' . $i,
                    'panchayat_code' => '0' . $i,
                    'mukhiya_contact' => '9876543210',
                    'number_of_surveyed_poles' => 50,
                    'number_of_installed_poles' => 45,
                    'total_poles' => 100,
                ]);

                // Get allotted_wards value for this task (cycle through test cases)
                $allottedWards = $allottedWardsTestCases[$i % count($allottedWardsTestCases)];

                // Create task with specific allotted_wards
                $task = StreetlightTask::create([
                    'project_id' => $project->id,
                    'site_id' => $site->id,
                    'vendor_id' => $vendor->id,
                    'status' => 'Pending',
                    'start_date' => now(),
                    'end_date' => now()->addDays(30),
                    'allotted_wards' => $allottedWards,
                ]);

                $tasksData[] = [
                    'task_id' => $task->id,
                    'site_id' => $site->id,
                    'allotted_wards' => $allottedWards,
                ];
            }

            // Make API request
            $response = $this->getJson("/api/vendor/{$vendor->id}/sites");

            // Assert: Response is successful
            $response->assertStatus(200);
            $response->assertJson(['status' => 'success']);

            // Assert: Each site's ward field matches the task's allotted_wards exactly
            $responseData = $response->json();
            $this->assertArrayHasKey('sites', $responseData);
            $sitesInResponse = $responseData['sites'];

            $this->assertCount(
                $taskCount,
                $sitesInResponse,
                "Response should contain {$taskCount} sites"
            );

            // Verify each site's ward field matches the corresponding task's allotted_wards
            foreach ($sitesInResponse as $index => $siteData) {
                $this->assertArrayHasKey('ward', $siteData, "Site at index {$index} should have 'ward' field");
                
                // Find the corresponding task data by site_id
                $matchingTask = null;
                foreach ($tasksData as $taskData) {
                    if ($taskData['site_id'] == $siteData['id']) {
                        $matchingTask = $taskData;
                        break;
                    }
                }

                $this->assertNotNull($matchingTask, "Could not find matching task for site ID {$siteData['id']}");

                // Assert exact match of allotted_wards to ward field
                $this->assertSame(
                    $matchingTask['allotted_wards'],
                    $siteData['ward'],
                    "Ward field should exactly match allotted_wards value. Expected: '{$matchingTask['allotted_wards']}', Got: '{$siteData['ward']}'"
                );

                // Verify it's NOT the original site ward value
                $this->assertNotEquals(
                    'Original Ward ' . $index,
                    $siteData['ward'],
                    "Ward field should be from task's allotted_wards, not from site's ward field"
                );
            }
        });
    }
}
