<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Inventory;
use App\Models\Project;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaginationTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('inventory');
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
            $table->integer('project_type')->default(0);
            $table->string('project_name')->nullable();
            $table->string('work_order_number')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });

        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('site_name')->nullable();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('productName')->nullable();
            $table->string('brand')->nullable();
            $table->text('description')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('initialQuantity', 10, 2)->nullable();
            $table->decimal('quantityStock', 10, 2)->nullable();
            $table->decimal('rate', 10, 2)->nullable();
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

        $this->admin = User::create([
            'name'     => 'Admin',
            'username' => 'admin_user',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password'),
            'role'     => UserRole::ADMIN->value,
            'status'   => 'active',
        ]);

        $this->project = Project::create([
            'project_name'      => 'Test Project',
            'project_type'      => 0,
            'work_order_number' => 'WO-PAG-001',
        ]);
    }

    // ─── Site pagination ──────────────────────────────────────────────────────

    public function test_sites_index_returns_paginated_structure(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Site::create([
                'project_id' => $this->project->id,
                'site_name'  => "Site {$i}",
                'state'      => 'State',
                'district'   => 'District',
                'location'   => 'Location',
            ]);
        }

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/site');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'current_page',
            'per_page',
            'total',
            'last_page',
        ]);
    }

    public function test_sites_index_respects_per_page_param(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Site::create([
                'project_id' => $this->project->id,
                'site_name'  => "Site {$i}",
                'state'      => 'State',
                'district'   => 'District',
                'location'   => 'Location',
            ]);
        }

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/site?per_page=3');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        $this->assertEquals(3, $response->json('per_page'));
    }

    // ─── Inventory pagination ─────────────────────────────────────────────────

    public function test_inventory_index_returns_paginated_structure(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Inventory::create([
                'productName'  => "Product {$i}",
                'brand'        => 'Brand',
                'unit'         => 'pcs',
                'quantityStock'=> 100,
            ]);
        }

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/inventories');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'current_page',
            'per_page',
            'total',
        ]);
    }

    public function test_inventory_index_respects_per_page_param(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Inventory::create([
                'productName'  => "Product {$i}",
                'brand'        => 'Brand',
                'unit'         => 'pcs',
                'quantityStock'=> 50,
            ]);
        }

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/inventories?per_page=4');

        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data'));
        $this->assertEquals(4, $response->json('per_page'));
    }
}
