<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Candidate;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NplusOneTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('user_category');
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('candidates');
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
            $table->unsignedBigInteger('usercategory_id')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->integer('project_type')->default(0);
            $table->string('project_name')->nullable();
            $table->timestamps();
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('role')->nullable();
            $table->timestamps();
        });

        Schema::create('user_category', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('engineer_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('status')->nullable()->default('Pending');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->default('pending');
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
            $table->string('module')->nullable();
            $table->string('action')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('changes')->nullable();
            $table->timestamps();
        });

        $this->admin = User::create([
            'name'     => 'Admin',
            'username' => 'admin_user',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password'),
            'role'     => UserRole::ADMIN->value,
            'status'   => 'active',
        ]);
    }

    /**
     * Verify that staff index query count does not grow linearly with staff count.
     * Before the fix: 6 queries per staff member (N+1).
     * After the fix: constant number of queries regardless of staff count.
     */
    public function test_staff_index_query_count_is_constant_regardless_of_staff_count(): void
    {
        $project = Project::create(['project_name' => 'P1', 'project_type' => 0]);

        // Create 3 engineers with tasks
        for ($i = 1; $i <= 3; $i++) {
            $engineer = User::create([
                'name'     => "Engineer {$i}",
                'username' => "engineer_{$i}",
                'email'    => "engineer{$i}@example.com",
                'password' => Hash::make('password'),
                'role'     => UserRole::SITE_ENGINEER->value,
                'status'   => 'active',
            ]);
            Task::create(['engineer_id' => $engineer->id, 'project_id' => $project->id, 'status' => 'Pending']);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($this->admin)->get('/staff');
        $queryCount3 = count(DB::getQueryLog());

        // Create 3 more engineers
        for ($i = 4; $i <= 6; $i++) {
            $engineer = User::create([
                'name'     => "Engineer {$i}",
                'username' => "engineer_{$i}",
                'email'    => "engineer{$i}@example.com",
                'password' => Hash::make('password'),
                'role'     => UserRole::SITE_ENGINEER->value,
                'status'   => 'active',
            ]);
            Task::create(['engineer_id' => $engineer->id, 'project_id' => $project->id, 'status' => 'Pending']);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($this->admin)->get('/staff');
        $queryCount6 = count(DB::getQueryLog());

        // With N+1 fixed, query count must not grow by 6× the new staff count (18 extra queries for 6 more engineers)
        // Accept a small variance (auth/session queries). The fix ensures it grows by 0, not by N.
        $this->assertLessThanOrEqual($queryCount3 + 3, $queryCount6,
            "Query count grew from {$queryCount3} to {$queryCount6} — N+1 may still be present");
    }

    /**
     * Verify that candidate emails bulk-update uses a single UPDATE instead of N individual UPDATEs.
     */
    public function test_send_emails_uses_bulk_update(): void
    {
        Mail::fake();

        for ($i = 1; $i <= 5; $i++) {
            Candidate::create([
                'name'   => "Candidate {$i}",
                'email'  => "candidate{$i}@example.com",
                'status' => 'pending',
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($this->admin)->post('/candidates/send-emails');

        $queries = DB::getQueryLog();

        // Count UPDATE queries — should be exactly 1 (bulk), not 5 (per-row)
        $updateQueries = array_filter($queries, fn($q) => stripos($q['query'], 'update') !== false);
        $this->assertCount(1, $updateQueries, 'Expected a single bulk UPDATE, got ' . count($updateQueries));

        // All candidates should be marked emailed
        $this->assertDatabaseMissing('candidates', ['status' => 'pending']);
    }
}
